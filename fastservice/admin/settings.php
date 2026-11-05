<?php
include_once("includes/configuration.php");

$conn->query("CREATE TABLE IF NOT EXISTS `site_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL UNIQUE,
  `setting_value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

$msg = "";
$msg_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_settings'])) {
    if (isset($_POST['settings']) && is_array($_POST['settings'])) {
        $stmt = $conn->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        foreach ($_POST['settings'] as $key => $val) {
            $key_clean = trim($key);
            $val_clean = trim($val);
            $stmt->bind_param("sss", $key_clean, $val_clean, $val_clean);
            $stmt->execute();
        }
        $stmt->close();
        $msg = "সকল সেটিংস সফলভাবে সংরক্ষিত হয়েছে!";
        $msg_type = "success";
    }
}

$settings = [];
$res = $conn->query("SELECT setting_key, setting_value FROM site_settings");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
}
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

    <form method="POST" action="" class="space-y-6">

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

                <div class="space-y-1.5 md:col-span-2">
                    <label class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                        <i class="fa-solid fa-mobile-screen-button text-purple-600 text-sm"></i> রকেট পার্সোনাল নম্বর (ঐচ্ছিক)
                    </label>
                    <input type="text" name="settings[rocket_number]" value="<?php echo htmlspecialchars($settings['rocket_number'] ?? ''); ?>" placeholder="01XXXXXXXXX" class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-700 bg-slate-50 outline-none focus:border-purple-600 focus:bg-white transition">
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