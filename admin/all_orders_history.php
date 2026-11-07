<?php
session_start();
include_once("../includes/configuration.php");

$alert_msg = "";
$alert_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action_order'])) {
    $action = $_POST['action_order'];
    $order_id = intval($_POST['order_id'] ?? 0);
    $res_text = trim($_POST['result_text'] ?? '');

    if ($action === 'update_order_status') {
        $st = trim($_POST['status'] ?? 'pending');
        $file_path = null;

        if (isset($_FILES['result_file']) && $_FILES['result_file']['error'] === 0) {
            $dir = "../uploads/results/";
            if (!is_dir($dir)) { mkdir($dir, 0755, true); }
            $ext = strtolower(pathinfo($_FILES["result_file"]["name"], PATHINFO_EXTENSION));
            $filename = "res_" . time() . "_" . rand(100, 999) . "." . $ext;
            $dest = $dir . $filename;
            if (move_uploaded_file($_FILES["result_file"]["tmp_name"], $dest)) {
                $file_path = "uploads/results/" . $filename;
            }
        }

        if ($file_path) {
            $up = $conn->prepare("UPDATE orders SET status = ?, result_text = ?, result_file = ? WHERE id = ?");
            $up->bind_param("sssi", $st, $res_text, $file_path, $order_id);
        } else {
            $up = $conn->prepare("UPDATE orders SET status = ?, result_text = ? WHERE id = ?");
            $up->bind_param("ssi", $st, $res_text, $order_id);
        }
        $up->execute();
        $up->close();

        if ($st === 'cancel' && isset($_POST['refund_user']) && $_POST['refund_user'] === '1') {
            $q = $conn->query("SELECT user_id, price FROM orders WHERE id = $order_id LIMIT 1")->fetch_assoc();
            if ($q) {
                $uid = $q['user_id'];
                $pr = floatval($q['price']);
                $conn->query("UPDATE users SET balance = balance + $pr WHERE id = $uid");
            }
        }

        $alert_msg = "অর্ডার স্ট্যাটাস সফলভাবে আপডেট করা হয়েছে!";
        $alert_type = "success";
    } elseif ($action === 'delete_order') {
        $conn->query("DELETE FROM orders WHERE id = $order_id");
        $alert_msg = "অর্ডার মুছে ফেলা হয়েছে!";
        $alert_type = "success";
    }
}

$stat_orders = $conn->query("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as success,
    SUM(CASE WHEN status = 'cancel' THEN 1 ELSE 0 END) as cancel
FROM orders")->fetch_assoc();

$stat_deposits = $conn->query("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
    SUM(CASE WHEN status IN ('failed','cancelled') THEN 1 ELSE 0 END) as failed
FROM deposit_requests")->fetch_assoc();

$stat_server_copy = $conn->query("SELECT COUNT(*) as total FROM server_copy")->fetch_assoc()['total'];

$orders_list = $conn->query("SELECT o.*, u.name as user_name, u.email as user_email, u.mobile as user_mobile 
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id 
    ORDER BY o.id DESC LIMIT 300")->fetch_all(MYSQLI_ASSOC);

$server_copy_list = $conn->query("SELECT * FROM server_copy ORDER BY id DESC LIMIT 200")->fetch_all(MYSQLI_ASSOC);
$history_work_list = $conn->query("SELECT * FROM history_work ORDER BY id DESC LIMIT 200")->fetch_all(MYSQLI_ASSOC);
$deposits_list = $conn->query("SELECT d.*, u.name as user_name, u.email as user_email 
    FROM deposit_requests d 
    LEFT JOIN users u ON d.user_id = u.id 
    ORDER BY d.id DESC LIMIT 200")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Orders, Logs & Server Copy - Sokol Kaj</title>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Hind Siliguri', 'Plus Jakarta Sans', sans-serif; background: #F8FAFC; color: #1E293B; }
        .tab-nav-btn { padding: 9px 18px; border-radius: 12px; font-weight: 700; font-size: 13px; transition: all 0.2s; white-space: nowrap; }
        .tab-nav-btn.active { background: #4F46E5; color: #FFFFFF; box-shadow: 0 4px 14px rgba(79, 70, 229, 0.25); }
        .tab-nav-btn:not(.active) { background: #FFFFFF; color: #475569; border: 1.5px solid #E2E8F0; }
        .tab-nav-btn:not(.active):hover { background: #F1F5F9; }
        .dataTables_wrapper .dataTables_length, .dataTables_wrapper .dataTables_filter { margin-bottom: 14px; font-size: 12px; font-weight: 600; }
        .dataTables_wrapper .dataTables_filter input { border: 1.5px solid #CBD5E1; border-radius: 8px; padding: 4px 10px; outline: none; }
        table.dataTable thead th { background: #1E1B4B; color: #FFFFFF; font-size: 12px; padding: 12px 10px; font-weight: 700; }
        table.dataTable tbody td { font-size: 12.5px; padding: 10px; border-bottom: 1px solid #F1F5F9; vertical-align: middle; }
    </style>
</head>
<body class="p-3 md:p-6">

    <div class="max-w-7xl mx-auto">
        
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-3 mb-6 bg-white p-5 rounded-2xl border border-slate-200/90 shadow-sm">
            <div>
                <h2 class="text-xl font-extrabold text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-boxes-stacked text-indigo-600"></i> অল অর্ডার, সার্ভারকপি ও এপিআই এরর লগ
                </h2>
                <p class="text-xs text-slate-500 mt-1">সিস্টেমের সকল সার্ভিস অর্ডার ডেলিভারি, অটো সার্ভারকপি ও ডিপোজিট হিস্ট্রি পর্যবেক্ষণ করুন</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="all_pricing.php" class="text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 px-3.5 py-2 rounded-xl hover:bg-amber-100 transition">
                    <i class="fa-solid fa-coins"></i> প্রাইসিং কন্ট্রোল
                </a>
                <a href="dashboard.php" class="text-xs font-bold bg-slate-100 text-slate-700 px-3.5 py-2 rounded-xl hover:bg-slate-200 transition">
                    <i class="fa-solid fa-arrow-left"></i> ড্যাশবোর্ড
                </a>
            </div>
        </div>

        <?php if (!empty($alert_msg)): ?>
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl mb-5 text-xs font-bold flex items-center gap-2">
                <i class="fa-solid fa-circle-check text-emerald-600"></i> <?php echo $alert_msg; ?>
            </div>
        <?php endif; ?>

        <!-- স্ট্যাটাস উইজেট -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
            <div class="bg-white p-4 rounded-2xl border border-slate-200/90 shadow-sm">
                <span class="text-[11px] font-bold text-slate-400 block uppercase">পেন্ডিং অর্ডার</span>
                <h3 class="text-2xl font-black text-amber-500 mt-1"><?php echo intval($stat_orders['pending']); ?> টি</h3>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-slate-200/90 shadow-sm">
                <span class="text-[11px] font-bold text-slate-400 block uppercase">সফল অর্ডার</span>
                <h3 class="text-2xl font-black text-emerald-600 mt-1"><?php echo intval($stat_orders['success']); ?> টি</h3>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-slate-200/90 shadow-sm">
                <span class="text-[11px] font-bold text-slate-400 block uppercase">অটো সার্ভারকপি লগ</span>
                <h3 class="text-2xl font-black text-sky-600 mt-1"><?php echo intval($stat_server_copy); ?> টি</h3>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-slate-200/90 shadow-sm">
                <span class="text-[11px] font-bold text-slate-400 block uppercase">ডিপোজিট ফেইল্ড / এরর</span>
                <h3 class="text-2xl font-black text-rose-500 mt-1"><?php echo intval($stat_deposits['failed']); ?> টি</h3>
            </div>
        </div>

        <div class="flex gap-2 overflow-x-auto pb-3 mb-5 border-b border-slate-200">
            <button onclick="switchTab('ordersTab', this)" class="tab-nav-btn active"><i class="fa-solid fa-cart-shopping me-1"></i> লাইভ সার্ভিস অর্ডার (<?php echo count($orders_list); ?>)</button>
            <button onclick="switchTab('serverCopyTab', this)" class="tab-nav-btn"><i class="fa-solid fa-server me-1"></i> সার্ভারকপি লগ (<?php echo count($server_copy_list); ?>)</button>
            <button onclick="switchTab('depositTab', this)" class="tab-nav-btn"><i class="fa-solid fa-money-bill-transfer me-1"></i> ডিপোজিট ও এরর লগ (<?php echo count($deposits_list); ?>)</button>
            <button onclick="switchTab('workHistoryTab', this)" class="tab-nav-btn"><i class="fa-solid fa-clock-rotate-left me-1"></i> ব্যালেন্স হিস্ট্রি (<?php echo count($history_work_list); ?>)</button>
        </div>

        <!-- ১. লাইভ অর্ডার টেবিল -->
        <div id="ordersTab" class="order-content bg-white p-5 rounded-2xl border border-slate-200/90 shadow-sm">
            <div class="overflow-x-auto">
                <table id="tblOrders" class="w-full">
                    <thead>
                        <tr>
                            <th>#ID</th>
                            <th>গ্রাহকের তথ্য</th>
                            <th>সার্ভিসের ধরন</th>
                            <th>প্রদত্ত ইনপুট</th>
                            <th>মূল্য</th>
                            <th>স্ট্যাটাস</th>
                            <th>ডেলিভারি ফাইল/টেক্সট</th>
                            <th>তারিখ ও সময়</th>
                            <th>একশন</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders_list as $o): 
                            $st_badge = 'bg-amber-50 text-amber-700 border border-amber-200';
                            if ($o['status'] === 'success') $st_badge = 'bg-emerald-50 text-emerald-700 border border-emerald-200';
                            if ($o['status'] === 'cancel') $st_badge = 'bg-rose-50 text-rose-700 border border-rose-200';
                        ?>
                        <tr>
                            <td class="font-bold">#<?php echo $o['id']; ?></td>
                            <td>
                                <span class="font-bold block"><?php echo htmlspecialchars($o['user_name'] ?? 'ID: '.$o['user_id']); ?></span>
                                <span class="text-[11px] text-slate-400"><?php echo htmlspecialchars($o['user_email'] ?? $o['user_mobile']); ?></span>
                            </td>
                            <td class="font-semibold text-indigo-900"><?php echo htmlspecialchars($o['order_type']); ?></td>
                            <td class="max-w-xs break-words text-slate-700 font-mono text-[11.5px]"><?php echo htmlspecialchars($o['input_data']); ?></td>
                            <td class="font-bold text-emerald-600">৳<?php echo number_format($o['price'], 2); ?></td>
                            <td><span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold <?php echo $st_badge; ?>"><?php echo htmlspecialchars($o['status']); ?></span></td>
                            <td>
                                <?php if (!empty($o['result_file'])): ?>
                                    <a href="../<?php echo htmlspecialchars($o['result_file']); ?>" target="_blank" class="text-xs text-indigo-600 font-bold hover:underline flex items-center gap-1">
                                        <i class="fa-solid fa-file-arrow-down"></i> ফাইল দেখুন
                                    </a>
                                <?php endif; ?>
                                <?php if (!empty($o['result_text'])): ?>
                                    <span class="text-xs text-slate-600 block"><?php echo htmlspecialchars($o['result_text']); ?></span>
                                <?php endif; ?>
                                <?php if (empty($o['result_file']) && empty($o['result_text'])): ?>
                                    <span class="text-slate-400 text-xs">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-[11px] text-slate-400"><?php echo date('d M Y, h:i A', strtotime($o['created_at'])); ?></td>
                            <td>
                                <button onclick="openOrderModal(<?php echo htmlspecialchars(json_encode($o)); ?>)" class="text-xs bg-indigo-50 text-indigo-600 px-2.5 py-1 rounded-lg font-bold hover:bg-indigo-100">
                                    <i class="fa-solid fa-pen-to-square"></i> আপডেট
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ২. সার্ভার কপি লগ -->
        <div id="serverCopyTab" class="order-content hidden bg-white p-5 rounded-2xl border border-slate-200/90 shadow-sm">
            <div class="overflow-x-auto">
                <table id="tblServerCopy" class="w-full">
                    <thead>
                        <tr>
                            <th>#ID</th>
                            <th>ব্যবহারকারী</th>
                            <th>নাম (বাংলা / ইংরেজি)</th>
                            <th>জাতীয় পরিচয়পত্র / PIN</th>
                            <th>জন্ম তারিখ</th>
                            <th>মোবাইল / ঠিকানা</th>
                            <th>ভার্সন</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($server_copy_list as $sc): ?>
                        <tr>
                            <td class="font-bold">#<?php echo $sc['id']; ?></td>
                            <td class="text-xs text-slate-500"><?php echo htmlspecialchars($sc['user_mail']); ?></td>
                            <td>
                                <span class="font-bold block"><?php echo htmlspecialchars($sc['nameBn']); ?></span>
                                <span class="text-[11px] text-slate-400"><?php echo htmlspecialchars($sc['nameEn']); ?></span>
                            </td>
                            <td class="font-mono text-xs">
                                <div><b>NID:</b> <?php echo htmlspecialchars($sc['nationalId']); ?></div>
                                <div class="text-slate-400"><b>PIN:</b> <?php echo htmlspecialchars($sc['pin']); ?></div>
                            </td>
                            <td class="text-xs"><?php echo htmlspecialchars($sc['dateOfBirth']); ?></td>
                            <td class="text-[11px] max-w-xs truncate" title="<?php echo htmlspecialchars($sc['presentAddressLine'] ?? $sc['permanentAddressLine']); ?>">
                                <?php echo htmlspecialchars($sc['presentAddressLine'] ?? $sc['permanentAddressLine'] ?? '-'); ?>
                            </td>
                            <td><span class="text-[11px] font-bold px-2 py-0.5 rounded bg-sky-50 text-sky-700 border border-sky-200"><?php echo htmlspecialchars($sc['server_version']); ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ৩. ডিপোজিট ও এরর লগ -->
        <div id="depositTab" class="order-content hidden bg-white p-5 rounded-2xl border border-slate-200/90 shadow-sm">
            <div class="overflow-x-auto">
                <table id="tblDeposits" class="w-full">
                    <thead>
                        <tr>
                            <th>#ID</th>
                            <th>ব্যবহারকারী</th>
                            <th>গেটওয়ে / মেথড</th>
                            <th>TrxID</th>
                            <th>পরিমাণ</th>
                            <th>স্ট্যাটাস</th>
                            <th>তারিখ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($deposits_list as $d): 
                            $d_badge = 'bg-amber-50 text-amber-700 border border-amber-200';
                            if ($d['status'] === 'completed') $d_badge = 'bg-emerald-50 text-emerald-700 border border-emerald-200';
                            if (in_array($d['status'], ['failed', 'cancelled'])) $d_badge = 'bg-rose-50 text-rose-700 border border-rose-200';
                        ?>
                        <tr>
                            <td class="font-bold">#<?php echo $d['id']; ?></td>
                            <td>
                                <span class="font-bold block"><?php echo htmlspecialchars($d['user_name'] ?? 'ID: '.$d['user_id']); ?></span>
                                <span class="text-[11px] text-slate-400"><?php echo htmlspecialchars($d['user_email']); ?></span>
                            </td>
                            <td>
                                <span class="uppercase font-bold text-xs"><?php echo htmlspecialchars($d['method']); ?></span>
                                <span class="text-[10px] text-slate-400 block"><?php echo htmlspecialchars($d['gateway']); ?></span>
                            </td>
                            <td class="font-mono text-xs text-indigo-600"><?php echo htmlspecialchars($d['trx_id']); ?></td>
                            <td class="font-bold text-emerald-600">৳<?php echo number_format($d['amount'], 2); ?></td>
                            <td><span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold <?php echo $d_badge; ?>"><?php echo htmlspecialchars($d['status']); ?></span></td>
                            <td class="text-[11px] text-slate-400"><?php echo date('d M Y, h:i A', strtotime($d['created_at'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ৪. ব্যালেন্স কাটার হিস্ট্রি -->
        <div id="workHistoryTab" class="order-content hidden bg-white p-5 rounded-2xl border border-slate-200/90 shadow-sm">
            <div class="overflow-x-auto">
                <table id="tblHistoryWork" class="w-full">
                    <thead>
                        <tr>
                            <th>#ID</th>
                            <th>ইউজার ইমেইল</th>
                            <th>কাজের ধরন</th>
                            <th>চার্জ</th>
                            <th>পূর্বের ব্যালেন্স</th>
                            <th>পরবর্তী ব্যালেন্স</th>
                            <th>সময়</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($history_work_list as $hw): ?>
                        <tr>
                            <td class="font-bold">#<?php echo $hw['id']; ?></td>
                            <td class="text-xs text-slate-600"><?php echo htmlspecialchars($hw['email']); ?></td>
                            <td class="font-semibold text-xs"><?php echo htmlspecialchars($hw['order_type']); ?></td>
                            <td class="font-bold text-rose-600">-৳<?php echo number_format($hw['price'], 2); ?></td>
                            <td class="text-xs">৳<?php echo number_format($hw['current_balance'], 2); ?></td>
                            <td class="text-xs font-bold text-slate-700">৳<?php echo number_format($hw['balance_after_cut'], 2); ?></td>
                            <td class="text-[11px] text-slate-400"><?php echo date('d M Y, h:i A', strtotime($hw['order_time'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- অর্ডার আপডেট মডাল -->
    <div id="orderModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl border">
            <div class="flex items-center justify-between border-b pb-3 mb-4">
                <h4 class="font-bold text-base text-slate-800">অর্ডার স্ট্যাটাস ও ডেলিভারি আপডেট</h4>
                <button type="button" onclick="closeOrderModal()" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>
            <form action="" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action_order" value="update_order_status">
                <input type="hidden" name="order_id" id="modalOrderId" value="">

                <div class="mb-4">
                    <label class="block text-xs font-bold text-slate-600 mb-1">স্ট্যাটাস পরিবর্তন</label>
                    <select name="status" id="modalStatusSelect" class="w-full border rounded-xl p-2.5 text-xs font-bold bg-white" required>
                        <option value="pending">পেন্ডিং (Pending)</option>
                        <option value="success">সফল (Success)</option>
                        <option value="cancel">বাতিল (Cancel)</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-xs font-bold text-slate-600 mb-1">ফলাফল / টেক্সট নোট</label>
                    <textarea name="result_text" id="modalResultText" rows="2" class="w-full border rounded-xl p-2.5 text-xs" placeholder="যেমন: ডেলিভারি সম্পন্ন হয়েছে অথবা বাতিল করার কারণ"></textarea>
                <p class="text-xs text-slate-500 mt-2">জন্ম নিবন্ধনের অর্ডারে কর্তৃপক্ষের ইস্যু করা নম্বর, আবেদন আইডি বা নির্দেশনা এখানে দিন; সনদ থাকলে রেজাল্ট ফাইলও যুক্ত করুন। ব্যবহারকারী তার হিস্টোরিতে টেক্সট ও ফাইল দুটোই পাবেন।</p>
                </div>

                <div class="mb-4">
                    <label class="block text-xs font-bold text-slate-600 mb-1">ডেলিভারি ফাইল (PDF / ছবি)</label>
                    <input type="file" name="result_file" class="w-full border rounded-xl p-2 text-xs">
                </div>

                <div class="mb-5 flex items-center gap-2">
                    <input type="checkbox" name="refund_user" id="refundCheck" value="1" class="rounded text-indigo-600">
                    <label for="refundCheck" class="text-xs text-rose-600 font-bold cursor-pointer">অর্ডার বাতিল হলে টাকা ইউজারের অ্যাকাউন্টে রিফান্ড করুন</label>
                </div>

                <div class="flex gap-2">
                    <button type="button" onclick="closeOrderModal()" class="w-1/3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs py-3 rounded-xl">বাতিল</button>
                    <button type="submit" class="w-2/3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs py-3 rounded-xl shadow">সংরক্ষণ করুন</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#tblOrders').DataTable({ pageLength: 25, order: [[0, 'desc']] });
            $('#tblServerCopy').DataTable({ pageLength: 25, order: [[0, 'desc']] });
            $('#tblDeposits').DataTable({ pageLength: 25, order: [[0, 'desc']] });
            $('#tblHistoryWork').DataTable({ pageLength: 25, order: [[0, 'desc']] });
        });

        function switchTab(tabId, el) {
            $('.order-content').addClass('hidden');
            $('#' + tabId).removeClass('hidden');
            $('.tab-nav-btn').removeClass('active');
            $(el).addClass('active');
        }

        function openOrderModal(order) {
            $('#modalOrderId').val(order.id);
            $('#modalStatusSelect').val(order.status);
            $('#modalResultText').val(order.result_text || '');
            $('#orderModal').removeClass('hidden');
        }

        function closeOrderModal() {
            $('#orderModal').addClass('hidden');
        }
    </script>
</body>
</html>