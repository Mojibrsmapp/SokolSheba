<?php
include_once("header.php");

$msg = "";
$err = "";

if (isset($_GET['action']) && isset($_GET['id'])) {
    $dep_id = intval($_GET['id']);
    $action = trim($_GET['action']);

    $chk = $conn->prepare("SELECT user_id, amount, status FROM deposit_requests WHERE id = ? LIMIT 1");
    $chk->bind_param("i", $dep_id);
    $chk->execute();
    $dep_data = $chk->get_result()->fetch_assoc();
    $chk->close();

    if ($dep_data) {
        if ($dep_data['status'] !== 'pending') {$err = "এই রিকোয়েস্টটি ইতিমধ্যে প্রসেস করা হয়েছে!";
        } else {
            $user_id = intval($dep_data['user_id']);
            $amount = floatval($dep_data['amount']);

            if ($action === 'approve') {$conn->begin_transaction();
                try {
                    $up1 =$conn->prepare("UPDATE deposit_requests SET status = 'completed' WHERE id = ?");
                    $up1->bind_param("i", $dep_id);
                    $up1->execute();$up1->close();

                    $up2 =$conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
                    $up2->bind_param("di", $amount,$user_id);
                    $up2->execute();$up2->close();

                    $conn->commit();$msg = "রিচার্জ সফলভাবে অনুমোদন করা হয়েছে এবং ব্যবহারকারীর অ্যাকাউন্টে ব্যালেন্স যোগ হয়েছে!";
                } catch (Exception $e) {
                    $conn->rollback();$err = "ব্যালেন্স যোগ করতে সমস্যা হয়েছে!";
                }
            } elseif ($action === 'reject') {
                $up =$conn->prepare("UPDATE deposit_requests SET status = 'failed' WHERE id = ?");
                $up->bind_param("i", $dep_id);$up->execute();
                $up->close();$msg = "রিচার্জ রিকোয়েস্টটি বাতিল করা হয়েছে।";
            }
        }
    } else {
        $err = "রিকোয়েস্ট খুঁজে পাওয়া যায়নি!";
    }
}

$status_filter = isset($_GET['status']) ? trim($_GET['status']) : 'pending';
$method_filter = isset($_GET['method']) ? trim($_GET['method']) : '';$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

$where_clauses = [];
$params = [];$types = "";

if ($status_filter !== 'all') {$where_clauses[] = "d.status = ?";
    $params[] =$status_filter;
    $types .= "s";
}

if (!empty($method_filter)) {$where_clauses[] = "d.method = ?";
    $params[] =$method_filter;
    $types .= "s";
}

if (!empty($search_query)) {$where_clauses[] = "(d.trx_id LIKE ? OR u.name LIKE ? OR u.mobile LIKE ?)";
    $like = "%" . $search_query . "%";
    $params[] =$like;
    $params[] =$like;
    $params[] =$like;
    $types .= "sss";
}

$where_sql = !empty($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";

$sql = "SELECT d.*, u.name as user_name, u.mobile as user_phone 
        FROM deposit_requests d 
        LEFT JOIN users u ON u.id = d.user_id 
        $where_sql 
        ORDER BY d.id DESC, d.created_at DESC LIMIT 150";

$stmt = $conn->prepare($sql);
if (!empty($params)) {$stmt->bind_param($types, ...$params);
}
$stmt->execute();$deposits_result = $stmt->get_result();$deposits_list = [];
while ($row =$deposits_result->fetch_assoc()) {
    $deposits_list[] =$row;
}
$stmt->close();

$counts = ['pending' => 0, 'completed' => 0, 'failed' => 0, 'all' => 0];
$c_res =$conn->query("SELECT status, COUNT(id) as cnt FROM deposit_requests GROUP BY status");
if ($c_res &&$c_res->num_rows > 0) {
    while ($crow =$c_res->fetch_assoc()) {
        $st =$crow['status'];
        if (isset($counts[$st])) {$counts[$st] = intval($crow['cnt']);
        }
        $counts['all'] += intval($crow['cnt']);
    }
}
?>

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    body {
        background: #f1f5f9 !important;
        font-family: 'Plus Jakarta Sans', 'Hind Siliguri', sans-serif !important;
        color: #1e293b;
    }
    .panel-card {
        background: #ffffff;
        border-radius: 16px;
        border: 1px solid rgba(226, 232, 240, 0.9);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
        margin-bottom: 16px;
    }
    .compact-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        table-layout: auto;
    }
    .compact-table th {
        background: #f8fafc;
        color: #475569;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        padding: 10px 12px;
        border-bottom: 1.5px solid #e2e8f0;
        white-space: nowrap;
    }
    .compact-table td {
        padding: 8px 12px;
        font-size: 12.5px;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
        vertical-align: middle;
        white-space: nowrap;
    }
    .compact-table tbody tr:hover td {
        background: #f8fafc;
    }
    .filter-btn-pill {
        padding: 6px 14px;
        border-radius: 10px;
        font-size: 12px;
        font-weight: 700;
        transition: all 0.2s ease;
        text-decoration: none !important;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    .filter-btn-pill.active {
        background: #4f46e5;
        color: #ffffff !important;
        box-shadow: 0 3px 10px rgba(79, 70, 229, 0.25);
    }
    .filter-btn-pill:not(.active) {
        background: #f8fafc;
        color: #475569 !important;
        border: 1px solid #e2e8f0;
    }
    .filter-btn-pill:not(.active):hover {
        background: #ffffff;
        border-color: #cbd5e1;
    }
</style>

<div class="container-fluid px-2 px-md-3 py-3">

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-2 mb-3">
        <div>
            <h3 class="text-lg font-extrabold text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-money-bill-transfer text-indigo-600"></i> ডিপোজিট ও রিচার্জ ব্যবস্থাপনা
            </h3>
        </div>
        <div class="flex items-center gap-2">
            <a href="reports.php" class="text-xs font-bold bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-3 py-1.5 rounded-lg flex items-center gap-1.5 shadow-sm transition">
                <i class="fa-solid fa-chart-pie"></i> সেলস রিপোর্ট
            </a>
            <a href="dashboard.php" class="text-xs font-bold bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-3 py-1.5 rounded-lg flex items-center gap-1.5 shadow-sm transition">
                <i class="fa-solid fa-arrow-left"></i> ড্যাশবোর্ড
            </a>
        </div>
    </div>

    <?php if(!empty($msg)): ?>
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-3 py-2 rounded-xl mb-3 text-xs font-bold flex items-center gap-2">
            <i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i> <?php echo $msg; ?>
        </div>
    <?php elseif(!empty($err)): ?>
        <div class="bg-rose-50 border border-rose-200 text-rose-800 px-3 py-2 rounded-xl mb-3 text-xs font-bold flex items-center gap-2">
            <i class="fa-solid fa-circle-exclamation text-rose-600 text-sm"></i> <?php echo $err; ?>
        </div>
    <?php endif; ?>

    <div class="panel-card p-3">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
            
            <div class="flex flex-wrap items-center gap-1.5">
                <a href="manage_deposits.php?status=pending&method=<?php echo urlencode($method_filter); ?>&search=<?php echo urlencode($search_query); ?>" class="filter-btn-pill <?php echo ($status_filter === 'pending') ? 'active' : ''; ?>">
                    <i class="fa-solid fa-clock text-amber-500"></i> পেন্ডিং (<?php echo $counts['pending']; ?>)
                </a>
                <a href="manage_deposits.php?status=completed&method=<?php echo urlencode($method_filter); ?>&search=<?php echo urlencode($search_query); ?>" class="filter-btn-pill <?php echo ($status_filter === 'completed') ? 'active' : ''; ?>">
                    <i class="fa-solid fa-circle-check text-emerald-500"></i> অনুমোদিত (<?php echo $counts['completed']; ?>)
                </a>
                <a href="manage_deposits.php?status=failed&method=<?php echo urlencode($method_filter); ?>&search=<?php echo urlencode($search_query); ?>" class="filter-btn-pill <?php echo ($status_filter === 'failed') ? 'active' : ''; ?>">
                    <i class="fa-solid fa-circle-xmark text-rose-500"></i> বাতিল (<?php echo $counts['failed']; ?>)
                </a>
                <a href="manage_deposits.php?status=all&method=<?php echo urlencode($method_filter); ?>&search=<?php echo urlencode($search_query); ?>" class="filter-btn-pill <?php echo ($status_filter === 'all') ? 'active' : ''; ?>">
                    <i class="fa-solid fa-list-ul"></i> সকল (<?php echo $counts['all']; ?>)
                </a>
            </div>

            <form method="GET" action="manage_deposits.php" class="flex flex-wrap items-center gap-2 w-full lg:w-auto">
                <input type="hidden" name="status" value="<?php echo htmlspecialchars($status_filter); ?>">
                
                <select name="method" class="bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs font-bold outline-none focus:border-indigo-500 focus:bg-white transition">
                    <option value="">সকল মেথড</option>
                    <option value="bkash" <?php echo ($method_filter === 'bkash') ? 'selected' : ''; ?>>বিকাশ</option>
                    <option value="nagad" <?php echo ($method_filter === 'nagad') ? 'selected' : ''; ?>>নগদ</option>
                </select>

                <div class="relative flex-1 lg:w-48">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search_query); ?>" placeholder="TrxID, নাম, ফোন..." class="w-full bg-slate-50 border border-slate-200 rounded-lg pl-8 pr-2.5 py-1.5 text-xs font-semibold outline-none focus:border-indigo-500 focus:bg-white transition">
                </div>

                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs px-3.5 py-1.5 rounded-lg transition shrink-0">
                    ফিল্টার
                </button>
                
                <?php if (!empty($method_filter) || !empty($search_query) ||$status_filter !== 'pending'): ?>
                    <a href="manage_deposits.php" class="bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-xs px-2.5 py-1.5 rounded-lg transition shrink-0">
                        রিসেট
                    </a>
                <?php endif; ?>
            </form>

        </div>
    </div>

    <div class="panel-card p-0 overflow-hidden">
        <div class="px-3 py-2.5 border-b border-slate-100 flex items-center justify-between">
            <span class="text-xs font-extrabold text-slate-700 uppercase tracking-wider">ডিপোজিট রিকোয়েস্ট তালিকা</span>
            <span class="text-[11px] font-bold text-slate-400">মোট: <?php echo count($deposits_list); ?> টি</span>
        </div>

        <div class="overflow-x-auto">
            <table class="compact-table">
                <thead>
                    <tr>
                        <th>আইডি</th>
                        <th>গ্রাহকের তথ্য</th>
                        <th>মেথড</th>
                        <th>ট্রানজেকশন আইডি (TrxID)</th>
                        <th>পরিমাণ</th>
                        <th>স্ক্রিনশট</th>
                        <th>স্ট্যাটাস</th>
                        <th>তারিখ ও সময়</th>
                        <th class="text-center">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($deposits_list)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-6 text-slate-400 text-xs">
                                কোনো ডিপোজিট ডাটা পাওয়া যায়নি!
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($deposits_list as$row): 
                            $badge = 'bg-amber-50 text-amber-700 border border-amber-200';$badge_text = 'পেন্ডিং';
                            if ($row['status'] === 'completed') {
                                $badge = 'bg-emerald-50 text-emerald-700 border border-emerald-200';$badge_text = 'অনুমোদিত';
                            } elseif ($row['status'] === 'failed') {
                                $badge = 'bg-rose-50 text-rose-700 border border-rose-200';$badge_text = 'বাতিল';
                            }
                        ?>
                        <tr>
                            <td class="font-bold text-slate-800">#<?php echo htmlspecialchars($row['id']); ?></td>
                            <td>
                                <div class="font-bold text-slate-800 text-xs"><?php echo htmlspecialchars($row['user_name'] ?? 'অজানা'); ?></div>
                                <div class="text-[10.5px] text-slate-400 font-mono"><?php echo htmlspecialchars($row['user_phone'] ?? ''); ?></div>
                            </td>
                            <td>
                                <span class="uppercase text-[11px] font-black tracking-wider px-2 py-0.5 rounded <?php echo ($row['method'] === 'bkash') ? 'bg-pink-50 text-pink-600 border border-pink-200' : 'bg-orange-50 text-orange-600 border border-orange-200'; ?>">
                                    <?php echo htmlspecialchars($row['method']); ?>
                                </span>
                            </td>
                            <td>
                                <code class="text-xs bg-slate-100 px-2 py-0.5 rounded font-bold text-slate-800 tracking-wider select-all"><?php echo htmlspecialchars($row['trx_id']); ?></code>
                            </td>
                            <td class="font-extrabold text-emerald-600 text-sm">৳ <?php echo number_format($row['amount'], 2); ?></td>
                            <td>
                                <?php if (!empty($row['screenshot'])): ?>
                                    <a href="<?php echo htmlspecialchars($row['screenshot']); ?>" target="_blank" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 flex items-center gap-1">
                                        <i class="fa-solid fa-image"></i> দেখুন
                                    </a>
                                <?php else: ?>
                                    <span class="text-slate-300 text-xs">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold <?php echo $badge; ?>">
                                    <?php echo $badge_text; ?>
                                </span>
                            </td>
                            <td class="text-slate-500 text-[11px]">
                                <?php echo date('d M Y, h:i A', strtotime($row['created_at'])); ?>
                            </td>
                            <td class="text-center">
                                <?php if ($row['status'] === 'pending'): ?>
                                    <div class="flex items-center justify-center gap-1">
                                        <button onclick="confirmApproval(<?php echo $row['id']; ?>, '<?php echo number_format($row['amount'], 2); ?>', '<?php echo htmlspecialchars(addslashes($row['user_name'] ?? 'গ্রাহক')); ?>')" class="bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-[11px] px-2.5 py-1 rounded-lg shadow-sm transition flex items-center gap-1">
                                            <i class="fa-solid fa-check"></i> অ্যাপ্রুভ
                                        </button>
                                        <button onclick="confirmReject(<?php echo $row['id']; ?>)" class="bg-rose-50 hover:bg-rose-500 text-rose-600 hover:text-white font-bold text-[11px] px-2 py-1 rounded-lg transition flex items-center gap-1">
                                            <i class="fa-solid fa-xmark"></i> বাতিল
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <span class="text-[11px] font-bold text-slate-400">প্রসেসড</span>
                                <?php endif; ?>
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
function confirmApproval(id, amount, name) {
    Swal.fire({
        title: 'রিচার্জ অনুমোদন করবেন?',
        text: `${name}-এর অ্যাকাউন্টে ৳ ${amount} যোগ করা হবে।`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'হ্যাঁ, অনুমোদন করুন',
        cancelButtonText: 'বাতিল'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `manage_deposits.php?action=approve&id=${id}&status=<?php echo urlencode($status_filter); ?>&method=<?php echo urlencode($method_filter); ?>&search=<?php echo urlencode($search_query); ?>`;
        }
    });
}

function confirmReject(id) {
    Swal.fire({
        title: 'রিকোয়েস্টটি বাতিল করবেন?',
        text: 'বাতিল করলে ইউজারের অ্যাকাউন্টে কোনো ব্যালেন্স যোগ হবে না।',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#f43f5e',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'হ্যাঁ, বাতিল করুন',
        cancelButtonText: 'না'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `manage_deposits.php?action=reject&id=${id}&status=<?php echo urlencode($status_filter); ?>&method=<?php echo urlencode($method_filter); ?>&search=<?php echo urlencode($search_query); ?>`;
        }
    });
}
</script>

<?php include_once("footer.php"); ?>