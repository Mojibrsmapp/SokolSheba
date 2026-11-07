<?php 
session_start();
include_once("includes/configuration.php");

if (isset($_SESSION['user_token'])) {
    $token_user = $_SESSION['user_token'];

    $stmt = $conn->prepare("SELECT id, balance, email, name, token, mobile FROM users WHERE token = ?");
    if ($stmt) {
        $stmt->bind_param("s", $token_user);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $id = $row["id"];
            $balance = $row["balance"];
            $email = $row["email"];
            $name = $row["name"];
        } else {
            header("Location: index.php");
            exit();
        }
        $stmt->close();
    } else {
        header("Location: index.php");
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}

$conn->query("CREATE TABLE IF NOT EXISTS `call_sms_services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_key` varchar(50) NOT NULL UNIQUE,
  `service_name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 1150.00,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `icon_bg` varchar(30) NOT NULL DEFAULT 'bg-pink-100',
  `icon_color` varchar(30) NOT NULL DEFAULT 'text-pink-600',
  `icon_class` varchar(50) NOT NULL DEFAULT 'fa-phone-volume',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$init_check = $conn->query("SELECT id FROM call_sms_services LIMIT 1");
if ($init_check && $init_check->num_rows == 0) {
    $conn->query("INSERT INTO `call_sms_services` (`service_key`, `service_name`, `price`, `status`, `icon_bg`, `icon_color`, `icon_class`) VALUES
    ('call_3m', '৩ মাসের কল লিস্ট', 1150.00, 'active', 'bg-pink-100', 'text-pink-600', 'fa-phone-volume'),
    ('call_6m', '৬ মাসের কল লিস্ট', 1600.00, 'active', 'bg-amber-100', 'text-amber-600', 'fa-phone-volume'),
    ('call_1y', '১ বছরের কল লিস্ট', 2500.00, 'active', 'bg-sky-100', 'text-sky-600', 'fa-phone-volume'),
    ('sms_3m', 'এস এম লিস্ট ৩ মাস', 1150.00, 'active', 'bg-indigo-100', 'text-indigo-600', 'fa-comment'),
    ('sms_6m', 'এস এম লিস্ট ৬ মাস', 1600.00, 'active', 'bg-violet-100', 'text-violet-600', 'fa-comment'),
    ('sms_1y', 'এস এম লিস্ট ১ বছর', 2500.00, 'active', 'bg-rose-100', 'text-rose-600', 'fa-comment')");
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'place_call_sms_order') {
    header('Content-Type: application/json');
    $service_id = intval($_POST['service_id'] ?? 0);
    $target_number = trim($_POST['target_number'] ?? '');

    if (empty($target_number) || strlen($target_number) < 11) {
        echo json_encode(['status' => 'error', 'message' => 'অনুগ্রহ করে সঠিক মোবাইল নম্বর লিখুন!']);
        exit();
    }

    $chk_stmt = $conn->prepare("SELECT service_name, price, status FROM call_sms_services WHERE id = ? LIMIT 1");
    if ($chk_stmt) {
        $chk_stmt->bind_param("i", $service_id);
        $chk_stmt->execute();
        $srv_info = $chk_stmt->get_result()->fetch_assoc();
        $chk_stmt->close();
    } else {
        $srv_info = null;
    }

    if (!$srv_info) {
        echo json_encode(['status' => 'error', 'message' => 'সার্ভিস খুঁজে পাওয়া যায়নি!']);
        exit();
    }

    if ($srv_info['status'] !== 'active') {
        echo json_encode(['status' => 'error', 'message' => 'এই প্যাকেজটি বর্তমানে বন্ধ রয়েছে!']);
        exit();
    }

    $cost = floatval($srv_info['price']);

    $u_stmt = $conn->prepare("SELECT balance FROM users WHERE id = ? LIMIT 1");
    $u_stmt->bind_param("i", $id);
    $u_stmt->execute();
    $user_current_bal = floatval($u_stmt->get_result()->fetch_assoc()['balance'] ?? 0);
    $u_stmt->close();

    if ($user_current_bal < $cost) {
        echo json_encode(['status' => 'error', 'message' => 'আপনার অ্যাকাউন্টে পর্যাপ্ত ব্যালেন্স নেই! রিচার্জ করুন।']);
        exit();
    }

    $conn->begin_transaction();
    try {
        $up_bal = $conn->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
        $up_bal->bind_param("di", $cost, $id);
        $up_bal->execute();
        $up_bal->close();

        $order_type = $srv_info['service_name'];
        $ins_ord = $conn->prepare("INSERT INTO orders (user_id, order_type, input_data, price, status) VALUES (?, ?, ?, ?, 'pending')");
        $ins_ord->bind_param("issd", $id, $order_type, $target_number, $cost);
        $ins_ord->execute();
        $ins_ord->close();

        $conn->commit();
        echo json_encode([
            'status' => 'success', 
            'message' => 'কল/এসএমএস হিস্ট্রি অর্ডার সফলভাবে সম্পন্ন হয়েছে!', 
            'new_balance' => number_format($user_current_bal - $cost, 2)
        ]);
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => 'অর্ডার সম্পন্ন করতে সমস্যা হয়েছে!']);
        exit();
    }
}

$services_map = [];
$res_items = $conn->query("SELECT * FROM call_sms_services ORDER BY id ASC");
if ($res_items && $res_items->num_rows > 0) {
    while ($r = $res_items->fetch_assoc()) {
        $services_map[$r['service_key']] = $r;
    }
}

$url_type = isset($_GET['type']) ? trim($_GET['type']) : 'call_3m';
if (!isset($services_map[$url_type])) {
    $url_type = 'call_3m';
}

$initial_group = str_starts_with($url_type, 'sms') ? 'sms' : 'call';
$initial_dur = str_replace(['call_', 'sms_'], '', $url_type);
if (!in_array($initial_dur, ['3m', '6m', '1y'])) {
    $initial_dur = '3m';
}

$recent_orders = [];
$match_sql = "%লিস্ট%";
$h_stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? AND (order_type LIKE ? OR order_type LIKE '%কল%' OR order_type LIKE '%এস এম%') ORDER BY id DESC LIMIT 20");
if ($h_stmt) {
    $h_stmt->bind_param("is", $id, $match_sql);$h_stmt->execute();
    $h_res =$h_stmt->get_result();
    if ($h_res) {
        while ($h_row =$h_res->fetch_assoc()) {
            $recent_orders[] =$h_row;
        }
    }
    $h_stmt->close();
}
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>কল ও এসএমএস হিস্ট্রি (CDR) - Sokol Kaj</title>

    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="shortcut icon" href="<?php echo soko_e(soko_brand('site_favicon')); ?>">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }

        body {
            font-family: 'Hind Siliguri', 'Plus Jakarta Sans', sans-serif;
            background: #F1F5F9;
            color: #1E293B;
            min-height: 100vh;
            padding-bottom: 70px;
        }

        .top-navbar {
            background: #FFFFFF;
            padding: 10px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .header-logo {
            height: 38px;
            width: auto;
            max-width: 160px;
            object-fit: contain;
        }

        .balance-badge-top {
            background: #F59E0B;
            color: #FFFFFF;
            padding: 6px 16px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 13px;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.25);
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
        }

        .user-avatar-top {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #E2E8F0;
        }

        .page-container {
            max-width: 860px;
            margin: 0 auto;
            padding: 20px 14px;
        }

        .cdr-panel-card {
            background: #FFFFFF;
            border-radius: 28px;
            padding: 24px 22px;
            border: 1px solid rgba(226, 232, 240, 0.95);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
            margin-bottom: 24px;
        }

        .service-switch-tab {
            background: #F8FAFC;
            border: 2px solid #E2E8F0;
            border-radius: 20px;
            padding: 14px 18px;
            display: flex;
            align-items: center;
            gap: 14px;
            cursor: pointer;
            transition: all 0.25s ease;
        }
        .service-switch-tab:hover {
            border-color: #CBD5E1;
            transform: translateY(-2px);
        }
        .service-switch-tab.active {
            background: #EFF6FF;
            border-color: #2563EB;
            box-shadow: 0 4px 16px rgba(37, 99, 235, 0.12);
        }

        .switch-icon-box {
            width: 44px;
            height: 44px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 19px;
            transition: all 0.25s ease;
        }
        .service-switch-tab.active .switch-icon-box {
            transform: scale(1.08);
        }

        .duration-card {
            background: #FFFFFF;
            border: 2px solid #E2E8F0;
            border-radius: 20px;
            padding: 16px 12px;
            text-align: center;
            cursor: pointer;
            transition: all 0.25s ease;
            position: relative;
        }
        .duration-card:hover {
            border-color: #93C5FD;
            transform: translateY(-3px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.04);
        }
        .duration-card.active {
            border-color: #2563EB;
            background: #F0F7FF;
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.14);
        }

        .duration-badge {
            font-size: 10px;
            font-weight: 800;
            padding: 2px 8px;
            border-radius: 10px;
            background: #F1F5F9;
            color: #64748B;
            display: inline-block;
            margin-bottom: 6px;
        }
        .duration-card.active .duration-badge {
            background: #DBEAFE;
            color: #1D4ED8;
        }

        .phone-input-field {
            width: 100%;
            border: 2px solid #E2E8F0;
            border-radius: 16px;
            padding: 14px 18px 14px 46px;
            font-size: 15px;
            font-weight: 600;
            outline: none;
            transition: all 0.2s ease;
            background: #FFFFFF;
        }
        .phone-input-field:focus {
            border-color: #2563EB;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
        }

        .btn-order-submit {
            background: linear-gradient(135deg, #2563EB 0%, #1D4ED8 100%);
            color: #FFFFFF;
            font-weight: 700;
            font-size: 15px;
            padding: 15px 24px;
            border-radius: 16px;
            width: 100%;
            border: none;
            cursor: pointer;
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.28);
            transition: all 0.25s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        .btn-order-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 24px rgba(37, 99, 235, 0.35);
        }

        .history-card {
            background: #FFFFFF;
            border-radius: 24px;
            padding: 22px 18px;
            border: 1px solid rgba(226, 232, 240, 0.95);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.03);
        }

        .history-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            overflow: hidden;
            border-radius: 16px;
        }
        .history-table thead tr {
            background: #3B49B8;
            color: #FFFFFF;
        }
        .history-table th {
            padding: 12px 14px;
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
        }
        .history-table td {
            padding: 12px 14px;
            font-size: 12.5px;
            border-bottom: 1px solid #F1F5F9;
            color: #334155;
            white-space: nowrap;
            vertical-align: middle;
        }
        .history-table tbody tr:hover td {
            background: #F8FAFC;
        }

        .fab-group {
            position: fixed;
            bottom: 24px;
            right: 20px;
            z-index: 50;
        }
        .fab-join-btn {
            background: #14B8A6;
            color: #FFFFFF;
            border-radius: 30px;
            padding: 10px 18px;
            font-size: 13px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 6px 20px rgba(20, 184, 166, 0.35);
            text-decoration: none;
            transition: transform 0.2s;
        }
        .fab-join-btn:hover {
            transform: scale(1.05);
        }

        .drawer-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.45);
            z-index: 90;
            display: none;
        }
        .drawer-menu {
            position: fixed;
            top: 0;
            right: -320px;
            width: 300px;
            height: 100%;
            background: #FFFFFF;
            box-shadow: -4px 0 25px rgba(0, 0, 0, 0.15);
            z-index: 100;
            transition: right 0.3s ease;
            display: flex;
            flex-direction: column;
        }
        .drawer-menu.open { right: 0; }
        .drawer-header {
            padding: 18px;
            background: linear-gradient(135deg, #4F46E5 0%, #6366F1 100%);
            color: #FFFFFF;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .drawer-body {
            flex: 1;
            overflow-y: auto;
            padding: 10px 0;
        }
        .drawer-link {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 12px 18px;
            color: #334155;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            border-bottom: 1px solid #F8FAFC;
        }
        .drawer-link:hover {
            background: #F1F5F9;
            color: #4F46E5;
        }
        .drawer-link i {
            width: 22px;
            font-size: 16px;
            text-align: center;
        }
    </style>
</head>
<body>

    <?php include __DIR__ . '/includes/site_header.php'; ?>

    <div class="page-container">

        <div class="cdr-panel-card">

            <div class="flex items-center justify-between border-b pb-4 mb-5">
                <a href="dashboard.php" class="text-xs font-bold text-slate-500 hover:text-blue-600 flex items-center gap-1.5 transition">
                    <i class="fa-solid fa-arrow-left"></i> ড্যাশবোর্ড
                </a>
                <h4 class="font-bold text-blue-700 text-base md:text-lg leading-snug">
                    <u>( CDR ) কল ও মেসেজ হিস্ট্রি সার্ভিস অর্ডার করুন</u>
                </h4>
                <div class="text-xs font-bold bg-blue-50 text-blue-700 px-3 py-1 rounded-full border border-blue-100">
                    ব্যালেন্স: ৳ <?php echo number_format($balance, 2); ?>
                </div>
            </div>

            <form id="callSmsConfigForm">

                <div class="mb-5">
                    <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-500 mb-2.5">
                        ১. রেকর্ডের ধরন নির্বাচন করুন
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="service-switch-tab <?php echo ($initial_group === 'call') ? 'active' : ''; ?>" onclick="switchGroup('call', this)">
                            <div class="switch-icon-box bg-pink-100 text-pink-600">
                                <i class="fa-solid fa-phone-volume"></i>
                            </div>
                            <div class="flex-1">
                                <h5 class="text-sm font-bold text-slate-800">কল হিস্ট্রি লিস্ট</h5>
                                <p class="text-[11px] text-slate-400">সকল ইনকামিং ও আউটগোয়িং কল লগ</p>
                            </div>
                            <div class="text-blue-600 text-lg check-mark <?php echo ($initial_group === 'call') ? '' : 'hidden'; ?>">
                                <i class="fa-solid fa-circle-check"></i>
                            </div>
                        </div>

                        <div class="service-switch-tab <?php echo ($initial_group === 'sms') ? 'active' : ''; ?>" onclick="switchGroup('sms', this)">
                            <div class="switch-icon-box bg-indigo-100 text-indigo-600">
                                <i class="fa-solid fa-comment-sms"></i>
                            </div>
                            <div class="flex-1">
                                <h5 class="text-sm font-bold text-slate-800">এস এম এস হিস্ট্রি লিস্ট</h5>
                                <p class="text-[11px] text-slate-400">মেসেজ আদান-প্রদানের পূর্ণ বিবরণ</p>
                            </div>
                            <div class="text-blue-600 text-lg check-mark <?php echo ($initial_group === 'sms') ? '' : 'hidden'; ?>">
                                <i class="fa-solid fa-circle-check"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-5">
                    <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-500 mb-2.5">
                        ২. প্যাকেজের মেয়াদ নির্বাচন করুন
                    </label>
                    <div class="grid grid-cols-3 gap-3">
                        <div class="duration-card <?php echo ($initial_dur === '3m') ? 'active' : ''; ?>" onclick="switchDuration('3m', this)">
                            <span class="duration-badge">৩ মাস মেয়াদ</span>
                            <h5 class="text-sm font-black text-slate-800 mb-1">৩ মাস</h5>
                            <span class="text-sm font-black text-blue-600 price-text" id="durPrice_3m">৳ 1150</span>
                        </div>

                        <div class="duration-card <?php echo ($initial_dur === '6m') ? 'active' : ''; ?>" onclick="switchDuration('6m', this)">
                            <span class="duration-badge">জনপ্রিয়</span>
                            <h5 class="text-sm font-black text-slate-800 mb-1">৬ মাস</h5>
                            <span class="text-sm font-black text-blue-600 price-text" id="durPrice_6m">৳ 1600</span>
                        </div>

                        <div class="duration-card <?php echo ($initial_dur === '1y') ? 'active' : ''; ?>" onclick="switchDuration('1y', this)">
                            <span class="duration-badge">পূর্ণ বছর</span>
                            <h5 class="text-sm font-black text-slate-800 mb-1">১ বছর</h5>
                            <span class="text-sm font-black text-blue-600 price-text" id="durPrice_1y">৳ 2500</span>
                        </div>
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-500 mb-2">
                        ৩. টার্গেট মোবাইল নম্বর দিন
                    </label>
                    <div class="relative">
                        <i class="fa-solid fa-mobile-screen absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-base"></i>
                        <input type="text" id="targetPhoneNumber" class="phone-input-field" placeholder="017XXXXXXXX বা 018XXXXXXXX" required>
                    </div>
                </div>

                <button type="submit" id="cdrSubmitBtn" class="btn-order-submit">
                    <i class="fa-solid fa-file-pdf text-lg"></i>
                    <span id="btnText">অর্ডার কনফার্ম করুন (৳ 1150)</span>
                </button>
            </form>

        </div>

        <div class="history-card">
            <div class="flex items-center gap-2 mb-3.5">
                <i class="fa-solid fa-table-list text-sky-500 text-base"></i>
                <h5 class="font-bold text-sky-500 text-sm md:text-base">আপনার সাম্প্রতিক অর্ডারসমূহ</h5>
            </div>

            <div class="overflow-x-auto rounded-xl">
                <table class="history-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>অর্ডারের ধরন</th>
                            <th>তথ্য</th>
                            <th>রিসিভ স্ট্যাটাস</th>
                            <th>ডাউনলোড / স্ট্যাটাস</th>
                            <th>রেট</th>
                            <th>সময়</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recent_orders)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-6 text-slate-400 text-xs">
                                    কোনো অর্ডারের রেকর্ড পাওয়া যায়নি।
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $serial = 1; foreach ($recent_orders as$ord): 
                                $st_badge = 'bg-amber-50 text-amber-700 border border-amber-200';$st_text = 'পেন্ডিং';
                                if ($ord['status'] === 'success') {
                                    $st_badge = 'bg-emerald-50 text-emerald-700 border border-emerald-200';$st_text = 'সফল';
                                } elseif ($ord['status'] === 'cancel') {
                                    $st_badge = 'bg-rose-50 text-rose-700 border border-rose-200';$st_text = 'বাতিল';
                                }
                            ?>
                            <tr>
                                <td class="font-bold text-slate-700"><?php echo $serial++; ?></td>
                                <td class="font-bold text-slate-800"><?php echo htmlspecialchars($ord['order_type']); ?></td>
                                <td class="font-semibold text-slate-700"><?php echo htmlspecialchars($ord['input_data']); ?></td>
                                <td>
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold <?php echo $st_badge; ?>">
                                        <?php echo $st_text; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($ord['result_file'])): ?>
                                        <a href="<?php echo htmlspecialchars($ord['result_file']); ?>" target="_blank" class="text-xs font-bold text-indigo-600 hover:underline flex items-center gap-1">
                                            <i class="fa-solid fa-file-arrow-down"></i> ডাউনলোড
                                        </a>
                                    <?php elseif (!empty($ord['result_text'])): ?>
                                        <span class="text-xs text-slate-700 font-semibold"><?php echo htmlspecialchars($ord['result_text']); ?></span>
                                    <?php else: ?>
                                        <span class="text-slate-400 text-xs">অপেক্ষমান</span>
                                    <?php endif; ?>
                                </td>
                                <td class="font-bold text-emerald-600">৳ <?php echo number_format($ord['price'], 2); ?></td>
                                <td class="text-slate-400 text-xs">
                                    <?php echo date('d M Y, h:i A', strtotime($ord['created_at'])); ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>


    <script>
        const servicesData = <?php echo json_encode($services_map, JSON_UNESCAPED_UNICODE); ?>;
        let currentGroup = "<?php echo $initial_group; ?>";
        let currentDur = "<?php echo $initial_dur; ?>";

        function switchGroup(group, el) {
            $('.service-switch-tab').removeClass('active');$('.service-switch-tab .check-mark').addClass('hidden');

            $(el).addClass('active');$(el).find('.check-mark').removeClass('hidden');

            currentGroup = group;
            updateDisplay();
        }

        function switchDuration(dur, el) {
            $('.duration-card').removeClass('active');$(el).addClass('active');

            currentDur = dur;
            updateDisplay();
        }

        function updateDisplay() {
            const key3m = `${currentGroup}_3m`;
            const key6m = `${currentGroup}_6m`;
            const key1y = `${currentGroup}_1y`;

            const p3 = (servicesData[key3m] && servicesData[key3m].price) ? parseInt(servicesData[key3m].price) : 1150;
            const p6 = (servicesData[key6m] && servicesData[key6m].price) ? parseInt(servicesData[key6m].price) : 1600;
            const p1 = (servicesData[key1y] && servicesData[key1y].price) ? parseInt(servicesData[key1y].price) : 2500;

            $('#durPrice_3m').text('৳ ' + p3);
            $('#durPrice_6m').text('৳ ' + p6);
            $('#durPrice_1y').text('৳ ' + p1);

            const activeKey = `${currentGroup}_${currentDur}`;
            const activeService = servicesData[activeKey];
            const activePrice = activeService ? parseInt(activeService.price) : 1150;

            $('#btnText').text(`অর্ডার কনফার্ম করুন (৳ ${activePrice})`);
        }

        function getActiveServiceId() {
            const activeKey = `${currentGroup}_${currentDur}`;
            if (servicesData[activeKey]) {
                return servicesData[activeKey].id;
            }
            return 1;
        }

        document.getElementById('callSmsConfigForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const phoneVal = document.getElementById('targetPhoneNumber').value.trim();
            if (!phoneVal) {
                Swal.fire('তথ্য দিন', 'অনুগ্রহ করে মোবাইল নম্বর লিখুন।', 'warning');
                return;
            }

            const activeServiceId = getActiveServiceId();
            const btn = document.getElementById('cdrSubmitBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> অর্ডার প্রসেস হচ্ছে...';

            $.ajax({
                url: 'call_sms_list.php',
                type: 'POST',
                data: {
                    action: 'place_call_sms_order',
                    service_id: activeServiceId,
                    target_number: phoneVal
                },
                dataType: 'json',
                success: function(res) {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa-solid fa-file-pdf text-lg"></i> <span id="btnText">অর্ডার কনফার্ম করুন</span>';

                    if (res && res.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'সফল!',
                            text: res.message
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire('ত্রুটি!', res.message || 'অর্ডার সম্পন্ন হয়নি!', 'error');
                    }
                },
                error: function() {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa-solid fa-file-pdf text-lg"></i> <span id="btnText">অর্ডার কনফার্ম করুন</span>';
                    Swal.fire('সার্ভার ত্রুটি', 'সার্ভারে সমস্যা হয়েছে। পুনরায় চেষ্টা করুন।', 'error');
                }
            });
        });

        $('#openDrawerBtn').click(function() {
            $('#drawerOverlay').fadeIn(200);
            $('#drawerMenu').addClass('open');
        });

        $('#closeDrawerBtn, #drawerOverlay').click(function() {
            $('#drawerOverlay').fadeOut(200);
            $('#drawerMenu').removeClass('open');
        });

        $(document).ready(function() {
            updateDisplay();
            $('#targetPhoneNumber').focus();
        });
    </script>
<?php include __DIR__ . '/includes/site_footer.php'; ?>
</body>
</html>