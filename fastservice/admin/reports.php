<?php
include_once("header.php");

$from_date = isset($_GET['from_date']) && !empty($_GET['from_date']) ? $_GET['from_date'] : date('Y-m-01');
$to_date = isset($_GET['to_date']) && !empty($_GET['to_date']) ? $_GET['to_date'] : date('Y-m-d');

$today_date = date('Y-m-d');
$month_start = date('Y-m-01');

$today_deposit = 0;
$month_deposit = 0;
$total_pending_deposit = 0;
$pending_count = 0;
$bkash_total = 0;
$nagad_total = 0;
$history_list = [];

$check_dep = $conn->query("SHOW TABLES LIKE 'deposit_requests'");
if ($check_dep && $check_dep->num_rows > 0) {
    $stmt1 = $conn->prepare("SELECT SUM(amount) AS total FROM deposit_requests WHERE status = 'completed' AND DATE(created_at) = ?");
    if ($stmt1) {
        $stmt1->bind_param("s", $today_date);
        $stmt1->execute();
        $res1 = $stmt1->get_result()->fetch_assoc();
        $today_deposit = floatval($res1['total'] ?? 0);
        $stmt1->close();
    }

    $stmt2 = $conn->prepare("SELECT SUM(amount) AS total FROM deposit_requests WHERE status = 'completed' AND DATE(created_at) >= ?");
    if ($stmt2) {
        $stmt2->bind_param("s", $month_start);
        $stmt2->execute();
        $res2 = $stmt2->get_result()->fetch_assoc();
        $month_deposit = floatval($res2['total'] ?? 0);
        $stmt2->close();
    }

    $res3 = $conn->query("SELECT COUNT(id) as total_count, SUM(amount) AS total FROM deposit_requests WHERE status = 'pending'");
    if ($res3) {
        $r3 = $res3->fetch_assoc();
        $total_pending_deposit = floatval($r3['total'] ?? 0);
        $pending_count = intval($r3['total_count'] ?? 0);
    }

    $stmt4 = $conn->prepare("SELECT SUM(amount) AS total FROM deposit_requests WHERE status = 'completed' AND method = 'bkash' AND DATE(created_at) BETWEEN ? AND ?");
    if ($stmt4) {
        $stmt4->bind_param("ss", $from_date, $to_date);
        $stmt4->execute();
        $res4 = $stmt4->get_result()->fetch_assoc();
        $bkash_total = floatval($res4['total'] ?? 0);
        $stmt4->close();
    }

    $stmt5 = $conn->prepare("SELECT SUM(amount) AS total FROM deposit_requests WHERE status = 'completed' AND method = 'nagad' AND DATE(created_at) BETWEEN ? AND ?");
    if ($stmt5) {
        $stmt5->bind_param("ss", $from_date, $to_date);
        $stmt5->execute();
        $res5 = $stmt5->get_result()->fetch_assoc();
        $nagad_total = floatval($res5['total'] ?? 0);
        $stmt5->close();
    }

    $stmt6 = $conn->prepare("SELECT d.id, d.method, d.trx_id, d.amount, d.screenshot, d.status, d.created_at, u.name as user_name, u.mobile as user_phone 
                            FROM deposit_requests d 
                            LEFT JOIN users u ON u.id = d.user_id 
                            WHERE DATE(d.created_at) BETWEEN ? AND ? 
                            ORDER BY d.created_at DESC LIMIT 100");
    if ($stmt6) {
        $stmt6->bind_param("ss", $from_date, $to_date);
        $stmt6->execute();
        $res6 = $stmt6->get_result();
        while ($row = $res6->fetch_assoc()) {
            $history_list[] = $row;
        }
        $stmt6->close();
    }
}

$range_total_deposit = $bkash_total + $nagad_total;
?>

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<script src="https://cdn.tailwindcss.com"></script>

<style>
    body {
        background: #f1f5f9 !important;
        font-family: 'Plus Jakarta Sans', 'Hind Siliguri', sans-serif !important;
        color: #1e293b;
    }

    .report-stat-card {
        background: #ffffff;
        border-radius: 20px;
        padding: 22px;
        border: 1px solid rgba(226, 232, 240, 0.9);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }

    .report-stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.06);
    }

    .stat-badge-icon {
        width: 52px;
        height: 52px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
    }

    .filter-panel {
        background: #ffffff;
        border-radius: 20px;
        border: 1px solid rgba(226, 232, 240, 0.9);
        padding: 20px 24px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
        margin-bottom: 24px;
    }

    .table-panel {
        background: #ffffff;
        border-radius: 20px;
        border: 1px solid rgba(226, 232, 240, 0.9);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
        padding: 24px;
    }

    .report-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .report-table th {
        background: #f8fafc;
        color: #475569;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        padding: 14px 16px;
        border-bottom: 1.5px solid #e2e8f0;
    }

    .report-table td {
        padding: 14px 16px;
        font-size: 13.5px;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
    }

    .report-table tbody tr:hover td {
        background: #f8fafc;
    }

    .date-input {
        border: 1.5px solid #cbd5e1;
        border-radius: 12px;
        padding: 9px 14px;
        font-size: 13px;
        font-weight: 600;
        outline: none;
        color: #334155;
        transition: border-color 0.2s;
    }

    .date-input:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
    }
</style>

<div class="container-fluid px-3 px-md-4 py-4">

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 mb-4">
        <div>
            <h3 class="text-xl font-extrabold text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-chart-pie text-indigo-600"></i> সেলস ও ফাইন্যান্স রিপোর্ট
            </h3>
            <p class="text-xs text-slate-500 mt-1">পেমেন্ট কালেকশন, রিচার্জ এবং ট্রানজেকশন অ্যানালিটিক্স</p>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="text-xs font-bold bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-3.5 py-2 rounded-xl flex items-center gap-1.5 shadow-sm transition">
                <i class="fa-solid fa-print"></i> প্রিন্ট রিপোর্ট
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
        <div class="report-stat-card">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">আজকের ডিপোজিট</span>
                <h3 class="text-2xl font-black text-slate-800 mt-1">৳ <?php echo number_format($today_deposit, 2); ?></h3>
                <span class="text-[11px] text-emerald-600 font-semibold"><i class="fa-solid fa-bolt me-1"></i>আজকের কালেকশন</span>
            </div>
            <div class="stat-badge-icon bg-emerald-50 text-emerald-600">
                <i class="fa-solid fa-calendar-day"></i>
            </div>
        </div>

        <div class="report-stat-card">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">চলতি মাসের ডিপোজিট</span>
                <h3 class="text-2xl font-black text-indigo-600 mt-1">৳ <?php echo number_format($month_deposit, 2); ?></h3>
                <span class="text-[11px] text-indigo-500 font-semibold"><i class="fa-solid fa-arrow-trend-up me-1"></i>এই মাসে মোট</span>
            </div>
            <div class="stat-badge-icon bg-indigo-50 text-indigo-600">
                <i class="fa-solid fa-calendar-check"></i>
            </div>
        </div>

        <div class="report-stat-card">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">ফিল্টারকৃত মোট কালেকশন</span>
                <h3 class="text-2xl font-black text-slate-800 mt-1">৳ <?php echo number_format($range_total_deposit, 2); ?></h3>
                <span class="text-[11px] text-slate-500 font-semibold">নির্বাচিত তারিখ অনুযায়ী</span>
            </div>
            <div class="stat-badge-icon bg-sky-50 text-sky-600">
                <i class="fa-solid fa-wallet"></i>
            </div>
        </div>

        <div class="report-stat-card">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">পেন্ডিং ডিপোজিট</span>
                <h3 class="text-2xl font-black text-amber-600 mt-1">৳ <?php echo number_format($total_pending_deposit, 2); ?></h3>
                <span class="text-[11px] text-amber-600 font-bold"><?php echo $pending_count; ?> টি অর্ডার অপেক্ষমান</span>
            </div>
            <div class="stat-badge-icon bg-amber-50 text-amber-600">
                <i class="fa-solid fa-clock-rotate-left"></i>
            </div>
        </div>
    </div>

    <div class="filter-panel">
        <form method="GET" action="reports.php" class="flex flex-wrap items-end gap-3 justify-between">
            <div class="flex flex-wrap items-center gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">তারিখ থেকে</label>
                    <input type="date" name="from_date" value="<?php echo htmlspecialchars($from_date); ?>" class="date-input">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">তারিখ পর্যন্ত</label>
                    <input type="date" name="to_date" value="<?php echo htmlspecialchars($to_date); ?>" class="date-input">
                </div>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs px-5 py-2.5 rounded-xl shadow-md transition flex items-center gap-1.5 self-end">
                    <i class="fa-solid fa-filter"></i> ফিল্টার করুন
                </button>
            </div>

            <div class="flex items-center gap-3 self-end">
                <div class="bg-slate-50 border border-slate-200 px-3 py-2 rounded-xl text-xs font-semibold text-slate-700">
                    <span class="text-pink-600 font-extrabold">bKash:</span> ৳ <?php echo number_format($bkash_total, 2); ?>
                </div>
                <div class="bg-slate-50 border border-slate-200 px-3 py-2 rounded-xl text-xs font-semibold text-slate-700">
                    <span class="text-amber-600 font-extrabold">Nagad:</span> ৳ <?php echo number_format($nagad_total, 2); ?>
                </div>
            </div>
        </form>
    </div>

    <div class="table-panel">
        <div class="flex items-center justify-between mb-4 pb-2 border-b">
            <h5 class="font-extrabold text-slate-800 text-sm md:text-base flex items-center gap-2">
                <i class="fa-solid fa-list-check text-indigo-600"></i> ট্রানজেকশন ও রিচার্জ রিপোর্ট তালিকা
            </h5>
            <span class="text-xs text-slate-500 font-medium">সর্বোচ্চ ১০০ টি সাম্প্রতিক রেকর্ড</span>
        </div>

        <div class="overflow-x-auto rounded-xl border border-slate-200">
            <table class="report-table">
                <thead>
                    <tr>
                        <th>আইডি</th>
                        <th>গ্রাহকের তথ্য</th>
                        <th>মেথড</th>
                        <th>ট্রানজেকশন আইডি</th>
                        <th>পরিমাণ</th>
                        <th>স্ক্রিনশট</th>
                        <th>স্ট্যাটাস</th>
                        <th>তারিখ ও সময়</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($history_list)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-6 text-slate-400 text-xs">নির্বাচিত তারিখে কোনো ট্রানজেকশন পাওয়া যায়নি</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($history_list as $row): 
                            $status_class = 'bg-slate-100 text-slate-700';
                            $status_text = 'অন্যান্য';
                            if ($row['status'] === 'completed') {
                                $status_class = 'bg-emerald-50 text-emerald-700 border border-emerald-200';
                                $status_text = 'সফল';
                            } elseif ($row['status'] === 'pending') {
                                $status_class = 'bg-amber-50 text-amber-700 border border-amber-200';
                                $status_text = 'পেন্ডিং';
                            } elseif ($row['status'] === 'failed' || $row['status'] === 'cancelled') {
                                $status_class = 'bg-rose-50 text-rose-700 border border-rose-200';
                                $status_text = 'বাতিল';
                            }
                        ?>
                        <tr>
                            <td class="font-extrabold text-slate-800">#<?php echo htmlspecialchars($row['id']); ?></td>
                            <td>
                                <div class="font-bold text-slate-800 text-xs"><?php echo htmlspecialchars($row['user_name'] ?? 'গ্রাহক'); ?></div>
                                <div class="text-[11px] text-slate-400 font-mono"><?php echo htmlspecialchars($row['user_phone'] ?? ''); ?></div>
                            </td>
                            <td>
                                <span class="uppercase text-xs font-black tracking-wider px-2 py-0.5 rounded <?php echo ($row['method'] === 'bkash') ? 'bg-pink-50 text-pink-600 border border-pink-200' : 'bg-orange-50 text-orange-600 border border-orange-200'; ?>">
                                    <?php echo htmlspecialchars($row['method']); ?>
                                </span>
                            </td>
                            <td>
                                <code class="text-xs bg-slate-100 px-2 py-1 rounded font-bold text-slate-700 select-all"><?php echo htmlspecialchars($row['trx_id']); ?></code>
                            </td>
                            <td class="font-extrabold text-slate-800">৳ <?php echo number_format($row['amount'], 2); ?></td>
                            <td>
                                <?php if (!empty($row['screenshot'])): ?>
                                    <a href="<?php echo htmlspecialchars($row['screenshot']); ?>" target="_blank" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 flex items-center gap-1">
                                        <i class="fa-solid fa-image"></i> দেখুন
                                    </a>
                                <?php else: ?>
                                    <span class="text-slate-400 text-xs">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold <?php echo $status_class; ?>">
                                    <?php echo $status_text; ?>
                                </span>
                            </td>
                            <td class="text-slate-500 text-xs">
                                <?php echo date('d M Y, h:i A', strtotime($row['created_at'])); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php include_once("footer.php"); ?>