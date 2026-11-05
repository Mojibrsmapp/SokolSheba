<?php
include_once("header.php");

$filter_status = isset($_GET['status']) ? trim($_GET['status']) : '';$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_order'])) {$order_id = intval($_POST['order_id'] ?? 0);$new_status = trim($_POST['status'] ?? 'pending');$result_text = trim($_POST['result_text'] ?? '');$result_file_path = null;
    if (isset($_FILES['result_file']) && $_FILES['result_file']['error'] === 0) {$upDir = "uploads/results/";
        if (!is_dir($upDir)) {
            mkdir($upDir, 0755, true);
        }
        $ext = strtolower(pathinfo($_FILES["result_file"]["name"], PATHINFO_EXTENSION));
        $allowed = ['pdf', 'jpg', 'jpeg', 'png', 'webp', 'zip', 'txt'];
        if (in_array($ext, $allowed)) {$newFilename = "res_" . $order_id . "_" . time() . "." . $ext;
            $targetPath = $upDir .$newFilename;
            if (move_uploaded_file($_FILES["result_file"]["tmp_name"], $targetPath)) {
                $result_file_path =$targetPath;
            }
        }
    }

    $check_table =$conn->query("SHOW TABLES LIKE 'orders'");
    if ($check_table &&$check_table->num_rows > 0) {
        if (!empty($result_file_path)) {
            $stmt =$conn->prepare("UPDATE orders SET status = ?, result_text = ?, result_file = ? WHERE id = ?");
            $stmt->bind_param("sssi", $new_status,$result_text, $result_file_path,$order_id);
        } else {
            $stmt =$conn->prepare("UPDATE orders SET status = ?, result_text = ? WHERE id = ?");
            $stmt->bind_param("ssi", $new_status, $result_text,$order_id);
        }
        $stmt->execute();$stmt->close();
    }
    
    echo "<script>window.location.href='orders.php?status=" . urlencode($filter_status) . "';</script>";
    exit();
}

$conn->query("CREATE TABLE IF NOT EXISTS `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `order_type` varchar(100) NOT NULL,
  `input_data` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `result_text` text DEFAULT NULL,
  `result_file` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$where_clauses = [];
$params = [];$types = "";

if (!empty($filter_status)) {$where_clauses[] = "o.status = ?";
    $params[] =$filter_status;
    $types .= "s";
}

if (!empty($search_query)) {$where_clauses[] = "(o.input_data LIKE ? OR o.order_type LIKE ? OR u.name LIKE ? OR u.mobile LIKE ?)";
    $like = "\%" . $search_query . "%";
    $params[] =$like;
    $params[] =$like;
    $params[] =$like;
    $params[] =$like;
    $types .= "ssss";
}

$where_sql = !empty($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";

$sql = "SELECT o.*, u.name as user_name, u.mobile as user_phone 
        FROM orders o 
        LEFT JOIN users u ON u.id = o.user_id 
        $where_sql 
        ORDER BY o.created_at DESC LIMIT 100";

$stmt = $conn->prepare($sql);
if (!empty($params)) {$stmt->bind_param($types, ...$params);
}
$stmt->execute();$orders_result = $stmt->get_result();$orders_list = [];
while ($row =$orders_result->fetch_assoc()) {
    $orders_list[] =$row;
}
$stmt->close();

$counts = ['pending' => 0, 'success' => 0, 'cancel' => 0, 'total' => 0];
$c_res =$conn->query("SELECT status, COUNT(id) as cnt FROM orders GROUP BY status");
if ($c_res &&$c_res->num_rows > 0) {
    while ($crow =$c_res->fetch_assoc()) {
        $st =$crow['status'];
        if (isset($counts[$st])) {$counts[$st] = intval($crow['cnt']);
        }
        $counts['total'] += intval($crow['cnt']);
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
        border-radius: 20px;
        border: 1px solid rgba(226, 232, 240, 0.9);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
        padding: 24px;
        margin-bottom: 24px;
    }
    .status-tab {
        padding: 8px 16px;
        border-radius: 12px;
        font-size: 13px;
        font-weight: 700;
        transition: all 0.2s ease;
        text-decoration: none !important;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .status-tab.active {
        background: #4f46e5;
        color: #ffffff !important;
        box-shadow: 0 4px 14px rgba(79, 70, 229, 0.25);
    }
    .status-tab:not(.active) {
        background: #f8fafc;
        color: #475569 !important;
        border: 1px solid #e2e8f0;
    }
    .status-tab:not(.active):hover {
        background: #ffffff;
        border-color: #cbd5e1;
    }
    .orders-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }
    .orders-table th {
        background: #f8fafc;
        color: #475569;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        padding: 14px 16px;
        border-bottom: 1.5px solid #e2e8f0;
    }
    .orders-table td {
        padding: 14px 16px;
        font-size: 13px;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
    }
    .orders-table tbody tr:hover td {
        background: #f8fafc;
    }
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.5);
        backdrop-filter: blur(4px);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }
    .modal-content-card {
        background: #ffffff;
        border-radius: 24px;
        width: 100%;
        max-width: 520px;
        padding: 24px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.15);
    }
</style>

<div class="container-fluid px-3 px-md-4 py-4">

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 mb-4">
        <div>
            <h3 class="text-xl font-extrabold text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-boxes-stacked text-indigo-600"></i> সকল অর্ডার ম্যানেজমেন্ট
            </h3>
            <p class="text-xs text-slate-500 mt-1">ব্যবহারকারীদের সার্ভিস অর্ডারের তথ্য, রেজাল্ট আপলোড ও স্ট্যাটাস নিয়ন্ত্রণ</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="dashboard.php" class="text-xs font-bold bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-3.5 py-2 rounded-xl flex items-center gap-1.5 shadow-sm transition">
                <i class="fa-solid fa-arrow-left"></i> ড্যাশবোর্ড
            </a>
        </div>
    </div>

    <div class="panel-card p-4">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            
            <div class="flex flex-wrap items-center gap-2">
                <a href="orders.php" class="status-tab <?php echo empty($filter_status) ? 'active' : ''; ?>">
                    <i class="fa-solid fa-list-ul"></i> সকল অর্ডার (<?php echo $counts['total']; ?>)
                </a>
                <a href="orders.php?status=pending" class="status-tab <?php echo ($filter_status === 'pending') ? 'active' : ''; ?>">
                    <i class="fa-solid fa-clock text-amber-500"></i> পেন্ডিং (<?php echo $counts['pending']; ?>)
                </a>
                <a href="orders.php?status=success" class="status-tab <?php echo ($filter_status === 'success') ? 'active' : ''; ?>">
                    <i class="fa-solid fa-circle-check text-emerald-500"></i> সফল (<?php echo $counts['success']; ?>)
                </a>
                <a href="orders.php?status=cancel" class="status-tab <?php echo ($filter_status === 'cancel') ? 'active' : ''; ?>">
                    <i class="fa-solid fa-circle-xmark text-rose-500"></i> বাতিল (<?php echo $counts['cancel']; ?>)
                </a>
            </div>

            <form method="GET" action="orders.php" class="flex items-center gap-2 w-full lg:w-auto">
                <?php if (!empty($filter_status)): ?>
                    <input type="hidden" name="status" value="<?php echo htmlspecialchars($filter_status); ?>">
                <?php endif; ?>
                <div class="relative w-full lg:w-64">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search_query); ?>" placeholder="অর্ডার বা গ্রাহক খুঁজুন..." class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-9 pr-3 py-2 text-xs font-semibold outline-none focus:border-indigo-500 focus:bg-white transition">
                </div>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs px-4 py-2 rounded-xl transition shrink-0">
                    খুঁজুন
                </button>
            </form>

        </div>
    </div>

    <div class="panel-card p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>আইডি</th>
                        <th>গ্রাহক</th>
                        <th>সার্ভিস প্রকার</th>
                        <th>ইনপুট তথ্য</th>
                        <th>চার্জ</th>
                        <th>স্ট্যাটাস</th>
                        <th>রেজাল্ট</th>
                        <th>সময়</th>
                        <th class="text-center">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders_list)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-8 text-slate-400 text-xs">কোনো অর্ডার তথ্য পাওয়া যায়নি</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($orders_list as$row): 
                            $st_badge = 'bg-amber-50 text-amber-700 border border-amber-200';$st_text = 'পেন্ডিং';
                            if ($row['status'] === 'success') {
                                $st_badge = 'bg-emerald-50 text-emerald-700 border border-emerald-200';$st_text = 'সফল';
                            } elseif ($row['status'] === 'cancel') {
                                $st_badge = 'bg-rose-50 text-rose-700 border border-rose-200';$st_text = 'বাতিল';
                            }
                        ?>
                        <tr>
                            <td class="font-bold text-slate-800">#<?php echo htmlspecialchars($row['id']); ?></td>
                            <td>
                                <div class="font-bold text-slate-800"><?php echo htmlspecialchars($row['user_name'] ?? 'গ্রাহক'); ?></div>
                                <div class="text-[11px] text-slate-400 font-mono"><?php echo htmlspecialchars($row['user_phone'] ?? ''); ?></div>
                            </td>
                            <td>
                                <span class="bg-indigo-50 text-indigo-700 font-bold px-2 py-0.5 rounded text-xs border border-indigo-100">
                                    <?php echo htmlspecialchars($row['order_type']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="max-w-[200px] truncate text-slate-700 font-medium" title="<?php echo htmlspecialchars($row['input_data']); ?>">
                                    <?php echo htmlspecialchars($row['input_data']); ?>
                                </div>
                            </td>
                            <td class="font-extrabold text-slate-800">৳ <?php echo number_format($row['price'], 2); ?></td>
                            <td>
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold <?php echo $st_badge; ?>">
                                    <?php echo $st_text; ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($row['result_file'])): ?>
                                    <a href="<?php echo htmlspecialchars($row['result_file']); ?>" target="_blank" class="text-xs font-bold text-indigo-600 hover:underline flex items-center gap-1">
                                        <i class="fa-solid fa-file-arrow-down"></i> ফাইল
                                    </a>
                                <?php elseif (!empty($row['result_text'])): ?>
                                    <button type="button" onclick="showTextResult('<?php echo htmlspecialchars(addslashes($row['result_text'])); ?>')" class="text-xs font-bold text-emerald-600 hover:underline flex items-center gap-1">
                                        <i class="fa-solid fa-align-left"></i> টেক্সট
                                    </button>
                                <?php else: ?>
                                    <span class="text-slate-300 text-xs">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-slate-400 text-xs">
                                <?php echo date('d M Y, h:i A', strtotime($row['created_at'])); ?>
                            </td>
                            <td class="text-center">
                                <button type="button" onclick="openOrderModal(<?php echo htmlspecialchars(json_encode($row)); ?>)" class="bg-indigo-50 hover:bg-indigo-600 text-indigo-600 hover:text-white px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1 mx-auto">
                                    <i class="fa-solid fa-pen-to-square"></i> আপডেট
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<div class="modal-overlay" id="orderModal">
    <div class="modal-content-card">
        <div class="flex items-center justify-between border-b pb-3 mb-4">
            <h4 class="font-bold text-slate-800 text-base flex items-center gap-2">
                <i class="fa-solid fa-sliders text-indigo-600"></i> অর্ডার আপডেট (#<span id="modalOrderIdText"></span>)
            </h4>
            <button type="button" onclick="closeOrderModal()" class="text-slate-400 hover:text-slate-700 text-lg">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="order_id" id="modalOrderId">

            <div class="mb-3">
                <label class="block text-xs font-bold text-slate-600 mb-1">সার্ভিস ও ইনপুট তথ্য</label>
                <div class="bg-slate-50 border border-slate-200 p-2.5 rounded-xl text-xs text-slate-700 font-semibold" id="modalOrderInfo"></div>
            </div>

            <div class="mb-3">
                <label class="block text-xs font-bold text-slate-600 mb-1">স্ট্যাটাস পরিবর্তন</label>
                <select name="status" id="modalStatusSelect" class="w-full bg-white border border-slate-300 rounded-xl p-2.5 text-xs font-bold outline-none focus:border-indigo-500">
                    <option value="pending">পেন্ডিং (Pending)</option>
                    <option value="success">সফল (Success)</option>
                    <option value="cancel">বাতিল (Cancel)</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="block text-xs font-bold text-slate-600 mb-1">রেজাল্ট ফাইল আপলোড (PDF / Image)</label>
                <input type="file" name="result_file" class="w-full border border-slate-300 rounded-xl p-2 text-xs">
            </div>

            <div class="mb-4">
                <label class="block text-xs font-bold text-slate-600 mb-1">রেজাল্ট টেক্সট / লিংক (ঐচ্ছিক)</label>
                <textarea name="result_text" id="modalResultText" rows="3" class="w-full border border-slate-300 rounded-xl p-2.5 text-xs outline-none focus:border-indigo-500" placeholder="ফলাফল টেক্সট বা ডাউনলোড লিংক দিন..."></textarea>
            </div>

            <div class="flex items-center justify-end gap-2">
                <button type="button" onclick="closeOrderModal()" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs px-4 py-2.5 rounded-xl transition">
                    বাতিল
                </button>
                <button type="submit" name="update_order" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs px-5 py-2.5 rounded-xl shadow-md transition">
                    সংরক্ষণ করুন
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openOrderModal(data) {
    document.getElementById('modalOrderId').value = data.id;
    document.getElementById('modalOrderIdText').innerText = data.id;
    document.getElementById('modalOrderInfo').innerText = data.order_type + " — " + data.input_data;
    document.getElementById('modalStatusSelect').value = data.status;
    document.getElementById('modalResultText').value = data.result_text || '';
    document.getElementById('orderModal').style.display = 'flex';
}

function closeOrderModal() {
    document.getElementById('orderModal').style.display = 'none';
}

function showTextResult(text) {
    Swal.fire({
        title: 'অর্ডার রেজাল্ট টেক্সট',
        html: `<div style="text-align: left; background: #f8fafc; padding: 12px; border-radius: 10px; font-size: 13px; word-break: break-all;">${text}</div>`,
        confirmButtonColor: '#4f46e5'
    });
}
</script>

<?php include_once("footer.php"); ?>