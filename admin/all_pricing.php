<?php
session_start();
include_once("../includes/configuration.php");

$alert_msg = "";
$alert_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action_type'])) {
    $tbl = trim($_POST['target_table'] ?? '');
    $ids = $_POST['row_id'] ?? [];
    $prices = $_POST['price'] ?? [];
    $statuses = $_POST['status'] ?? [];

    if ($tbl === 'location_services') {
        for ($i = 0; $i < count($ids); $i++) {
            $up = $conn->prepare("UPDATE location_services SET price = ?, status = ? WHERE id = ?");
            $up->bind_param("dsi", $prices[$i], $statuses[$i], $ids[$i]);
            $up->execute();
            $up->close();
        }
        $alert_msg = "লোকেশন ট্র্যাকিং সার্ভিসের মূল্য ও সক্রিয়তা আপডেট হয়েছে!";
        $alert_type = "success";
    } elseif ($tbl === 'call_sms_services') {
        for ($i = 0; $i < count($ids); $i++) {
            $up = $conn->prepare("UPDATE call_sms_services SET price = ?, status = ? WHERE id = ?");
            $up->bind_param("dsi", $prices[$i], $statuses[$i], $ids[$i]);
            $up->execute();
            $up->close();
        }
        $alert_msg = "কল ও এসএমএস হিস্ট্রি সার্ভিসের মূল্য ও সক্রিয়তা আপডেট হয়েছে!";
        $alert_type = "success";
    } elseif ($tbl === 'imei_services') {
        for ($i = 0; $i < count($ids); $i++) {
            $up = $conn->prepare("UPDATE imei_services SET price = ?, status = ? WHERE id = ?");
            $up->bind_param("dsi", $prices[$i], $statuses[$i], $ids[$i]);
            $up->execute();
            $up->close();
        }
        $alert_msg = "IMEI সার্ভিসের মূল্য ও সক্রিয়তা আপডেট হয়েছে!";
        $alert_type = "success";
    } elseif ($tbl === 'mfs_services') {
        for ($i = 0; $i < count($ids); $i++) {
            $up = $conn->prepare("UPDATE mfs_services SET price = ?, status = ? WHERE id = ?");
            $up->bind_param("dsi", $prices[$i], $statuses[$i], $ids[$i]);
            $up->execute();
            $up->close();
        }
        $alert_msg = "বিকাশ/নগদ/রকেট সার্ভিসের মূল্য ও সক্রিয়তা আপডেট হয়েছে!";
        $alert_type = "success";
    } elseif ($tbl === 'number_to_server_services') {
        for ($i = 0; $i < count($ids); $i++) {
            $up = $conn->prepare("UPDATE number_to_server_services SET price = ?, status = ? WHERE id = ?");
            $up->bind_param("dsi", $prices[$i], $statuses[$i], $ids[$i]);
            $up->execute();
            $up->close();
        }
        $alert_msg = "নাম্বার টু সার্ভার সার্ভিসের মূল্য ও সক্রিয়তা আপডেট হয়েছে!";
        $alert_type = "success";
    } elseif ($tbl === 'order_text_services') {
        for ($i = 0; $i < count($ids); $i++) {
            $up = $conn->prepare("UPDATE order_text_services SET price = ?, status = ? WHERE id = ?");
            $up->bind_param("dsi", $prices[$i], $statuses[$i], $ids[$i]);
            $up->execute();
            $up->close();
        }
        $alert_msg = "অর্ডার টেক্সট সার্ভিসের মূল্য ও সক্রিয়তা আপডেট হয়েছে!";
        $alert_type = "success";
    } elseif ($tbl === 'order_file_services') {
        for ($i = 0; $i < count($ids); $i++) {
            $up = $conn->prepare("UPDATE order_file_services SET price = ?, status = ? WHERE id = ?");
            $up->bind_param("dsi", $prices[$i], $statuses[$i], $ids[$i]);
            $up->execute();
            $up->close();
        }
        $alert_msg = "অর্ডার ফাইল সার্ভিসের মূল্য ও সক্রিয়তা আপডেট হয়েছে!";
        $alert_type = "success";
    } elseif ($tbl === 'prices') {
        for ($i = 0; $i < count($ids); $i++) {
            $st_val = intval($statuses[$i]);
            $pr_val = intval($prices[$i]);
            $up = $conn->prepare("UPDATE prices SET price = ?, work_status = ? WHERE id = ?");
            $up->bind_param("iii", $pr_val, $st_val, $ids[$i]);
            $up->execute();
            $up->close();
        }
        $alert_msg = "রেগুলার সার্ভিস মূল্য তালিকা সফলভাবে আপডেট হয়েছে!";
        $alert_type = "success";
    } elseif ($tbl === 'price_list') {
        for ($i = 0; $i < count($ids); $i++) {
            $up = $conn->prepare("UPDATE price_list SET price = ? WHERE id = ?");
            $up->bind_param("di", $prices[$i], $ids[$i]);
            $up->execute();
            $up->close();
        }
        $alert_msg = "সার্ভার কপি রেট সফলভাবে আপডেট হয়েছে!";
        $alert_type = "success";
    }
}

$loc_items = $conn->query("SELECT * FROM location_services ORDER BY id ASC")->fetch_all(MYSQLI_ASSOC);
$call_items = $conn->query("SELECT * FROM call_sms_services ORDER BY id ASC")->fetch_all(MYSQLI_ASSOC);
$imei_items = $conn->query("SELECT * FROM imei_services ORDER BY id ASC")->fetch_all(MYSQLI_ASSOC);
$mfs_items = $conn->query("SELECT * FROM mfs_services ORDER BY id ASC")->fetch_all(MYSQLI_ASSOC);
$num_items = $conn->query("SELECT * FROM number_to_server_services ORDER BY id ASC")->fetch_all(MYSQLI_ASSOC);
$text_items = $conn->query("SELECT * FROM order_text_services ORDER BY id ASC")->fetch_all(MYSQLI_ASSOC);
$file_items = $conn->query("SELECT * FROM order_file_services ORDER BY id ASC")->fetch_all(MYSQLI_ASSOC);
$regular_items = $conn->query("SELECT * FROM prices ORDER BY id ASC")->fetch_all(MYSQLI_ASSOC);
$server_make_items = $conn->query("SELECT * FROM price_list ORDER BY id ASC")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Pricing Control - Sokol Kaj</title>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Hind Siliguri', 'Plus Jakarta Sans', sans-serif; background: #F8FAFC; color: #1E293B; }
        .tab-btn { padding: 9px 18px; border-radius: 12px; font-weight: 700; font-size: 13px; transition: all 0.2s; white-space: nowrap; }
        .tab-btn.active { background: #4F46E5; color: #FFFFFF; box-shadow: 0 4px 14px rgba(79, 70, 229, 0.25); }
        .tab-btn:not(.active) { background: #FFFFFF; color: #475569; border: 1.5px solid #E2E8F0; }
        .tab-btn:not(.active):hover { background: #F1F5F9; color: #1E293B; }
        .price-card { background: #FFFFFF; border: 1.5px solid #E2E8F0; border-radius: 18px; padding: 14px 16px; display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 10px; }
        .custom-input { border: 1.5px solid #CBD5E1; border-radius: 10px; padding: 7px 12px; font-weight: 700; font-size: 13px; outline: none; background: #FFFFFF; width: 100%; }
        .custom-input:focus { border-color: #4F46E5; box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.12); }
    </style>
</head>
<body class="p-4 md:p-6">

    <div class="max-w-6xl mx-auto">
        
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-3 mb-6 bg-white p-5 rounded-2xl border border-slate-200/90 shadow-sm">
            <div>
                <h2 class="text-xl font-extrabold text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-coins text-amber-500"></i> অল-ইন-ওয়ান মাস্টার প্রাইসিং ও স্ট্যাটাস কন্ট্রোল
                </h2>
                <p class="text-xs text-slate-500 mt-1">প্ল্যাটফর্মের প্রতিটি সার্ভিসের মূল্য ও চালু/বন্ধ স্ট্যাটাস পরিবর্তন করুন</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="all_orders_history.php" class="text-xs font-bold bg-indigo-50 text-indigo-600 border border-indigo-200 px-3.5 py-2 rounded-xl hover:bg-indigo-100 transition">
                    <i class="fa-solid fa-clock-rotate-left"></i> অর্ডার ও লগ হিস্ট্রি
                </a>
                <a href="dashboard.php" class="text-xs font-bold bg-slate-100 text-slate-700 px-3.5 py-2 rounded-xl hover:bg-slate-200 transition">
                    <i class="fa-solid fa-arrow-left"></i> ড্যাশবোর্ড
                </a>
            </div>
        </div>

        <?php if (!empty($alert_msg)): ?>
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl mb-5 text-xs font-bold flex items-center gap-2">
                <i class="fa-solid fa-circle-check text-emerald-600"></i> <?php echo $alert_msg; ?>
            </div>
        <?php endif; ?>

        <div class="flex gap-2 overflow-x-auto pb-3 mb-5 border-b border-slate-200">
            <button onclick="switchTab('locTab', this)" class="tab-btn active"><i class="fa-solid fa-location-dot me-1"></i> লোকেশন (<?php echo count($loc_items); ?>)</button>
            <button onclick="switchTab('callTab', this)" class="tab-btn"><i class="fa-solid fa-phone-volume me-1"></i> কল/SMS (<?php echo count($call_items); ?>)</button>
            <button onclick="switchTab('imeiTab', this)" class="tab-btn"><i class="fa-solid fa-mobile-screen me-1"></i> IMEI (<?php echo count($imei_items); ?>)</button>
            <button onclick="switchTab('mfsTab', this)" class="tab-btn"><i class="fa-solid fa-wallet me-1"></i> MFS (<?php echo count($mfs_items); ?>)</button>
            <button onclick="switchTab('numTab', this)" class="tab-btn"><i class="fa-solid fa-sim-card me-1"></i> নাম্বার টু বায়ো (<?php echo count($num_items); ?>)</button>
            <button onclick="switchTab('textTab', this)" class="tab-btn"><i class="fa-solid fa-pen-to-square me-1"></i> টেক্সট অর্ডার (<?php echo count($text_items); ?>)</button>
            <button onclick="switchTab('fileTab', this)" class="tab-btn"><i class="fa-solid fa-file-arrow-up me-1"></i> ফাইল অর্ডার (<?php echo count($file_items); ?>)</button>
            <button onclick="switchTab('regTab', this)" class="tab-btn"><i class="fa-solid fa-bars me-1"></i> রেগুলার সার্ভিস (<?php echo count($regular_items); ?>)</button>
            <button onclick="switchTab('srvMakeTab', this)" class="tab-btn"><i class="fa-solid fa-server me-1"></i> সার্ভার কপি</button>
        </div>

        <!-- ১. লোকেশন সার্ভিস -->
        <div id="locTab" class="tab-content">
            <form action="" method="POST">
                <input type="hidden" name="action_type" value="update_price">
                <input type="hidden" name="target_table" value="location_services">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-5">
                    <?php foreach ($loc_items as $item): ?>
                    <div class="price-card">
                        <input type="hidden" name="row_id[]" value="<?php echo $item['id']; ?>">
                        <div class="flex-1">
                            <span class="text-xs font-bold text-slate-800 block"><?php echo htmlspecialchars($item['service_name']); ?></span>
                            <span class="text-[10px] text-slate-400 font-mono"><?php echo htmlspecialchars($item['service_key']); ?></span>
                        </div>
                        <div class="w-28">
                            <input type="number" step="1" name="price[]" value="<?php echo floatval($item['price']); ?>" class="custom-input text-indigo-600" required>
                        </div>
                        <div class="w-28">
                            <select name="status[]" class="custom-input font-bold <?php echo ($item['status'] === 'active') ? 'text-emerald-600' : 'text-rose-600'; ?>">
                                <option value="active" <?php echo ($item['status'] === 'active') ? 'selected' : ''; ?>>চালু</option>
                                <option value="inactive" <?php echo ($item['status'] === 'inactive') ? 'selected' : ''; ?>>বন্ধ</option>
                            </select>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3.5 rounded-xl shadow transition">লোকেশন মূল্য সংরক্ষণ করুন</button>
            </form>
        </div>

        <!-- ২. কল ও এসএমএস -->
        <div id="callTab" class="tab-content hidden">
            <form action="" method="POST">
                <input type="hidden" name="action_type" value="update_price">
                <input type="hidden" name="target_table" value="call_sms_services">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-5">
                    <?php foreach ($call_items as $item): ?>
                    <div class="price-card">
                        <input type="hidden" name="row_id[]" value="<?php echo $item['id']; ?>">
                        <div class="flex-1">
                            <span class="text-xs font-bold text-slate-800 block"><?php echo htmlspecialchars($item['service_name']); ?></span>
                            <span class="text-[10px] text-slate-400 font-mono"><?php echo htmlspecialchars($item['service_key']); ?></span>
                        </div>
                        <div class="w-28"><input type="number" step="1" name="price[]" value="<?php echo floatval($item['price']); ?>" class="custom-input text-indigo-600" required></div>
                        <div class="w-28">
                            <select name="status[]" class="custom-input font-bold <?php echo ($item['status'] === 'active') ? 'text-emerald-600' : 'text-rose-600'; ?>">
                                <option value="active" <?php echo ($item['status'] === 'active') ? 'selected' : ''; ?>>চালু</option>
                                <option value="inactive" <?php echo ($item['status'] === 'inactive') ? 'selected' : ''; ?>>বন্ধ</option>
                            </select>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3.5 rounded-xl shadow transition">কল ও এসএমএস মূল্য সংরক্ষণ করুন</button>
            </form>
        </div>

        <!-- ৩. IMEI -->
        <div id="imeiTab" class="tab-content hidden">
            <form action="" method="POST">
                <input type="hidden" name="action_type" value="update_price">
                <input type="hidden" name="target_table" value="imei_services">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-5">
                    <?php foreach ($imei_items as $item): ?>
                    <div class="price-card">
                        <input type="hidden" name="row_id[]" value="<?php echo $item['id']; ?>">
                        <div class="flex-1">
                            <span class="text-xs font-bold text-slate-800 block"><?php echo htmlspecialchars($item['service_name']); ?></span>
                            <span class="text-[10px] text-slate-400 font-mono"><?php echo htmlspecialchars($item['service_key']); ?></span>
                        </div>
                        <div class="w-28"><input type="number" step="1" name="price[]" value="<?php echo floatval($item['price']); ?>" class="custom-input text-indigo-600" required></div>
                        <div class="w-28">
                            <select name="status[]" class="custom-input font-bold <?php echo ($item['status'] === 'active') ? 'text-emerald-600' : 'text-rose-600'; ?>">
                                <option value="active" <?php echo ($item['status'] === 'active') ? 'selected' : ''; ?>>চালু</option>
                                <option value="inactive" <?php echo ($item['status'] === 'inactive') ? 'selected' : ''; ?>>বন্ধ</option>
                            </select>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3.5 rounded-xl shadow transition">IMEI মূল্য সংরক্ষণ করুন</button>
            </form>
        </div>

        <!-- ৪. MFS -->
        <div id="mfsTab" class="tab-content hidden">
            <form action="" method="POST">
                <input type="hidden" name="action_type" value="update_price">
                <input type="hidden" name="target_table" value="mfs_services">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-5">
                    <?php foreach ($mfs_items as $item): ?>
                    <div class="price-card">
                        <input type="hidden" name="row_id[]" value="<?php echo $item['id']; ?>">
                        <div class="flex-1">
                            <span class="text-xs font-bold text-slate-800 block"><?php echo htmlspecialchars($item['service_name']); ?></span>
                            <span class="text-[10px] text-slate-400 font-mono"><?php echo htmlspecialchars($item['service_key']); ?></span>
                        </div>
                        <div class="w-28"><input type="number" step="1" name="price[]" value="<?php echo floatval($item['price']); ?>" class="custom-input text-indigo-600" required></div>
                        <div class="w-28">
                            <select name="status[]" class="custom-input font-bold <?php echo ($item['status'] === 'active') ? 'text-emerald-600' : 'text-rose-600'; ?>">
                                <option value="active" <?php echo ($item['status'] === 'active') ? 'selected' : ''; ?>>চালু</option>
                                <option value="inactive" <?php echo ($item['status'] === 'inactive') ? 'selected' : ''; ?>>বন্ধ</option>
                            </select>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3.5 rounded-xl shadow transition">MFS মূল্য সংরক্ষণ করুন</button>
            </form>
        </div>

        <!-- ৫. নাম্বার টু বায়ো -->
        <div id="numTab" class="tab-content hidden">
            <form action="" method="POST">
                <input type="hidden" name="action_type" value="update_price">
                <input type="hidden" name="target_table" value="number_to_server_services">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-5">
                    <?php foreach ($num_items as $item): ?>
                    <div class="price-card">
                        <input type="hidden" name="row_id[]" value="<?php echo $item['id']; ?>">
                        <div class="flex-1">
                            <span class="text-xs font-bold text-slate-800 block"><?php echo htmlspecialchars($item['operator_name']); ?></span>
                            <span class="text-[10px] text-slate-400 font-mono"><?php echo htmlspecialchars($item['operator_key']); ?></span>
                        </div>
                        <div class="w-28"><input type="number" step="1" name="price[]" value="<?php echo floatval($item['price']); ?>" class="custom-input text-indigo-600" required></div>
                        <div class="w-28">
                            <select name="status[]" class="custom-input font-bold <?php echo ($item['status'] === 'active') ? 'text-emerald-600' : 'text-rose-600'; ?>">
                                <option value="active" <?php echo ($item['status'] === 'active') ? 'selected' : ''; ?>>চালু</option>
                                <option value="inactive" <?php echo ($item['status'] === 'inactive') ? 'selected' : ''; ?>>বন্ধ</option>
                            </select>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3.5 rounded-xl shadow transition">নাম্বার টু বায়ো রেট সংরক্ষণ করুন</button>
            </form>
        </div>

        <!-- ৬. অর্ডার টেক্সট -->
        <div id="textTab" class="tab-content hidden">
            <form action="" method="POST">
                <input type="hidden" name="action_type" value="update_price">
                <input type="hidden" name="target_table" value="order_text_services">
                <div class="space-y-2.5 max-h-[580px] overflow-y-auto pr-2 mb-5">
                    <?php foreach ($text_items as $item): ?>
                    <div class="price-card">
                        <input type="hidden" name="row_id[]" value="<?php echo $item['id']; ?>">
                        <div class="flex-1">
                            <span class="text-xs font-bold text-slate-800 block"><?php echo htmlspecialchars($item['service_title']); ?></span>
                            <span class="text-[10px] text-slate-400"><?php echo htmlspecialchars($item['placeholder']); ?></span>
                        </div>
                        <div class="w-28"><input type="number" step="1" name="price[]" value="<?php echo floatval($item['price']); ?>" class="custom-input text-indigo-600" required></div>
                        <div class="w-28">
                            <select name="status[]" class="custom-input font-bold <?php echo ($item['status'] === 'active') ? 'text-emerald-600' : 'text-rose-600'; ?>">
                                <option value="active" <?php echo ($item['status'] === 'active') ? 'selected' : ''; ?>>চালু</option>
                                <option value="inactive" <?php echo ($item['status'] === 'inactive') ? 'selected' : ''; ?>>বন্ধ</option>
                            </select>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3.5 rounded-xl shadow transition">টেক্সট সার্ভিস মূল্য সংরক্ষণ করুন</button>
            </form>
        </div>

        <!-- ৭. অর্ডার ফাইল -->
        <div id="fileTab" class="tab-content hidden">
            <form action="" method="POST">
                <input type="hidden" name="action_type" value="update_price">
                <input type="hidden" name="target_table" value="order_file_services">
                <div class="space-y-2.5 max-h-[580px] overflow-y-auto pr-2 mb-5">
                    <?php foreach ($file_items as $item): ?>
                    <div class="price-card">
                        <input type="hidden" name="row_id[]" value="<?php echo $item['id']; ?>">
                        <div class="flex-1">
                            <span class="text-xs font-bold text-slate-800 block"><?php echo htmlspecialchars($item['service_title']); ?></span>
                            <span class="text-[10px] text-slate-400"><?php echo htmlspecialchars($item['placeholder']); ?></span>
                        </div>
                        <div class="w-28"><input type="number" step="1" name="price[]" value="<?php echo floatval($item['price']); ?>" class="custom-input text-indigo-600" required></div>
                        <div class="w-28">
                            <select name="status[]" class="custom-input font-bold <?php echo ($item['status'] === 'active') ? 'text-emerald-600' : 'text-rose-600'; ?>">
                                <option value="active" <?php echo ($item['status'] === 'active') ? 'selected' : ''; ?>>চালু</option>
                                <option value="inactive" <?php echo ($item['status'] === 'inactive') ? 'selected' : ''; ?>>বন্ধ</option>
                            </select>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3.5 rounded-xl shadow transition">ফাইল সার্ভিস মূল্য সংরক্ষণ করুন</button>
            </form>
        </div>

        <!-- ৮. রেগুলার সার্ভিস -->
        <div id="regTab" class="tab-content hidden">
            <form action="" method="POST">
                <input type="hidden" name="action_type" value="update_price">
                <input type="hidden" name="target_table" value="prices">
                <div class="space-y-2.5 max-h-[580px] overflow-y-auto pr-2 mb-5">
                    <?php foreach ($regular_items as $item): ?>
                    <div class="price-card">
                        <input type="hidden" name="row_id[]" value="<?php echo $item['id']; ?>">
                        <div class="flex-1">
                            <span class="text-xs font-bold text-slate-800 block"><?php echo htmlspecialchars($item['name'] ?? $item['orderName']); ?></span>
                            <span class="text-[10px] text-slate-400 font-mono"><?php echo htmlspecialchars($item['orderName']); ?> (<?php echo htmlspecialchars($item['typeOforder']); ?>)</span>
                        </div>
                        <div class="w-28"><input type="number" step="1" name="price[]" value="<?php echo intval($item['price']); ?>" class="custom-input text-indigo-600" required></div>
                        <div class="w-28">
                            <select name="status[]" class="custom-input font-bold <?php echo ($item['work_status'] == 1) ? 'text-emerald-600' : 'text-rose-600'; ?>">
                                <option value="1" <?php echo ($item['work_status'] == 1) ? 'selected' : ''; ?>>চালু</option>
                                <option value="0" <?php echo ($item['work_status'] == 0) ? 'selected' : ''; ?>>বন্ধ</option>
                            </select>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3.5 rounded-xl shadow transition">রেগুলার সার্ভিস রেট সংরক্ষণ করুন</button>
            </form>
        </div>

        <!-- ৯. সার্ভার কপি মেক রেট -->
        <div id="srvMakeTab" class="tab-content hidden">
            <form action="" method="POST">
                <input type="hidden" name="action_type" value="update_price">
                <input type="hidden" name="target_table" value="price_list">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-5">
                    <?php foreach ($server_make_items as $item): ?>
                    <div class="price-card">
                        <input type="hidden" name="row_id[]" value="<?php echo $item['id']; ?>">
                        <div class="flex-1">
                            <span class="text-xs font-bold text-slate-800 block">সার্ভার কপি কার্ড তৈরি (NID Make)</span>
                            <span class="text-[10px] text-slate-400 font-mono"><?php echo htmlspecialchars($item['un_server']); ?></span>
                        </div>
                        <div class="w-36">
                            <input type="number" step="0.5" name="price[]" value="<?php echo floatval($item['price']); ?>" class="custom-input text-indigo-600" required>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3.5 rounded-xl shadow transition">সার্ভার কপি মূল্য সংরক্ষণ করুন</button>
            </form>
        </div>

    </div>

    <script>
        function switchTab(tabId, el) {
            $('.tab-content').addClass('hidden');
            $('#' + tabId).removeClass('hidden');
            $('.tab-btn').removeClass('active');
            $(el).addClass('active');
        }
    </script>
</body>
</html>