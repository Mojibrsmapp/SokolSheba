<?php
ob_start();
session_start();
include_once("includes/configuration.php");
include_once("includes/payment_flow.php");

if (!isset($_SESSION['user_token'])) {
    header("Location: index.php");
    exit();
}

$token_user = $_SESSION['user_token'];
$stmt = $conn->prepare("SELECT id, name, email, mobile, balance FROM users WHERE token = ?");
$stmt->bind_param("s", $token_user);
$stmt->execute();
$res = $stmt->get_result();

if ($res && $res->num_rows > 0) {
    $user = $res->fetch_assoc();
    $id = (int)$user['id'];
    $name = $user['name'];
    $email = $user['email'];
    $phone = $user['mobile'];
    $balance = (float)$user['balance'];
} else {
    unset($_SESSION['user_token'], $_SESSION['user_id']);
    header("Location: index.php");
    exit();
}
$stmt->close();

$gateway_numbers = [];
$pay_check = @$conn->query("SHOW TABLES LIKE 'payment_settings'");
if ($pay_check && $pay_check->num_rows > 0) {
    $q_p = @$conn->query("SELECT method_key, number FROM payment_settings");
    if ($q_p) {
        while ($r = $q_p->fetch_assoc()) {
            if (!empty($r['number'])) {
                $gateway_numbers[$r['method_key']] = trim($r['number']);
            }
        }
    }
}

if (empty($gateway_numbers['rocket']) || empty($gateway_numbers['bkash']) || empty($gateway_numbers['nagad'])) {
    $site_set = @$conn->query("SELECT setting_key, setting_value FROM site_settings WHERE setting_key IN ('bkash_number', 'nagad_number', 'rocket_number', 'upay_number')");
    if ($site_set) {
        while ($s = $site_set->fetch_assoc()) {
            $cleaned_key = str_replace('_number', '', $s['setting_key']);
            if (empty($gateway_numbers[$cleaned_key]) && !empty($s['setting_value'])) {
                $gateway_numbers[$cleaned_key] = trim($s['setting_value']);
            }
        }
    }
}

$first_available_method = !empty($gateway_numbers['bkash']) ? 'bkash' : (!empty($gateway_numbers) ? array_key_first($gateway_numbers) : '');

$service_name = isset($_GET['service']) ? trim($_GET['service']) : 'ব্যালেন্স রিচার্জ';
$service_category = isset($_GET['category']) ? trim($_GET['category']) : 'টপ-আপ সেবা';

$order_submitted = false;
$new_dep_id = 0;
$receipt_data = null;
$error_msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_deposit'])) {
    $amount = floatval($_POST['payable_amount'] ?? 0);
    $method = trim($_POST['method_key'] ?? 'bkash');
    $trx_id = trim($_POST['trx_id'] ?? '');

    if ($amount < 10) {
        $error_msg = "সঠিক অ্যামাউন্ট প্রদান করুন।";
    } elseif (empty($trx_id)) {
        $error_msg = "অনুগ্রহ করে ট্রানজেকশন আইডি (TrxID) লিখুন।";
    } else {
        $screenshot_path = null;
        if (isset($_FILES['screenshot']) && $_FILES['screenshot']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['screenshot']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            if (in_array($ext, $allowed)) {
                $up_dir = __DIR__ . '/uploads/deposits/';
                if (!is_dir($up_dir)) { mkdir($up_dir, 0755, true); }
                $fname = 'dep_' . $id . '_' . time() . '.' . $ext;
                if (move_uploaded_file($_FILES['screenshot']['tmp_name'], $up_dir . $fname)) {
                    $screenshot_path = 'uploads/deposits/' . $fname;
                }
            }
        }

        $ins = $conn->prepare("INSERT INTO deposit_requests (user_id, gateway, method, trx_id, amount, screenshot, status, created_at) VALUES (?, 'manual', ?, ?, ?, ?, 'pending', NOW())");
        $ins->bind_param("issds", $id, $method, $trx_id, $amount, $screenshot_path);

        if ($ins->execute()) {
            $new_dep_id = $ins->insert_id;
            $order_submitted = true;
            $order_serial = "SS" . date('Ymd') . rand(100, 999);
            $receipt_data = [
                'order_no'     => $order_serial,
                'date'         => date('d M Y, h:i A'),
                'name'         => $name,
                'phone'        => $phone,
                'email'        => $email,
                'service_name' => $service_name,
                'service_cat'  => $service_category,
                'price'        => $amount,
                'method'       => $method,
                'trx_id'       => $trx_id,
                'status'       => 'যাচাই হচ্ছে'
            ];

            $botToken = "6260586366:AAFlUeC0t6ua9GZDvywnfL5JSBEPCflura8";
            $chatId   = "1875687264";

            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
            $host_domain = $_SERVER['HTTP_HOST'];
            $site_url = $protocol . $host_domain;
            $screenshot_url = $screenshot_path ? ($site_url . '/' . $screenshot_path) : "নেই";

            $m_bangla = strtoupper($method);
            if ($method === 'bkash') $m_bangla = 'বিকাশ';
            elseif ($method === 'nagad') $m_bangla = 'নগদ';
            elseif ($method === 'rocket') $m_bangla = 'রকেট';
            elseif ($method === 'upay') $m_bangla = 'উপায়';

            $tg_msg = "🔔 <b><u>নতুন রিচার্জ রিকোয়েস্ট এসেছে!</u></b>\n\n";
            $tg_msg .= "🌐 <b>ওয়েবসাইট:</b> " . $site_url . "\n";
            $tg_msg .= "🆔 <b>রিকোয়েস্ট আইডি:</b> #" . $new_dep_id . " (" . $order_serial . ")\n";
            $tg_msg .= "👤 <b>গ্রাহকের নাম:</b> " . htmlspecialchars($name) . "\n";
            $tg_msg .= "📧 <b>ইমেইল/ইউজারনেম:</b> " . htmlspecialchars($email) . "\n";
            $tg_msg .= "📞 <b>ফোন নম্বর:</b> " . htmlspecialchars($phone) . "\n";
            $tg_msg .= "💼 <b>বর্তমান ব্যালেন্স:</b> ৳ " . number_format($balance, 2) . "\n";
            $tg_msg .= "━━━━━━━━━━━━━━━━━━━━\n";
            $tg_msg .= "💵 <b>রিচার্জের পরিমাণ:</b> ৳ " . number_format($amount, 2) . "\n";
            $tg_msg .= "💳 <b>পেমেন্ট মেথড:</b> " . $m_bangla . "\n";
            $tg_msg .= "🔢 <b>TrxID:</b> <code>" . htmlspecialchars($trx_id) . "</code>\n";
            $tg_msg .= "🕒 <b>তারিখ ও সময়:</b> " . date('d M Y, h:i A') . "\n";
            $tg_msg .= "📸 <b>স্ক্রিনশট লিংক:</b> " . $screenshot_url . "\n\n";
            $tg_msg .= "⚡ <i>দয়া করে এডমিন প্যানেল থেকে ট্রানজেকশনটি ভেরিফাই করুন।</i>";

            $tg_url = "https://api.telegram.org/bot" . $botToken . "/sendMessage";
            $tg_data = [
                'chat_id'    => $chatId,
                'text'       => $tg_msg,
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => false
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $tg_url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($tg_data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_exec($ch);
            curl_close($ch);

        } else {
            $error_msg = "পেমেন্ট সংরক্ষণ করতে সমস্যা হয়েছে। ট্রানজেকশন আইডিটি ইতিমধ্যে ব্যবহৃত কিনা যাচাই করুন।";
        }
        $ins->close();
    }
}

$history_records = [];
$h_stmt = $conn->prepare("SELECT id, method, trx_id, amount, status, created_at FROM deposit_requests WHERE user_id = ? ORDER BY id DESC LIMIT 15");
if ($h_stmt) {
    $h_stmt->bind_param("i", $id);
    $h_stmt->execute();
    $h_res = $h_stmt->get_result();
    if ($h_res) {
        while ($row = $h_res->fetch_assoc()) {
            $history_records[] = $row;
        }
    }
    $h_stmt->close();
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo $order_submitted ? 'পেমেন্ট রশিদ' : 'পেমেন্ট গেটওয়ে'; ?> - <?php echo soko_e(soko_brand('site_name')); ?></title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/maateen/bangla-fonts/Nikosh/stylesheet.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="shortcut icon" href="<?php echo soko_e(soko_brand('site_favicon')); ?>">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Nikosh', sans-serif !important;
            -webkit-font-smoothing: antialiased;
        }
        .fa, .fas, .far, .fab, .fa-solid, .fa-regular, .fa-brands {
            font-family: "Font Awesome 6 Free", "Font Awesome 6 Brands" !important;
        }
        body {
            background: #eaf6ee;
            color: #1E293B;
            min-height: 100vh;
        }

        .phone-card {
            background: #ffffff;
            border-radius: 26px;
            border: 1px solid #D1E7DD;
            box-shadow: 0 10px 30px -5px rgba(16, 124, 65, 0.08);
            overflow: hidden;
            width: 100%;
        }

        .method-tile {
            position: relative;
            border: 1.6px solid #E5E7EB;
            border-radius: 14px;
            padding: 13px 10px 10px;
            cursor: pointer;
            transition: all 0.18s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 6px;
            min-height: 82px;
            background: #FFFFFF;
        }
        .method-tile .mt-logo {
            height: 34px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .method-tile .mt-logo img {
            max-height: 34px;
            max-width: 92px;
            object-fit: contain;
            display: block;
        }
        .method-tile .mt-name {
            font-size: 11px;
            font-weight: 700;
            color: #64748B;
            letter-spacing: .2px;
        }
        .method-tile.selected .mt-name { color: #07612E; }
        .method-tile .radio-dot {
            position: absolute;
            top: 8px;
            right: 8px;
            margin: 0;
        }
        .method-tile:hover {
            border-color: #107C41;
            background: #F0FDF4;
        }
        .method-tile.selected {
            border-color: #107C41;
            background: #F0FDF4;
        }
        .radio-dot {
            width: 18px;
            height: 18px;
            border-radius: 50%;
            border: 2px solid #CBD5E1;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .method-tile.selected .radio-dot {
            border-color: #107C41;
            background: #107C41;
        }
        .method-tile.selected .radio-dot::after {
            content: '';
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #ffffff;
        }

        .btn-green-submit {
            background: #107C41;
            color: #ffffff;
            font-weight: 700;
            border-radius: 9999px;
            padding: 11px 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: 0.2s;
            width: 100%;
        }
        .btn-green-submit:hover {
            background: #0D6334;
        }

        .custom-scroll::-webkit-scrollbar {
            width: 5px;
        }
        .custom-scroll::-webkit-scrollbar-track {
            background: #F1F5F9;
            border-radius: 10px;
        }
        .custom-scroll::-webkit-scrollbar-thumb {
            background: #CBD5E1;
            border-radius: 10px;
        }
        .custom-scroll::-webkit-scrollbar-thumb:hover {
            background: #94A3B8;
        }

        @media print {
            body { background: #ffffff !important; padding: 0 !important; }
            .no-print { display: none !important; }
            .phone-card { box-shadow: none !important; border: none !important; max-width: 100% !important; border-radius: 0 !important; }
            .grid { display: block !important; }
            .phone-card { page-break-inside: avoid; }
            @page { size: A4; margin: 8mm; }
        }
    </style>
</head>
<body class="p-3 sm:p-6 lg:p-8 flex items-center justify-center">
<?php include __DIR__ . '/includes/site_header.php'; ?>

    <div class="w-full max-w-5xl mx-auto">

        <?php if (!empty($error_msg)): ?>
            <div class="mb-4 p-3 bg-rose-50 border border-rose-200 text-rose-700 text-xs sm:text-sm font-bold rounded-xl flex items-center gap-2">
                <i class="fa-solid fa-circle-exclamation text-base"></i>
                <span><?php echo $error_msg; ?></span>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">

            <div class="lg:col-span-6 w-full">
                <div class="phone-card">

                    <?php if (!$order_submitted): ?>
                    <div id="step1_view">


                        <div class="bg-gradient-to-r from-[#0F5132] via-[#107C41] to-[#15803D] text-white p-4 text-center">
                            <h3 class="text-base font-black tracking-wide">নিরাপদ ও দ্রুত পেমেন্ট গেটওয়ে</h3>
                            <p class="text-[11px] text-green-100 mt-0.5">আপনার পছন্দের মোবাইল ব্যাংকিং নির্বাচন করে পেমেন্ট করুন</p>
                        </div>

                        <div class="p-4 sm:p-5 space-y-4">

                            <div class="grid grid-cols-2 gap-2.5">
                                <?php if (isset($gateway_numbers['bkash'])): ?>
                                <div class="method-tile <?php echo ($first_available_method === 'bkash') ? 'selected' : ''; ?>" onclick="chooseMethod('bkash', 'bKash')">
                                    <?php $rc_logo = function_exists('soko_pay_method_logo') ? soko_pay_method_logo('bkash') : ''; ?>
                                    <div class="radio-dot"></div>
                                    <?php if ($rc_logo !== ''): ?>
                                        <span class="mt-logo"><img src="<?php echo htmlspecialchars($rc_logo); ?>" alt="bKash"></span>
                                    <?php else: ?>
                                        <span class="mt-name" style="font-size:15px;font-weight:800">বিকাশ</span>
                                    <?php endif; ?>
                                    <span class="mt-name">bKash</span>
                                </div>
                                <?php endif; ?>

                                <?php if (isset($gateway_numbers['nagad'])): ?>
                                <div class="method-tile <?php echo ($first_available_method === 'nagad') ? 'selected' : ''; ?>" onclick="chooseMethod('nagad', 'নগদ')">
                                    <?php $rc_logo = function_exists('soko_pay_method_logo') ? soko_pay_method_logo('nagad') : ''; ?>
                                    <div class="radio-dot"></div>
                                    <?php if ($rc_logo !== ''): ?>
                                        <span class="mt-logo"><img src="<?php echo htmlspecialchars($rc_logo); ?>" alt="Nagad"></span>
                                    <?php else: ?>
                                        <span class="mt-name" style="font-size:15px;font-weight:800">নগদ</span>
                                    <?php endif; ?>
                                    <span class="mt-name">Nagad</span>
                                </div>
                                <?php endif; ?>

                                <?php if (isset($gateway_numbers['rocket'])): ?>
                                <div class="method-tile <?php echo ($first_available_method === 'rocket') ? 'selected' : ''; ?>" onclick="chooseMethod('rocket', 'রকেট')">
                                    <?php $rc_logo = function_exists('soko_pay_method_logo') ? soko_pay_method_logo('rocket') : ''; ?>
                                    <div class="radio-dot"></div>
                                    <?php if ($rc_logo !== ''): ?>
                                        <span class="mt-logo"><img src="<?php echo htmlspecialchars($rc_logo); ?>" alt="Rocket"></span>
                                    <?php else: ?>
                                        <span class="mt-name" style="font-size:15px;font-weight:800">রকেট</span>
                                    <?php endif; ?>
                                    <span class="mt-name">Rocket</span>
                                </div>
                                <?php endif; ?>

                                <?php if (isset($gateway_numbers['upay'])): ?>
                                <div class="method-tile <?php echo ($first_available_method === 'upay') ? 'selected' : ''; ?>" onclick="chooseMethod('upay', 'উপায়')">
                                    <?php $rc_logo = function_exists('soko_pay_method_logo') ? soko_pay_method_logo('upay') : ''; ?>
                                    <div class="radio-dot"></div>
                                    <?php if ($rc_logo !== ''): ?>
                                        <span class="mt-logo"><img src="<?php echo htmlspecialchars($rc_logo); ?>" alt="Upay"></span>
                                    <?php else: ?>
                                        <span class="mt-name" style="font-size:15px;font-weight:800">উপায়</span>
                                    <?php endif; ?>
                                    <span class="mt-name">Upay</span>
                                </div>
                                <?php endif; ?>
                            </div>

                            <div class="bg-emerald-50/50 border border-emerald-200 rounded-2xl p-4 space-y-2.5">
                                <div class="flex items-center gap-2 text-green-800 font-extrabold text-xs pb-1 border-b border-emerald-100">
                                    <i class="fa-solid fa-file-invoice"></i>
                                    <span>অর্ডার সারাংশ: <?php echo htmlspecialchars($service_name); ?></span>
                                </div>

                                <div class="space-y-1">
                                    <label class="block text-xs font-bold text-slate-700">
                                        টাকার পরিমাণ লিখুন <span class="text-rose-500">*</span>
                                    </label>
                                    <div class="relative flex items-center">
                                        <span class="absolute left-3 text-slate-400 font-bold text-sm">৳</span>
                                        <input type="number" min="10" step="1" id="input_amount" value="" placeholder="টাকার পরিমাণ দিন (যেমন: ১০০, ২০০)" oninput="updateSummaryAmount()" class="w-full text-left pl-8 pr-3 py-2 font-bold text-sm bg-white border border-slate-300 rounded-xl focus:border-green-600 focus:ring-1 focus:ring-green-600 outline-none">
                                    </div>
                                </div>

                                <div class="flex justify-between items-center text-xs text-slate-600 pt-1">
                                    <span>সার্ভিস চার্জ</span>
                                    <span class="font-bold">৳ ০</span>
                                </div>

                                <div class="flex justify-between items-center text-sm font-black text-slate-900 pt-1.5 border-t border-emerald-200">
                                    <span class="text-green-800">মোট পরিশোধ</span>
                                    <span class="text-green-800" id="total_display">৳ ০.০০</span>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 pt-0.5">
                                <input type="checkbox" id="check_agree" checked class="accent-green-700 w-4 h-4 rounded">
                                <label for="check_agree" class="text-xs font-bold text-slate-600 cursor-pointer">
                                    পেমেন্টের তথ্য যাচাই করেছি
                                </label>
                            </div>

                            <button type="button" onclick="goToStep2()" class="btn-green-submit">
                                <span>পেমেন্ট করতে এগিয়ে যান</span>
                                <i class="fa-solid fa-arrow-right text-xs"></i>
                            </button>

                            <div class="text-center pt-1 text-[11px] font-bold text-slate-400 flex items-center justify-center gap-1.5">
                                <i class="fa-solid fa-shield-halved text-green-700"></i>
                                <span>নিরাপদ পেমেন্ট | আপনার তথ্য সুরক্ষিত রাখুন</span>
                            </div>
                        </div>
                    </div>

                    <div id="step2_view" style="display: none;">
                        <div class="bg-gradient-to-r from-[#0F5132] via-[#107C41] to-[#15803D] text-white p-5 text-center">
                            <h3 class="text-lg font-black tracking-wide">নিরাপদ ও দ্রুত ব্যালেন্স টপ-আপ</h3>
                            <p class="text-[11px] text-green-100 mt-0.5">সরাসরি সেন্ড মানি করে TrxID দিয়ে নিশ্চিত করুন</p>
                        </div>

                        <form action="" method="POST" enctype="multipart/form-data" class="p-4 sm:p-5 space-y-4">
                            <input type="hidden" name="method_key" id="form_method_key" value="<?php echo htmlspecialchars($first_available_method); ?>">
                            <input type="hidden" name="payable_amount" id="form_payable_amount" value="">

                            <div class="text-center space-y-1">
                                <h4 class="text-base font-black text-slate-900" id="step2_method_title">পেমেন্ট মেথড</h4>
                                <p class="text-xs text-slate-500">নিচের নম্বরে সেন্ড মানি করুন</p>
                            </div>

                            <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4 text-center space-y-1.5">
                                <span class="text-xs font-bold text-slate-500 block">পরিশোধ করতে হবে:</span>
                                <h2 class="text-2xl font-black text-rose-600" id="step2_display_amount">৳ 0.00</h2>

                                <span class="text-[11px] font-bold text-slate-400 block pt-1">পার্সোনাল নম্বর:</span>
                                <div class="flex items-center justify-center gap-2">
                                    <span class="text-lg font-black text-slate-800 tracking-wider font-mono" id="copyable_number">-</span>
                                    <button type="button" onclick="copyNumber()" class="bg-slate-200 hover:bg-slate-300 text-slate-800 font-bold text-xs px-3 py-1 rounded-lg transition flex items-center gap-1 shadow-sm">
                                        <i class="fa-regular fa-copy"></i> কপি
                                    </button>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">
                                    ট্রানজেকশন আইডি (Trx ID) <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" name="trx_id" required placeholder="যেমন: 9N78GH65" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm font-semibold uppercase tracking-wider text-slate-800 focus:bg-white focus:outline-none focus:border-green-600">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">
                                    পেমেন্টের স্ক্রিনশট আপলোড (ঐচ্ছিক)
                                </label>
                                <input type="file" name="screenshot" accept="image/*" class="w-full text-xs text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-200 file:text-slate-700 hover:file:bg-slate-300 cursor-pointer border border-slate-200 rounded-xl p-1.5 bg-slate-50">
                            </div>

                            <div class="grid grid-cols-2 gap-2 pt-2">
                                <button type="button" onclick="goToStep1()" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs py-3 rounded-xl transition">
                                    ফিরে যান
                                </button>
                                <button type="submit" name="confirm_deposit" class="bg-[#107C41] hover:bg-green-800 text-white font-extrabold text-xs py-3 rounded-xl transition shadow-sm">
                                    টাকা জমা দিয়েছি
                                </button>
                            </div>
                        </form>
                    </div>

                    <?php else: ?>
                    <div class="p-4 sm:p-5 space-y-3.5">


                        <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-2.5 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full bg-emerald-600 text-white flex items-center justify-center text-xs">
                                    <i class="fa-solid fa-check"></i>
                                </div>
                                <div>
                                    <h4 class="text-sm font-black text-green-950 leading-tight">পেমেন্ট রশিদ</h4>
                                    <span class="text-[8.5px] font-bold text-slate-500 uppercase">Payment Receipt</span>
                                </div>
                            </div>
                            <div class="text-right text-xs">
                                <span class="font-extrabold text-slate-800 block text-[11px]">রশিদ নং: #<?php echo $receipt_data['order_no']; ?></span>
                                <span class="text-[10px] text-slate-500 block"><?php echo $receipt_data['date']; ?></span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-2.5">
                            <div class="sm:col-span-7 bg-white border border-slate-200 rounded-2xl p-3 space-y-1.5 text-xs text-slate-700">
                                <div class="bg-[#107C41] text-white text-[11px] font-bold px-2 py-0.5 rounded-lg inline-flex items-center gap-1 mb-1">
                                    <i class="fa-solid fa-user"></i> গ্রাহকের তথ্য
                                </div>
                                <div class="grid grid-cols-3"><span class="text-slate-400">নাম</span><span class="col-span-2 font-bold">: <?php echo htmlspecialchars($receipt_data['name']); ?></span></div>
                                <div class="grid grid-cols-3"><span class="text-slate-400">মোবাইল</span><span class="col-span-2 font-bold font-mono">: <?php echo htmlspecialchars($receipt_data['phone']); ?></span></div>
                                <div class="grid grid-cols-3"><span class="text-slate-400">ইমেইল</span><span class="col-span-2 font-bold truncate">: <?php echo htmlspecialchars($receipt_data['email']); ?></span></div>
                            </div>

                            <div class="sm:col-span-5 bg-amber-50 border border-amber-200 rounded-2xl p-3 flex flex-col items-center justify-center text-center">
                                <div class="w-9 h-9 rounded-full bg-amber-500 text-white flex items-center justify-center text-lg mb-1 animate-pulse">
                                    <i class="fa-solid fa-clock-rotate-left"></i>
                                </div>
                                <h5 class="text-xs font-black text-amber-900">আপনার পেমেন্ট যাচাই হচ্ছে</h5>
                                <p class="text-[10px] text-amber-700 mt-0.5 leading-tight">পেমেন্ট সফলভাবে রিসিভ হয়েছে, এডমিন যাচাই করে ব্যালেন্স যুক্ত করে দেবে।</p>
                            </div>
                        </div>

                        <div class="bg-white border border-slate-200 rounded-2xl p-3 space-y-1.5 text-xs text-slate-700">
                            <div class="bg-[#107C41] text-white text-[11px] font-bold px-2 py-0.5 rounded-lg inline-flex items-center gap-1 mb-1">
                                <i class="fa-solid fa-layer-group"></i> সেবার বিবরণ
                            </div>
                            <div class="grid grid-cols-3"><span class="text-slate-400">সেবার নাম</span><span class="col-span-2 font-bold">: <?php echo htmlspecialchars($receipt_data['service_name']); ?></span></div>
                            <div class="grid grid-cols-3"><span class="text-slate-400">সেবার ধরন</span><span class="col-span-2 font-bold">: <?php echo htmlspecialchars($receipt_data['service_cat']); ?></span></div>
                            <div class="grid grid-cols-3"><span class="text-slate-400">পরিমাণ</span><span class="col-span-2 font-black text-green-800 text-sm">: ৳ <?php echo number_format($receipt_data['price'], 2); ?></span></div>
                        </div>

                        <div class="bg-white border border-slate-200 rounded-2xl p-3 space-y-1.5 text-xs text-slate-700">
                            <div class="bg-[#107C41] text-white text-[11px] font-bold px-2 py-0.5 rounded-lg inline-flex items-center gap-1 mb-1">
                                <i class="fa-solid fa-receipt"></i> পেমেন্টের বিবরণ
                            </div>
                            <div class="grid grid-cols-3"><span class="text-slate-400">পেমেন্ট পদ্ধতি</span><span class="col-span-2 font-bold uppercase text-pink-600">: <?php echo htmlspecialchars($receipt_data['method']); ?></span></div>
                            <div class="grid grid-cols-3"><span class="text-slate-400">ট্রানজেকশন আইডি</span><span class="col-span-2 font-mono font-black text-indigo-700">: <?php echo htmlspecialchars($receipt_data['trx_id']); ?></span></div>
                            <div class="grid grid-cols-3"><span class="text-slate-400">পেমেন্টের স্ট্যাটাস</span><span class="col-span-2 font-bold text-amber-600">: প্রসেসিং / অপেক্ষমান</span></div>
                        </div>

                        <div class="bg-slate-50 border border-slate-200 rounded-xl p-2.5 text-[11px] text-slate-600 space-y-1">
                            <span class="font-extrabold text-slate-800 block">
                                <i class="fa-solid fa-circle-info text-emerald-600"></i> গুরুত্বপূর্ণ নির্দেশনা:
                            </span>
                            <p>• আপনার দেওয়া TrxID এবং পেমেন্টের তথ্য সিস্টেমে জমা হয়েছে।</p>
                            <p>• এডমিন প্যানেল থেকে ভেরিফাই হওয়ামাত্রই ব্যালেন্স বা অর্ডার কমপ্লিট হবে।</p>
                        </div>

                        <div class="no-print pt-1 flex items-center gap-2">
                            <a href="receipt.php?deposit=<?php echo (int)$new_dep_id; ?>&new=1" class="flex-1 bg-green-700 hover:bg-green-800 text-white font-extrabold text-xs py-2.5 rounded-xl transition shadow flex items-center justify-center gap-1.5">
                                <i class="fa-solid fa-print"></i> রশিদ প্রিন্ট / ডাউনলোড
                            </a>
                            <a href="dashboard.php" class="bg-slate-100 hover:bg-slate-200 text-slate-800 font-extrabold text-xs py-2.5 px-4 rounded-xl transition text-center">
                                ড্যাশবোর্ড
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>

                </div>
            </div>

            <div class="lg:col-span-6 w-full no-print">
                <div class="phone-card p-4 sm:p-5">
                    <div class="flex items-center justify-between pb-3.5 border-b border-slate-100">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-sm shadow-sm">
                                <i class="fa-solid fa-clock-rotate-left"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-black text-slate-800">রিচার্জের ইতিহাস</h4>
                                <p class="text-[10px] font-bold text-slate-500">আপনার সাম্প্রতিক জমার তালিকা ও রশিদ</p>
                            </div>
                        </div>
                        <span class="text-[11px] font-extrabold px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <?php echo count($history_records); ?> টি লেনদেন
                        </span>
                    </div>

                    <div class="mt-3.5">
                        <?php if (empty($history_records)): ?>
                            <div class="bg-slate-50 border border-dashed border-slate-200 rounded-2xl p-6 text-center">
                                <i class="fa-regular fa-folder-open text-slate-400 text-2xl mb-1.5"></i>
                                <p class="text-xs font-bold text-slate-500">এখনও কোনো রিচার্জের রেকর্ড নেই</p>
                            </div>
                        <?php else: ?>
                            <div class="space-y-2.5 max-h-[560px] overflow-y-auto pr-1 custom-scroll">
                                <?php foreach ($history_records as $hist): 
                                    $status_badge = 'bg-amber-100 text-amber-700 border-amber-200';
                                    $status_label = 'অপেক্ষমান';
                                    if ($hist['status'] === 'completed') {
                                        $status_badge = 'bg-emerald-100 text-emerald-700 border-emerald-200';
                                        $status_label = 'সফল';
                                    } elseif ($hist['status'] === 'failed' || $hist['status'] === 'cancelled') {
                                        $status_badge = 'bg-rose-100 text-rose-700 border-rose-200';
                                        $status_label = 'বাতিল';
                                    }

                                    $m_name = strtoupper($hist['method']);
                                    if ($hist['method'] === 'bkash') $m_name = 'বিকাশ';
                                    elseif ($hist['method'] === 'nagad') $m_name = 'নগদ';
                                    elseif ($hist['method'] === 'rocket') $m_name = 'রকেট';
                                    elseif ($hist['method'] === 'upay') $m_name = 'উপায়';
                                ?>
                                <div class="bg-slate-50 hover:bg-white border border-slate-200 hover:border-emerald-300 rounded-2xl p-3 sm:p-3.5 flex items-center justify-between transition shadow-sm">
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs sm:text-sm font-black text-slate-800"><?php echo htmlspecialchars($m_name); ?></span>
                                            <span class="text-[10px] font-mono font-bold text-indigo-700 bg-indigo-50 px-1.5 py-0.5 rounded border border-indigo-100">
                                                <?php echo htmlspecialchars($hist['trx_id']); ?>
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-2 text-[10.5px] text-slate-500 font-semibold">
                                            <span><?php echo date('d M Y, h:i A', strtotime($hist['created_at'])); ?></span>
                                            <span>•</span>
                                            <span class="px-1.5 py-0.5 rounded border text-[9.5px] font-bold <?php echo $status_badge; ?>">
                                                <?php echo $status_label; ?>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="text-right space-y-1.5">
                                        <span class="text-sm sm:text-base font-black text-green-800 block">
                                            ৳ <?php echo number_format($hist['amount'], 2); ?>
                                        </span>
                                        <a href="receipt.php?deposit=<?php echo (int)$hist['id']; ?>"
                                            class="text-[11px] font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 px-3 py-1 rounded-xl transition inline-flex items-center gap-1.5 shadow-sm">
                                            <i class="fa-solid fa-receipt"></i> রশিদ
                                        </a>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>

    </div>


    <script>
        const allNumbers = <?php echo json_encode($gateway_numbers); ?>;
        let selectedMethodKey = "<?php echo $first_available_method; ?>";
        let selectedMethodTitle = (selectedMethodKey === 'bkash' ? 'bKash' : (selectedMethodKey === 'nagad' ? 'নগদ' : (selectedMethodKey === 'rocket' ? 'রকেট' : 'উপায়'))) + " পার্সোনাল পেমেন্ট";
        let selectedNumber = allNumbers[selectedMethodKey] || "";

        function chooseMethod(key, title) {
            $('.method-tile').removeClass('selected');
            $(event.currentTarget).addClass('selected');

            selectedMethodKey = key;
            selectedMethodTitle = title + " পার্সোনাল পেমেন্ট";
            selectedNumber = allNumbers[key] || "";
        }

        function updateSummaryAmount() {
            let val = parseFloat($('#input_amount').val());
            if (isNaN(val) || val <= 0) {
                $('#total_display').text('৳ ০.০০');
            } else {
                $('#total_display').text('৳ ' + val.toFixed(2));
            }
        }

        function goToStep2() {
            let amt = parseFloat($('#input_amount').val());
            if (isNaN(amt) || amt < 10) {
                Swal.fire({
                    icon: 'warning',
                    title: 'অ্যামাউন্ট দিন',
                    text: 'অনুগ্রহ করে কত টাকা পরিশোধ করবেন তা সঠিকভাবে লিখুন (সর্বনিম্ন ১০ টাকা)।',
                    confirmButtonColor: '#107C41'
                });
                return;
            }

            if (!selectedNumber) {
                selectedNumber = allNumbers[selectedMethodKey] || "";
            }

            $('#form_method_key').val(selectedMethodKey);
            $('#form_payable_amount').val(amt);

            $('#step2_method_title').text(selectedMethodTitle);
            $('#step2_display_amount').text('৳ ' + amt.toFixed(2));
            $('#copyable_number').text(selectedNumber || 'নম্বর পাওয়া যায়নি');

            $('#step1_view').fadeOut(150, function() {
                $('#step2_view').fadeIn(150);
            });
        }

        function goToStep1() {
            $('#step2_view').fadeOut(150, function() {
                $('#step1_view').fadeIn(150);
            });
        }

        function copyNumber() {
            if (!selectedNumber) return;
            navigator.clipboard.writeText(selectedNumber).then(() => {
                Swal.fire({
                    icon: 'success',
                    title: 'নম্বর কপি হয়েছে!',
                    text: selectedNumber,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 1500
                });
            });
        }

    </script>
<?php include __DIR__ . '/includes/site_footer.php'; ?>
</body>
</html>
<?php ob_end_flush(); ?>