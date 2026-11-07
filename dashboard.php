<?php 
ob_start();
session_start();
include_once("includes/configuration.php");
include_once("includes/service_catalog.php");

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
    <title><?php echo soko_e(soko_brand('site_name')); ?> - <?php echo soko_e(soko_brand('site_tagline')); ?></title>

    <!-- Nikosh Bangla Font -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/maateen/bangla-fonts/Nikosh/stylesheet.css">
    <!-- FontAwesome 6 Free CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
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
    <?php include __DIR__ . '/includes/site_header.php'; ?>

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
                        <div class="swiper-slide cursor-pointer" onclick="location.href='services.php'">
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
            <a href="services.php" class="text-xs font-bold text-green-700 hover:underline flex items-center gap-1">
                সব বিভাগ দেখুন <i class="fa-solid fa-arrow-right text-[10px]"></i>
            </a>
        </div>

        <!-- 2 Columns in Mobile Grid (grid-cols-2 md:grid-cols-3) -->
        <div class="grid grid-cols-2 md:grid-cols-3 gap-2.5 sm:gap-3.5" id="servicesContainer">

            <!-- 1. NID Seba -->
            <div class="category-box">
                <div>
                    <div class="category-header bg-[#107C41] cursor-pointer" onclick="location.href='services.php?cat=NID%20%E0%A6%B8%E0%A7%87%E0%A6%AC%E0%A6%BE'">
                        <span class="flex items-center gap-1.5 truncate">
                            <i class="fa-solid fa-id-card text-xs"></i> NID সেবা
                        </span>
                        <i class="fa-solid fa-chevron-right text-[10px] opacity-80 shrink-0"></i>
                    </div>
                    <ul class="category-list space-y-1">
                        <li onclick="location.href='<?php echo soko_service_link($conn, 'সাইন কপি'); ?>'" class="hover:text-green-700 cursor-pointer truncate">• সাইন কপি</li>
                        <li onclick="location.href='<?php echo soko_service_link($conn, 'আইডি কার্ড'); ?>'" class="hover:text-green-700 cursor-pointer truncate">• আইডি কার্ড</li>
                        <li onclick="location.href='<?php echo soko_service_link($conn, 'সার্ভার কপি ১'); ?>'" class="hover:text-green-700 cursor-pointer truncate">• সার্ভার কপি ১</li>
                        <li onclick="location.href='<?php echo soko_service_link($conn, 'স্মার্ট কার্ড পিডিএফ'); ?>'" class="hover:text-green-700 cursor-pointer truncate">• স্মার্ট কার্ড পিডিএফ</li>
                        <li onclick="location.href='<?php echo soko_service_link($conn, 'অটো NID মেক'); ?>'" class="hover:text-green-700 cursor-pointer truncate">• অটো NID মেক</li>
                    </ul>
                </div>
                <div class="px-2.5 py-1.5 border-t border-slate-100">
                    <a href="services.php?cat=NID%20%E0%A6%B8%E0%A7%87%E0%A6%AC%E0%A6%BE" class="text-[11px] font-bold text-[#107C41] hover:underline">আরও দেখুন...</a>
                </div>
            </div>

            <!-- 2. Passport Seba -->
            <div class="category-box">
                <div>
                    <div class="category-header bg-[#0078D4] cursor-pointer" onclick="location.href='services.php?cat=%E0%A6%AA%E0%A6%BE%E0%A6%B8%E0%A6%AA%E0%A7%8B%E0%A6%B0%E0%A7%8D%E0%A6%9F%20%E0%A6%B8%E0%A7%87%E0%A6%AC%E0%A6%BE'">
                        <span class="flex items-center gap-1.5 truncate">
                            <i class="fa-solid fa-passport text-xs"></i> পাসপোর্ট সেবা
                        </span>
                        <i class="fa-solid fa-chevron-right text-[10px] opacity-80 shrink-0"></i>
                    </div>
                    <ul class="category-list space-y-1">
                        <li onclick="location.href='<?php echo soko_service_link($conn, 'পাসপোর্ট স্লিপ PDF'); ?>'" class="hover:text-blue-700 cursor-pointer truncate">• পাসপোর্ট স্লিপ PDF</li>
                        <li onclick="location.href='<?php echo soko_service_link($conn, 'প্রথম পাতা PDF'); ?>'" class="hover:text-blue-700 cursor-pointer truncate">• প্রথম পাতা PDF</li>
                        <li onclick="location.href='<?php echo soko_service_link($conn, 'NID দিয়ে পাসপোর্ট'); ?>'" class="hover:text-blue-700 cursor-pointer truncate">• NID দিয়ে পাসপোর্ট</li>
                        <li onclick="location.href='<?php echo soko_service_link($conn, 'ভিসা যাচাই'); ?>'" class="hover:text-blue-700 cursor-pointer truncate">• ভিসা যাচাই</li>
                        <li onclick="location.href='<?php echo soko_service_link($conn, 'পুলিশ ক্লিয়ারেন্স'); ?>'" class="hover:text-blue-700 cursor-pointer truncate">• পুলিশ ক্লিয়ারেন্স</li>
                    </ul>
                </div>
                <div class="px-2.5 py-1.5 border-t border-slate-100">
                    <a href="services.php?cat=%E0%A6%AA%E0%A6%BE%E0%A6%B8%E0%A6%AA%E0%A7%8B%E0%A6%B0%E0%A7%8D%E0%A6%9F%20%E0%A6%B8%E0%A7%87%E0%A6%AC%E0%A6%BE" class="text-[11px] font-bold text-[#0078D4] hover:underline">আরও দেখুন...</a>
                </div>
            </div>

            <!-- 3. Jonmo Nibondhon Seba -->
            <div class="category-box">
                <div>
                    <div class="category-header bg-[#7B1FA2] cursor-pointer" onclick="location.href='services.php?cat=%E0%A6%9C%E0%A6%A8%E0%A7%8D%E0%A6%AE%20%E0%A6%A8%E0%A6%BF%E0%A6%AC%E0%A6%A8%E0%A7%8D%E0%A6%A7%E0%A6%A8%20%E0%A6%B8%E0%A7%87%E0%A6%AC%E0%A6%BE'">
                        <span class="flex items-center gap-1.5 truncate">
                            <i class="fa-solid fa-file-lines text-xs"></i> জন্ম নিবন্ধন সেবা
                        </span>
                        <i class="fa-solid fa-chevron-right text-[10px] opacity-80 shrink-0"></i>
                    </div>
                    <ul class="category-list space-y-1">
                        <li onclick="location.href='<?php echo soko_service_link($conn, 'জন্ম নিবন্ধন পিডিএফ'); ?>'" class="hover:text-purple-700 cursor-pointer truncate">• জন্ম নিবন্ধন পিডিএফ</li>
                        <li onclick="location.href='<?php echo soko_service_link($conn, 'ক্লোন কপি'); ?>'" class="hover:text-purple-700 cursor-pointer truncate">• ক্লোন কপি</li>
                        <li onclick="location.href='<?php echo soko_service_link($conn, 'নতুন জন্ম নিবন্ধন'); ?>'" class="hover:text-purple-700 cursor-pointer truncate">• নতুন জন্ম নিবন্ধন</li>
                        <li onclick="location.href='<?php echo soko_service_link($conn, 'হারানো উত্তোলন'); ?>'" class="hover:text-purple-700 cursor-pointer truncate">• হারানো উত্তোলন</li>
                        <li onclick="location.href='<?php echo soko_service_link($conn, 'Server Copy'); ?>'" class="hover:text-purple-700 cursor-pointer truncate">• Server Copy</li>
                    </ul>
                </div>
                <div class="px-2.5 py-1.5 border-t border-slate-100">
                    <a href="services.php?cat=%E0%A6%9C%E0%A6%A8%E0%A7%8D%E0%A6%AE%20%E0%A6%A8%E0%A6%BF%E0%A6%AC%E0%A6%A8%E0%A7%8D%E0%A6%A7%E0%A6%A8%20%E0%A6%B8%E0%A7%87%E0%A6%AC%E0%A6%BE" class="text-[11px] font-bold text-[#7B1FA2] hover:underline">আরও দেখুন...</a>
                </div>
            </div>

            <!-- 4. bKash Nagad Rocket -->
            <div class="category-box">
                <div>
                    <div class="category-header bg-[#D81B60] cursor-pointer" onclick="location.href='services.php?cat=%E0%A6%AC%E0%A6%BF%E0%A6%95%E0%A6%BE%E0%A6%B6%20%E0%A6%A8%E0%A6%97%E0%A6%A6%20%E0%A6%B0%E0%A6%95%E0%A7%87%E0%A6%9F'">
                        <span class="flex items-center gap-1.5 truncate">
                            <i class="fa-solid fa-mobile-retro text-xs"></i> বিকাশ-নগদ-রকেট
                        </span>
                        <i class="fa-solid fa-chevron-right text-[10px] opacity-80 shrink-0"></i>
                    </div>
                    <ul class="category-list space-y-1">
                        <li onclick="location.href='<?php echo soko_service_link($conn, 'বিকাশ ইনফো'); ?>'" class="hover:text-pink-600 cursor-pointer truncate">• বিকাশ ইনফো</li>
                        <li onclick="location.href='<?php echo soko_service_link($conn, 'নগদ ইনফো'); ?>'" class="hover:text-pink-600 cursor-pointer truncate">• নগদ ইনফো</li>
                        <li onclick="location.href='<?php echo soko_service_link($conn, 'রকেট ইনফো'); ?>'" class="hover:text-pink-600 cursor-pointer truncate">• রকেট ইনফো</li>
                        <li onclick="location.href='<?php echo soko_service_link($conn, 'উপায় ইনফো'); ?>'" class="hover:text-pink-600 cursor-pointer truncate">• উপায় ইনফো</li>
                        <li onclick="location.href='<?php echo soko_service_link($conn, 'স্টেটমেন্ট তথ্য'); ?>'" class="hover:text-pink-600 cursor-pointer truncate">• স্টেটমেন্ট তথ্য</li>
                    </ul>
                </div>
                <div class="px-2.5 py-1.5 border-t border-slate-100">
                    <a href="services.php?cat=%E0%A6%AC%E0%A6%BF%E0%A6%95%E0%A6%BE%E0%A6%B6%20%E0%A6%A8%E0%A6%97%E0%A6%A6%20%E0%A6%B0%E0%A6%95%E0%A7%87%E0%A6%9F" class="text-[11px] font-bold text-[#D81B60] hover:underline">আরও দেখুন...</a>
                </div>
            </div>

            <!-- 5. Sim Biometric -->
            <div class="category-box">
                <div>
                    <div class="category-header bg-[#E65100] cursor-pointer" onclick="location.href='services.php?cat=%E0%A6%B8%E0%A6%BF%E0%A6%AE%20%E0%A6%AC%E0%A6%BE%E0%A6%AF%E0%A6%BC%E0%A7%8B%E0%A6%AE%E0%A7%87%E0%A6%9F%E0%A7%8D%E0%A6%B0%E0%A6%BF%E0%A6%95'">
                        <span class="flex items-center gap-1.5 truncate">
                            <i class="fa-solid fa-sim-card text-xs"></i> সিম বায়োমেট্রিক
                        </span>
                        <i class="fa-solid fa-chevron-right text-[10px] opacity-80 shrink-0"></i>
                    </div>
                    <ul class="category-list space-y-1">
                        <li onclick="location.href='<?php echo soko_service_link($conn, 'NID থেকে সিম বাতিল'); ?>'" class="hover:text-orange-600 cursor-pointer truncate">• NID থেকে সিম বাতিল</li>
                        <li onclick="location.href='<?php echo soko_service_link($conn, 'সকল সিম বাতিল'); ?>'" class="hover:text-orange-600 cursor-pointer truncate">• সকল সিম বাতিল</li>
                        <li onclick="location.href='<?php echo soko_service_link($conn, 'রবি বায়োমেট্রিক'); ?>'" class="hover:text-orange-600 cursor-pointer truncate">• রবি বায়োমেট্রিক</li>
                        <li onclick="location.href='<?php echo soko_service_link($conn, 'এয়ারটেল বায়োমেট্রিক'); ?>'" class="hover:text-orange-600 cursor-pointer truncate">• এয়ারটেল বায়োমেট্রিক</li>
                        <li onclick="location.href='<?php echo soko_service_link($conn, 'গ্রামীণ বায়োমেট্রিক'); ?>'" class="hover:text-orange-600 cursor-pointer truncate">• গ্রামীণ বায়োমেট্রিক</li>
                    </ul>
                </div>
                <div class="px-2.5 py-1.5 border-t border-slate-100">
                    <a href="services.php?cat=%E0%A6%B8%E0%A6%BF%E0%A6%AE%20%E0%A6%AC%E0%A6%BE%E0%A6%AF%E0%A6%BC%E0%A7%8B%E0%A6%AE%E0%A7%87%E0%A6%9F%E0%A7%8D%E0%A6%B0%E0%A6%BF%E0%A6%95" class="text-[11px] font-bold text-[#E65100] hover:underline">আরও দেখুন...</a>
                </div>
            </div>

            <!-- 6. Location Track -->
            <div class="category-box">
                <div>
                    <div class="category-header bg-[#00897B] cursor-pointer" onclick="location.href='services.php?cat=%E0%A6%B2%E0%A7%8B%E0%A6%95%E0%A7%87%E0%A6%B6%E0%A6%A8%20%E0%A6%9F%E0%A7%8D%E0%A6%B0%E0%A7%8D%E0%A6%AF%E0%A6%BE%E0%A6%95'">
                        <span class="flex items-center gap-1.5 truncate">
                            <i class="fa-solid fa-location-dot text-xs"></i> লোকেশন ট্র্যাক
                        </span>
                        <i class="fa-solid fa-chevron-right text-[10px] opacity-80 shrink-0"></i>
                    </div>
                    <ul class="category-list space-y-1">
                        <li onclick="location.href='<?php echo soko_service_link($conn, 'সকল সিমের লোকেশন'); ?>'" class="hover:text-teal-700 cursor-pointer truncate">• সকল সিমের লোকেশন</li>
                        <li onclick="location.href='<?php echo soko_service_link($conn, 'এনআইডি টু নাম্বার'); ?>'" class="hover:text-teal-700 cursor-pointer truncate">• এনআইডি টু নাম্বার</li>
                        <li onclick="location.href='<?php echo soko_service_link($conn, 'পিন পয়েন্ট লোকেশন'); ?>'" class="hover:text-teal-700 cursor-pointer truncate">• পিন পয়েন্ট লোকেশন</li>
                        <li onclick="location.href='<?php echo soko_service_link($conn, 'জিও লোকেশন'); ?>'" class="hover:text-teal-700 cursor-pointer truncate">• জিও লোকেশন</li>
                        <li onclick="location.href='<?php echo soko_service_link($conn, 'IMEI টু নাম্বার'); ?>'" class="hover:text-teal-700 cursor-pointer truncate">• IMEI টু নাম্বার</li>
                    </ul>
                </div>
                <div class="px-2.5 py-1.5 border-t border-slate-100">
                    <a href="services.php?cat=%E0%A6%B2%E0%A7%8B%E0%A6%95%E0%A7%87%E0%A6%B6%E0%A6%A8%20%E0%A6%9F%E0%A7%8D%E0%A6%B0%E0%A7%8D%E0%A6%AF%E0%A6%BE%E0%A6%95" class="text-[11px] font-bold text-[#00897B] hover:underline">আরও দেখুন...</a>
                </div>
            </div>

            <!-- 7. TIN Certificate -->
            <div class="category-box">
                <div>
                    <div class="category-header bg-[#F57F17] cursor-pointer" onclick="location.href='services.php?cat=%E0%A6%9F%E0%A6%BF%E0%A6%A8%20%E0%A6%B8%E0%A6%BE%E0%A6%B0%E0%A7%8D%E0%A6%9F%E0%A6%BF%E0%A6%AB%E0%A6%BF%E0%A6%95%E0%A7%87%E0%A6%9F'">
                        <span class="flex items-center gap-1.5 truncate">
                            <i class="fa-solid fa-file-invoice text-xs"></i> টিন সার্টিফিকেট
                        </span>
                        <i class="fa-solid fa-chevron-right text-[10px] opacity-80 shrink-0"></i>
                    </div>
                    <ul class="category-list space-y-1">
                        <li onclick="location.href='<?php echo soko_service_link($conn, 'নতুন টিন আবেদন'); ?>'" class="hover:text-amber-700 cursor-pointer truncate">• নতুন টিন আবেদন</li>
                        <li onclick="location.href='<?php echo soko_service_link($conn, 'হারানো সার্টিফিকেট'); ?>'" class="hover:text-amber-700 cursor-pointer truncate">• হারানো সার্টিফিকেট</li>
                        <li onclick="location.href='<?php echo soko_service_link($conn, 'নিজ নাম সংশোধন'); ?>'" class="hover:text-amber-700 cursor-pointer truncate">• নিজ নাম সংশোধন</li>
                        <li onclick="location.href='<?php echo soko_service_link($conn, 'অল নাম সংশোধন'); ?>'" class="hover:text-amber-700 cursor-pointer truncate">• অল নাম সংশোধন</li>
                        <li onclick="location.href='<?php echo soko_service_link($conn, 'অটো টিন জেনারেট'); ?>'" class="hover:text-amber-700 cursor-pointer truncate">• অটো টিন জেনারেট</li>
                    </ul>
                </div>
                <div class="px-2.5 py-1.5 border-t border-slate-100">
                    <a href="services.php?cat=%E0%A6%9F%E0%A6%BF%E0%A6%A8%20%E0%A6%B8%E0%A6%BE%E0%A6%B0%E0%A7%8D%E0%A6%9F%E0%A6%BF%E0%A6%AB%E0%A6%BF%E0%A6%95%E0%A7%87%E0%A6%9F" class="text-[11px] font-bold text-[#F57F17] hover:underline">আরও দেখুন...</a>
                </div>
            </div>

            <!-- 8. Trade License -->
            <div class="category-box">
                <div>
                    <div class="category-header bg-[#5E35B1] cursor-pointer" onclick="location.href='services.php?cat=%E0%A6%9F%E0%A7%8D%E0%A6%B0%E0%A7%87%E0%A6%A1%20%E0%A6%B2%E0%A6%BE%E0%A6%87%E0%A6%B8%E0%A7%87%E0%A6%A8%E0%A7%8D%E0%A6%B8'">
                        <span class="flex items-center gap-1.5 truncate">
                            <i class="fa-solid fa-store text-xs"></i> ট্রেড লাইসেন্স
                        </span>
                        <i class="fa-solid fa-chevron-right text-[10px] opacity-80 shrink-0"></i>
                    </div>
                    <ul class="category-list space-y-1">
                        <li onclick="location.href='<?php echo soko_service_link($conn, 'লাইসেন্স আবেদন'); ?>'" class="hover:text-purple-700 cursor-pointer truncate">• লাইসেন্স আবেদন</li>
                        <li onclick="location.href='<?php echo soko_service_link($conn, 'হারানো উত্তোলন'); ?>'" class="hover:text-purple-700 cursor-pointer truncate">• হারানো উত্তোলন</li>
                        <li onclick="location.href='<?php echo soko_service_link($conn, 'জিরো রিটার্ন'); ?>'" class="hover:text-purple-700 cursor-pointer truncate">• জিরো রিটার্ন</li>
                        <li onclick="location.href='<?php echo soko_service_link($conn, 'নবায়ন আবেদন'); ?>'" class="hover:text-purple-700 cursor-pointer truncate">• নবায়ন আবেদন</li>
                        <li onclick="location.href='<?php echo soko_service_link($conn, 'লাইসেন্স সংশোধন'); ?>'" class="hover:text-purple-700 cursor-pointer truncate">• লাইসেন্স সংশোধন</li>
                    </ul>
                </div>
                <div class="px-2.5 py-1.5 border-t border-slate-100">
                    <a href="services.php?cat=%E0%A6%9F%E0%A7%8D%E0%A6%B0%E0%A7%87%E0%A6%A1%20%E0%A6%B2%E0%A6%BE%E0%A6%87%E0%A6%B8%E0%A7%87%E0%A6%A8%E0%A7%8D%E0%A6%B8" class="text-[11px] font-bold text-[#5E35B1] hover:underline">আরও দেখুন...</a>
                </div>
            </div>

            <!-- 9. Bhumi Seba -->
            <div class="category-box">
                <div>
                    <div class="category-header bg-[#0288D1] cursor-pointer" onclick="location.href='services.php?cat=%E0%A6%AD%E0%A7%82%E0%A6%AE%E0%A6%BF%20%E0%A6%B8%E0%A7%87%E0%A6%AC%E0%A6%BE'">
                        <span class="flex items-center gap-1.5 truncate">
                            <i class="fa-solid fa-mountain-sun text-xs"></i> ভূমি সেবা
                        </span>
                        <i class="fa-solid fa-chevron-right text-[10px] opacity-80 shrink-0"></i>
                    </div>
                    <ul class="category-list space-y-1">
                        <li onclick="location.href='<?php echo soko_service_link($conn, 'মৌজা ম্যাপ'); ?>'" class="hover:text-sky-700 cursor-pointer truncate">• মৌজা ম্যাপ</li>
                        <li onclick="location.href='<?php echo soko_service_link($conn, 'খাজনা অনুমোদন'); ?>'" class="hover:text-sky-700 cursor-pointer truncate">• খাজনা অনুমোদন</li>
                        <li onclick="location.href='<?php echo soko_service_link($conn, 'সার্ভে খতিয়ান কপি'); ?>'" class="hover:text-sky-700 cursor-pointer truncate">• সার্ভে খতিয়ান কপি</li>
                        <li onclick="location.href='<?php echo soko_service_link($conn, 'নামজারি খতিয়ান কপি'); ?>'" class="hover:text-sky-700 cursor-pointer truncate">• নামজারি খতিয়ান কপি</li>
                        <li onclick="location.href='<?php echo soko_service_link($conn, 'খাজনা ডিসকাউন্ট'); ?>'" class="hover:text-sky-700 cursor-pointer truncate">• খাজনা ডিসকাউন্ট</li>
                    </ul>
                </div>
                <div class="px-2.5 py-1.5 border-t border-slate-100">
                    <a href="services.php?cat=%E0%A6%AD%E0%A7%82%E0%A6%AE%E0%A6%BF%20%E0%A6%B8%E0%A7%87%E0%A6%AC%E0%A6%BE" class="text-[11px] font-bold text-[#0288D1] hover:underline">আরও দেখুন...</a>
                </div>
            </div>

            <!-- 10. Abedon Seba -->
            <div class="category-box">
                <div>
                    <div class="category-header bg-[#C2185B] cursor-pointer" onclick="location.href='services.php?cat=%E0%A6%86%E0%A6%AC%E0%A7%87%E0%A6%A6%E0%A6%A8%20%E0%A6%B8%E0%A7%87%E0%A6%AC%E0%A6%BE'">
                        <span class="flex items-center gap-1.5 truncate">
                            <i class="fa-solid fa-file-signature text-xs"></i> আবেদন সেবা
                        </span>
                        <i class="fa-solid fa-chevron-right text-[10px] opacity-80 shrink-0"></i>
                    </div>
                    <ul class="category-list space-y-1">
                        <li onclick="location.href='<?php echo soko_service_link($conn, 'নতুন NID জন্য আবেদন'); ?>'" class="hover:text-pink-700 cursor-pointer truncate">• নতুন NID জন্য আবেদন</li>
                        <li onclick="location.href='<?php echo soko_service_link($conn, 'নতুন পাসপোর্ট আবেদন'); ?>'" class="hover:text-pink-700 cursor-pointer truncate">• নতুন পাসপোর্ট আবেদন</li>
                        <li onclick="location.href='<?php echo soko_service_link($conn, 'নতুন জন্ম নিবন্ধন'); ?>'" class="hover:text-pink-700 cursor-pointer truncate">• নতুন জন্ম নিবন্ধন</li>
                        <li onclick="location.href='<?php echo soko_service_link($conn, 'পল্লী বিদ্যুৎ মিটার আবেদন'); ?>'" class="hover:text-pink-700 cursor-pointer truncate">• পল্লী বিদ্যুৎ মিটার আবেদন</li>
                        <li onclick="location.href='<?php echo soko_service_link($conn, 'অন্যান্য অনলাইন আবেদন'); ?>'" class="hover:text-pink-700 cursor-pointer truncate">• অন্যান্য অনলাইন আবেদন</li>
                    </ul>
                </div>
                <div class="px-2.5 py-1.5 border-t border-slate-100">
                    <a href="services.php?cat=%E0%A6%86%E0%A6%AC%E0%A7%87%E0%A6%A6%E0%A6%A8%20%E0%A6%B8%E0%A7%87%E0%A6%AC%E0%A6%BE" class="text-[11px] font-bold text-[#C2185B] hover:underline">আরও দেখুন...</a>
                </div>
            </div>

        </div>

        <!-- Bottom Notice Marquee -->
        <div class="bg-white border border-slate-200 rounded-xl px-3 py-2 flex items-center gap-2 text-xs font-bold text-slate-700 shadow-sm">
            <i class="fa-solid fa-bullhorn text-green-700 text-xs shrink-0"></i>
            <span class="truncate text-[11px] sm:text-xs"><?php echo soko_e(soko_brand('site_notice')); ?></span>
        </div>

    </main>

    <!-- Swiper JS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script>
        var swiper = new Swiper(".sokol-slider-box", {
            loop: true,
            autoplay: { delay: 3500, disableOnInteraction: false },
            pagination: { el: ".slider-dots", clickable: true },
            speed: 600,
        });

        $('#serviceSearchInput').on('keyup', function() {
            var value = $(this).val().toLowerCase();
            $("#servicesContainer .category-box").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
            });
        });

    </script>
<?php include __DIR__ . '/includes/site_footer.php'; ?>
</body>
</html>
<?php ob_end_flush(); ?>