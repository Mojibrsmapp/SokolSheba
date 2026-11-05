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

// Site settings
$settings_data = [];
$q_set = @$conn->query("SELECT setting_key, setting_value FROM site_settings");
if ($q_set) {
    while ($rs = mysqli_fetch_assoc($q_set)) {
        $settings_data[$rs['setting_key']] = $rs['setting_value'];
    }
}
$support_num = !empty($settings_data['support_whatsapp_num']) ? $settings_data['support_whatsapp_num'] : '01849207118';
$whatsapp_link = !empty($settings_data['whatsapp_group']) ? $settings_data['whatsapp_group'] : 'https://wa.me/8801849207118';
$telegram_link = !empty($settings_data['telegram_link']) ? $settings_data['telegram_link'] : 'https://t.me/Sheba_kit';
$support_email = 'Shokolsheba@gmail.com';

// Dynamic Slider
$slider_images = ['/assets/slider/1.jpg', '/assets/slider/2.jpg'];
$slider_path = __DIR__ . '/assets/slider';
if (is_dir($slider_path)) {
    $files = @scandir($slider_path);
    if ($files) {
        $temp_images = [];
        foreach ($files as $file) {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                $temp_images[] = '/assets/slider/' . $file;
            }
        }
        if (!empty($temp_images)) { $slider_images = $temp_images; }
    }
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>সকল সেবা - সব কাজ, এক প্ল্যাটফর্মে</title>
    
    <!-- Nikosh Bangla Font -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/maateen/bangla-fonts/Nikosh/stylesheet.css">
    <!-- FontAwesome 6 Free CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
            padding-bottom: 84px; 
        }

        .header-logo-img {
            width: 52px !important;
            height: 52px !important;
            min-width: 52px !important;
            min-height: 52px !important;
            max-width: 52px !important;
            max-height: 52px !important;
            border-radius: 50% !important;
            object-fit: cover !important;
        }

        @media (min-width: 640px) {
            .header-logo-img {
                width: 58px !important;
                height: 58px !important;
                min-width: 58px !important;
                min-height: 58px !important;
                max-width: 58px !important;
                max-height: 58px !important;
            }
        }

        .category-box {
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid #E5E7EB;
            overflow: hidden;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.03);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: all 0.2s ease;
        }
        .category-box:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 14px rgba(0, 0, 0, 0.07);
        }

        .category-header {
            padding: 7px 10px;
            color: #ffffff;
            font-size: 13.5px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .category-list {
            padding: 8px 10px;
            font-size: 11.5px;
            font-weight: 600;
            color: #475569;
            line-height: 1.45;
        }

        .sokol-slider-box {
            border-radius: 18px;
            overflow: hidden;
            border: 1px solid #A7F3D0;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.08);
            background: #ECFDF5;
        }
        .sokol-slider-box .swiper-slide img {
            width: 100%;
            height: auto;
            max-height: 230px;
            object-fit: cover;
            display: block;
        }
        .slider-dots .swiper-pagination-bullet {
            width: 8px;
            height: 8px;
            background: #D1D5DB;
            opacity: 1;
            margin: 0 3px !important;
            transition: 0.3s;
        }
        .slider-dots .swiper-pagination-bullet-active {
            background: #059669 !important;
            transform: scale(1.2);
        }

        /* Draggable Support Button */
        .fab-container { 
            position: fixed; 
            bottom: 90px; 
            right: 18px; 
            z-index: 90;
            touch-action: none;
        }
        .fab-btn {
            width: 50px; 
            height: 50px; 
            background: linear-gradient(135deg, #107C41 0%, #16A34A 100%);
            border-radius: 50%; 
            color: #fff; 
            display: flex; 
            align-items: center; 
            justify-content: center;
            font-size: 20px; 
            box-shadow: 0 6px 18px rgba(16, 124, 65, 0.4); 
            cursor: grab; 
            transition: transform 0.2s ease;
            border: 2px solid #ffffff;
        }
        .fab-btn:active { cursor: grabbing; transform: scale(0.95); }
        .fab-menu {
            position: absolute; 
            bottom: 60px; 
            right: 0; 
            background: #ffffff; 
            border-radius: 18px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15); 
            border: 1px solid #E2E8F0; 
            width: 230px;
            padding: 8px; 
            display: none; 
            flex-direction: column; 
            gap: 5px; 
            z-index: 95;
        }
        .fab-item {
            display: flex; 
            align-items: center; 
            gap: 10px; 
            padding: 9px 12px; 
            border-radius: 12px;
            font-size: 13px; 
            font-weight: 700; 
            color: #334155; 
            text-decoration: none; 
            transition: background 0.2s;
        }
        .fab-item:hover { background: #F1F5F9; color: #107C41; }

        /* Floating Nav Center Action Button */
        .center-action-btn {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: #0f172a;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.35);
            border: 3px solid #ffffff;
            margin-top: -24px;
            transition: all 0.25s ease;
            cursor: pointer;
        }
        .center-action-btn:hover {
            transform: scale(1.05);
            background: #107c41;
        }

        /* Auto Service Pill Item */
        .auto-pill-item {
            background: #ffffff;
            color: #1e293b;
            border: 1.5px solid #e2e8f0;
            padding: 10px 14px;
            border-radius: 9999px;
            font-size: 13px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
            transition: all 0.2s ease;
            text-decoration: none;
            width: 100%;
            justify-content: flex-start;
        }
        .auto-pill-item:hover {
            background: #f0fdf4;
            border-color: #107c41;
            color: #107c41;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>

    <!-- 1. Top Header -->
    <header class="bg-white sticky top-0 z-40 border-b border-slate-100 shadow-sm">
        <div class="max-w-6xl mx-auto px-3 py-2 flex items-center justify-between gap-2">
            
            <a href="dashboard.php" class="flex items-center gap-2 shrink-0">
                <img src="/assets/logo.jpg" onerror="this.src='https://cdn-icons-png.flaticon.com/512/3135/3135715.png'" alt="Logo" class="header-logo-img border-2 border-green-600 p-0.5 shadow-sm">
                <div class="flex flex-col items-center justify-center text-center">
                    <div class="flex items-center justify-center gap-0.5 leading-none">
                        <span class="text-xl sm:text-2xl font-black text-green-700 tracking-tight">সকল</span>
                        <span class="text-xl sm:text-2xl font-black text-red-600 tracking-tight">সেবা</span>
                    </div>
                    <p class="text-[9.5px] sm:text-[10px] font-bold text-slate-500 mt-1 leading-none tracking-tight">সব কাজ, এক প্ল্যাটফর্মে</p>
                </div>
            </a>

            <div class="flex items-center gap-1.5 sm:gap-2">
                <button onclick="location.href='recharge.php'" class="flex items-center gap-2 bg-white hover:bg-slate-50 border border-slate-200 text-slate-800 px-3 py-1.5 rounded-xl shadow-sm transition">
                    <div class="w-6 h-6 rounded-lg bg-green-50 text-green-700 flex items-center justify-center text-xs">
                        <i class="fa-solid fa-wallet"></i>
                    </div>
                    <div class="text-left leading-none">
                        <span class="text-[9px] text-slate-400 font-bold block mb-0.5">ওয়ালেট</span>
                        <span class="text-xs font-black text-green-700">৳ <?php echo htmlspecialchars($balance); ?></span>
                    </div>
                </button>

                <button onclick="Swal.fire('জরুরী নোটিশ', 'সকল কাজ চালু আছে, অযথা এডমিনকে ফোন করে কেউ বিরক্ত করবেন না। ধন্যবাদ সবাইকে', 'info')" class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-700 flex items-center justify-center relative border border-slate-200 transition">
                    <i class="fa-regular fa-bell text-base text-slate-600"></i>
                    <span class="w-2 h-2 bg-red-500 rounded-full absolute top-2 right-2 border border-white"></span>
                </button>

                <button id="openDrawerBtn" class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-800 flex items-center justify-center border border-slate-200 transition">
                    <i class="fa-solid fa-bars text-lg"></i>
                </button>
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="max-w-6xl mx-auto px-3 sm:px-5 py-3.5 space-y-3.5">

        <!-- Search Bar -->
        <div class="bg-white p-1 rounded-full shadow-sm border border-slate-200 flex items-center">
            <div class="pl-3.5 pr-2 text-slate-400">
                <i class="fa-solid fa-magnifying-glass text-xs sm:text-sm"></i>
            </div>
            <input type="text" id="serviceSearchInput" placeholder="যে কোন সার্ভিস সার্চ করুন..." class="w-full py-1 text-xs sm:text-sm focus:outline-none text-slate-700 bg-transparent">
            <button class="bg-[#156839] hover:bg-green-800 text-white font-bold text-xs sm:text-sm px-5 sm:px-6 py-1.5 sm:py-2 rounded-full transition shrink-0 shadow-sm">
                খুঁজুন
            </button>
        </div>

        <!-- Slider Banner -->
        <div>
            <div class="swiper sokol-slider-box">
                <div class="swiper-wrapper">
                    <?php foreach ($slider_images as $idx => $img_url): ?>
                        <div class="swiper-slide cursor-pointer" onclick="location.href='order_file.php'">
                            <img src="<?php echo htmlspecialchars($img_url); ?>" alt="Banner">
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="slider-dots flex justify-center items-center mt-2"></div>
        </div>

        <!-- Title Header -->
        <div class="flex items-center justify-between pt-0.5">
            <div class="flex items-center gap-1.5">
                <div class="grid grid-cols-2 gap-0.5 text-green-700 text-xs">
                    <i class="fa-solid fa-square"></i><i class="fa-solid fa-square"></i>
                    <i class="fa-solid fa-square"></i><i class="fa-solid fa-circle text-emerald-500"></i>
                </div>
                <h3 class="text-base sm:text-lg font-extrabold text-slate-800">সকল সেবা বিভাগ</h3>
            </div>
            <a href="order_file.php" class="text-xs font-bold text-green-700 hover:underline flex items-center gap-1">
                সব বিভাগ দেখুন <i class="fa-solid fa-arrow-right text-[10px]"></i>
            </a>
        </div>

        <!-- 2 Columns in Mobile Grid (grid-cols-2 md:grid-cols-3) -->
        <div class="grid grid-cols-2 md:grid-cols-3 gap-2.5 sm:gap-3.5" id="servicesContainer">
            
            <!-- 1. NID Seba -->
            <div class="category-box">
                <div>
                    <div class="category-header bg-[#107C41]">
                        <span class="flex items-center gap-1.5 truncate">
                            <i class="fa-solid fa-id-card text-xs"></i> NID সেবা
                        </span>
                        <i class="fa-solid fa-chevron-right text-[10px] opacity-80 shrink-0"></i>
                    </div>
                    <ul class="category-list space-y-1">
                        <li onclick="location.href='sign_order.php?type=10_12_17'" class="hover:text-green-700 cursor-pointer truncate">• সাইন কপি</li>
                        <li onclick="location.href='auto_service.php?type=nid'" class="hover:text-green-700 cursor-pointer truncate">• আইডি কার্ড</li>
                        <li onclick="location.href='serverCopy.php'" class="hover:text-green-700 cursor-pointer truncate">• সার্ভার কপি ১</li>
                        <li onclick="location.href='order_file.php'" class="hover:text-green-700 cursor-pointer truncate">• স্মার্ট কার্ড পিডিএফ</li>
                        <li onclick="location.href='free_service.php?type=make'" class="hover:text-green-700 cursor-pointer truncate">• অটো NID মেক</li>
                    </ul>
                </div>
                <div class="px-2.5 py-1.5 border-t border-slate-100">
                    <a href="order_file.php" class="text-[11px] font-bold text-[#107C41] hover:underline">আরও দেখুন...</a>
                </div>
            </div>

            <!-- 2. Passport Seba -->
            <div class="category-box">
                <div>
                    <div class="category-header bg-[#0078D4]">
                        <span class="flex items-center gap-1.5 truncate">
                            <i class="fa-solid fa-passport text-xs"></i> পাসপোর্ট সেবা
                        </span>
                        <i class="fa-solid fa-chevron-right text-[10px] opacity-80 shrink-0"></i>
                    </div>
                    <ul class="category-list space-y-1">
                        <li onclick="location.href='order_file.php'" class="hover:text-blue-700 cursor-pointer truncate">• পাসপোর্ট স্লিপ PDF</li>
                        <li onclick="location.href='order_file.php'" class="hover:text-blue-700 cursor-pointer truncate">• প্রথম পাতা PDF</li>
                        <li onclick="location.href='order_file.php'" class="hover:text-blue-700 cursor-pointer truncate">• NID দিয়ে পাসপোর্ট</li>
                        <li onclick="location.href='order_file.php'" class="hover:text-blue-700 cursor-pointer truncate">• ভিসা যাচাই</li>
                        <li onclick="location.href='order_file.php'" class="hover:text-blue-700 cursor-pointer truncate">• পুলিশ ক্লিয়ারেন্স</li>
                    </ul>
                </div>
                <div class="px-2.5 py-1.5 border-t border-slate-100">
                    <a href="order_file.php" class="text-[11px] font-bold text-[#0078D4] hover:underline">আরও দেখুন...</a>
                </div>
            </div>

            <!-- 3. Jonmo Nibondhon Seba -->
            <div class="category-box">
                <div>
                    <div class="category-header bg-[#7B1FA2]">
                        <span class="flex items-center gap-1.5 truncate">
                            <i class="fa-solid fa-file-lines text-xs"></i> জন্ম নিবন্ধন সেবা
                        </span>
                        <i class="fa-solid fa-chevron-right text-[10px] opacity-80 shrink-0"></i>
                    </div>
                    <ul class="category-list space-y-1">
                        <li onclick="location.href='order_file.php'" class="hover:text-purple-700 cursor-pointer truncate">• জন্ম নিবন্ধন পিডিএফ</li>
                        <li onclick="location.href='order_file.php'" class="hover:text-purple-700 cursor-pointer truncate">• ক্লোন কপি</li>
                        <li onclick="location.href='order_file.php'" class="hover:text-purple-700 cursor-pointer truncate">• নতুন জন্ম নিবন্ধন</li>
                        <li onclick="location.href='order_file.php'" class="hover:text-purple-700 cursor-pointer truncate">• হারানো উত্তোলন</li>
                        <li onclick="location.href='order_file.php'" class="hover:text-purple-700 cursor-pointer truncate">• Server Copy</li>
                    </ul>
                </div>
                <div class="px-2.5 py-1.5 border-t border-slate-100">
                    <a href="order_file.php" class="text-[11px] font-bold text-[#7B1FA2] hover:underline">আরও দেখুন...</a>
                </div>
            </div>

            <!-- 4. bKash Nagad Rocket -->
            <div class="category-box">
                <div>
                    <div class="category-header bg-[#D81B60]">
                        <span class="flex items-center gap-1.5 truncate">
                            <i class="fa-solid fa-mobile-retro text-xs"></i> বিকাশ-নগদ-রকেট
                        </span>
                        <i class="fa-solid fa-chevron-right text-[10px] opacity-80 shrink-0"></i>
                    </div>
                    <ul class="category-list space-y-1">
                        <li onclick="location.href='mfs_service.php'" class="hover:text-pink-600 cursor-pointer truncate">• বিকাশ ইনফো</li>
                        <li onclick="location.href='mfs_service.php'" class="hover:text-pink-600 cursor-pointer truncate">• নগদ ইনফো</li>
                        <li onclick="location.href='mfs_service.php'" class="hover:text-pink-600 cursor-pointer truncate">• রকেট ইনফো</li>
                        <li onclick="location.href='mfs_service.php'" class="hover:text-pink-600 cursor-pointer truncate">• উপায় ইনফো</li>
                        <li onclick="location.href='mfs_service.php'" class="hover:text-pink-600 cursor-pointer truncate">• স্টেটমেন্ট তথ্য</li>
                    </ul>
                </div>
                <div class="px-2.5 py-1.5 border-t border-slate-100">
                    <a href="mfs_service.php" class="text-[11px] font-bold text-[#D81B60] hover:underline">আরও দেখুন...</a>
                </div>
            </div>

            <!-- 5. Sim Biometric -->
            <div class="category-box">
                <div>
                    <div class="category-header bg-[#E65100]">
                        <span class="flex items-center gap-1.5 truncate">
                            <i class="fa-solid fa-sim-card text-xs"></i> সিম বায়োমেট্রিক
                        </span>
                        <i class="fa-solid fa-chevron-right text-[10px] opacity-80 shrink-0"></i>
                    </div>
                    <ul class="category-list space-y-1">
                        <li onclick="location.href='order_file.php'" class="hover:text-orange-600 cursor-pointer truncate">• NID থেকে সিম বাতিল</li>
                        <li onclick="location.href='order_file.php'" class="hover:text-orange-600 cursor-pointer truncate">• সকল সিম বাতিল</li>
                        <li onclick="location.href='numbertobio.php'" class="hover:text-orange-600 cursor-pointer truncate">• রবি বায়োমেট্রিক</li>
                        <li onclick="location.href='numbertobio.php'" class="hover:text-orange-600 cursor-pointer truncate">• এয়ারটেল বায়োমেট্রিক</li>
                        <li onclick="location.href='numbertobio.php'" class="hover:text-orange-600 cursor-pointer truncate">• গ্রামীণ বায়োমেট্রিক</li>
                    </ul>
                </div>
                <div class="px-2.5 py-1.5 border-t border-slate-100">
                    <a href="numbertobio.php" class="text-[11px] font-bold text-[#E65100] hover:underline">আরও দেখুন...</a>
                </div>
            </div>

            <!-- 6. Location Track -->
            <div class="category-box">
                <div>
                    <div class="category-header bg-[#00897B]">
                        <span class="flex items-center gap-1.5 truncate">
                            <i class="fa-solid fa-location-dot text-xs"></i> লোকেশন ট্র্যাক
                        </span>
                        <i class="fa-solid fa-chevron-right text-[10px] opacity-80 shrink-0"></i>
                    </div>
                    <ul class="category-list space-y-1">
                        <li onclick="location.href='location_service.php'" class="hover:text-teal-700 cursor-pointer truncate">• সকল সিমের লোকেশন</li>
                        <li onclick="location.href='order_file.php'" class="hover:text-teal-700 cursor-pointer truncate">• এনআইডি টু নাম্বার</li>
                        <li onclick="location.href='location_service.php'" class="hover:text-teal-700 cursor-pointer truncate">• পিন পয়েন্ট লোকেশন</li>
                        <li onclick="location.href='location_service.php'" class="hover:text-teal-700 cursor-pointer truncate">• জিও লোকেশন</li>
                        <li onclick="location.href='imei_service.php'" class="hover:text-teal-700 cursor-pointer truncate">• IMEI টু নাম্বার</li>
                    </ul>
                </div>
                <div class="px-2.5 py-1.5 border-t border-slate-100">
                    <a href="location_service.php" class="text-[11px] font-bold text-[#00897B] hover:underline">আরও দেখুন...</a>
                </div>
            </div>

            <!-- 7. TIN Certificate -->
            <div class="category-box">
                <div>
                    <div class="category-header bg-[#F57F17]">
                        <span class="flex items-center gap-1.5 truncate">
                            <i class="fa-solid fa-file-invoice text-xs"></i> টিন সার্টিফিকেট
                        </span>
                        <i class="fa-solid fa-chevron-right text-[10px] opacity-80 shrink-0"></i>
                    </div>
                    <ul class="category-list space-y-1">
                        <li onclick="location.href='auto_tin.php'" class="hover:text-amber-700 cursor-pointer truncate">• নতুন টিন আবেদন</li>
                        <li onclick="location.href='order_text.php'" class="hover:text-amber-700 cursor-pointer truncate">• হারানো সার্টিফিকেট</li>
                        <li onclick="location.href='order_text.php'" class="hover:text-amber-700 cursor-pointer truncate">• নিজ নাম সংশোধন</li>
                        <li onclick="location.href='order_text.php'" class="hover:text-amber-700 cursor-pointer truncate">• অল নাম সংশোধন</li>
                        <li onclick="location.href='auto_tin.php'" class="hover:text-amber-700 cursor-pointer truncate">• অটো টিন জেনারেট</li>
                    </ul>
                </div>
                <div class="px-2.5 py-1.5 border-t border-slate-100">
                    <a href="auto_tin.php" class="text-[11px] font-bold text-[#F57F17] hover:underline">আরও দেখুন...</a>
                </div>
            </div>

            <!-- 8. Trade License -->
            <div class="category-box">
                <div>
                    <div class="category-header bg-[#5E35B1]">
                        <span class="flex items-center gap-1.5 truncate">
                            <i class="fa-solid fa-store text-xs"></i> ট্রেড লাইসেন্স
                        </span>
                        <i class="fa-solid fa-chevron-right text-[10px] opacity-80 shrink-0"></i>
                    </div>
                    <ul class="category-list space-y-1">
                        <li onclick="location.href='order_file.php'" class="hover:text-purple-700 cursor-pointer truncate">• লাইসেন্স আবেদন</li>
                        <li onclick="location.href='order_file.php'" class="hover:text-purple-700 cursor-pointer truncate">• হারানো উত্তোলন</li>
                        <li onclick="location.href='order_text.php'" class="hover:text-purple-700 cursor-pointer truncate">• জিরো রিটার্ন</li>
                        <li onclick="location.href='order_file.php'" class="hover:text-purple-700 cursor-pointer truncate">• নবায়ন আবেদন</li>
                        <li onclick="location.href='order_file.php'" class="hover:text-purple-700 cursor-pointer truncate">• লাইসেন্স সংশোধন</li>
                    </ul>
                </div>
                <div class="px-2.5 py-1.5 border-t border-slate-100">
                    <a href="order_file.php" class="text-[11px] font-bold text-[#5E35B1] hover:underline">আরও দেখুন...</a>
                </div>
            </div>

            <!-- 9. Bhumi Seba -->
            <div class="category-box">
                <div>
                    <div class="category-header bg-[#0288D1]">
                        <span class="flex items-center gap-1.5 truncate">
                            <i class="fa-solid fa-mountain-sun text-xs"></i> ভূমি সেবা
                        </span>
                        <i class="fa-solid fa-chevron-right text-[10px] opacity-80 shrink-0"></i>
                    </div>
                    <ul class="category-list space-y-1">
                        <li onclick="location.href='order_file.php'" class="hover:text-sky-700 cursor-pointer truncate">• মৌজা ম্যাপ</li>
                        <li onclick="location.href='order_file.php'" class="hover:text-sky-700 cursor-pointer truncate">• খাজনা অনুমোদন</li>
                        <li onclick="location.href='order_file.php'" class="hover:text-sky-700 cursor-pointer truncate">• সার্ভে খতিয়ান কপি</li>
                        <li onclick="location.href='order_file.php'" class="hover:text-sky-700 cursor-pointer truncate">• নামজারি খতিয়ান কপি</li>
                        <li onclick="location.href='order_file.php'" class="hover:text-sky-700 cursor-pointer truncate">• খাজনা ডিসকাউন্ট</li>
                    </ul>
                </div>
                <div class="px-2.5 py-1.5 border-t border-slate-100">
                    <a href="order_file.php" class="text-[11px] font-bold text-[#0288D1] hover:underline">আরও দেখুন...</a>
                </div>
            </div>

            <!-- 10. Abedon Seba -->
            <div class="category-box">
                <div>
                    <div class="category-header bg-[#C2185B]">
                        <span class="flex items-center gap-1.5 truncate">
                            <i class="fa-solid fa-file-signature text-xs"></i> আবেদন সেবা
                        </span>
                        <i class="fa-solid fa-chevron-right text-[10px] opacity-80 shrink-0"></i>
                    </div>
                    <ul class="category-list space-y-1">
                        <li onclick="location.href='order_file.php'" class="hover:text-pink-700 cursor-pointer truncate">• নতুন NID জন্য আবেদন</li>
                        <li onclick="location.href='order_file.php'" class="hover:text-pink-700 cursor-pointer truncate">• নতুন পাসপোর্ট আবেদন</li>
                        <li onclick="location.href='order_file.php'" class="hover:text-pink-700 cursor-pointer truncate">• নতুন জন্ম নিবন্ধন</li>
                        <li onclick="location.href='order_file.php'" class="hover:text-pink-700 cursor-pointer truncate">• পল্লী বিদ্যুৎ মিটার আবেদন</li>
                        <li onclick="location.href='order_file.php'" class="hover:text-pink-700 cursor-pointer truncate">• অন্যান্য অনলাইন আবেদন</li>
                    </ul>
                </div>
                <div class="px-2.5 py-1.5 border-t border-slate-100">
                    <a href="order_file.php" class="text-[11px] font-bold text-[#C2185B] hover:underline">আরও দেখুন...</a>
                </div>
            </div>

        </div>

        <!-- Bottom Notice Marquee -->
        <div class="bg-white border border-slate-200 rounded-xl px-3 py-2 flex items-center gap-2 text-xs font-bold text-slate-700 shadow-sm">
            <i class="fa-solid fa-bullhorn text-green-700 text-xs shrink-0"></i>
            <span class="truncate text-[11px] sm:text-xs">বিঃদ্রঃ- সকল কাজ চালু আছে, অযথা এডমিনকে ফোন করে কেউ বিরক্ত করবেন না। ধন্যবাদ সবাইকে।</span>
        </div>

    </main>

    <!-- Draggable Support Button -->
    <div class="fab-container" id="draggableFab">
        <div class="fab-menu" id="fabMenu">
            <a href="<?php echo htmlspecialchars($whatsapp_link); ?>" target="_blank" class="fab-item">
                <i class="fa-brands fa-whatsapp text-emerald-600 text-base"></i> 
                <span>WhatsApp সাপোর্ট</span>
            </a>
            <a href="<?php echo htmlspecialchars($telegram_link); ?>" target="_blank" class="fab-item">
                <i class="fa-brands fa-telegram text-sky-500 text-base"></i> 
                <span>Telegram চ্যানেল</span>
            </a>
            <a href="mailto:<?php echo htmlspecialchars($support_email); ?>" class="fab-item">
                <i class="fa-solid fa-envelope text-red-500 text-sm"></i> 
                <span class="truncate"><?php echo htmlspecialchars($support_email); ?></span>
            </a>
        </div>
        <button class="fab-btn" id="fabToggle" title="সাপোর্ট পেতে ক্লিক করুন">
            <i class="fa-solid fa-headset"></i>
        </button>
    </div>

    <!-- Mobile Bottom Navigation with Center Popup Trigger (Image 2 style) -->
    <nav class="fixed bottom-0 inset-x-0 bg-white/95 backdrop-blur-md border-t border-slate-200 py-1.5 px-3 flex items-center justify-around z-40 shadow-[0_-4px_20px_rgba(0,0,0,0.06)]">
        <a href="dashboard.php" class="flex flex-col items-center gap-0.5 text-[#107C41] text-[10.5px] font-extrabold">
            <i class="fa-solid fa-house text-base"></i>
            <span>হোম</span>
        </a>
        <a href="services.php" class="flex flex-col items-center gap-0.5 text-slate-500 hover:text-green-700 text-[10.5px] font-semibold">
            <i class="fa-solid fa-table-cells-large text-base"></i>
            <span>সেবা</span>
        </a>

        <!-- Center Popup Trigger Button -->
        <button type="button" id="openAutoPopupBtn" class="center-action-btn" title="অটো সার্ভিস মেনু">
            <i class="fa-solid fa-bolt text-xl text-amber-400"></i>
        </button>

        <a href="recharge.php" class="flex flex-col items-center gap-0.5 text-slate-500 hover:text-green-700 text-[10.5px] font-semibold">
            <i class="fa-solid fa-wallet text-base"></i>
            <span>ওয়ালেট</span>
        </a>
        <a href="profile.php" class="flex flex-col items-center gap-0.5 text-slate-500 hover:text-green-700 text-[10.5px] font-semibold">
            <i class="fa-regular fa-user text-base"></i>
            <span>প্রোফাইল</span>
        </a>
    </nav>

    <!-- ======================================================== -->
    <!-- Auto Service Popup Modal (Image 2 - Curved Round Pills)   -->
    <!-- ======================================================== -->
    <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-md z-50 hidden transition-all duration-300 flex flex-col justify-end items-center pb-6 px-4" id="autoServiceModal">
        
        <div class="w-full max-w-sm flex flex-col items-center space-y-2.5 mb-3 overflow-y-auto max-h-[68vh] py-2 px-1 no-scrollbar">
            
            <div class="w-full text-center mb-1">
                <span class="inline-flex items-center gap-1.5 bg-emerald-900/80 border border-emerald-500/40 text-emerald-200 text-xs font-bold px-4 py-1.5 rounded-full shadow">
                    <i class="fa-solid fa-bolt text-amber-400"></i> ২৪/৭ অটো সার্ভিসসমূহ
                </span>
            </div>

            <!-- Auto Service Pill Buttons -->
            <a href="auto_service.php?type=voter" class="auto-pill-item">
                <i class="fa-solid fa-magnifying-glass text-emerald-600 w-5"></i>
                <span>ভোটার তথ্য অনুসন্ধান</span>
            </a>
            <a href="auto_service.php?type=userid" class="auto-pill-item">
                <i class="fa-solid fa-id-badge text-sky-600 w-5"></i>
                <span>User Id to Server Copy</span>
            </a>
            <a href="auto_service.php?type=death" class="auto-pill-item">
                <i class="fa-solid fa-file-medical text-rose-600 w-5"></i>
                <span>Death Cer. Make</span>
            </a>
            <a href="auto_service.php?type=sign2server" class="auto-pill-item">
                <i class="fa-solid fa-signature text-purple-600 w-5"></i>
                <span>Sign 2 Server Copy</span>
            </a>
            <a href="auto_service.php?type=haf" class="auto-pill-item">
                <i class="fa-solid fa-file-invoice text-amber-600 w-5"></i>
                <span>Haf Server Copy</span>
            </a>
            <a href="auto_service.php?type=epassport" class="auto-pill-item">
                <i class="fa-solid fa-passport text-blue-600 w-5"></i>
                <span>E-Passport Make</span>
            </a>
            <a href="auto_service.php?type=driving" class="auto-pill-item">
                <i class="fa-solid fa-id-card text-teal-600 w-5"></i>
                <span>Driving License</span>
            </a>
            <a href="auto_service.php?type=clone_sonod" class="auto-pill-item">
                <i class="fa-solid fa-clone text-indigo-600 w-5"></i>
                <span>Clone Sonod</span>
            </a>
            <a href="auto_service.php?type=sign2nid" class="auto-pill-item">
                <i class="fa-solid fa-file-lines text-pink-600 w-5"></i>
                <span>Sign 2 Nid</span>
            </a>
            <a href="auto_service.php?type=cv" class="auto-pill-item">
                <i class="fa-solid fa-file-pdf text-orange-600 w-5"></i>
                <span>CV Make</span>
            </a>

        </div>

        <!-- Big Close Action Button (Like Screenshot 2) -->
        <button type="button" id="closeAutoPopupBtn" class="w-14 h-14 rounded-full bg-[#0b1329] border-2 border-white/20 text-white flex items-center justify-center text-xl shadow-2xl transition hover:scale-105">
            <i class="fa-solid fa-xmark"></i>
        </button>

    </div>

    <!-- Side Drawer Menu -->
    <div class="fixed inset-0 bg-slate-950/40 backdrop-blur-[2px] z-50 hidden" id="drawerOverlay"></div>
    <div class="fixed top-0 right-[-100%] w-[55%] sm:w-56 h-full bg-white shadow-2xl z-50 transition-all duration-300 flex flex-col border-l border-slate-200" id="drawerMenu">
        <div class="px-3.5 py-3 bg-[#107C41] text-white flex items-center justify-between">
            <div class="flex items-center gap-1.5 min-w-0">
                <i class="fa-solid fa-layer-group text-emerald-200 text-xs"></i>
                <h5 class="font-extrabold text-xs truncate">সকল সেবা মেনু</h5>
            </div>
            <button id="closeDrawerBtn" class="w-6 h-6 rounded-md bg-white/15 hover:bg-white/25 text-white flex items-center justify-center text-xs transition">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-2 space-y-0.5 text-[11.5px] font-bold">
            <a href="dashboard.php" class="flex items-center gap-2 px-2.5 py-2 rounded-lg bg-green-50 text-green-800"><i class="fa-solid fa-house w-4 text-green-700 text-xs"></i> <span class="truncate">হোম ড্যাশবোর্ড</span></a>
            <a href="serverCopy.php" class="flex items-center gap-2 px-2.5 py-2 rounded-lg text-slate-700 hover:bg-slate-50"><i class="fa-solid fa-server w-4 text-purple-600 text-xs"></i> <span class="truncate">অটো সার্ভার কপি</span></a>
            <a href="sign_order.php?type=10_12_17" class="flex items-center gap-2 px-2.5 py-2 rounded-lg text-slate-700 hover:bg-slate-50"><i class="fa-solid fa-file-signature w-4 text-indigo-600 text-xs"></i> <span class="truncate">সাইন কপি অর্ডার</span></a>
            <a href="auto_tin.php" class="flex items-center gap-2 px-2.5 py-2 rounded-lg text-slate-700 hover:bg-slate-50"><i class="fa-solid fa-file-invoice w-4 text-amber-600 text-xs"></i> <span class="truncate">অটো টিন সার্টিফিকেট</span></a>
            <a href="numbertobio.php" class="flex items-center gap-2 px-2.5 py-2 rounded-lg text-slate-700 hover:bg-slate-50"><i class="fa-solid fa-sim-card w-4 text-orange-600 text-xs"></i> <span class="truncate">সিম বায়োমেট্রিক</span></a>
            <a href="location_service.php" class="flex items-center gap-2 px-2.5 py-2 rounded-lg text-slate-700 hover:bg-slate-50"><i class="fa-solid fa-location-dot w-4 text-teal-600 text-xs"></i> <span class="truncate">লোকেশন ট্র্যাকিং</span></a>
            <a href="mfs_service.php" class="flex items-center gap-2 px-2.5 py-2 rounded-lg text-slate-700 hover:bg-slate-50"><i class="fa-solid fa-wallet w-4 text-rose-600 text-xs"></i> <span class="truncate">বিকাশ-নগদ-রকেট</span></a>
            <a href="order_file.php" class="flex items-center gap-2 px-2.5 py-2 rounded-lg text-slate-700 hover:bg-slate-50"><i class="fa-solid fa-file-arrow-up w-4 text-emerald-600 text-xs"></i> <span class="truncate">কাস্টম ও ফাইল অর্ডার</span></a>
            <div class="my-1 border-t border-slate-100"></div>
            <a href="recharge.php" class="flex items-center gap-2 px-2.5 py-2 rounded-lg text-slate-700 hover:bg-slate-50"><i class="fa-solid fa-circle-plus w-4 text-green-600 text-xs"></i> <span class="truncate">ব্যালেন্স রিচার্জ</span></a>
            <a href="work_history.php" class="flex items-center gap-2 px-2.5 py-2 rounded-lg text-slate-700 hover:bg-slate-50"><i class="fa-solid fa-clock-rotate-left w-4 text-violet-600 text-xs"></i> <span class="truncate">অর্ডার হিস্টোরি</span></a>
            <a href="profile.php" class="flex items-center gap-2 px-2.5 py-2 rounded-lg text-slate-700 hover:bg-slate-50"><i class="fa-solid fa-user-gear w-4 text-slate-600 text-xs"></i> <span class="truncate">আমার প্রোফাইল</span></a>
            <a href="logout.php" class="flex items-center gap-2 px-2.5 py-2 rounded-lg text-red-600 hover:bg-red-50"><i class="fa-solid fa-arrow-right-from-bracket w-4 text-xs"></i> <span class="truncate">লগআউট</span></a>
        </div>
    </div>

    <!-- Swiper JS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script>
        var swiper = new Swiper(".sokol-slider-box", {
            loop: true,
            autoplay: { delay: 3500, disableOnInteraction: false },
            pagination: { el: ".slider-dots", clickable: true },
            speed: 600,
        });

        // Drawer
        $('#openDrawerBtn').click(function() {
            $('#drawerOverlay').fadeIn(200);
            $('#drawerMenu').css('right', '0');
        });
        $('#closeDrawerBtn, #drawerOverlay').click(function() {
            $('#drawerOverlay').fadeOut(200);
            $('#drawerMenu').css('right', '-100%');
        });

        // Auto Service Popup Handler (Image 2 match)
        $('#openAutoPopupBtn').click(function() {
            $('#autoServiceModal').removeClass('hidden').hide().fadeIn(250);
        });
        $('#closeAutoPopupBtn, #autoServiceModal').click(function(e) {
            if (e.target === this || e.target.id === 'closeAutoPopupBtn' || $(e.target).closest('#closeAutoPopupBtn').length) {
                $('#autoServiceModal').fadeOut(200, function() {
                    $(this).addClass('hidden');
                });
            }
        });

        $('#serviceSearchInput').on('keyup', function() {
            var value = $(this).val().toLowerCase();
            $("#servicesContainer .category-box").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
            });
        });

        // Draggable Support Button
        const fab = document.getElementById('draggableFab');
        const fabToggle = document.getElementById('fabToggle');
        const fabMenu = document.getElementById('fabMenu');
        
        let isDragging = false;
        let hasMoved = false;
        let startX, startY, origX, origY;

        function onTouchStart(e) {
            if (e.target.closest('.fab-menu')) return;
            isDragging = true;
            hasMoved = false;

            const evt = e.type === 'touchstart' ? e.touches[0] : e;
            startX = evt.clientX;
            startY = evt.clientY;

            const rect = fab.getBoundingClientRect();
            origX = rect.left;
            origY = rect.top;
        }

        function onTouchMove(e) {
            if (!isDragging) return;

            const evt = e.type === 'touchmove' ? e.touches[0] : e;
            const deltaX = evt.clientX - startX;
            const deltaY = evt.clientY - startY;

            if (Math.abs(deltaX) > 3 || Math.abs(deltaY) > 3) {
                hasMoved = true;
            }

            let newLeft = origX + deltaX;
            let newTop = origY + deltaY;

            const maxLeft = window.innerWidth - fab.offsetWidth - 10;
            const maxTop = window.innerHeight - fab.offsetHeight - 80;

            newLeft = Math.max(10, Math.min(newLeft, maxLeft));
            newTop = Math.max(60, Math.min(newTop, maxTop));

            fab.style.left = newLeft + 'px';
            fab.style.top = newTop + 'px';
            fab.style.bottom = 'auto';
            fab.style.right = 'auto';

            if (e.type === 'touchmove') {
                e.preventDefault();
            }
        }

        function onTouchEnd() {
            isDragging = false;
        }

        fabToggle.addEventListener('mousedown', onTouchStart);
        document.addEventListener('mousemove', onTouchMove);
        document.addEventListener('mouseup', onTouchEnd);

        fabToggle.addEventListener('touchstart', onTouchStart, { passive: true });
        document.addEventListener('touchmove', onTouchMove, { passive: false });
        document.addEventListener('touchend', onTouchEnd);

        $('#fabToggle').on('click', function(e) {
            if (!hasMoved) {
                $('#fabMenu').fadeToggle(200);
            }
        });

        $(document).click(function(e) {
            if (!$(e.target).closest('.fab-container').length) {
                $('#fabMenu').fadeOut(200);
            }
        });
    </script>
</body>
</html>
<?php ob_end_flush(); ?>