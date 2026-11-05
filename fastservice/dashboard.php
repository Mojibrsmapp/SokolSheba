<?php 
session_start();
include_once("includes/configuration.php");

if (isset($_SESSION['user_token'])) {
    $token_user = $_SESSION['user_token'];
    
    $stmt = $conn->prepare("SELECT id, balance, email, name, token, mobile, image, profile_pic FROM users WHERE token = ?");
    $stmt->bind_param("s", $token_user);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $id = $row["id"];
        $balance = $row["balance"];
        $email = $row["email"];
        $name = $row["name"];
        $token = $row["token"];
        $phone = $row["mobile"];
        $user_image = !empty($row["image"]) ? $row["image"] : (!empty($row["profile_pic"]) ? $row["profile_pic"] : "");
    } else {
        unset($_SESSION['user_token']);
        unset($_SESSION['user_id']);
        header("Location: index.php");
        exit();
    }
    $stmt->close();
} else {
    header("Location: index.php");
    exit();
}

$status_map = [];

$q_loc = $conn->query("SELECT service_key, status FROM location_services");
if ($q_loc) { while ($r = $q_loc->fetch_assoc()) { $status_map['loc_' . $r['service_key']] = $r['status']; } }

$q_call = $conn->query("SELECT service_key, status FROM call_sms_services");
if ($q_call) { while ($r = $q_call->fetch_assoc()) { $status_map['call_' . $r['service_key']] = $r['status']; } }

$q_imei = $conn->query("SELECT service_key, status FROM imei_services");
if ($q_imei) { while ($r = $q_imei->fetch_assoc()) { $status_map['imei_' . $r['service_key']] = $r['status']; } }

$q_mfs = $conn->query("SELECT service_key, status FROM mfs_services");
if ($q_mfs) { while ($r = $q_mfs->fetch_assoc()) { $status_map['mfs_' . $r['service_key']] = $r['status']; } }

$q_num = $conn->query("SELECT operator_key, status FROM number_to_server_services");
if ($q_num) { while ($r = $q_num->fetch_assoc()) { $status_map['num_' . $r['operator_key']] = $r['status']; } }
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>Fast Service - Portal</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="shortcut icon" href="https://fastservice.totthohub.com/assets/logo.jpg">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }

        body {
            font-family: 'Hind Siliguri', 'Plus Jakarta Sans', sans-serif;
            background: #F8FAFC;
            color: #0F172A;
            min-height: 100vh;
            padding-bottom: 24px;
        }

        .neo-nav {
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(226, 232, 240, 0.8);
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .user-hero-card {
            background: linear-gradient(135deg, #2E1065 0%, #581C87 50%, #7E22CE 100%);
            border-radius: 28px;
            box-shadow: 0 16px 36px -10px rgba(126, 34, 206, 0.35);
            position: relative;
            overflow: hidden;
        }

        .hero-pattern {
            position: absolute;
            inset: 0;
            background-image: radial-gradient(rgba(255, 255, 255, 0.12) 1px, transparent 1px);
            background-size: 16px 16px;
            opacity: 0.5;
        }

        .section-header-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 14px;
            border-radius: 9999px;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.01em;
        }

        .service-tile {
            background: #FFFFFF;
            border-radius: 20px;
            border: 1px solid #EEF2F6;
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.03);
            transition: all 0.22s ease-in-out;
            cursor: pointer;
        }

        .service-tile:hover {
            transform: translateY(-3px);
            box-shadow: 0 14px 28px -6px rgba(126, 34, 206, 0.14);
            border-color: #DDD6FE;
        }

        .icon-box {
            width: 44px;
            height: 44px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .bottom-bar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(14px);
            border-top: 1px solid #F1F5F9;
            box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.03);
        }

        @media (min-width: 769px) {
            .bottom-bar { display: none !important; }
        }
        @media (max-width: 768px) {
            body { padding-bottom: 90px; }
        }
    </style>
</head>
<body>

    <header class="neo-nav px-4 py-3">
        <div class="max-w-6xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="dashboard.php" class="flex items-center">
                    <img src="https://fastservice.totthohub.com/assets/logo_nav.png" alt="FAST SERVICE" class="h-9 w-auto object-contain">
                </a>
            </div>

            <div class="flex items-center gap-2 sm:gap-4">
                <button onclick="location.href='recharge.php'" class="flex items-center gap-2 bg-purple-50 hover:bg-purple-100 text-purple-700 px-3.5 py-1.5 rounded-full text-xs font-bold border border-purple-200 transition">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span id="balance-display"><?php echo htmlspecialchars($balance); ?></span> ৳
                    <i class="fa-solid fa-circle-plus text-purple-600 ml-0.5"></i>
                </button>

                <button id="openDrawerBtn" class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-purple-50 text-slate-700 hover:text-purple-700 flex items-center justify-center transition">
                    <i class="fa-solid fa-bars-staggered text-sm"></i>
                </button>
            </div>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-4 py-5 space-y-6">

        <div class="user-hero-card p-5 sm:p-7 text-white">
            <div class="hero-pattern"></div>
            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-5">
                
                <div class="flex items-center gap-4">
                    <div class="relative">
                        <?php if (!empty($user_image)): ?>
                            <img src="<?php echo htmlspecialchars($user_image); ?>" alt="Profile" class="w-14 h-14 rounded-2xl object-cover ring-2 ring-purple-300/40">
                        <?php else: ?>
                            <div class="w-14 h-14 rounded-2xl bg-white/10 backdrop-blur-md ring-2 ring-purple-300/40 flex items-center justify-center font-extrabold text-xl text-purple-200">
                                <?php echo strtoupper(mb_substr($name, 0, 1, "UTF-8")); ?>
                            </div>
                        <?php endif; ?>
                        <span class="absolute -bottom-1 -right-1 bg-emerald-500 w-4 h-4 rounded-full border-2 border-purple-900"></span>
                    </div>

                    <div>
                        <div class="inline-flex items-center gap-1.5 bg-white/10 px-2.5 py-0.5 rounded-md text-[11px] font-bold tracking-wide text-purple-200 mb-1">
                            <i class="fa-solid fa-shield-halved text-amber-300 text-[10px]"></i> FAST SERVICE MEMBER
                        </div>
                        <h2 class="text-xl sm:text-2xl font-bold leading-tight"><?php echo htmlspecialchars($name); ?></h2>
                        <p class="text-xs text-purple-200/80"><?php echo htmlspecialchars($email); ?></p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                    <div class="bg-white/10 backdrop-blur-md border border-white/15 px-4 py-2.5 rounded-2xl min-w-[100px] text-center">
                        <span class="text-[10px] text-purple-200 uppercase font-semibold">Total Orders</span>
                        <h4 class="text-base font-bold" id="totalOrders">0 টি</h4>
                    </div>
                    <div class="bg-white/10 backdrop-blur-md border border-white/15 px-4 py-2.5 rounded-2xl min-w-[100px] text-center">
                        <span class="text-[10px] text-purple-200 uppercase font-semibold">Today's</span>
                        <h4 class="text-base font-bold text-emerald-300" id="completedOrders">0 টি</h4>
                    </div>
                    <button onclick="location.href='recharge.php'" class="bg-gradient-to-r from-amber-400 to-orange-500 hover:from-amber-300 hover:to-orange-400 text-slate-950 font-bold text-xs px-4 py-3 rounded-2xl flex items-center gap-2 shadow-lg transition">
                        <i class="fa-solid fa-wallet"></i> রিচার্জ করুন
                    </button>
                </div>

            </div>
        </div>

        <div>
            <div class="flex items-center justify-between mb-3">
                <span class="section-header-pill bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <i class="fa-solid fa-sparkles text-emerald-600"></i> কমপ্লিমেন্টারি সার্ভিস
                </span>
                <span class="text-xs font-semibold text-slate-400">৩টি অ্যাক্টিভ টুলস</span>
            </div>

            <div class="grid grid-cols-3 gap-2.5 sm:gap-4">
                <div onclick="location.href='free_service.php?type=biometric'" class="service-tile p-3 sm:p-4 flex flex-col justify-between">
                    <div class="flex items-center justify-between mb-2">
                        <div class="icon-box bg-emerald-50 text-emerald-600"><i class="fa-solid fa-fingerprint"></i></div>
                        <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md">FREE</span>
                    </div>
                    <div>
                        <h4 class="text-xs sm:text-sm font-bold text-slate-800 line-clamp-1">ফ্রি বায়োমেট্রিক</h4>
                        <p class="text-[10px] sm:text-xs text-slate-400 mt-0.5">তাত্ক্ষণিক ভেরিফিকেশন</p>
                    </div>
                </div>

                <div onclick="location.href='free_service.php?type=server_copy'" class="service-tile p-3 sm:p-4 flex flex-col justify-between">
                    <div class="flex items-center justify-between mb-2">
                        <div class="icon-box bg-teal-50 text-teal-600"><i class="fa-solid fa-file-shield"></i></div>
                        <span class="text-[10px] font-bold text-teal-600 bg-teal-50 px-2 py-0.5 rounded-md">FREE</span>
                    </div>
                    <div>
                        <h4 class="text-xs sm:text-sm font-bold text-slate-800 line-clamp-1">ফ্রি সার্ভার কপি</h4>
                        <p class="text-[10px] sm:text-xs text-slate-400 mt-0.5">বেসিক তথ্য প্রিভিউ</p>
                    </div>
                </div>

                <div onclick="location.href='free_service.php?type=make'" class="service-tile p-3 sm:p-4 flex flex-col justify-between">
                    <div class="flex items-center justify-between mb-2">
                        <div class="icon-box bg-purple-50 text-purple-600"><i class="fa-solid fa-wand-magic-sparkles"></i></div>
                        <span class="text-[10px] font-bold text-purple-600 bg-purple-50 px-2 py-0.5 rounded-md">FREE</span>
                    </div>
                    <div>
                        <h4 class="text-xs sm:text-sm font-bold text-slate-800 line-clamp-1">ফ্রি মেক কপি</h4>
                        <p class="text-[10px] sm:text-xs text-slate-400 mt-0.5">সহজ ফাইল জেনারেটর</p>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <div class="flex items-center justify-between mb-3">
                <span class="section-header-pill bg-purple-50 text-purple-700 border border-purple-200">
                    <i class="fa-solid fa-bolt text-purple-600"></i> সার্ভার ও এনআইডি সার্ভিস
                </span>
                <span class="text-xs font-semibold text-slate-400">অটো ও ম্যানুয়াল প্রসেসিং</span>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-2.5 sm:gap-3.5">
                <div onclick="location.href='serverCopy.php'" class="service-tile p-3 text-center flex flex-col items-center justify-center gap-2">
                    <div class="icon-box bg-purple-100 text-purple-700"><i class="fa-solid fa-server"></i></div>
                    <span class="text-xs font-bold text-slate-800">অটো সার্ভার কপি</span>
                </div>

                <div onclick="location.href='sign_order.php?type=10_12_17'" class="service-tile p-3 text-center flex flex-col items-center justify-center gap-2">
                    <div class="icon-box bg-indigo-50 text-indigo-600"><i class="fa-solid fa-file-signature"></i></div>
                    <span class="text-xs font-bold text-slate-800">১০/১২/১৭ সাইন</span>
                </div>

                <div onclick="location.href='sign_order.php?type=13_form'" class="service-tile p-3 text-center flex flex-col items-center justify-center gap-2">
                    <div class="icon-box bg-violet-50 text-violet-600"><i class="fa-solid fa-id-card-clip"></i></div>
                    <span class="text-xs font-bold text-slate-800">১৩ ডিজিট/ফরম সাইন</span>
                </div>

                <div onclick="location.href='auto_service.php?type=nid'" class="service-tile p-3 text-center flex flex-col items-center justify-center gap-2">
                    <div class="icon-box bg-pink-50 text-pink-600"><i class="fa-solid fa-address-card"></i></div>
                    <span class="text-xs font-bold text-slate-800">Auto NID</span>
                </div>

                <div onclick="location.href='auto_service.php?type=password'" class="service-tile p-3 text-center flex flex-col items-center justify-center gap-2">
                    <div class="icon-box bg-sky-50 text-sky-600"><i class="fa-solid fa-key"></i></div>
                    <span class="text-xs font-bold text-slate-800">User Pass</span>
                </div>

                <div onclick="location.href='auto_service.php?type=ministry'" class="service-tile p-3 text-center flex flex-col items-center justify-center gap-2">
                    <div class="icon-box bg-rose-50 text-rose-600"><i class="fa-solid fa-building-columns"></i></div>
                    <span class="text-xs font-bold text-slate-800">Ministry Copy</span>
                </div>
            </div>
        </div>

        <div>
            <div class="flex items-center justify-between mb-3">
                <span class="section-header-pill bg-slate-100 text-slate-700 border border-slate-200">
                    <i class="fa-solid fa-sim-card text-purple-600"></i> সিম ও মোবাইল রেকর্ডস
                </span>
                <span class="text-xs font-semibold text-slate-400">টেলিকম ও সিডিআর তথ্য</span>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                <div onclick="location.href='numbertobio.php'" class="service-tile p-3.5 flex items-center gap-3">
                    <div class="icon-box bg-cyan-50 text-cyan-600"><i class="fa-solid fa-sim-card"></i></div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-800">রবি / এয়ারটেল</h4>
                        <span class="text-[10px] text-slate-400">নাম্বার টু সার্ভার</span>
                    </div>
                </div>

                <div onclick="location.href='numbertobio.php'" class="service-tile p-3.5 flex items-center gap-3">
                    <div class="icon-box bg-rose-50 text-rose-600"><i class="fa-solid fa-sim-card"></i></div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-800">বাংলালিংক / জিপি</h4>
                        <span class="text-[10px] text-slate-400">নাম্বার টু সার্ভার</span>
                    </div>
                </div>

                <div onclick="location.href='call_sms_list.php'" class="service-tile p-3.5 flex items-center gap-3">
                    <div class="icon-box bg-purple-50 text-purple-600"><i class="fa-solid fa-phone-volume"></i></div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-800">কল লিস্ট হিস্টোরি</h4>
                        <span class="text-[10px] text-slate-400">৩ মাস / ৬ মাস / ১ বছর</span>
                    </div>
                </div>

                <div onclick="location.href='call_sms_list.php'" class="service-tile p-3.5 flex items-center gap-3">
                    <div class="icon-box bg-emerald-50 text-emerald-600"><i class="fa-solid fa-comments"></i></div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-800">এসএমএস রেকর্ডস</h4>
                        <span class="text-[10px] text-slate-400">মেসেজ লগ হিস্টোরি</span>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <div class="flex items-center justify-between mb-3">
                <span class="section-header-pill bg-amber-50 text-amber-800 border border-amber-200">
                    <i class="fa-solid fa-radar text-amber-600"></i> লোকেশন ও ট্র্যাকিং সার্ভিস
                </span>
                <span class="text-xs font-semibold text-slate-400">রিয়েল-টাইম টেলিকম নেটওয়ার্ক</span>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-2.5">
                <div onclick="location.href='location_service.php?type=sim_on_radio'" class="service-tile p-3 text-center">
                    <div class="icon-box bg-amber-50 text-amber-600 mx-auto mb-2"><i class="fa-solid fa-tower-broadcast"></i></div>
                    <span class="text-[11px] font-bold text-slate-800 block">অন রেডিও লোকেশন</span>
                </div>

                <div onclick="location.href='location_service.php?type=sim_off_tower'" class="service-tile p-3 text-center">
                    <div class="icon-box bg-slate-100 text-slate-600 mx-auto mb-2"><i class="fa-solid fa-tower-cell"></i></div>
                    <span class="text-[11px] font-bold text-slate-800 block">অফ টাওয়ার লোকেশন</span>
                </div>

                <div onclick="location.href='location_service.php?type=sim_on_pinpoint'" class="service-tile p-3 text-center">
                    <div class="icon-box bg-purple-50 text-purple-600 mx-auto mb-2"><i class="fa-solid fa-crosshairs"></i></div>
                    <span class="text-[11px] font-bold text-slate-800 block">অন পিনপয়েন্ট</span>
                </div>

                <div onclick="location.href='location_service.php?type=sim_off_pinpoint'" class="service-tile p-3 text-center">
                    <div class="icon-box bg-violet-50 text-violet-600 mx-auto mb-2"><i class="fa-solid fa-location-crosshairs"></i></div>
                    <span class="text-[11px] font-bold text-slate-800 block">অফ পিনপয়েন্ট</span>
                </div>

                <div onclick="location.href='location_service.php?type=cid_sim_on'" class="service-tile p-3 text-center">
                    <div class="icon-box bg-teal-50 text-teal-600 mx-auto mb-2"><i class="fa-solid fa-satellite-dish"></i></div>
                    <span class="text-[11px] font-bold text-slate-800 block">CID অন লোকেশন</span>
                </div>

                <div onclick="location.href='location_service.php?type=cid_sim_off'" class="service-tile p-3 text-center">
                    <div class="icon-box bg-sky-50 text-sky-600 mx-auto mb-2"><i class="fa-solid fa-satellite"></i></div>
                    <span class="text-[11px] font-bold text-slate-800 block">CID অফ লোকেশন</span>
                </div>
            </div>
        </div>

        <div>
            <div class="flex items-center justify-between mb-3">
                <span class="section-header-pill bg-purple-50 text-purple-700 border border-purple-200">
                    <i class="fa-solid fa-toolbox text-purple-600"></i> আরও অন্যান্য প্রয়োজনীয় সার্ভিস
                </span>
                <span class="text-xs font-semibold text-slate-400">টিন, বিকাশ ও ফাইল রেকর্ডস</span>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div onclick="location.href='auto_tin.php'" class="service-tile p-3.5 flex items-center gap-3">
                    <div class="icon-box bg-rose-50 text-rose-600"><i class="fa-solid fa-file-invoice"></i></div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-800">অটো টিন সার্টিফিকেট</h4>
                        <span class="text-[10px] text-slate-400">তাত্ক্ষণিক জেনারেট</span>
                    </div>
                </div>

                <div onclick="location.href='imei_service.php'" class="service-tile p-3.5 flex items-center gap-3">
                    <div class="icon-box bg-sky-50 text-sky-600"><i class="fa-solid fa-mobile-screen"></i></div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-800">IMEI ট্র্যাকিং</h4>
                        <span class="text-[10px] text-slate-400">মোবাইল নম্বর ও তথ্য</span>
                    </div>
                </div>

                <div onclick="location.href='mfs_service.php'" class="service-tile p-3.5 flex items-center gap-3">
                    <div class="icon-box bg-pink-50 text-pink-600"><i class="fa-solid fa-wallet"></i></div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-800">MFS ইনফরমেশন</h4>
                        <span class="text-[10px] text-slate-400">বিকাশ / নগদ / রকেট</span>
                    </div>
                </div>

                <div onclick="location.href='order_file.php'" class="service-tile p-3.5 flex items-center gap-3">
                    <div class="icon-box bg-emerald-50 text-emerald-600"><i class="fa-solid fa-file-arrow-up"></i></div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-800">কাস্টম অর্ডার ফাইল</h4>
                        <span class="text-[10px] text-slate-400">সকল ডকুমেন্ট অর্ডার</span>
                    </div>
                </div>
            </div>
        </div>

    </main>

    <div class="fixed bottom-20 right-5 flex flex-col gap-2.5 z-40 md:bottom-7 md:right-7">
        <a href="<?php echo htmlspecialchars(get_site_setting($conn, 'whatsapp_group', '#')); ?>" target="_blank" class="w-11 h-11 rounded-full bg-emerald-500 hover:bg-emerald-600 text-white flex items-center justify-center text-xl shadow-lg transition transform hover:scale-105">
            <i class="fa-brands fa-whatsapp"></i>
        </a>
        <a href="<?php echo htmlspecialchars(get_site_setting($conn, 'telegram_link', '#')); ?>" target="_blank" class="w-11 h-11 rounded-full bg-sky-500 hover:bg-sky-600 text-white flex items-center justify-center text-xl shadow-lg transition transform hover:scale-105">
            <i class="fa-brands fa-telegram"></i>
        </a>
    </div>

    <nav class="bottom-bar fixed bottom-0 inset-x-0 py-2 flex items-center justify-around z-50">
        <a href="dashboard.php" class="flex flex-col items-center gap-1 text-purple-700 text-[11px] font-bold">
            <i class="fa-solid fa-house-chimney text-base"></i>
            <span>হোম</span>
        </a>
        <a href="work_history.php" class="flex flex-col items-center gap-1 text-slate-500 text-[11px] font-semibold">
            <i class="fa-solid fa-clock-rotate-left text-base"></i>
            <span>হিস্টোরি</span>
        </a>
        <a href="recharge.php" class="flex flex-col items-center gap-1 text-slate-500 text-[11px] font-semibold">
            <i class="fa-solid fa-wallet text-base"></i>
            <span>রিচার্জ</span>
        </a>
        <a href="profile.php" class="flex flex-col items-center gap-1 text-slate-500 text-[11px] font-semibold">
            <i class="fa-solid fa-user-circle text-base"></i>
            <span>প্রোফাইল</span>
        </a>
    </nav>

    <div class="fixed inset-0 bg-slate-950/40 backdrop-blur-sm z-50 hidden" id="drawerOverlay"></div>
    <div class="fixed top-0 right-[-320px] w-80 h-full bg-white shadow-2xl z-50 transition-all duration-300 flex flex-col" id="drawerMenu">
        <div class="p-5 bg-gradient-to-r from-purple-900 to-indigo-900 text-white flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-layer-group text-purple-300"></i>
                <h5 class="font-bold text-sm">কন্ট্রোল প্যানেল</h5>
            </div>
            <button id="closeDrawerBtn" class="text-white text-lg"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="flex-1 overflow-y-auto p-2 space-y-1">
            <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-700 hover:bg-purple-50 hover:text-purple-700 font-semibold text-xs transition"><i class="fa-solid fa-house text-purple-600 w-5"></i> হোম ড্যাশবোর্ড</a>
            <a href="serverCopy.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-700 hover:bg-purple-50 hover:text-purple-700 font-semibold text-xs transition"><i class="fa-solid fa-server text-sky-600 w-5"></i> অটো সার্ভার কপি</a>
            <a href="auto_tin.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-700 hover:bg-purple-50 hover:text-purple-700 font-semibold text-xs transition"><i class="fa-solid fa-file-invoice text-rose-600 w-5"></i> অটো টিন সার্টিফিকেট</a>
            <a href="order_file.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-700 hover:bg-purple-50 hover:text-purple-700 font-semibold text-xs transition"><i class="fa-solid fa-file-arrow-up text-emerald-600 w-5"></i> অর্ডার ফাইল</a>
            <a href="order_text.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-700 hover:bg-purple-50 hover:text-purple-700 font-semibold text-xs transition"><i class="fa-solid fa-pen-to-square text-indigo-600 w-5"></i> অর্ডার টেক্সট</a>
            <a href="numbertobio.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-700 hover:bg-purple-50 hover:text-purple-700 font-semibold text-xs transition"><i class="fa-solid fa-sim-card text-emerald-600 w-5"></i> নাম্বার টু বায়োমেট্রিক</a>
            <a href="call_sms_list.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-700 hover:bg-purple-50 hover:text-purple-700 font-semibold text-xs transition"><i class="fa-solid fa-phone-volume text-pink-600 w-5"></i> কল ও এসএমএস হিস্ট্রি</a>
            <a href="location_service.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-700 hover:bg-purple-50 hover:text-purple-700 font-semibold text-xs transition"><i class="fa-solid fa-location-dot text-indigo-600 w-5"></i> লোকেশন ট্র্যাকিং</a>
            <a href="imei_service.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-700 hover:bg-purple-50 hover:text-purple-700 font-semibold text-xs transition"><i class="fa-solid fa-mobile-screen text-amber-600 w-5"></i> IMEI সার্ভিস</a>
            <a href="mfs_service.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-700 hover:bg-purple-50 hover:text-purple-700 font-semibold text-xs transition"><i class="fa-solid fa-wallet text-purple-600 w-5"></i> MFS NID সার্ভিস</a>
            <a href="recharge.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-700 hover:bg-purple-50 hover:text-purple-700 font-semibold text-xs transition"><i class="fa-solid fa-wallet text-emerald-600 w-5"></i> ব্যালেন্স রিচার্জ</a>
            <a href="work_history.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-700 hover:bg-purple-50 hover:text-purple-700 font-semibold text-xs transition"><i class="fa-solid fa-clock-rotate-left text-violet-600 w-5"></i> অর্ডার হিস্টোরি</a>
            <a href="profile.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-700 hover:bg-purple-50 hover:text-purple-700 font-semibold text-xs transition"><i class="fa-solid fa-user-gear text-slate-600 w-5"></i> আমার প্রোফাইল</a>
            <a href="logout.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-rose-600 hover:bg-rose-50 font-bold text-xs transition"><i class="fa-solid fa-arrow-right-from-bracket w-5"></i> লগআউট</a>
        </div>
    </div>

    <script>
        $('#openDrawerBtn').click(function() {
            $('#drawerOverlay').fadeIn(200);
            $('#drawerMenu').css('right', '0');
        });

        $('#closeDrawerBtn, #drawerOverlay').click(function() {
            $('#drawerOverlay').fadeOut(200);
            $('#drawerMenu').css('right', '-320px');
        });

        function loadStats() {
            $.ajax({
                url: 'api/get_user_stats.php',
                method: 'GET',
                dataType: 'json',
                success: function(data) {
                    if (data && data.success) {
                        $('#balance-display').text(data.user_balance || '0.00');
                        $('#totalOrders').text((data.total_orders || 0) + ' টি');
                        $('#completedOrders').text((data.completed_orders || 0) + ' টি');
                    }
                }
            });
        }

        $(document).ready(function() {
            loadStats();
            setInterval(loadStats, 10000);
        });
    </script>
</body>
</html>