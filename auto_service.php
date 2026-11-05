<?php
session_start();
include_once("includes/configuration.php");

if (!isset($_SESSION['user_token'])) {
    header('Location: index.php');
    exit();
}

$token_user = $_SESSION['user_token'];

$userStatement = $conn->prepare('SELECT `id`, `name`, `email`, `balance`, `mobile` FROM `users` WHERE `token` = ? LIMIT 1');
if (!$userStatement) {
    error_log('Auto services page: failed to prepare user lookup.');
    http_response_code(500);
    exit('সার্ভারের সাথে সংযোগ স্থাপন করা সম্ভব হয়নি।');
}

$userStatement->bind_param('s', $token_user);
$userStatement->execute();
$userStatement->bind_result($userId, $userName, $userEmail, $userBalance, $userMobile);
$userFound = $userStatement->fetch();
$userStatement->close();

if (!$userFound) {
    unset($_SESSION['user_token'], $_SESSION['user_id']);
    header('Location: index.php');
    exit();
}

$query = "
    SELECT service.`id`, service.`service_name`, service.`category`, service.`price`,
           service.`description`, service.`access_link`, service.`status`, service.`service_type`,
           COALESCE(
               service.`custom_fields`,
               (
                   SELECT previous.`custom_fields`
                   FROM `custom_services` AS previous
                   WHERE previous.`category` = service.`category`
                     AND previous.`service_name` = service.`service_name`
                     AND previous.`custom_fields` IS NOT NULL
                     AND previous.`custom_fields` <> ''
                   ORDER BY previous.`id` DESC
                   LIMIT 1
               )
           ) AS `custom_fields`,
           service.`is_pinned`
    FROM `custom_services` AS service
    ORDER BY service.`category`, service.`is_pinned` DESC, service.`service_name`
";

$result = $conn->query($query);
if (!$result) {
    error_log('Auto services page: failed to load custom services.');
    http_response_code(500);
    exit('সার্ভিসের তালিকা লোড করা যায়নি।');
}

$servicesByCategory = array();
$categories = array();
$allServices = array();
$serviceCount = 0;
$restrictedTerms = array(
    'কল লিস্ট', 'কল রেকর্ড', 'সিমের লোকেশন', 'লোকেশন ট্র্যাক', 'জিও অথেনটিক', 'জিও অফিসিয়াল',
    'imei টু', 'imei to', 'সকল নাম্বার', 'সকল নম্বর', 'মোবাইল নাম্বার দিয়ে nid', 'মোবাইল নম্বর দিয়ে nid', 'বায়োমেট্রিক'
);

while ($row = $result->fetch_assoc()) {
    $serviceCount++;
    $serviceName = (string)$row['service_name'];
    $category = trim((string)$row['category']);
    $category = $category !== '' ? $category : 'অন্যান্য সেবা';

    if (!in_array($category, $categories)) {
        $categories[] = $category;
    }

    $searchText = function_exists('mb_strtolower')
        ? mb_strtolower($serviceName . ' ' . $category, 'UTF-8')
        : strtolower($serviceName . ' ' . $category);

    $isRestricted = false;
    foreach ($restrictedTerms as $term) {
        $normalizedTerm = function_exists('mb_strtolower')
            ? mb_strtolower($term, 'UTF-8')
            : strtolower($term);
        if (strpos($searchText, $normalizedTerm) !== false) {
            $isRestricted = true;
            break;
        }
    }

    $fieldDefinitions = array();
    if (!empty($row['custom_fields'])) {
        $decodedFields = json_decode($row['custom_fields'], true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decodedFields)) {
            $fieldDefinitions = $decodedFields;
        }
    }

    $accessLink = trim((string)$row['access_link']);
    $linkParts = parse_url($accessLink);
    $isLocalLink = $accessLink !== ''
        && $linkParts !== false
        && !isset($linkParts['scheme'])
        && !isset($linkParts['host'])
        && isset($linkParts['path'])
        && $linkParts['path'] !== ''
        && strpos($linkParts['path'], '/') !== 0
        && strpos($linkParts['path'], '\\') === false
        && strpos($linkParts['path'], '..') === false;

    $service = array(
        'id' => (int)$row['id'],
        'name' => $serviceName,
        'category' => $category,
        'price' => (float)$row['price'],
        'description' => (string)$row['description'],
        'fields' => $fieldDefinitions,
        'status' => strtolower(trim((string)$row['status'])),
        'type' => (string)$row['service_type'],
        'is_pinned' => (int)$row['is_pinned'],
        'link' => $isLocalLink ? $accessLink : 'order_file.php',
        'restricted' => $isRestricted
    );

    $servicesByCategory[$category][] = $service;
    $allServices[] = $service;
}
$result->free();

$escape = function ($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
};
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>সকল সার্ভিস - Sokol Sheba</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/maateen/bangla-fonts/Nikosh/stylesheet.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="shortcut icon" href="/assets/logo.jpg">
    <style>
        :root {
            --brand-green: #078B38;
            --brand-red: #E51B23;
            --dark-green: #065F2D;
            --dark-red: #B91018;
            --bg-page: #F5FAF6;
            --card-bg: #FFFFFF;
            --heading-color: #124D2B;
            --paragraph-color: #53665A;
            --border-color: #DFEBE1;
            --footer-color: #073D20;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background: var(--bg-page);
            color: var(--paragraph-color);
            font-family: 'Nikosh', 'SolaimanLipi', sans-serif;
            padding-bottom: 75px;
        }

        .fa, .fas, .fa-solid {
            font-family: "Font Awesome 6 Free" !important;
            font-weight: 900;
        }

        .tab-scroll-wrap {
            display: flex;
            gap: 8px;
            overflow-x: auto;
            padding-bottom: 8px;
            margin-bottom: 20px;
            scrollbar-width: thin;
        }
        .tab-scroll-wrap::-webkit-scrollbar {
            height: 4px;
        }
        .tab-scroll-wrap::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        .category-tab-btn {
            white-space: nowrap;
            padding: 7px 16px;
            border-radius: 9999px;
            font-size: 13px;
            font-weight: 700;
            border: 1.5px solid var(--border-color);
            background: #FFFFFF;
            color: var(--paragraph-color);
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .category-tab-btn:hover {
            border-color: var(--brand-green);
            color: var(--brand-green);
        }
        .category-tab-btn.active {
            background: var(--brand-green);
            border-color: var(--brand-green);
            color: #FFFFFF;
            box-shadow: 0 4px 12px rgba(7, 139, 56, 0.25);
        }

        .service-card-item {
            background: var(--card-bg);
            border: 1.5px solid var(--border-color);
            border-radius: 20px;
            padding: 18px;
            box-shadow: 0 4px 16px rgba(7, 139, 56, 0.04);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: all 0.25s ease;
            text-decoration: none !important;
        }
        .service-card-item:hover {
            border-color: var(--brand-green);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(7, 139, 56, 0.1);
        }

        .mobile-bottom-nav {
            display: none;
        }

        @media (max-width: 991px) {
            .mobile-bottom-nav {
                display: flex;
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                height: 64px;
                background: #FFFFFF;
                border-top: 1.5px solid var(--border-color);
                box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.08);
                z-index: 1050;
                align-items: center;
                justify-content: space-around;
                padding: 0 10px;
            }
            .m-nav-link {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                text-decoration: none !important;
                color: #64748B;
                font-size: 10.5px;
                font-weight: 700;
                flex: 1;
                transition: color 0.2s ease;
                gap: 3px;
            }
            .m-nav-link i {
                font-size: 18px;
            }
            .m-nav-link.active, .m-nav-link:hover {
                color: var(--brand-green);
            }
            .m-nav-center {
                position: relative;
                top: -16px;
                display: flex;
                align-items: center;
                justify-content: center;
                flex: 1;
            }
            .m-nav-center-btn {
                width: 50px;
                height: 50px;
                border-radius: 50%;
                background: linear-gradient(135deg, var(--brand-green) 0%, var(--dark-green) 100%);
                color: #FFFFFF;
                display: flex;
                align-items: center;
                justify-content: center;
                text-decoration: none !important;
                font-size: 20px;
                box-shadow: 0 6px 18px rgba(7, 139, 56, 0.45);
                border: 3.5px solid #FFFFFF;
                transition: transform 0.2s ease;
            }
            .m-nav-center-btn:active {
                transform: scale(0.92);
            }
        }
    </style>
</head>
<body>
    <header class="sticky top-0 z-40 border-b border-[#DFEBE1] bg-white shadow-sm">
        <div class="mx-auto flex max-w-6xl items-center justify-between gap-3 px-4 py-3">
            <div class="flex items-center gap-3">
                <a href="dashboard.php" class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200 transition" aria-label="ড্যাশবোর্ডে ফিরে যান">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <div>
                    <h1 class="text-base md:text-lg font-extrabold text-[#124D2B]">সকল সার্ভিস</h1>
                    <p class="text-xs text-[#53665A]">স্বাগতম, <?php echo $escape($userName); ?></p>
                </div>
            </div>
            <div class="flex items-center gap-2 bg-[#EAF6EE] border border-[#C8E3CF] px-3.5 py-1.5 rounded-full">
                <i class="fa-solid fa-wallet text-[#078B38] text-xs"></i>
                <span class="text-xs font-bold text-[#53665A]">ব্যালেন্স:</span>
                <span class="text-xs font-black text-[#078B38]">৳ <?php echo number_format((float)$userBalance, 2); ?></span>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-6xl space-y-6 px-4 py-5">
        
        <div class="tab-scroll-wrap" id="categoryTabs">
            <button type="button" class="category-tab-btn active" data-category="all">
                সকল সেবা (<?php echo $serviceCount; ?>)
            </button>
            <?php foreach ($categories as $catName): ?>
                <?php 
                    $catCount = isset($servicesByCategory[$catName]) ? count($servicesByCategory[$catName]) : 0; 
                ?>
                <button type="button" class="category-tab-btn" data-category="<?php echo $escape($catName); ?>">
                    <?php echo $escape($catName); ?> (<?php echo $catCount; ?>)
                </button>
            <?php endforeach; ?>
        </div>

        <?php if (empty($allServices)): ?>
            <div class="rounded-2xl border border-slate-200 bg-white p-8 text-center shadow-sm">
                <p class="font-bold text-slate-700">এখন কোনো active সার্ভিস পাওয়া যায়নি।</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3" id="servicesGrid">
                <?php foreach ($allServices as $service): 
                    $targetLink = $escape($service['link']);
                    $separator = (strpos($targetLink, '?') !== false) ? '&' : '?';
                    $targetLink .= $separator . 'service_id=' . $service['id'] . '&service_name=' . urlencode($service['name']);
                ?>
                    <div class="service-item-wrapper flex" data-category="<?php echo $escape($service['category']); ?>">
                        <a href="<?php echo ($service['status'] === 'active' && !$service['restricted']) ? $targetLink : '#'; ?>" 
                           class="service-card-item w-full <?php echo ($service['status'] !== 'active' || $service['restricted']) ? 'opacity-75 cursor-not-allowed' : ''; ?>">
                            
                            <div>
                                <div class="mb-2 flex items-start justify-between gap-2">
                                    <h3 class="text-sm font-extrabold text-slate-900"><?php echo $escape($service['name']); ?></h3>
                                    <span class="flex shrink-0 flex-wrap justify-end gap-1">
                                        <?php if ($service['type'] === 'auto'): ?>
                                            <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-800">অটো</span>
                                        <?php endif; ?>
                                        <?php if (!empty($service['is_pinned'])): ?>
                                            <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-800">জনপ্রিয়</span>
                                        <?php endif; ?>
                                    </span>
                                </div>

                                <?php if ($service['description'] !== ''): ?>
                                    <p class="mb-3 text-xs leading-relaxed text-slate-600"><?php echo $escape($service['description']); ?></p>
                                <?php endif; ?>

                                <div class="mb-3 text-sm font-extrabold text-[#078B38]">
                                    ৳<?php echo $escape(number_format((float)$service['price'], 2)); ?>
                                </div>

                                <?php if (!empty($service['fields'])): ?>
                                    <div class="mb-4 space-y-1 rounded-xl bg-[#F5FAF6] border border-[#DFEBE1] p-2.5">
                                        <h4 class="text-[11px] font-extrabold text-[#124D2B] mb-1">প্রয়োজনীয় তথ্য:</h4>
                                        <?php foreach ($service['fields'] as $field): ?>
                                            <?php if (is_array($field) && isset($field['title'])): ?>
                                                <div class="flex items-start gap-1.5 text-[11.5px] text-slate-600">
                                                    <i class="fa-solid fa-circle-dot mt-1 text-[8px] text-[#078B38]"></i>
                                                    <span>
                                                        <?php echo $escape($field['title']); ?>
                                                        <?php if (!empty($field['required'])): ?>
                                                            <span class="text-red-600">*</span>
                                                        <?php endif; ?>
                                                    </span>
                                                </div>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="mt-auto pt-2">
                                <?php if ($service['status'] !== 'active'): ?>
                                    <span class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-slate-100 px-4 py-2.5 text-xs font-bold text-slate-500">
                                        <i class="fa-solid fa-circle-pause"></i> সার্ভিস নিষ্ক্রিয়
                                    </span>
                                <?php elseif ($service['restricted']): ?>
                                    <span class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-slate-100 px-4 py-2.5 text-xs font-bold text-slate-500">
                                        <i class="fa-solid fa-shield-halved"></i> উপলভ্য নয়
                                    </span>
                                <?php else: ?>
                                    <span class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-[#078B38] px-4 py-2.5 text-xs font-bold text-white transition hover:bg-[#065F2D]">
                                        <span>সার্ভিস চালু করুন</span>
                                        <i class="fa-solid fa-arrow-right"></i>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <div class="mobile-bottom-nav">
        <a href="dashboard.php" class="m-nav-link">
            <i class="fa-solid fa-house"></i>
            <span>হোম</span>
        </a>

        <a href="dashboard.php#all_services" class="m-nav-link active">
            <i class="fa-solid fa-shapes"></i>
            <span>সেবা</span>
        </a>

        <div class="m-nav-center">
            <a href="recharge.php" class="m-nav-center-btn" title="রিচার্জ করুন">
                <i class="fa-solid fa-bolt" style="color: #ffdd44;"></i>
            </a>
        </div>

        <a href="work_history.php" class="m-nav-link">
            <i class="fa-solid fa-wallet"></i>
            <span>হিস্টোরি</span>
        </a>

        <a href="profile.php" class="m-nav-link">
            <i class="fa-solid fa-user"></i>
            <span>প্রোফাইল</span>
        </a>
    </div>

    <script>
        $(document).ready(function() {
            $('.category-tab-btn').on('click', function() {
                $('.category-tab-btn').removeClass('active');
                $(this).addClass('active');

                const selectedCategory = $(this).data('category');

                if (selectedCategory === 'all') {
                    $('.service-item-wrapper').show();
                } else {
                    $('.service-item-wrapper').each(function() {
                        if ($(this).data('category') === selectedCategory) {
                            $(this).show();
                        } else {
                            $(this).hide();
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>