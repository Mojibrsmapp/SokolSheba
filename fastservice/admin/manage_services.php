<?php
error_reporting(0);
session_start();
include_once("../includes/configuration.php");

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) &&$_POST['action'] === 'toggle_status') {
    header('Content-Type: application/json');
    $table = trim($_POST['table'] ?? '');
    $item_id = intval($_POST['id'] ?? 0);
    $new_status = trim($_POST['status'] ?? '');

    $allowed_tables = [
        'location_services',
        'call_sms_services',
        'imei_services',
        'mfs_services',
        'number_to_server_services',
        'order_text_services',
        'order_file_services',
        'custom_services',
        'prices'
    ];

    if (!in_array($table,$allowed_tables)) {
        echo json_encode(['success' => false, 'message' => 'অবৈধ টেবিল রিকোয়েস্ট!']);
        exit();
    }

    if ($table === 'prices') {$status_int = ($new_status === 'active' ||$new_status === '1') ? 1 : 0;
        $stmt =$conn->prepare("UPDATE prices SET work_status = ? WHERE id = ?");
        if ($stmt) {$stmt->bind_param("ii", $status_int,$item_id);
            $stmt->execute();$stmt->close();
            echo json_encode(['success' => true, 'message' => 'স্ট্যাটাস সফলভাবে পরিবর্তন হয়েছে!']);
            exit();
        }
    } else {
        $status_str = ($new_status === 'active' ||$new_status === '1') ? 'active' : 'inactive';
        $stmt =$conn->prepare("UPDATE `{$table}` SET `status` = ? WHERE `id` = ?");
        if ($stmt) {$stmt->bind_param("si", $status_str,$item_id);
            $stmt->execute();$stmt->close();
            echo json_encode(['success' => true, 'message' => 'স্ট্যাটাস সফলভাবে পরিবর্তন হয়েছে!']);
            exit();
        }
    }

    echo json_encode(['success' => false, 'message' => 'ডাটাবেজ আপডেট ব্যর্থ হয়েছে!']);
    exit();
}

$all_services = [];

$fetch_queries = [
    ["SELECT id, service_name as title, status, price, 'location_services' as tbl, 'লোকেশন সার্ভিস' as cat FROM location_services"],
    ["SELECT id, service_name as title, status, price, 'call_sms_services' as tbl, 'কল ও এসএমএস' as cat FROM call_sms_services"],
    ["SELECT id, service_name as title, status, price, 'imei_services' as tbl, 'IMEI সার্ভিস' as cat FROM imei_services"],
    ["SELECT id, service_name as title, status, price, 'mfs_services' as tbl, 'বিকাশ/নগদ/রকেট' as cat FROM mfs_services"],
    ["SELECT id, operator_name as title, status, price, 'number_to_server_services' as tbl, 'নাম্বার টু বায়ো' as cat FROM number_to_server_services"],
    ["SELECT id, service_title as title, status, price, 'order_text_services' as tbl, 'অর্ডার টেক্সট' as cat FROM order_text_services"],
    ["SELECT id, service_title as title, status, price, 'order_file_services' as tbl, 'অর্ডার ফাইল' as cat FROM order_file_services"],
    ["SELECT id, service_name as title, status, price, 'custom_services' as tbl, 'কাস্টম সার্ভিস' as cat FROM custom_services"],
    ["SELECT id, COALESCE(name, orderName) as title, IF(work_status=1, 'active', 'inactive') as status, price, 'prices' as tbl, 'রেগুলার সার্ভিস' as cat FROM prices"]
];

foreach ($fetch_queries as $q) {$res = $conn->query($q[0]);
    if ($res &&$res->num_rows > 0) {
        while ($row = $res->fetch_assoc()) {$all_services[] = [
                'id' => intval($row['id']),
                'title' => (string)($row['title'] ?? 'সার্ভিস'),
                'status' => (string)($row['status'] ?? 'inactive'),
                'price' => floatval($row['price'] ?? 0),                 'tbl' => (string)($row['tbl'] ?? ''),
                'cat' => (string)($row['cat'] ?? 'সাধারণ')
            ];
        }
    }
}

$total_count = count($all_services);$active_count = 0;
$inactive_count = 0;
foreach ($all_services as$s) {
    if ($s['status'] === 'active') {$active_count++;
    } else {
        $inactive_count++;
    }
}
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>সকল সার্ভিস অন/অফ ম্যানেজার - FAST SERVICE</title>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Hind Siliguri', 'Plus Jakarta Sans', sans-serif; background: #F8FAFC; color: #1E293B; }
        .service-row-card { background: #FFFFFF; border: 1.5px solid #E2E8F0; border-radius: 18px; padding: 14px 18px; transition: all 0.2s ease; }
        .service-row-card:hover { border-color: #CBD5E1; box-shadow: 0 4px 16px rgba(0,0,0,0.03); }
        .filter-btn { padding: 7px 16px; border-radius: 20px; font-size: 12px; font-weight: 700; transition: all 0.2s; white-space: nowrap; cursor: pointer; }
        .filter-btn.active { background: #4F46E5; color: #FFFFFF; }
        .filter-btn:not(.active) { background: #FFFFFF; color: #64748B; border: 1.5px solid #E2E8F0; }
        .filter-btn:not(.active):hover { background: #F1F5F9; color: #1E293B; }
    </style>
</head>
<body class="p-3 md:p-6">

    <div class="max-w-6xl mx-auto">
        
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-3 mb-6 bg-white p-5 rounded-2xl border border-slate-200/90 shadow-sm">
            <div>
                <h2 class="text-xl font-extrabold text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-power-off text-rose-500"></i> সকল সার্ভিস অন / অফ কন্ট্রোল সেন্টার
                </h2>
                <p class="text-xs text-slate-500 mt-1">যেকোনো সার্ভিস এক ক্লিকে ইনস্ট্যান্ট চালু অথবা বন্ধ করুন</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="all_pricing.php" class="text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 px-3.5 py-2 rounded-xl hover:bg-amber-100 transition">
                    <i class="fa-solid fa-coins"></i> রেট পরিবর্তন
                </a>
                <a href="all_orders_history.php" class="text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 px-3.5 py-2 rounded-xl hover:bg-indigo-100 transition">
                    <i class="fa-solid fa-clock-rotate-left"></i> অর্ডার হিস্ট্রি
                </a>
                <a href="dashboard.php" class="text-xs font-bold bg-slate-100 text-slate-700 px-3.5 py-2 rounded-xl hover:bg-slate-200 transition">
                    <i class="fa-solid fa-arrow-left"></i> ড্যাশবোর্ড
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-6">
            <div class="bg-white p-4 rounded-2xl border border-slate-200/90 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold text-slate-400 block uppercase">সর্বমোট সার্ভিস</span>
                    <h3 class="text-2xl font-black text-slate-800 mt-0.5" id="totalCountDisplay"><?php echo $total_count; ?> টি</h3>
                </div>
                <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg font-bold">
                    <i class="fa-solid fa-list-check"></i>
                </div>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-slate-200/90 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold text-slate-400 block uppercase">বর্তমানে চালু আছে</span>
                    <h3 class="text-2xl font-black text-emerald-600 mt-0.5" id="activeCountDisplay"><?php echo $active_count; ?> টি</h3>
                </div>
                <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg font-bold">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-slate-200/90 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold text-slate-400 block uppercase">সাময়িক বন্ধ আছে</span>
                    <h3 class="text-2xl font-black text-rose-600 mt-0.5" id="inactiveCountDisplay"><?php echo $inactive_count; ?> টি</h3>
                </div>
                <div class="w-11 h-11 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg font-bold">
                    <i class="fa-solid fa-circle-xmark"></i>
                </div>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/90 shadow-sm mb-5 flex flex-col md:flex-row items-center justify-between gap-3">
            <div class="relative w-full md:w-80">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" id="searchServiceInput" placeholder="সার্ভিসের নাম দিয়ে সার্চ করুন..." class="w-full pl-9 pr-3 py-2 border rounded-xl text-xs font-semibold outline-none focus:border-indigo-500 bg-slate-50">
            </div>

            <div class="flex items-center gap-1.5 overflow-x-auto w-full md:w-auto pb-1 md:pb-0">
                <button onclick="filterStatus('all', this)" class="filter-btn active">সকল সার্ভিস</button>
                <button onclick="filterStatus('active', this)" class="filter-btn">শুধুমাত্র চালু</button>
                <button onclick="filterStatus('inactive', this)" class="filter-btn">শুধুমাত্র বন্ধ</button>
            </div>
        </div>

        <div class="flex gap-2 overflow-x-auto pb-3 mb-5 border-b border-slate-200 text-xs">
            <button onclick="filterCategory('all', this)" class="filter-btn active">সব ক্যাটাগরি</button>
            <button onclick="filterCategory('লোকেশন সার্ভিস', this)" class="filter-btn">লোকেশন</button>
            <button onclick="filterCategory('কল ও এসএমএস', this)" class="filter-btn">কল/SMS</button>
            <button onclick="filterCategory('IMEI সার্ভিস', this)" class="filter-btn">IMEI</button>
            <button onclick="filterCategory('বিকাশ/নগদ/রকেট', this)" class="filter-btn">MFS</button>
            <button onclick="filterCategory('নাম্বার টু বায়ো', this)" class="filter-btn">নাম্বার টু বায়ো</button>
            <button onclick="filterCategory('অর্ডার টেক্সট', this)" class="filter-btn">অর্ডার টেক্সট</button>
            <button onclick="filterCategory('অর্ডার ফাইল', this)" class="filter-btn">অর্ডার ফাইল</button>
            <button onclick="filterCategory('রেগুলার সার্ভিস', this)" class="filter-btn">রেগুলার</button>
        </div>

        <div class="space-y-2.5" id="serviceListContainer">
            <?php foreach ($all_services as$s): 
                $is_active = ($s['status'] === 'active');
            ?>
            <div class="service-row-card flex items-center justify-between gap-3" 
                 data-title="<?php echo htmlspecialchars(mb_strtolower((string)$s['title'], 'UTF-8')); ?>" 
                 data-status="<?php echo htmlspecialchars($s['status']); ?>" 
                 data-category="<?php echo htmlspecialchars($s['cat']); ?>"
                 data-id="<?php echo $s['id']; ?>"
                 data-table="<?php echo htmlspecialchars($s['tbl']); ?>">
                
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 border border-slate-200">
                            <?php echo htmlspecialchars($s['cat']); ?>
                        </span>
                        <span class="text-xs font-black text-indigo-600">
                            ৳ <?php echo floatval($s['price']); ?>
                        </span>
                    </div>
                    <h5 class="text-xs md:text-sm font-bold text-slate-800 leading-snug">
                        <?php echo htmlspecialchars($s['title']); ?>
                    </h5>
                </div>

                <div class="flex items-center gap-3">
                    <span class="status-indicator text-xs font-bold <?php echo $is_active ? 'text-emerald-600' : 'text-rose-500'; ?>">
                        <?php echo $is_active ? 'চালু আছে' : 'বন্ধ আছে'; ?>
                    </span>

                    <label class="relative inline-flex items-center cursor-pointer select-none">
                        <input type="checkbox" 
                               class="sr-only toggle-switch" 
                               <?php echo $is_active ? 'checked' : ''; ?>
                               onchange="handleToggle(this, '<?php echo htmlspecialchars($s['tbl']); ?>', <?php echo$s['id']; ?>)">
                        <div class="w-12 h-6 bg-slate-200 rounded-full transition-colors duration-200 ease-in-out switch-bg <?php echo $is_active ? '!bg-emerald-500' : '!bg-slate-300'; ?>"></div>
                        <div class="dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition-transform duration-200 ease-in-out <?php echo $is_active ? 'transform translate-x-6' : ''; ?>"></div>
                    </label>
                </div>

            </div>
            <?php endforeach; ?>
        </div>

        <div id="noResultsNotice" class="hidden bg-white p-8 rounded-2xl border text-center text-slate-400 text-xs mt-3">
            কোনো সার্ভিস খুঁজে পাওয়া যায়নি।
        </div>

    </div>

    <script>
        let currentStatusFilter = 'all';
        let currentCategoryFilter = 'all';

        function handleToggle(checkbox, table, id) {
            const isChecked = checkbox.checked;
            const newStatus = isChecked ? 'active' : 'inactive';
            const rowCard = $(checkbox).closest('.service-row-card');
            const statusLabel = rowCard.find('.status-indicator');
            const switchBg = rowCard.find('.switch-bg');
            const dot = rowCard.find('.dot');

            checkbox.disabled = true;

            $.ajax({
                url: 'manage_services.php',
                type: 'POST',
                data: {
                    action: 'toggle_status',
                    table: table,
                    id: id,
                    status: newStatus
                },
                dataType: 'json',
                success: function(res) {
                    checkbox.disabled = false;
                    if (res && res.success) {
                        rowCard.attr('data-status', newStatus);

                        if (isChecked) {
                            statusLabel.text('চালু আছে').removeClass('text-rose-500').addClass('text-emerald-600');
                            switchBg.removeClass('!bg-slate-300').addClass('!bg-emerald-500');
                            dot.addClass('transform translate-x-6');
                        } else {
                            statusLabel.text('বন্ধ আছে').removeClass('text-emerald-600').addClass('text-rose-500');
                            switchBg.removeClass('!bg-emerald-500').addClass('!bg-slate-300');
                            dot.removeClass('transform translate-x-6');
                        }

                        updateCounterBadges();

                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 1200
                        });
                        Toast.fire({
                            icon: 'success',
                            title: res.message
                        });
                    } else {
                        checkbox.checked = !isChecked;
                        Swal.fire('ত্রুটি!', res.message || 'স্ট্যাটাস আপডেট ব্যর্থ হয়েছে!', 'error');
                    }
                },
                error: function() {
                    checkbox.disabled = false;
                    checkbox.checked = !isChecked;
                    Swal.fire('সার্ভার ত্রুটি', 'সার্ভারে সংযোগ স্থাপন করা যায়নি!', 'error');
                }
            });
        }

        function filterStatus(status, el) {
            currentStatusFilter = status;
            $(el).siblings().removeClass('active');$(el).addClass('active');
            applyFilters();
        }

        function filterCategory(cat, el) {
            currentCategoryFilter = cat;
            $(el).siblings().removeClass('active');$(el).addClass('active');
            applyFilters();
        }

        $('#searchServiceInput').on('input', function() {
            applyFilters();
        });

        function applyFilters() {
            const searchQuery = $('#searchServiceInput').val().trim().toLowerCase();
            let visibleCount = 0;

            $('.service-row-card').each(function() {
                const title = $(this).attr('data-title');
                const status = $(this).attr('data-status');
                const category = $(this).attr('data-category');

                const matchesSearch = title.includes(searchQuery);
                const matchesStatus = (currentStatusFilter === 'all') || (status === currentStatusFilter);
                const matchesCategory = (currentCategoryFilter === 'all') || (category === currentCategoryFilter);

                if (matchesSearch && matchesStatus && matchesCategory) {
                    $(this).removeClass('hidden');
                    visibleCount++;
                } else {
                    $(this).addClass('hidden');
                }
            });

            if (visibleCount === 0) {
                $('#noResultsNotice').removeClass('hidden');
            } else {
                $('#noResultsNotice').addClass('hidden');
            }
        }

        function updateCounterBadges() {
            let active = 0;
            let inactive = 0;
            $('.service-row-card').each(function() {
                if ($(this).attr('data-status') === 'active') {
                    active++;
                } else {
                    inactive++;
                }
            });
            $('#activeCountDisplay').text(active + ' টি');
            $('#inactiveCountDisplay').text(inactive + ' টি');
        }
    </script>
</body>
</html>