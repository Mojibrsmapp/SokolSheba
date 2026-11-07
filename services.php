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

// সাইট সেটিংস (সাপোর্ট / হোয়াটসঅ্যাপ)
$settings_data = [];
$q_set = @$conn->query("SELECT setting_key, setting_value FROM site_settings");
if ($q_set) {
    while ($rs = $q_set->fetch_assoc()) {
        $settings_data[$rs['setting_key']] = $rs['setting_value'];
    }
}
$support_num = !empty($settings_data['support_whatsapp_num']) ? $settings_data['support_whatsapp_num'] : '01849207118';
$whatsapp_link = !empty($settings_data['whatsapp_group']) ? $settings_data['whatsapp_group'] : 'https://wa.me/88' . preg_replace('/[^0-9]/', '', $support_num);

// Database theke custom_services gulo category onujayi group kora
$services_by_cat = [];
$total_services = 0;
$q_serv = @$conn->query("SELECT id, service_name, category, price, service_type, access_link, description, status FROM custom_services WHERE status = 'active' ORDER BY category ASC, id ASC");
if ($q_serv && $q_serv->num_rows > 0) {
    while ($srv = $q_serv->fetch_assoc()) {
        $cat = !empty($srv['category']) ? trim($srv['category']) : 'অন্যান্য সেবা';
        $services_by_cat[$cat][] = $srv;
        $total_services++;
    }
}

// Category color palette helper
$category_colors = [
    'NID সেবা'          => ['bg' => '#107C41', 'icon' => 'fa-id-card', 'soft' => '#E7F4EC'],
    'পাসপোর্ট সেবা'     => ['bg' => '#0078D4', 'icon' => 'fa-passport', 'soft' => '#E7F1FB'],
    'জন্ম নিবন্ধন সেবা' => ['bg' => '#7B1FA2', 'icon' => 'fa-file-lines', 'soft' => '#F4EAF8'],
    'বিকাশ নগদ রকেট'    => ['bg' => '#D81B60', 'icon' => 'fa-mobile-retro', 'soft' => '#FCE9F0'],
    'সিম বায়োমেট্রিক'   => ['bg' => '#E65100', 'icon' => 'fa-sim-card', 'soft' => '#FDEFE7'],
    'লোকেশন ট্র্যাক'     => ['bg' => '#00897B', 'icon' => 'fa-location-dot', 'soft' => '#E6F4F2'],
    'টিন সার্টিফিকেট'   => ['bg' => '#F57F17', 'icon' => 'fa-file-invoice', 'soft' => '#FEF3E2'],
    'ট্রেড লাইসেন্স'     => ['bg' => '#5E35B1', 'icon' => 'fa-store', 'soft' => '#EEEAF8'],
    'ভূমি সেবা'         => ['bg' => '#0288D1', 'icon' => 'fa-mountain-sun', 'soft' => '#E7F4FB'],
    'আবেদন সেবা'        => ['bg' => '#C2185B', 'icon' => 'fa-file-signature', 'soft' => '#FBE9F0'],
    'অটো সার্ভিস'       => ['bg' => '#0F766E', 'icon' => 'fa-bolt', 'soft' => '#E6F4F3'],
];
$default_color = ['bg' => '#0B7A3B', 'icon' => 'fa-layer-group', 'soft' => '#E7F4EC'];

// কোন ক্যাটাগরির হিরো দেখাবে (?cat= অথবা প্রথম ক্যাটাগরি)
$all_cats = array_keys($services_by_cat);
$active_cat = isset($_GET['cat']) ? trim($_GET['cat']) : '';
if ($active_cat !== '' && !in_array($active_cat, $all_cats, true)) {
    // হুবহু না মিললে (যেমন স্পেস/এনকোডিং পার্থক্য) নরম মিল খোঁজা হয়
    $active_cat = '';
    $needle = isset($_GET['cat']) ? trim($_GET['cat']) : '';
    if ($needle !== '') {
        foreach ($all_cats as $c) {
            if (trim($c) === $needle || strpos($c, $needle) !== false || strpos($needle, $c) !== false) {
                $active_cat = $c;
                break;
            }
        }
    }
}
$is_cat_page = ($active_cat !== '' && isset($services_by_cat[$active_cat]));
if ($is_cat_page) {
    // এই পেজে শুধু ওই ক্যাটাগরির সেবাগুলোই থাকবে
    $services_by_cat = array($active_cat => $services_by_cat[$active_cat]);
}
$hero_cat = $is_cat_page ? $active_cat : (!empty($all_cats) ? $all_cats[0] : 'সকল সেবা');
$hero_meta = $category_colors[$hero_cat] ?? $default_color;

$hero_copy = [
    'NID সেবা'          => ['জাতীয় পরিচয়পত্র (NID)', 'সরকারি অনুমোদিত সকল NID সেবা এখন একই প্ল্যাটফর্মে, সহজে ও নির্ভরযোগ্যভাবে।'],
    'পাসপোর্ট সেবা'     => ['পাসপোর্ট সেবা', 'পাসপোর্ট সংক্রান্ত সকল সেবা — স্লিপ, PDF, যাচাই ও ক্লিয়ারেন্স এক জায়গায়।'],
    'জন্ম নিবন্ধন সেবা' => ['জন্ম নিবন্ধন সেবা', 'জন্ম নিবন্ধন সংক্রান্ত সকল সেবা দ্রুত ও নির্ভরযোগ্যভাবে সম্পন্ন করুন।'],
    'বিকাশ নগদ রকেট'    => ['মোবাইল ব্যাংকিং সেবা', 'বিকাশ, নগদ, রকেট ও উপায়ের তথ্য ও স্টেটমেন্ট সেবা এক জায়গায়।'],
    'সিম বায়োমেট্রিক'   => ['সিম ও বায়োমেট্রিক সেবা', 'সিম বাতিল, বায়োমেট্রিক যাচাইসহ সকল সেবা সহজে অর্ডার করুন।'],
    'লোকেশন ট্র্যাক'     => ['লোকেশন ও ট্র্যাকিং সেবা', 'মোবাইল লোকেশন ও কল লিস্ট সংক্রান্ত সেবা দ্রুত পান।'],
    'টিন সার্টিফিকেট'   => ['টিন সার্টিফিকেট সেবা', 'TIN আবেদন, সংশোধন ও সার্টিফিকেট সেবা এক প্ল্যাটফর্মে।'],
    'ট্রেড লাইসেন্স'     => ['ট্রেড লাইসেন্স সেবা', 'ট্রেড লাইসেন্স আবেদন ও উত্তোলন সেবা সহজে সম্পন্ন করুন।'],
    'ভূমি সেবা'         => ['ভূমি সেবা', 'খতিয়ান, মৌজা ম্যাপ ও খাজনা সংক্রান্ত সেবা এক জায়গায়।'],
    'আবেদন সেবা'        => ['আবেদন সেবা', 'NID, পাসপোর্ট ও মিটার সংক্রান্ত আবেদন সেবা সহজে করুন।'],
    'অটো সার্ভিস'       => ['অটো সার্ভিস', 'তাৎক্ষণিক ফলাফল পেতে অটো সার্ভিসগুলো থেকে অর্ডার করুন।'],
];
$hero_h1 = isset($hero_copy[$hero_cat]) ? $hero_copy[$hero_cat][0] : ($hero_cat !== 'সকল সেবা' ? $hero_cat : 'সকল সেবা');
$hero_sub = isset($hero_copy[$hero_cat]) ? $hero_copy[$hero_cat][1] : 'যে কোনো প্রয়োজনে সহজে ও নির্ভরযোগ্যভাবে অর্ডার করুন।';
$search_q = isset($_GET['q']) ? trim($_GET['q']) : '';
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title><?php echo $is_cat_page ? htmlspecialchars($hero_cat) . ' - ' : 'সকল সেবাসমূহ - '; ?>সকল সেবা</title>

    <link rel="shortcut icon" href="assets/logo.jpg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <style>
        :root{
            --green:#0B7A3B; --green-d:#07612E; --green-soft:#EAF6EE; --green-line:#D6EBDC;
            --red:#E51B23; --ink:#12241B; --muted:#5E7466; --line:#E4EDE7; --page:#F4F7F5; --radius:14px;
        }
        *{margin:0;padding:0;box-sizing:border-box;-webkit-tap-highlight-color:transparent}
        body{font-family:'Hind Siliguri',system-ui,-apple-system,'Segoe UI',sans-serif;background:var(--page);color:var(--ink);font-size:15px;line-height:1.5;padding-bottom:78px}
        a{text-decoration:none;color:inherit}
        button{font-family:inherit;border:0;background:none;cursor:pointer;color:inherit}

        /* ---------- top bar ---------- */
        .topbar{background:#fff;border-bottom:1px solid var(--line);position:sticky;top:0;z-index:60}
        .topbar-in{max-width:1080px;margin:0 auto;display:flex;align-items:center;gap:9px;padding:8px 12px}
        .brand{display:flex;align-items:center;gap:8px;flex:1;min-width:0}
        .brand img{width:38px;height:38px;border-radius:50%;object-fit:cover;border:2px solid var(--green);flex:0 0 38px;background:#fff}
        .brand b{display:block;font-size:19px;font-weight:700;color:var(--green);line-height:1.1;letter-spacing:-.3px}
        .brand b em{color:var(--red);font-style:normal}
        .brand small{display:block;font-size:10.5px;color:var(--muted);font-weight:500}
        .top-actions{display:flex;align-items:center;gap:6px}
        .wallet{display:flex;align-items:center;gap:6px;border:1.4px solid var(--green-line);background:#fff;border-radius:10px;padding:4px 9px}
        .wallet i{color:var(--green);font-size:12px}
        .wallet small{display:block;font-size:9px;color:var(--muted);line-height:1.1}
        .wallet b{display:block;font-size:12px;font-weight:700;line-height:1.2}
        .ib{width:36px;height:36px;border-radius:10px;border:1.4px solid var(--line);background:#fff;display:flex;align-items:center;justify-content:center;font-size:14px;position:relative;color:var(--ink)}
        .ib .dot{position:absolute;top:7px;right:8px;width:7px;height:7px;border-radius:50%;background:var(--red);border:1.5px solid #fff}
        .menu-panel{position:absolute;right:12px;top:56px;background:#fff;border:1px solid var(--line);border-radius:12px;box-shadow:0 12px 28px rgba(9,32,20,.12);padding:6px;min-width:174px;display:none;z-index:80}
        .menu-panel.is-open{display:block}
        .menu-panel a{display:flex;align-items:center;gap:9px;padding:9px 10px;border-radius:9px;font-size:13.5px;font-weight:600}
        .menu-panel a:hover{background:var(--green-soft)}
        .menu-panel a i{width:16px;color:var(--green);font-size:13px}

        /* ---------- breadcrumb ---------- */
        .crumb{background:var(--green);color:#fff}
        .crumb-in{max-width:1080px;margin:0 auto;display:flex;align-items:center;gap:9px;padding:10px 14px}
        .crumb-in .back{font-size:16px;line-height:1}
        .crumb-in b{font-size:17px;font-weight:700}
        .crumb-chip{margin-left:auto;background:rgba(255,255,255,.16);border:1px solid rgba(255,255,255,.5);color:#fff;font-size:11.5px;font-weight:700;padding:4px 10px;border-radius:9px;display:flex;align-items:center;gap:5px}

        /* ---------- shell ---------- */
        .shell{max-width:1080px;margin:0 auto;padding:11px 12px 6px}

        /* ---------- hero ---------- */
        .hero{position:relative;overflow:hidden;border-radius:16px;color:#fff;padding:13px;display:flex;align-items:center;gap:12px;background:linear-gradient(115deg,#0E8C4B 0%,#0B7A3B 48%,#096B35 100%);box-shadow:0 8px 20px rgba(11,122,59,.16);min-height:96px}
        .hero .memorial{position:absolute;right:-6px;bottom:0;height:96%;width:auto;opacity:.34;pointer-events:none;z-index:0}
        .hero .memorial svg{height:100%;width:auto;display:block}
        .hero-txt{flex:1;min-width:0;position:relative;z-index:1}
        .hero h1{font-size:16.5px;font-weight:700;line-height:1.28;letter-spacing:-.2px}
        .hero p{font-size:11.2px;opacity:.95;margin-top:3px;line-height:1.45}
        .hero-chips{display:flex;gap:5px;flex-wrap:wrap;margin-top:8px}
        .hero-chips span{display:inline-flex;align-items:center;gap:4px;background:rgba(255,255,255,.2);border:1px solid rgba(255,255,255,.36);border-radius:20px;padding:2px 8px;font-size:9.5px;font-weight:600;white-space:nowrap}
        .hero-chips i{font-size:8.5px}
        .hero-art{flex:0 0 84px;position:relative;z-index:1}
        .id-card{background:linear-gradient(160deg,#FFFFFF,#EDF6F1);color:#123225;border-radius:8px;padding:6px;box-shadow:0 8px 16px rgba(0,0,0,.22);transform:rotate(-5deg)}
        .id-card .ic-top{display:flex;align-items:center;gap:3px;margin-bottom:4px}
        .id-card .ic-flag{width:12px;height:8px;border-radius:2px;background:#006A4E;position:relative;flex:0 0 12px}
        .id-card .ic-flag:after{content:"";position:absolute;left:3.5px;top:2px;width:4px;height:4px;border-radius:50%;background:#F42A41}
        .id-card .ic-top b{font-size:5.6px;line-height:1.15;font-weight:700;color:#0B5C31}
        .id-card .ic-body{display:flex;gap:3.5px}
        .id-card .ic-photo{width:21px;height:26px;border-radius:3px;background:#DCE7E1;flex:0 0 21px;position:relative;overflow:hidden}
        .id-card .ic-photo:after{content:"";position:absolute;left:5px;top:5px;width:11px;height:11px;border-radius:50%;background:#B9CEC3}
        .id-card .ic-photo:before{content:"";position:absolute;left:2px;bottom:-4px;width:17px;height:12px;border-radius:50% 50% 0 0;background:#B9CEC3}
        .id-card .ic-lines{flex:1;display:flex;flex-direction:column;gap:2.2px;justify-content:center}
        .id-card .ic-lines i{display:block;height:2.4px;border-radius:2px;background:#D3E2D9}
        .id-card .ic-lines i:nth-child(2){width:78%}
        .id-card .ic-lines i:nth-child(3){width:60%}
        .id-card .ic-bottom{margin-top:4px;height:2.4px;border-radius:2px;background:#E3EDE7}

        /* ---------- search ---------- */
        .search-row{display:flex;align-items:center;gap:7px;margin-top:11px}
        .search{flex:1;display:flex;align-items:center;gap:7px;background:#fff;border:1px solid var(--line);border-radius:11px;padding:8px 11px}
        .search i{color:#8AA396;font-size:12.5px}
        .search input{flex:1;border:0;outline:none;font-size:12.8px;background:transparent;color:var(--ink)}
        .search input::placeholder{color:#9DB0A4}
        .filter-btn{display:flex;align-items:center;gap:5px;background:var(--green);color:#fff;border-radius:11px;padding:9px 12px;font-size:12px;font-weight:700;box-shadow:0 3px 10px rgba(11,122,59,.2)}

        /* ---------- category tabs ---------- */
        .tabs{display:none;gap:6px;overflow-x:auto;padding:9px 0 2px;scrollbar-width:none}
        .tabs.is-open{display:flex}
        .tabs::-webkit-scrollbar{display:none}
        .tab{white-space:nowrap;background:#fff;border:1px solid var(--line);color:var(--muted);font-size:11.5px;font-weight:700;padding:6px 11px;border-radius:9px;cursor:pointer}
        .tab.is-on{background:var(--green);color:#fff;border-color:var(--green)}

        /* ---------- section head ---------- */
        .sec-head{display:flex;align-items:center;gap:7px;margin:14px 0 9px}
        .sec-head .sh-ic{width:22px;height:22px;border-radius:7px;background:var(--green-soft);color:var(--green);display:flex;align-items:center;justify-content:center;font-size:11px}
        .sec-head h2{font-size:14.5px;font-weight:700}
        .sec-head .sh-count{margin-left:auto;font-size:10.5px;font-weight:700;color:var(--muted);background:#fff;border:1px solid var(--line);border-radius:20px;padding:2px 9px}

        /* ---------- service cards ---------- */
        .cat-block{margin-bottom:14px}
        .cat-title{display:none}
        .grid{display:grid;grid-template-columns:1fr 1fr;gap:8px}
        @media (min-width:720px){
            .grid{grid-template-columns:repeat(3,1fr);gap:11px}
            .hero{padding:18px 20px;min-height:118px}
            .hero h1{font-size:20px}
            .hero p{font-size:12.5px;max-width:520px}
            .hero-art{flex:0 0 104px}
            .hero .memorial{height:104%;right:4px;opacity:.4}
            .sc-ic{width:36px;height:36px;flex:0 0 36px;font-size:15px}
            .sc-txt h4{font-size:13.5px}
            .sc-txt p{font-size:10.8px}
            .price{font-size:11.5px;padding:4px 10px}
            .order-btn{font-size:11px;padding:6px 12px}
            .sec-head h2{font-size:16px}
            .search input{font-size:13.5px}
        }
        @media (min-width:1040px){
            .grid{grid-template-columns:repeat(4,1fr)}
            .shell{padding:16px 18px 8px}
        }
        .service-card{background:#fff;border:1px solid var(--line);border-radius:12px;padding:9px;display:flex;flex-direction:column;justify-content:space-between;gap:9px;transition:.15s}
        .service-card:hover{border-color:#B7D9C4;box-shadow:0 5px 14px rgba(11,122,59,.08)}
        .sc-top{display:flex;gap:7px;align-items:flex-start}
        .sc-ic{width:30px;height:30px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:13px;flex:0 0 30px}
        .sc-txt{min-width:0;flex:1}
        .sc-txt h4{font-size:11.8px;font-weight:700;line-height:1.3;display:-webkit-box;-webkit-line-clamp:1;-webkit-box-orient:vertical;overflow:hidden}
        .sc-txt p{font-size:9.6px;color:var(--muted);line-height:1.35;margin-top:2px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
        .sc-bottom{display:flex;align-items:center;justify-content:space-between;gap:6px;border-top:1px solid #F1F6F3;padding-top:7px}
        .price{display:inline-flex;align-items:center;gap:3px;background:var(--green-soft);color:var(--green-d);font-size:10.5px;font-weight:700;padding:3px 8px;border-radius:20px}
        .price i{font-size:9px}
        .order-btn{background:var(--green);color:#fff;font-size:10px;font-weight:700;padding:5px 10px;border-radius:20px;display:inline-flex;align-items:center;gap:4px;box-shadow:0 2px 7px rgba(11,122,59,.22)}
        .order-btn i{font-size:8px}

        /* ---------- help strip ---------- */
        .help{display:flex;align-items:center;gap:9px;background:var(--green-soft);border:1px solid var(--green-line);border-radius:12px;padding:9px 11px;margin-top:4px}
        .help .h-ic{width:24px;height:24px;border-radius:50%;background:#1E9C55;color:#fff;display:flex;align-items:center;justify-content:center;font-size:10px;flex:0 0 24px}
        .help .h-txt{flex:1;min-width:0}
        .help b{display:block;font-size:11.3px;font-weight:700;color:var(--green-d)}
        .help small{display:block;font-size:9.6px;color:#4C6B58;line-height:1.35}
        .wa-btn{background:#25D366;color:#fff;font-size:10.3px;font-weight:700;padding:6px 10px;border-radius:9px;display:inline-flex;align-items:center;gap:5px;flex:0 0 auto}
        .note-strip{display:flex;align-items:center;gap:6px;background:#EBF5EE;border:1px solid #DCEFE2;border-radius:9px;padding:6px 10px;margin-top:8px;font-size:9.6px;color:#3D6450;font-weight:600}

        /* ---------- bottom nav ---------- */
        .bnav{position:fixed;left:0;right:0;bottom:0;z-index:70;background:#fff;border-top:1px solid var(--line);box-shadow:0 -4px 16px rgba(0,0,0,.05)}
        .bnav-in{max-width:1080px;margin:0 auto;display:flex;align-items:center;justify-content:space-around;padding:7px 6px 9px;position:relative}
        .bnav a{display:flex;flex-direction:column;align-items:center;gap:2px;font-size:10px;font-weight:600;color:#7C8F84;flex:1}
        .bnav a i{font-size:15px}
        .bnav a.is-on{color:var(--green-d);font-weight:700}
        .fab{position:absolute;left:50%;transform:translate(-50%,-52%);top:0;width:46px;height:46px;border-radius:50%;background:linear-gradient(140deg,#12241B,#0B7A3B);color:#fff;display:flex;align-items:center;justify-content:center;font-size:17px;box-shadow:0 6px 16px rgba(11,122,59,.35);border:3px solid #fff}
        .bnav .spacer{flex:1}

        @media (max-width:360px){
            .hero-art{display:none}
            .brand b{font-size:17px}
        }
    </style>
</head>
<body>

<!-- ================= header ================= -->
<header class="topbar">
    <div class="topbar-in">
        <a href="dashboard.php" class="brand">
            <img src="assets/logo.jpg" alt="সকল সেবা">
            <span>
                <b>সকল <em>সেবা</em></b>
                <small>সব কাজ, এক প্লাটফর্ম</small>
            </span>
        </a>
        <div class="top-actions">
            <a href="recharge.php" class="wallet">
                <i class="fa-solid fa-wallet"></i>
                <span>
                    <small>ওয়ালেট</small>
                    <b>৳ <?php echo number_format((float)$balance, 2); ?></b>
                </span>
            </a>
            <a href="support.php" class="ib" aria-label="নোটিফিকেশন"><i class="fa-regular fa-bell"></i><span class="dot"></span></a>
            <button type="button" class="ib" id="menuBtn" aria-label="মেনু"><i class="fa-solid fa-bars"></i></button>
        </div>
    </div>
    <div class="menu-panel" id="menuPanel">
        <a href="dashboard.php"><i class="fa-solid fa-house"></i> হোম</a>
        <a href="recharge.php"><i class="fa-solid fa-wallet"></i> ওয়ালেট</a>
        <a href="work_history.php"><i class="fa-solid fa-file-invoice"></i> অর্ডার হিস্টরি</a>
        <a href="profile.php"><i class="fa-regular fa-user"></i> প্রোফাইল</a>
        <a href="support.php"><i class="fa-solid fa-headset"></i> সাপোর্ট</a>
    </div>
</header>

<!-- ================= breadcrumb ================= -->
<div class="crumb">
    <div class="crumb-in">
        <a href="dashboard.php" class="back" aria-label="পিছনে"><i class="fa-solid fa-arrow-left"></i></a>
        <b><?php echo htmlspecialchars($hero_cat); ?></b>
        <a href="services.php" class="crumb-chip"><i class="fa-solid fa-layer-group"></i> <?php echo $is_cat_page ? 'সব ক্যাটাগরি' : 'সকল সেবা'; ?></a>
    </div>
</div>

<main class="shell">

    <!-- ================= hero ================= -->
    <section class="hero">
        <span class="memorial" aria-hidden="true">
            <svg viewBox="0 0 140 104" fill="none" xmlns="http://www.w3.org/2000/svg">
                <g fill="rgba(255,255,255,.62)">
                    <polygon points="14,92 22,44 30,92"/>
                    <polygon points="33,92 43,30 53,92"/>
                    <polygon points="56,92 68,18 80,92"/>
                    <polygon points="83,92 93,30 103,92"/>
                    <polygon points="106,92 114,44 122,92"/>
                </g>
                <rect x="8" y="92" width="124" height="4" rx="2" fill="rgba(255,255,255,.55)"/>
                <rect x="18" y="97" width="104" height="3" rx="1.5" fill="rgba(255,255,255,.32)"/>
            </svg>
        </span>

        <div class="hero-art" aria-hidden="true">
            <div class="id-card">
                <div class="ic-top">
                    <span class="ic-flag"></span>
                    <b>গণপ্রজাতন্ত্রী<br>বাংলাদেশ</b>
                </div>
                <div class="ic-body">
                    <span class="ic-photo"></span>
                    <span class="ic-lines"><i></i><i></i><i></i></span>
                </div>
                <div class="ic-bottom"></div>
            </div>
        </div>

        <div class="hero-txt">
            <h1 id="heroTitle"><?php echo htmlspecialchars($hero_h1); ?></h1>
            <p id="heroSub"><?php echo htmlspecialchars($hero_sub); ?></p>
            <div class="hero-chips">
                <span><i class="fa-solid fa-circle-check"></i> সঠিক সেবা</span>
                <span><i class="fa-solid fa-circle-check"></i> নির্ভরযোগ্য লেনদেন</span>
                <span><i class="fa-solid fa-circle-check"></i> বিশ্বস্ত সেবা</span>
            </div>
        </div>
    </section>

    <!-- ================= search ================= -->
    <div class="search-row">
        <div class="search">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="serviceSearchInput" value="<?php echo htmlspecialchars($search_q); ?>" placeholder="<?php echo htmlspecialchars($hero_cat); ?> খুঁজুন...">
        </div>
        <?php if (!$is_cat_page): ?>
            <button type="button" class="filter-btn" id="filterBtn"><i class="fa-solid fa-sliders"></i> ফিল্টার</button>
        <?php endif; ?>
    </div>

    <!-- category tabs (ফিল্টার বাটনে খোলে) -->
    <div class="tabs" id="catTabs">
        <button type="button" class="tab is-on" data-cat="all" data-title="সকল সেবা" data-sub="প্রয়োজনীয় সব সেবা এক জায়গায় — সহজে ও নির্ভরযোগ্যভাবে অর্ডার করুন।">সবগুলো</button>
        <?php foreach ($services_by_cat as $cName => $items): ?>
            <button type="button" class="tab" data-cat="<?php echo md5($cName); ?>" data-title="<?php echo htmlspecialchars(isset($hero_copy[$cName]) ? $hero_copy[$cName][0] : $cName); ?>" data-sub="<?php echo htmlspecialchars(isset($hero_copy[$cName]) ? $hero_copy[$cName][1] : 'যে কোনো প্রয়োজনে সহজে ও নির্ভরযোগ্যভাবে অর্ডার করুন।'); ?>">
                <?php echo htmlspecialchars($cName); ?>
            </button>
        <?php endforeach; ?>
    </div>

    <!-- ================= services ================= -->
    <div id="servicesListWrapper">
        <?php if (!empty($services_by_cat)): ?>
            <?php $is_first_block = true; foreach ($services_by_cat as $catName => $items): ?>
                <?php
                    $color_meta = $category_colors[$catName] ?? $default_color;
                    $catId = md5($catName);
                ?>
                <div class="cat-block" data-category="<?php echo $catId; ?>" data-cat-name="<?php echo htmlspecialchars($catName); ?>" data-cat-color="<?php echo $color_meta['bg']; ?>" data-cat-icon="<?php echo $color_meta['icon']; ?>">
                    <div class="sec-head">
                        <span class="sh-ic"><i class="fa-solid fa-table-cells-large"></i></span>
                        <h2><?php echo htmlspecialchars($catName); ?>সমূহ</h2>
                        <span class="sh-count"><?php echo count($items); ?> টি সেবা</span>
                    </div>
                    <div class="grid">
                        <?php foreach ($items as $srv): ?>
                            <?php
                                $srv_type = isset($srv['service_type']) ? strtolower(trim($srv['service_type'])) : 'custom';
                                if ($srv_type === 'auto' && !empty($srv['access_link'])) {
                                    $srv_link = $srv['access_link'];
                                } else {
                                    $srv_link = 'service_order.php?id=' . (int)$srv['id'];
                                }
                                $srv_icon = function_exists('soko_service_icon') ? soko_service_icon($srv['service_name'], $catName) : $color_meta['icon'];
                                $srv_desc = !empty($srv['description']) ? $srv['description'] : 'দ্রুত ও নির্ভরযোগ্যভাবে সেবা নিন।';
                            ?>
                            <div class="service-card service-item">
                                <div class="sc-top">
                                    <span class="sc-ic" style="background:<?php echo $color_meta['soft']; ?>;color:<?php echo $color_meta['bg']; ?>">
                                        <i class="fa-solid <?php echo htmlspecialchars($srv_icon); ?>"></i>
                                    </span>
                                    <div class="sc-txt">
                                        <h4><?php echo htmlspecialchars($srv['service_name']); ?></h4>
                                        <p><?php echo htmlspecialchars($srv_desc); ?></p>
                                    </div>
                                </div>
                                <div class="sc-bottom">
                                    <span class="price"><i class="fa-solid fa-bangladeshi-taka-sign"></i> <?php echo (int)$srv['price']; ?></span>
                                    <a href="<?php echo htmlspecialchars($srv_link); ?>" class="order-btn"><i class="fa-solid fa-cart-shopping"></i> অর্ডার করুন</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="help">
                <span class="h-ic"><i class="fa-solid fa-check"></i></span>
                <div class="h-txt">
                    <b>সাহায্য প্রয়োজন?</b>
                    <small>যেকোনো প্রয়োজনে অ্যাডমিনের সাথে যোগাযোগ করুন।</small>
                </div>
                <a class="wa-btn" href="<?php echo htmlspecialchars($whatsapp_link); ?>" target="_blank" rel="noopener">
                    <i class="fa-brands fa-whatsapp"></i> মেসেজ করুন
                </a>
            </div>

            <div class="note-strip">
                <i class="fa-solid fa-circle-info"></i>
                <span>সকল কাজ শুরু আছে — যে কোন প্রয়োজনে অ্যাডমিনের সাথে সরাসরি যোগাযোগ করুন।</span>
            </div>
        <?php else: ?>
            <div style="text-align:center;padding:40px 12px;background:#fff;border:1px solid var(--line);border-radius:12px">
                <i class="fa-solid fa-folder-open" style="font-size:30px;color:#C7D6CD;margin-bottom:8px"></i>
                <p style="font-size:12px;font-weight:700;color:var(--muted)">ডাটাবেজে বর্তমানে কোনো সার্ভিস পাওয়া যায়নি।</p>
            </div>
        <?php endif; ?>
    </div>

</main>

<!-- ================= bottom nav ================= -->
<nav class="bnav">
    <div class="bnav-in">
        <a href="dashboard.php"><i class="fa-solid fa-house"></i><span>হোম</span></a>
        <a href="services.php" class="is-on"><i class="fa-solid fa-table-cells-large"></i><span>সেবা</span></a>
        <a href="services.php" class="fab" aria-label="অর্ডার"><i class="fa-solid fa-bolt"></i></a>
        <span class="spacer"></span>
        <a href="recharge.php"><i class="fa-solid fa-wallet"></i><span>ওয়ালেট</span></a>
        <a href="profile.php"><i class="fa-regular fa-user"></i><span>প্রোফাইল</span></a>
    </div>
</nav>

<script>
(function () {
    /* ---- menu ---- */
    var menuBtn = document.getElementById('menuBtn');
    var menuPanel = document.getElementById('menuPanel');
    if (menuBtn && menuPanel) {
        menuBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            menuPanel.classList.toggle('is-open');
        });
        document.addEventListener('click', function () { menuPanel.classList.remove('is-open'); });
    }

    /* ---- filter tabs ---- */
    var filterBtn = document.getElementById('filterBtn');
    var catTabs = document.getElementById('catTabs');
    if (filterBtn && catTabs) {
        filterBtn.addEventListener('click', function () { catTabs.classList.toggle('is-open'); });
    }

    var tabs = document.querySelectorAll('.tab');
    var blocks = document.querySelectorAll('.cat-block');
    var heroTitle = document.getElementById('heroTitle');
    var heroSub = document.getElementById('heroSub');

    /* ---- live search ---- */
    var searchInput = document.getElementById('serviceSearchInput');
    function applyServiceSearch() {
        var value = (searchInput ? searchInput.value : '').toLowerCase().trim();
        var items = document.querySelectorAll('.service-item');
        Array.prototype.forEach.call(items, function (item) {
            var match = item.textContent.toLowerCase().indexOf(value) > -1;
            item.style.display = match ? '' : 'none';
        });
        Array.prototype.forEach.call(blocks, function (block) {
            var visible = block.querySelectorAll('.service-item:not([style*="display: none"])').length;
            block.style.display = visible > 0 ? '' : 'none';
        });
    }
    if (searchInput) {
        searchInput.addEventListener('input', applyServiceSearch);
        if (searchInput.value.trim() !== '') { applyServiceSearch(); }
    }

    /* ---- category filter ---- */
    Array.prototype.forEach.call(tabs, function (tab) {
        tab.addEventListener('click', function () {
            Array.prototype.forEach.call(tabs, function (t) { t.classList.remove('is-on'); });
            tab.classList.add('is-on');

            var cat = tab.getAttribute('data-cat');
            Array.prototype.forEach.call(blocks, function (block) {
                var show = (cat === 'all') || (block.getAttribute('data-category') === cat);
                block.setAttribute('data-hidden', show ? '0' : '1');
                block.style.display = show ? '' : 'none';
            });

            if (cat !== 'all') {
                var shown = document.querySelector('.cat-block:not([style*="display: none"])');
                if (shown && heroTitle) {
                    heroTitle.textContent = tab.getAttribute('data-title') || tab.textContent.trim();
                    if (heroSub) { heroSub.textContent = tab.getAttribute('data-sub') || ''; }
                }
            } else if (heroTitle) {
                heroTitle.textContent = tab.getAttribute('data-title') || 'সকল সেবা';
                if (heroSub) { heroSub.textContent = tab.getAttribute('data-sub') || ''; }
            }

            if (searchInput && searchInput.value.trim() !== '') { applyServiceSearch(); }
        });
    });
})();
</script>
</body>
</html>
