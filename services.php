<?php
ob_start();
session_start();
include_once("includes/configuration.php");

if (isset($_SESSION['user_token'])) {
    $token_user = $_SESSION['user_token'];
    
    $stmt = $conn->prepare("SELECT id, balance, email, name, token, mobile, image FROM users WHERE token = ?");
    if ($stmt) {
        $stmt->bind_param("s", $token_user);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $id = (int)$row["id"];
            $balance = $row["balance"];
            $email = $row["email"];
            $name = $row["name"];
            $token = $row["token"];
            $phone = $row["mobile"];
            $user_image = !empty($row["image"]) ? $row["image"] : "";
        } else {
            unset($_SESSION['user_token'], $_SESSION['user_id']);
            header("Location: index.php");
            exit();
        }
        $stmt->close();
    }
} else {
    header("Location: index.php");
    exit();
}

// Database theke custom_services gulo category onujayi group kora
$services_by_cat = [];
$q_serv = @$conn->query("SELECT id, service_name, category, price, service_type, access_link, description, status FROM custom_services WHERE status = 'active' ORDER BY category ASC, id ASC");
if ($q_serv && $q_serv->num_rows > 0) {
    while ($srv = $q_serv->fetch_assoc()) {
        $cat = !empty($srv['category']) ? trim($srv['category']) : 'অন্যান্য সেবা';
        $services_by_cat[$cat][] = $srv;
    }
}

// Category color palette helper
$category_colors = [
    'NID সেবা'          => ['bg' => '#107C41', 'icon' => 'fa-id-card', 'badge' => 'bg-emerald-50 text-emerald-800'],
    'পাসপোর্ট সেবা'     => ['bg' => '#0078D4', 'icon' => 'fa-passport', 'badge' => 'bg-blue-50 text-blue-800'],
    'জন্ম নিবন্ধন সেবা' => ['bg' => '#7B1FA2', 'icon' => 'fa-file-lines', 'badge' => 'bg-purple-50 text-purple-800'],
    'বিকাশ নগদ রকেট'    => ['bg' => '#D81B60', 'icon' => 'fa-mobile-retro', 'badge' => 'bg-pink-50 text-pink-800'],
    'সিম বায়োমেট্রিক'   => ['bg' => '#E65100', 'icon' => 'fa-sim-card', 'badge' => 'bg-orange-50 text-orange-800'],
    'লোকেশন ট্র্যাক'     => ['bg' => '#00897B', 'icon' => 'fa-location-dot', 'badge' => 'bg-teal-50 text-teal-800'],
    'টিন সার্টিফিকেট'   => ['bg' => '#F57F17', 'icon' => 'fa-file-invoice', 'badge' => 'bg-amber-50 text-amber-800'],
    'ট্রেড লাইসেন্স'     => ['bg' => '#5E35B1', 'icon' => 'fa-store', 'badge' => 'bg-indigo-50 text-indigo-800'],
    'ভূমি সেবা'         => ['bg' => '#0288D1', 'icon' => 'fa-mountain-sun', 'badge' => 'bg-sky-50 text-sky-800'],
    'আবেদন সেবা'        => ['bg' => '#C2185B', 'icon' => 'fa-file-signature', 'badge' => 'bg-rose-50 text-rose-800'],
    'অটো সার্ভিস'       => ['bg' => '#0F766E', 'icon' => 'fa-bolt', 'badge' => 'bg-teal-50 text-teal-800'],
];
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>সকল সেবাসমূহ - সকল সেবা</title>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/maateen/bangla-fonts/Nikosh/stylesheet.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="shortcut icon" href="/assets/logo.jpg">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }

        body, input, button, select, textarea, p, h1, h2, h3, h4, h5, h6, span, a, label, li {
            font-family: 'Nikosh', 'SolaimanLipi', sans-serif !important;
            -webkit-font-smoothing: antialiased;
        }

        .fa, .fas, .far, .fal, .fad, .fab, .fa-solid, .fa-regular, .fa-brands {
            font-family: "Font Awesome 6 Free", "Font Awesome 6 Brands" !important;
        }

        body { 
            background: #F3F4F6; 
            color: #1E293B; 
            min-height: 100vh; 
            padding-bottom: 74px; 
        }

        .service-card {
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid #E5E7EB;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.03);
            transition: all 0.2s ease;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .service-card:hover {
            transform: translateY(-2px);
            border-color: #10B981;
            box-shadow: 0 6px 16px rgba(16, 185, 129, 0.08);
        }
    </style>
</head>
<body>

    <!-- Header Section -->
    <header class="bg-white sticky top-0 z-40 border-b border-slate-100 shadow-sm">
        <div class="max-w-6xl mx-auto px-4 py-2.5 flex items-center justify-between gap-3">
            <a href="dashboard.php" class="flex items-center gap-2 text-green-800 font-extrabold text-sm">
                <i class="fa-solid fa-arrow-left text-base"></i>
                <span>ড্যাশবোর্ড</span>
            </a>
            
            <div class="flex items-center gap-2">
                <button onclick="location.href='recharge.php'" class="flex items-center gap-1.5 bg-[#059669] text-white px-3 py-1.5 rounded-xl text-xs font-bold shadow-sm">
                    <i class="fa-solid fa-wallet text-sm"></i>
                    <span>৳ <?php echo htmlspecialchars($balance); ?></span>
                </button>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-6xl mx-auto px-3.5 sm:px-6 py-4 space-y-4">

        <!-- Search Bar -->
        <div class="bg-white p-1 rounded-full shadow-sm border border-slate-200 flex items-center">
            <div class="pl-3.5 pr-2 text-slate-400">
                <i class="fa-solid fa-magnifying-glass text-xs sm:text-sm"></i>
            </div>
            <input type="text" id="serviceSearchInput" placeholder="ডাটাবেজ থেকে যে কোন সার্ভিস খুঁজুন..." class="w-full py-1 text-xs sm:text-sm focus:outline-none text-slate-700 bg-transparent">
        </div>

        <!-- Filter Category Tabs -->
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 no-scrollbar text-xs font-bold">
            <button onclick="filterCategory('all')" class="cat-filter-btn px-3 py-1.5 rounded-xl bg-green-700 text-white whitespace-nowrap shadow-sm">সবগুলো</button>
            <?php foreach (array_keys($services_by_cat) as $cName): ?>
                <button onclick="filterCategory('<?php echo md5($cName); ?>')" class="cat-filter-btn px-3 py-1.5 rounded-xl bg-white text-slate-700 hover:bg-slate-100 border border-slate-200 whitespace-nowrap transition">
                    <?php echo htmlspecialchars($cName); ?>
                </button>
            <?php endforeach; ?>
        </div>

        <!-- Services Grouped By Category -->
        <div class="space-y-5" id="servicesListWrapper">
            <?php if (!empty($services_by_cat)): ?>
                <?php foreach ($services_by_cat as $catName => $items): 
                    $color_meta = $category_colors[$catName] ?? ['bg' => '#107C41', 'icon' => 'fa-layer-group', 'badge' => 'bg-emerald-50 text-emerald-800'];
                    $catId = md5($catName);
                ?>
                    <div class="category-block space-y-2.5" data-category="<?php echo $catId; ?>">
                        <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                            <h3 class="text-sm sm:text-base font-extrabold text-slate-800 flex items-center gap-2">
                                <span class="w-6 h-6 rounded-lg flex items-center justify-center text-white text-xs" style="background-color: <?php echo $color_meta['bg']; ?>;">
                                    <i class="fa-solid <?php echo $color_meta['icon']; ?>"></i>
                                </span>
                                <span><?php echo htmlspecialchars($catName); ?></span>
                            </h3>
                            <span class="text-[11px] font-bold px-2 py-0.5 rounded-full border border-slate-200 bg-white text-slate-600">
                                <?php echo count($items); ?> টি সেবা
                            </span>
                        </div>

                        <!-- Service Grid (Mobile 2 Columns) -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-2.5 sm:gap-3.5">
                            <?php foreach ($items as $srv): 
                                // custom service -> nijer order page (protiti service er alada field), auto service -> tar nijer page
                                $srv_type = isset($srv['service_type']) ? strtolower(trim($srv['service_type'])) : 'custom';
                                if ($srv_type === 'auto' && !empty($srv['access_link'])) {
                                    $srv_link = $srv['access_link'];
                                } else {
                                    $srv_link = 'service_order.php?id=' . (int)$srv['id'];
                                }
                            ?>
                                <div class="service-card p-3 service-item">
                                    <div>
                                        <h4 class="text-xs sm:text-sm font-extrabold text-slate-800 line-clamp-1">
                                            <?php echo htmlspecialchars($srv['service_name']); ?>
                                        </h4>
                                        <p class="text-[11px] text-slate-500 mt-0.5 line-clamp-1">
                                            <?php echo !empty($srv['description']) ? htmlspecialchars($srv['description']) : 'তাত্ক্ষণিক অর্ডার সেবা'; ?>
                                        </p>
                                    </div>

                                    <div class="flex items-center justify-between mt-2.5 pt-2 border-t border-slate-100">
                                        <span class="text-xs font-black text-green-700">
                                            ৳ <?php echo (int)$srv['price']; ?>
                                        </span>
                                        <a href="<?php echo htmlspecialchars($srv_link); ?>" class="bg-[#107C41] hover:bg-green-800 text-white text-[10.5px] font-bold px-2.5 py-1 rounded-full shadow-sm transition">
                                            অর্ডার
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-12 bg-white rounded-2xl border border-slate-200">
                    <i class="fa-solid fa-folder-open text-4xl text-slate-300 mb-2"></i>
                    <p class="text-xs font-bold text-slate-500">ডাটাবেজে বর্তমানে কোনো সার্ভিস পাওয়া যায়নি।</p>
                </div>
            <?php endif; ?>
        </div>

    </main>

    <!-- Bottom 5-Tab Bar -->
    <nav class="fixed bottom-0 inset-x-0 bg-white border-t border-slate-200 py-1.5 px-3 flex items-center justify-around z-50 shadow-[0_-4px_15px_rgba(0,0,0,0.04)]">
        <a href="dashboard.php" class="flex flex-col items-center gap-0.5 text-slate-500 hover:text-green-700 text-[10.5px] font-semibold">
            <i class="fa-solid fa-house text-base sm:text-lg"></i>
            <span>হোম</span>
        </a>
        <a href="services.php" class="flex flex-col items-center gap-0.5 text-[#107C41] text-[10.5px] font-extrabold">
            <i class="fa-solid fa-table-cells-large text-base sm:text-lg"></i>
            <span>সেবা</span>
        </a>
        <a href="recharge.php" class="flex flex-col items-center gap-0.5 text-slate-500 hover:text-green-700 text-[10.5px] font-semibold">
            <i class="fa-solid fa-wallet text-base sm:text-lg"></i>
            <span>ওয়ালেট</span>
        </a>
        <a href="work_history.php" class="flex flex-col items-center gap-0.5 text-slate-500 hover:text-green-700 text-[10.5px] font-semibold">
            <i class="fa-solid fa-file-invoice text-base sm:text-lg"></i>
            <span>অর্ডার</span>
        </a>
        <a href="profile.php" class="flex flex-col items-center gap-0.5 text-slate-500 hover:text-green-700 text-[10.5px] font-semibold">
            <i class="fa-regular fa-user text-base sm:text-lg"></i>
            <span>প্রোফাইল</span>
        </a>
    </nav>

    <script>
        // Live Search Filter
        $('#serviceSearchInput').on('keyup', function() {
            var value = $(this).val().toLowerCase();
            $('.service-item').filter(function() {
                var match = $(this).text().toLowerCase().indexOf(value) > -1;
                $(this).toggle(match);
            });

            // Hide empty category wrappers if no service matches
            $('.category-block').each(function() {
                var visibleCount = $(this).find('.service-item:visible').length;
                $(this).toggle(visibleCount > 0);
            });
        });

        // Category Tab Filter
        function filterCategory(catId) {
            $('.cat-filter-btn').removeClass('bg-green-700 text-white').addClass('bg-white text-slate-700 border border-slate-200');
            $(event.target).removeClass('bg-white text-slate-700 border border-slate-200').addClass('bg-green-700 text-white');

            if (catId === 'all') {
                $('.category-block').show();
            } else {
                $('.category-block').hide();
                $('.category-block[data-category="' + catId + '"]').fadeIn(200);
            }
        }
    </script>
</body>
</html>
<?php ob_end_flush(); ?>