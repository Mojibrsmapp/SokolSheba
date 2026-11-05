<?php
include_once("header.php");

$msg = "";
$err = "";

if (isset($_GET['action']) && isset($_GET['id'])) {
    $service_id = intval($_GET['id']);
    $action = trim($_GET['action']);

    if ($action === 'delete') {
        $del_stmt =$conn->prepare("DELETE FROM custom_services WHERE id = ?");
        $del_stmt->bind_param("i", $service_id);
        if ($del_stmt->execute()) {$msg = "সার্ভিসটি সফলভাবে মুছে ফেলা হয়েছে!";
        } else {
            $err = "সার্ভিস মুছতে সমস্যা হয়েছে!";
        }
        $del_stmt->close();
    } elseif ($action === 'toggle_status') {$new_status = (isset($_GET['to']) &&$_GET['to'] === 'active') ? 'active' : 'inactive';
        $up_stmt =$conn->prepare("UPDATE custom_services SET status = ? WHERE id = ?");
        $up_stmt->bind_param("si", $new_status,$service_id);
        if ($up_stmt->execute()) {$msg = "স্ট্যাটাস সফলভাবে আপডেট করা হয়েছে!";
        } else {
            $err = "স্ট্যাটাস পরিবর্তন ব্যর্থ হয়েছে!";
        }
        $up_stmt->close();
    } elseif ($action === 'toggle_pin') {$new_pin = (isset($_GET['to']) &&$_GET['to'] == '1') ? 1 : 0;
        $pin_stmt =$conn->prepare("UPDATE custom_services SET is_pinned = ? WHERE id = ?");
        $pin_stmt->bind_param("ii", $new_pin,$service_id);
        if ($pin_stmt->execute()) {$msg = "পিন স্ট্যাটাস পরিবর্তন করা হয়েছে!";
        } else {
            $err = "পিন আপডেট ব্যর্থ হয়েছে!";
        }
        $pin_stmt->close();
    }
}

$conn->query("CREATE TABLE IF NOT EXISTS `custom_services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_name` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL DEFAULT 'সাধারণ সার্ভিস',
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `service_type` varchar(50) NOT NULL DEFAULT 'custom',
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `is_pinned` tinyint(1) NOT NULL DEFAULT 0,
  `description` text DEFAULT NULL,
  `access_link` text DEFAULT NULL,
  `service_image` varchar(255) DEFAULT NULL,
  `custom_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';$where_sql = "";
$params = [];$types = "";

if (!empty($search_query)) {$where_sql = "WHERE service_name LIKE ? OR category LIKE ? OR description LIKE ?";
    $like = "\%" . $search_query . "%";
    $params[] =$like;
    $params[] =$like;
    $params[] =$like;
    $types = "sss";
}

$sql = "SELECT * FROM custom_services $where_sql ORDER BY is_pinned DESC, id DESC";
$stmt = $conn->prepare($sql);
if (!empty($params)) {$stmt->bind_param($types, ...$params);
}
$stmt->execute();$services_result = $stmt->get_result();$services_list = [];
while ($row =$services_result->fetch_assoc()) {
    $services_list[] =$row;
}
$stmt->close();
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
        border-radius: 18px;
        border: 1px solid rgba(226, 232, 240, 0.9);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
        margin-bottom: 20px;
    }
    .compact-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }
    .compact-table th {
        background: #f8fafc;
        color: #475569;
        font-size: 11.5px;
        font-weight: 700;
        text-transform: uppercase;
        padding: 12px 14px;
        border-bottom: 1.5px solid #e2e8f0;
        white-space: nowrap;
    }
    .compact-table td {
        padding: 10px 14px;
        font-size: 12.5px;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
        vertical-align: middle;
        white-space: nowrap;
    }
    .compact-table tbody tr:hover td {
        background: #f8fafc;
    }
</style>

<div class="container-fluid px-3 px-md-4 py-3">

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 mb-4">
        <div>
            <h3 class="text-xl font-extrabold text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-layer-group text-indigo-600"></i> সার্ভিস তালিকা ও নিয়ন্ত্রণ
            </h3>
            <p class="text-xs text-slate-500 mt-1">কাস্টম এবং API সার্ভিসের মূল্য, স্ট্যাটাস, পিন ও এডিট নিয়ন্ত্রণ করুন</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="add_service.php" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs px-3.5 py-2 rounded-xl shadow-sm transition flex items-center gap-1.5">
                <i class="fa-solid fa-plus-circle"></i> নতুন সার্ভিস যোগ
            </a>
            <a href="dashboard.php" class="text-xs font-bold bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-3.5 py-2 rounded-xl flex items-center gap-1.5 shadow-sm transition">
                <i class="fa-solid fa-arrow-left"></i> ড্যাশবোর্ড
            </a>
        </div>
    </div>

    <?php if(!empty($msg)): ?>
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-3.5 py-2.5 rounded-xl mb-3 text-xs font-bold flex items-center gap-2">
            <i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i> <?php echo $msg; ?>
        </div>
    <?php elseif(!empty($err)): ?>
        <div class="bg-rose-50 border border-rose-200 text-rose-800 px-3.5 py-2.5 rounded-xl mb-3 text-xs font-bold flex items-center gap-2">
            <i class="fa-solid fa-circle-exclamation text-rose-600 text-sm"></i> <?php echo $err; ?>
        </div>
    <?php endif; ?>

    <div class="panel-card p-3">
        <form method="GET" action="services_list.php" class="flex items-center justify-between gap-3">
            <div class="relative w-full max-w-sm">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" name="search" value="<?php echo htmlspecialchars($search_query); ?>" placeholder="সার্ভিস বা ক্যাটাগরি খুঁজুন..." class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-9 pr-3 py-2 text-xs font-semibold outline-none focus:border-indigo-500 focus:bg-white transition">
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs px-4 py-2 rounded-xl transition">
                    অনুসন্ধান
                </button>
                <?php if (!empty($search_query)): ?>
                    <a href="services_list.php" class="bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-xs px-3 py-2 rounded-xl transition">
                        রিসেট
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="panel-card p-0 overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
            <span class="text-xs font-extrabold text-slate-700 uppercase tracking-wider">বিদ্যমান সার্ভিস তালিকা</span>
            <span class="text-[11px] font-bold text-slate-400">মোট সার্ভিস: <?php echo count($services_list); ?> টি</span>
        </div>

        <div class="overflow-x-auto">
            <table class="compact-table">
                <thead>
                    <tr>
                        <th>ছবি</th>
                        <th>সার্ভিসের নাম</th>
                        <th>ক্যাটাগরি</th>
                        <th>ধরন</th>
                        <th>মূল্য</th>
                        <th>পিন</th>
                        <th>স্ট্যাটাস</th>
                        <th>ফিল্ড সংখ্যা</th>
                        <th>তৈরির তারিখ</th>
                        <th class="text-center">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($services_list)): ?>
                        <tr>
                            <td colspan="10" class="text-center py-8 text-slate-400 text-xs">
                                কোনো সার্ভিস পাওয়া যায়নি!
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($services_list as $row):$fields = json_decode($row['custom_fields'] ?? '[]', true);$field_count = is_array($fields) ? count($fields) : 0;
                            $img_src = !empty($row['service_image']) ?$row['service_image'] : '/assets/logo.jpg';
                        ?>
                        <tr>
                            <td>
                                <img src="<?php echo htmlspecialchars($img_src); ?>" class="w-8 h-8 rounded-lg object-cover border border-slate-200" alt="Icon">
                            </td>
                            <td>
                                <div class="font-bold text-slate-800 text-xs"><?php echo htmlspecialchars($row['service_name']); ?></div>
                                <?php if (!empty($row['description'])): ?>
                                    <div class="text-[11px] text-slate-400 truncate max-w-xs"><?php echo htmlspecialchars($row['description']); ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="bg-indigo-50 text-indigo-700 text-[11px] font-bold px-2 py-0.5 rounded-md border border-indigo-100">
                                    <?php echo htmlspecialchars($row['category'] ?? 'সাধারণ'); ?>
                                </span>
                            </td>
                            <td>
                                <span class="text-[11px] font-bold px-2 py-0.5 rounded <?php echo ($row['service_type'] === 'api') ? 'bg-purple-50 text-purple-700 border border-purple-200' : 'bg-slate-100 text-slate-700'; ?>">
                                    <?php echo ($row['service_type'] === 'api') ? 'API Service' : 'কাস্টম'; ?>
                                </span>
                            </td>
                            <td class="font-extrabold text-emerald-600 text-xs">
                                ৳ <?php echo number_format($row['price'], 2); ?>
                            </td>
                            <td>
                                <?php if ($row['is_pinned'] == 1): ?>
                                    <a href="services_list.php?action=toggle_pin&id=<?php echo $row['id']; ?>&to=0" title="আনপিন করুন" class="text-amber-500 hover:text-amber-600 text-sm">
                                        <i class="fa-solid fa-thumbtack"></i>
                                    </a>
                                <?php else: ?>
                                    <a href="services_list.php?action=toggle_pin&id=<?php echo $row['id']; ?>&to=1" title="পিন করুন" class="text-slate-300 hover:text-slate-400 text-sm">
                                        <i class="fa-solid fa-thumbtack"></i>
                                    </a>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($row['status'] === 'active'): ?>
                                    <a href="services_list.php?action=toggle_status&id=<?php echo $row['id']; ?>&to=inactive" class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200" title="বন্ধ করতে ক্লিক করুন">
                                        চালু
                                    </a>
                                <?php elseif ($row['status'] === 'inactive'): ?>
                                    <a href="services_list.php?action=toggle_status&id=<?php echo $row['id']; ?>&to=active" class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200" title="চালু করতে ক্লিক করুন">
                                        বন্ধ
                                    </a>
                                <?php else: ?>
                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-500">হাইড</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="bg-slate-100 text-slate-700 text-[11px] font-bold px-2 py-0.5 rounded-md">
                                    <?php echo $field_count; ?> টি
                                </span>
                            </td>
                            <td class="text-slate-400 text-[11px]">
                                <?php echo date('d M Y', strtotime($row['created_at'])); ?>
                            </td>
                            <td class="text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="edit_service.php?id=<?php echo $row['id']; ?>" class="bg-indigo-50 hover:bg-indigo-600 text-indigo-600 hover:text-white p-1.5 rounded-lg text-xs transition" title="এডিট করুন">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <button onclick="confirmDelete(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars(addslashes($row['service_name'])); ?>')" class="bg-rose-50 hover:bg-rose-500 text-rose-600 hover:text-white p-1.5 rounded-lg text-xs transition" title="মুছে ফেলুন">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </div>
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
function confirmDelete(id, name) {
    Swal.fire({
        title: 'সার্ভিসটি মুছতে চান?',
        text: `"${name}" মুছে ফেললে ইউজাররা এটি আর দেখতে পারবে না।`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#f43f5e',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'হ্যাঁ, মুছুন',
        cancelButtonText: 'বাতিল'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `services_list.php?action=delete&id=${id}`;
        }
    });
}
</script>

<?php include_once("footer.php"); ?>