<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
include_once("includes/configuration.php");

// Authorize before processing settings or uploads (not only when rendering header).
if (empty($_SESSION['admin_token'])) {
    header('Location: index.php');
    exit;
}
$auth = $conn->prepare('SELECT id FROM admin_user WHERE token = ? LIMIT 1');
$auth->bind_param('s', $_SESSION['admin_token']);
$auth->execute();
$authorized = $auth->get_result()->fetch_assoc();
$auth->close();
if (!$authorized) {
    header('Location: index.php');
    exit;
}
if (empty($_SESSION['settings_csrf'])) $_SESSION['settings_csrf'] = bin2hex(random_bytes(32));

$conn->query("CREATE TABLE IF NOT EXISTS `site_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL UNIQUE,
  `setting_value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

$msg = '';
$msg_type = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    $uploaded_paths = [];
    try {
        if (!is_string($_POST['csrf_token'] ?? null) || !hash_equals($_SESSION['settings_csrf'], $_POST['csrf_token'])) {
            throw new RuntimeException('সেশন যাচাই ব্যর্থ। পেজ রিফ্রেশ করে আবার চেষ্টা করুন।');
        }
        $allowed_keys = array_merge(array_keys(soko_brand_fields()), ['whatsapp_group','telegram_link','support_whatsapp_num','bkash_number','nagad_number','rocket_number','upay_number']);
        $values = [];
        if (!is_array($_POST['settings'] ?? null)) throw new RuntimeException('সেটিংস সঠিক নয়।');
        foreach ($_POST['settings'] as $key => $val) {
            if (!in_array($key, $allowed_keys, true) || !is_string($val)) throw new RuntimeException('সেটিংস সঠিক নয়।');
            $val = trim($val);
            if (strlen($val) > 10000) throw new RuntimeException('সেটিংসের তথ্য অতিরিক্ত দীর্ঘ।');
            if ($key === 'site_name' && ($val === '' || strlen($val) > 200)) throw new RuntimeException('সাইটের নাম দিন (সর্বোচ্চ ২০০ বাইট)।');
            if ($key === 'support_email' && $val !== '' && !filter_var($val, FILTER_VALIDATE_EMAIL)) throw new RuntimeException('সঠিক সাপোর্ট ইমেইল দিন।');
            if (in_array($key, ['site_logo','nav_logo','site_favicon','whatsapp_group','telegram_link'], true) && $val !== '' && soko_safe_url($val, '') === '') throw new RuntimeException('লোগো বা লিংক: HTTPS URL অথবা assets/uploads-এর ফাইল পাথ দিন।');
            $values[$key] = $val;
        }
        foreach (['site_logo','nav_logo','site_favicon'] as $key) {
            $file = $_FILES[$key . '_upload'] ?? null;
            if (!$file || $file['error'] === UPLOAD_ERR_NO_FILE) continue;
            if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > 2 * 1024 * 1024) throw new RuntimeException('প্রতিটি লোগো সর্বোচ্চ ২ MB হতে হবে।');
            $image = @getimagesize($file['tmp_name']);
            $types = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','image/gif'=>'gif'];
            if (!$image || !isset($types[$image['mime']]) || $image[0] > 4096 || $image[1] > 4096) throw new RuntimeException('সঠিক JPG, PNG, WebP বা GIF ছবি দিন (সর্বোচ্চ 4096×4096)।');
            $directory = dirname(__DIR__) . '/uploads/branding';
            if (!is_dir($directory) && !mkdir($directory, 0755, true)) throw new RuntimeException('লোগো ফোল্ডার তৈরি করা যায়নি।');
            $filename = bin2hex(random_bytes(16)) . '.' . $types[$image['mime']];
            $destination = $directory . '/' . $filename;
            if (!move_uploaded_file($file['tmp_name'], $destination)) throw new RuntimeException('লোগো আপলোড করা যায়নি।');
            $uploaded_paths[] = $destination;
            $values[$key] = '/uploads/branding/' . $filename;
        }
        $conn->begin_transaction();
        $stmt = $conn->prepare('INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
        if (!$stmt) throw new RuntimeException('সেটিংস সংরক্ষণ করা যায়নি।');
        foreach ($values as $key => $val) {
            $stmt->bind_param('ss', $key, $val);
            if (!$stmt->execute()) throw new RuntimeException('সেটিংস সংরক্ষণ করা যায়নি।');
        }
        $stmt->close();
        $has_pay_table = $conn->query("SHOW TABLES LIKE 'payment_settings'");
        if ($has_pay_table && $has_pay_table->num_rows > 0) {
            $p_stmt = $conn->prepare("INSERT INTO payment_settings (method_key, method_name, number, type) VALUES (?, ?, ?, 'Personal') ON DUPLICATE KEY UPDATE number = VALUES(number)");
            if (!$p_stmt) throw new RuntimeException('পেমেন্ট সেটিংস সংরক্ষণ করা যায়নি।');
            foreach (['bkash'=>'bKash','nagad'=>'Nagad','rocket'=>'Rocket','upay'=>'Upay'] as $method => $label) {
                if (!isset($values[$method . '_number'])) continue;
                $number = $values[$method . '_number'];
                $p_stmt->bind_param('sss', $method, $label, $number);
                if (!$p_stmt->execute()) throw new RuntimeException('পেমেন্ট সেটিংস সংরক্ষণ করা যায়নি।');
            }
            $p_stmt->close();
        }
        $conn->commit();
        // Redirect clears the per-request branding cache and prevents repeat uploads.
        header('Location: settings.php?saved=1');
        exit;
    } catch (Throwable $error) {
        $conn->rollback();
        foreach ($uploaded_paths as $path) @unlink($path);
        $msg = $error instanceof RuntimeException ? $error->getMessage() : 'সেটিংস সংরক্ষণ করা যায়নি। আবার চেষ্টা করুন।';
        $msg_type = 'error';
    }
}
if (isset($_GET['saved'])) { $msg = 'সকল সেটিংস সফলভাবে সংরক্ষিত হয়েছে!'; $msg_type = 'success'; }
$settings = [];
$res = $conn->query('SELECT setting_key, setting_value FROM site_settings');
if ($res) while ($row = $res->fetch_assoc()) $settings[$row['setting_key']] = $row['setting_value'];
?>
<?php include_once("header.php"); ?>

<div class="space-y-6 max-w-5xl mx-auto">

    <div class="bg-white p-5 md:p-6 rounded-3xl border border-slate-200/90 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-700 flex items-center justify-center text-xl font-bold">
                <i class="fa-solid fa-sliders"></i>
            </div>
            <div>
                <h3 class="text-lg md:text-xl font-extrabold text-slate-800">ওয়েবসাইট জেনারেল সেটিংস</h3>
                <p class="text-xs text-slate-400 font-medium">হোয়াটসঅ্যাপ, টেলিগ্রাম, হেল্পলাইন ও পেমেন্ট নম্বর নিয়ন্ত্রণ করুন</p>
            </div>
        </div>
        <a href="index.php" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 self-start sm:self-auto">
            <i class="fa-solid fa-arrow-left"></i> ড্যাশবোর্ড
        </a>
    </div>

    <?php if (!empty($msg)): ?>
    <div class="p-4 rounded-2xl flex items-center gap-3 <?php echo ($msg_type === 'success') ? 'bg-emerald-50 border border-emerald-200 text-emerald-700' : 'bg-rose-50 border border-rose-200 text-rose-700'; ?>">
        <i class="fa-solid <?php echo ($msg_type === 'success') ? 'fa-circle-check text-emerald-600' : 'fa-circle-exclamation text-rose-600'; ?> text-lg"></i>
        <span class="text-xs font-bold"><?php echo htmlspecialchars($msg); ?></span>
    </div>
    <?php endif; ?>

    <form method="POST" action="" enctype="multipart/form-data" class="space-y-6">
        <input type="hidden" name="csrf_token" value="<?php echo soko_e($_SESSION['settings_csrf']); ?>">
        <div class="bg-white p-5 md:p-6 rounded-3xl border border-slate-200/90 shadow-sm space-y-5">
            <h4 class="text-sm font-extrabold text-slate-800">সাইটের পরিচয় ও লোগো</h4>
            <p class="text-xs text-slate-500">সব পেজের নাম, হেডার লোগো, favicon ও প্রোফাইলের ডিফল্ট ছবি এখান থেকে নিয়ন্ত্রণ করুন। ছবি আপলোড করলে সেটি URL-এর পরিবর্তে ব্যবহৃত হবে।</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <?php foreach (soko_brand_fields() as $brand_key => $brand_field): if ($brand_key === 'site_notice') continue;
                $is_image = in_array($brand_key, ['site_logo','nav_logo','site_favicon'], true);
                $current_value = $settings[$brand_key] ?? $brand_field[1];
            ?>
                <div class="space-y-2">
                    <label for="brand-<?php echo $brand_key; ?>" class="block text-xs font-bold text-slate-700"><?php echo soko_e($brand_field[0]); ?></label>
                    <input id="brand-<?php echo $brand_key; ?>" type="<?php echo $brand_key === 'support_email' ? 'email' : 'text'; ?>" name="settings[<?php echo $brand_key; ?>]" value="<?php echo soko_e($current_value); ?>" <?php echo $brand_key === 'site_name' ? 'required maxlength="200"' : ''; ?> class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-700 bg-slate-50" placeholder="<?php echo $is_image ? 'https://... অথবা /uploads/...' : ''; ?>">
                    <?php if ($is_image): ?>
                        <img src="<?php echo soko_e(soko_safe_url($current_value, soko_brand('site_logo'))); ?>" alt="বর্তমান লোগো" class="w-16 h-16 object-contain rounded-xl border border-slate-200 bg-white">
                        <input type="file" name="<?php echo $brand_key; ?>_upload" accept="image/png,image/jpeg,image/webp,image/gif" aria-label="<?php echo soko_e($brand_field[0]); ?> আপলোড" class="w-full text-xs text-slate-600">
                        <p class="text-xs text-slate-400">PNG, JPG, WebP, GIF • সর্বোচ্চ ২ MB</p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            </div>
        </div>

        <div class="bg-white p-5 md:p-6 rounded-3xl border border-slate-200/90 shadow-sm space-y-5">
            <div class="flex items-center gap-2 pb-3 border-b border-slate-100">
                <i class="fa-solid fa-share-nodes text-purple-700 text-base"></i>
                <h4 class="text-sm font-extrabold text-slate-800">কমিউনিটি ও সোশ্যাল সাপোর্ট লিংক</h4>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                        <i class="fa-brands fa-whatsapp text-emerald-500 text-sm"></i> WhatsApp গ্রুপ লিংক
                    </label>
                    <input type="text" name="settings[whatsapp_group]" value="<?php echo htmlspecialchars($settings['whatsapp_group'] ?? ''); ?>" placeholder="https://chat.whatsapp.com/..." class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-700 bg-slate-50 outline-none focus:border-purple-600 focus:bg-white transition">
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                        <i class="fa-brands fa-telegram text-sky-500 text-sm"></i> Telegram চ্যানেল/গ্রুপ লিংক
                    </label>
                    <input type="text" name="settings[telegram_link]" value="<?php echo htmlspecialchars($settings['telegram_link'] ?? ''); ?>" placeholder="https://t.me/..." class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-700 bg-slate-50 outline-none focus:border-purple-600 focus:bg-white transition">
                </div>

                <div class="space-y-1.5 md:col-span-2">
                    <label class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                        <i class="fa-solid fa-headset text-purple-600 text-sm"></i> সরাসরি হেল্পলাইন WhatsApp নম্বর
                    </label>
                    <input type="text" name="settings[support_whatsapp_num]" value="<?php echo htmlspecialchars($settings['support_whatsapp_num'] ?? ''); ?>" placeholder="01XXXXXXXXX" class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-700 bg-slate-50 outline-none focus:border-purple-600 focus:bg-white transition">
                </div>
            </div>
        </div>

        <div class="bg-white p-5 md:p-6 rounded-3xl border border-slate-200/90 shadow-sm space-y-5">
            <div class="flex items-center gap-2 pb-3 border-b border-slate-100">
                <i class="fa-solid fa-wallet text-purple-700 text-base"></i>
                <h4 class="text-sm font-extrabold text-slate-800">ম্যানুয়াল রিচার্জ পেমেন্ট নম্বর</h4>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                        <i class="fa-solid fa-mobile-screen-button text-pink-600 text-sm"></i> বিকাশ পার্সোনাল নম্বর
                    </label>
                    <input type="text" name="settings[bkash_number]" value="<?php echo htmlspecialchars($settings['bkash_number'] ?? ''); ?>" placeholder="01XXXXXXXXX" class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-700 bg-slate-50 outline-none focus:border-purple-600 focus:bg-white transition">
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                        <i class="fa-solid fa-mobile-screen-button text-orange-500 text-sm"></i> নগদ পার্সোনাল নম্বর
                    </label>
                    <input type="text" name="settings[nagad_number]" value="<?php echo htmlspecialchars($settings['nagad_number'] ?? ''); ?>" placeholder="01XXXXXXXXX" class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-700 bg-slate-50 outline-none focus:border-purple-600 focus:bg-white transition">
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                        <i class="fa-solid fa-mobile-screen-button text-purple-600 text-sm"></i> রকেট পার্সোনাল নম্বর
                    </label>
                    <input type="text" name="settings[rocket_number]" value="<?php echo htmlspecialchars($settings['rocket_number'] ?? ''); ?>" placeholder="01XXXXXXXXX" class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-700 bg-slate-50 outline-none focus:border-purple-600 focus:bg-white transition">
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                        <i class="fa-solid fa-mobile-screen-button text-amber-500 text-sm"></i> উপায় পার্সোনাল নম্বর
                    </label>
                    <input type="text" name="settings[upay_number]" value="<?php echo htmlspecialchars($settings['upay_number'] ?? ''); ?>" placeholder="01XXXXXXXXX" class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-700 bg-slate-50 outline-none focus:border-purple-600 focus:bg-white transition">
                </div>
            </div>
        </div>

        <div class="bg-white p-5 md:p-6 rounded-3xl border border-slate-200/90 shadow-sm space-y-5">
            <div class="flex items-center gap-2 pb-3 border-b border-slate-100">
                <i class="fa-solid fa-bullhorn text-purple-700 text-base"></i>
                <h4 class="text-sm font-extrabold text-slate-800">সাধারণ সাইট বার্তা / ফ্ল্যাশ নোটিশ</h4>
            </div>

            <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate-700">ব্যবহারকারীদের প্রদর্শনের জন্য সংক্ষিপ্ত নোটিশ</label>
                <textarea name="settings[site_notice]" rows="3" placeholder="যেকোনো নোটিশ লিখুন..." class="w-full border border-slate-200 rounded-xl p-3 text-xs text-slate-700 bg-slate-50 outline-none focus:border-purple-600 focus:bg-white transition"><?php echo htmlspecialchars($settings['site_notice'] ?? ''); ?></textarea>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <button type="submit" name="save_settings" class="bg-gradient-to-r from-purple-700 to-indigo-600 hover:from-purple-800 hover:to-indigo-700 text-white px-6 py-2.5 rounded-xl text-xs font-bold shadow-md hover:shadow-lg transition flex items-center gap-2">
                <i class="fa-solid fa-floppy-disk"></i> পরিবর্তন সংরক্ষণ করুন
            </button>
        </div>

    </form>

</div>

<?php include_once("footer.php"); ?>