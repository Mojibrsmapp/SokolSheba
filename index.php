<?php
// বাফারিং শুরু যাতে রিডাইরেক্টে কোনো 500 এরর না আসে
ob_start();
session_start();

// ইউজার লগইন থাকলে ড্যাশবোর্ডে রিডাইরেক্ট
if (isset($_SESSION['user_token']) || isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

include_once("includes/configuration.php");

// সেটিংস লোড
$settings_data = [];
if (isset($conn) && $conn) {
    $q_set = @mysqli_query($conn, "SELECT setting_key, setting_value FROM site_settings");
    if ($q_set) {
        while ($rs = mysqli_fetch_assoc($q_set)) {
            $settings_data[$rs['setting_key']] = $rs['setting_value'];
        }
    }
}

$support_num = !empty($settings_data['support_whatsapp_num']) ? $settings_data['support_whatsapp_num'] : '01964706489';
$whatsapp_link = !empty($settings_data['whatsapp_group']) ? $settings_data['whatsapp_group'] : 'https://wa.me/88' . preg_replace('/[^0-9]/', '', $support_num);
$telegram_link = !empty($settings_data['telegram_link']) ? $settings_data['telegram_link'] : 'https://t.me/Sheba_kit';

// নিরাপদ ডাইনামিক স্লাইডার লোডার
$slider_images = [
    '/assets/slider/1.jpg',
    '/assets/slider/2.jpg'
];

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
        if (!empty($temp_images)) {
            $slider_images = $temp_images;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo soko_e(soko_brand('site_name')); ?> - <?php echo soko_e(soko_brand('site_tagline')); ?></title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/maateen/bangla-fonts/Nikosh/stylesheet.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="shortcut icon" href="<?php echo soko_e(soko_brand('site_favicon')); ?>">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }

        html, body {
            max-width: 100%;
            overflow-x: hidden !important;
        }

        body {
            font-family: 'Nikosh', 'SolaimanLipi', sans-serif;
            background: #F4F7F6;
            color: #1E293B;
            min-height: 100vh;
        }

        .cat-card {
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid #E2E8F0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
            transition: all 0.25s ease;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .cat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.08);
        }

        .cat-header-ribbon {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            color: #ffffff;
            font-weight: 700;
            font-size: 15px;
            border-bottom-right-radius: 24px;
            margin-right: 12px;
            margin-top: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
        }

        .cat-icon-circle {
            width: 34px;
            height: 34px;
            background: #ffffff;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .sokol-slider-box {
            border-radius: 18px;
            overflow: hidden;
            border: 1px solid #81C784;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
            background: #E8F5E9;
        }
        .sokol-slider-box .swiper-slide img {
            width: 100%;
            height: auto;
            max-height: 280px;
            object-fit: cover;
            display: block;
        }
        @media (min-width: 768px) {
            .sokol-slider-box .swiper-slide img {
                max-height: 360px;
            }
        }

        .slider-dots .swiper-pagination-bullet {
            width: 9px;
            height: 9px;
            background: #cbd5e1;
            opacity: 1;
            margin: 0 4px !important;
            transition: all 0.3s ease;
        }
        .slider-dots .swiper-pagination-bullet-active {
            background: #15803D !important;
            transform: scale(1.2);
        }
    </style>
</head>
<body>

    <!-- ১. টপ হেডার -->
    <?php include __DIR__ . '/includes/site_header.php'; ?>

    <!-- ২. মেইন ল্যান্ডিং কন্টেন্ট -->
    <main class="max-w-7xl mx-auto px-3 sm:px-6 py-4 space-y-4 w-full">

        <!-- ফুল-উইডথ রাউন্ডেড সার্চ বার -->
        <div class="bg-white p-1 rounded-full shadow-sm border border-slate-200 flex items-center w-full">
            <div class="pl-3.5 pr-2 text-slate-400">
                <i class="fa-solid fa-magnifying-glass text-xs sm:text-sm"></i>
            </div>
            <input type="text" id="heroSearchInput" placeholder="যে কোন সার্ভিস সার্চ করুন..." class="w-full py-1.5 text-xs sm:text-sm focus:outline-none text-slate-700 bg-transparent">
            <button onclick="location.href='login.php'" class="bg-[#156839] hover:bg-green-800 text-white font-bold text-xs sm:text-sm px-5 py-1.5 sm:py-2 rounded-full transition shrink-0 shadow-sm">
                খুঁজুন
            </button>
        </div>

        <!-- ইমেজ স্লাইডার ব্যানার -->
        <div class="w-full">
            <div class="swiper sokol-slider-box">
                <div class="swiper-wrapper">
                    <?php foreach ($slider_images as $idx => $img_url): ?>
                        <div class="swiper-slide cursor-pointer" onclick="location.href='login.php'">
                            <img src="<?php echo htmlspecialchars($img_url); ?>" alt="Banner <?php echo $idx + 1; ?>">
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="slider-dots flex justify-center items-center mt-2.5"></div>
        </div>

        <!-- ৩. সকল সেবা বিভাগ -->
        <div>
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                    <div class="grid grid-cols-2 gap-0.5 text-green-700 text-xs">
                        <i class="fa-solid fa-square"></i><i class="fa-solid fa-square"></i>
                        <i class="fa-solid fa-square"></i><i class="fa-solid fa-circle text-emerald-500"></i>
                    </div>
                    <h3 class="text-base sm:text-xl font-extrabold text-slate-800">সকল সেবা বিভাগ</h3>
                </div>
                <a href="login.php" class="text-xs font-bold text-green-700 hover:underline flex items-center gap-1">
                    লগইন করে সব দেখুন <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">

                <div class="cat-card" onclick="location.href='login.php'" style="cursor:pointer;">
                    <div>
                        <div class="cat-header-ribbon bg-gradient-to-r from-[#2E7D32] to-[#4CAF50]">
                            <div class="cat-icon-circle text-[#2E7D32]"><i class="fa-solid fa-id-card"></i></div>
                            <span>NID সেবা</span>
                        </div>
                        <ul class="p-4 text-xs font-semibold text-slate-600 grid grid-cols-2 gap-y-2 gap-x-1">
                            <li>• সাইন কপি</li>
                            <li>• আইডি কার্ড</li>
                            <li>• সার্ভার কপি</li>
                            <li>• স্মার্ট কার্ড PDF</li>
                            <li>• অটো NID মেক</li>
                            <li>• আরো অনেক...</li>
                        </ul>
                    </div>
                    <div class="px-4 pb-3 pt-1 border-t border-slate-100">
                        <span class="text-xs font-bold text-[#2E7D32]">বিস্তারিত দেখুন →</span>
                    </div>
                </div>

                <div class="cat-card" onclick="location.href='login.php'" style="cursor:pointer;">
                    <div>
                        <div class="cat-header-ribbon bg-gradient-to-r from-[#1565C0] to-[#42A5F5]">
                            <div class="cat-icon-circle text-[#1565C0]"><i class="fa-solid fa-passport"></i></div>
                            <span>পাসপোর্ট সেবা</span>
                        </div>
                        <ul class="p-4 text-xs font-semibold text-slate-600 grid grid-cols-2 gap-y-2 gap-x-1">
                            <li>• পাসপোর্ট স্লিপ PDF</li>
                            <li>• প্রথম পাতা PDF</li>
                            <li>• NID দিয়ে পাসপোর্ট</li>
                            <li>• ভিসা যাচাই</li>
                            <li>• পুলিশ ক্লিয়ারেন্স</li>
                            <li>• আরো অনেক...</li>
                        </ul>
                    </div>
                    <div class="px-4 pb-3 pt-1 border-t border-slate-100">
                        <span class="text-xs font-bold text-[#1565C0]">বিস্তারিত দেখুন →</span>
                    </div>
                </div>

                <div class="cat-card" onclick="location.href='login.php'" style="cursor:pointer;">
                    <div>
                        <div class="cat-header-ribbon bg-gradient-to-r from-[#6A1B9A] to-[#AB47BC]">
                            <div class="cat-icon-circle text-[#6A1B9A]"><i class="fa-solid fa-file-lines"></i></div>
                            <span>জন্ম নিবন্ধন সেবা</span>
                        </div>
                        <ul class="p-4 text-xs font-semibold text-slate-600 grid grid-cols-2 gap-y-2 gap-x-1">
                            <li>• জন্ম নিবন্ধন পিডিএফ</li>
                            <li>• ক্লোন কপি</li>
                            <li>• নতুন জন্মনিবন্ধন</li>
                            <li>• হারানো উত্তোলন</li>
                            <li class="col-span-2">• Official Server Copy</li>
                        </ul>
                    </div>
                    <div class="px-4 pb-3 pt-1 border-t border-slate-100">
                        <span class="text-xs font-bold text-[#6A1B9A]">বিস্তারিত দেখুন →</span>
                    </div>
                </div>

                <div class="cat-card" onclick="location.href='login.php'" style="cursor:pointer;">
                    <div>
                        <div class="cat-header-ribbon bg-gradient-to-r from-[#AD1457] to-[#EC407A]">
                            <div class="cat-icon-circle text-[#AD1457]"><i class="fa-solid fa-mobile-retro"></i></div>
                            <span>বিকাশ-নগদ-রকেট সেবা</span>
                        </div>
                        <ul class="p-4 text-xs font-semibold text-slate-600 grid grid-cols-2 gap-y-2 gap-x-1">
                            <li>• বিকাশ ইনফো</li>
                            <li>• রকেট ইনফো</li>
                            <li>• নগদ ইনফো</li>
                            <li>• উপায় ইনফো</li>
                            <li class="col-span-2">• পিন রিসেট ও স্টেটমেন্ট</li>
                        </ul>
                    </div>
                    <div class="px-4 pb-3 pt-1 border-t border-slate-100">
                        <span class="text-xs font-bold text-[#AD1457]">বিস্তারিত দেখুন →</span>
                    </div>
                </div>

                <div class="cat-card" onclick="location.href='login.php'" style="cursor:pointer;">
                    <div>
                        <div class="cat-header-ribbon bg-gradient-to-r from-[#E65100] to-[#FF9800]">
                            <div class="cat-icon-circle text-[#E65100]"><i class="fa-solid fa-sim-card"></i></div>
                            <span>সিম বায়োমেট্রিক সেবা</span>
                        </div>
                        <ul class="p-4 text-xs font-semibold text-slate-600 space-y-1.5">
                            <li>• NID থেকে সিম চেক • সব সিম বায়োমেট্রিক</li>
                            <li>• রবি/এয়ারটেল/গ্রামীণ/বাংলালিংক</li>
                            <li>• কল লিস্ট (৩/৬/১২ মাস) • আরো অনেক...</li>
                        </ul>
                    </div>
                    <div class="px-4 pb-3 pt-1 border-t border-slate-100">
                        <span class="text-xs font-bold text-[#E65100]">বিস্তারিত দেখুন →</span>
                    </div>
                </div>

                <div class="cat-card" onclick="location.href='login.php'" style="cursor:pointer;">
                    <div>
                        <div class="cat-header-ribbon bg-gradient-to-r from-[#00695C] to-[#26A69A]">
                            <div class="cat-icon-circle text-[#00695C]"><i class="fa-solid fa-location-dot"></i></div>
                            <span>লোকেশন ট্র্যাক সেবা</span>
                        </div>
                        <ul class="p-4 text-xs font-semibold text-slate-600 grid grid-cols-2 gap-y-2 gap-x-1">
                            <li>• সিম থেকে লোকেশন</li>
                            <li>• এনআইডি টু নাম্বার</li>
                            <li>• পিন পয়েন্ট/ভিআইপি</li>
                            <li>• IMEI টু নাম্বার</li>
                            <li>• জিডির আবেদন</li>
                            <li>• আরো অনেক...</li>
                        </ul>
                    </div>
                    <div class="px-4 pb-3 pt-1 border-t border-slate-100">
                        <span class="text-xs font-bold text-[#00695C]">বিস্তারিত দেখুন →</span>
                    </div>
                </div>

                <div class="cat-card" onclick="location.href='login.php'" style="cursor:pointer;">
                    <div>
                        <div class="cat-header-ribbon bg-gradient-to-r from-[#F57F17] to-[#FBC02D]">
                            <div class="cat-icon-circle text-[#F57F17]"><i class="fa-solid fa-file-invoice"></i></div>
                            <span>টিন সার্টিফিকেট সেবা</span>
                        </div>
                        <ul class="p-4 text-xs font-semibold text-slate-600 grid grid-cols-2 gap-y-2 gap-x-1">
                            <li>• নতুন টিন আবেদন</li>
                            <li>• হারানো উত্তোলন</li>
                            <li>• নিজ নাম সংশোধন</li>
                            <li>• জিরো রিটার্ন</li>
                        </ul>
                    </div>
                    <div class="px-4 pb-3 pt-1 border-t border-slate-100">
                        <span class="text-xs font-bold text-[#F57F17]">বিস্তারিত দেখুন →</span>
                    </div>
                </div>

                <div class="rounded-2xl bg-gradient-to-br from-[#1B5E20] via-[#2E7D32] to-[#388E3C] text-white p-4 flex flex-col justify-between shadow-md">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-full bg-white text-green-600 flex items-center justify-center text-2xl shadow-md shrink-0">
                            <i class="fa-brands fa-whatsapp"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[11px] font-medium text-green-100">যেকোনো সেবার জন্য WhatsApp করুন</p>
                            <h4 class="text-sm font-extrabold text-amber-300 mt-0.5 truncate"><?php echo htmlspecialchars($support_num); ?></h4>
                        </div>
                    </div>
                    <div class="mt-3 flex gap-2">
                        <a href="<?php echo htmlspecialchars($whatsapp_link); ?>" target="_blank" class="flex-1 bg-amber-400 hover:bg-amber-300 text-slate-950 font-extrabold text-xs py-2 px-3 rounded-lg text-center transition">
                            মেসেজ করুন
                        </a>
                        <a href="<?php echo htmlspecialchars($telegram_link); ?>" target="_blank" class="bg-sky-500 hover:bg-sky-400 text-white font-extrabold text-xs py-2 px-3 rounded-lg text-center transition">
                            <i class="fa-brands fa-telegram"></i>
                        </a>
                    </div>
                    <p class="text-[10px] text-center bg-amber-200 text-slate-900 font-bold rounded py-1 mt-2">
                        সকল কাজ চালু আছে, অযথা কেউ বিরক্ত করবেন না। ধন্যবাদ
                    </p>
                </div>

            </div>
        </div>

        <!-- ট্রাস্ট ব্যাজ -->
        <div class="bg-white border border-slate-200 rounded-2xl p-4 grid grid-cols-2 md:grid-cols-4 gap-3 text-center shadow-sm">
            <div class="flex items-center justify-center gap-2">
                <i class="fa-solid fa-shield-halved text-xl sm:text-2xl text-green-700"></i>
                <div class="text-left">
                    <h5 class="text-xs font-extrabold text-slate-800">100% নিরাপদ</h5>
                    <p class="text-[10px] text-slate-500">ও নির্ভরযোগ্য</p>
                </div>
            </div>
            <div class="flex items-center justify-center gap-2">
                <i class="fa-solid fa-bolt text-xl sm:text-2xl text-green-700"></i>
                <div class="text-left">
                    <h5 class="text-xs font-extrabold text-slate-800">দ্রুত সেবা</h5>
                    <p class="text-[10px] text-slate-500">প্রদান</p>
                </div>
            </div>
            <div class="flex items-center justify-center gap-2">
                <i class="fa-solid fa-hand-holding-dollar text-xl sm:text-2xl text-green-700"></i>
                <div class="text-left">
                    <h5 class="text-xs font-extrabold text-slate-800">সাশ্রয়ী মূল্যে</h5>
                    <p class="text-[10px] text-slate-500">সেবা</p>
                </div>
            </div>
            <div class="flex items-center justify-center gap-2">
                <i class="fa-solid fa-headset text-xl sm:text-2xl text-green-700"></i>
                <div class="text-left">
                    <h5 class="text-xs font-extrabold text-slate-800">সার্বক্ষণিক</h5>
                    <p class="text-[10px] text-slate-500">সাপোর্ট</p>
                </div>
            </div>
        </div>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script>
        var swiper = new Swiper(".sokol-slider-box", {
            loop: true,
            autoplay: {
                delay: 3500,
                disableOnInteraction: false,
            },
            pagination: {
                el: ".slider-dots",
                clickable: true,
            },
            speed: 600,
        });

        $('#landingSearch, #heroSearchInput').on('keyup', function() {
            var value = $(this).val().toLowerCase();
            $(".cat-card").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
            });
        });
    </script>
<?php include __DIR__ . '/includes/site_footer.php'; ?>
</body>
</html>
<?php ob_end_flush(); ?>