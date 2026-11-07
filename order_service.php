<?php 
session_start();
include_once("includes/configuration.php");
include_once("includes/service_catalog.php");

if (isset($_SESSION['user_token'])) {
    $token_user = $_SESSION['user_token'];

    $stmt = $conn->prepare("SELECT id, balance, email, name, token, mobile FROM users WHERE token = ?");
    $stmt->bind_param("s", $token_user);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $id = $row["id"];
        $balance = $row["balance"];
        $email = $row["email"];
        $name = $row["name"];
    } else {
        header("Location: index.php");
        exit();
    }
    $stmt->close();
} else {
    header("Location: index.php");
    exit();
}

$service_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($service_id <= 0) {
    header("Location: dashboard.php");
    exit();
}

$srv_stmt = $conn->prepare("SELECT * FROM custom_services WHERE id = ? LIMIT 1");
$srv_stmt->bind_param("i", $service_id);
$srv_stmt->execute();
$service = $srv_stmt->get_result()->fetch_assoc();
$srv_stmt->close();

if (!$service) {
    header("Location: dashboard.php");
    exit();
}

$custom_fields = json_decode($service['custom_fields'] ?? '[]', true);
if (!is_array($custom_fields)) {
    $custom_fields = [];
}

if (soko_is_new_birth_application($service['service_name'])) $custom_fields = soko_match_preset($service['service_name']);

$order_success = "";
$order_error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_custom_order'])) {
    if ($service['status'] !== 'active') {
        $order_error = "এই সার্ভিসটি বর্তমানে সাময়িকভাবে বন্ধ আছে!";
    } else {
        $cost = floatval($service['price']);

        $u_stmt = $conn->prepare("SELECT balance FROM users WHERE id = ? LIMIT 1");
        $u_stmt->bind_param("i", $id);
        $u_stmt->execute();
        $current_user_bal = floatval($u_stmt->get_result()->fetch_assoc()['balance'] ?? 0);
        $u_stmt->close();

        if ($current_user_bal < $cost) {
            $order_error = "আপনার অ্যাকাউন্টে পর্যাপ্ত ব্যালেন্স নেই! রিচার্জ করুন।";
        } else {
            $submitted_data = [];
            $has_missing_required = false;

            foreach ($custom_fields as $idx => $f) {
                $f_title = $f['title'] ?? ('ফিল্ড ' . ($idx + 1));
                $f_type = $f['type'] ?? 'text';
                $is_req = !empty($f['required']);

                if ($f_type === 'file') {
                    $file_key = "field_" . $idx;
                    if (isset($_FILES[$file_key]) && $_FILES[$file_key]['error'] === 0) {
                        $upDir = "uploads/orders/";
                        if (!is_dir($upDir)) {
                            mkdir($upDir, 0755, true);
                        }
                        $ext = strtolower(pathinfo($_FILES[$file_key]["name"], PATHINFO_EXTENSION));
                        $newFilename = "ord_" . $id . "_" . time() . "_" . $idx . "." . $ext;
                        $targetPath = $upDir . $newFilename;
                        if (move_uploaded_file($_FILES[$file_key]["tmp_name"], $targetPath)) {
                            $submitted_data[$f_title] = $targetPath;
                        } else {
                            $submitted_data[$f_title] = "";
                        }
                    } else {
                        if ($is_req) $has_missing_required = true;
                        $submitted_data[$f_title] = "";
                    }
                } else {
                    $val = trim($_POST['field_' . $idx] ?? '');
                    if ($is_req && empty($val)) {
                        $has_missing_required = true;
                    }
                    $submitted_data[$f_title] = $val;
                }
            }

            if (empty($custom_fields)) {
                $general_info = trim($_POST['general_info'] ?? '');
                if (empty($general_info)) {
                    $has_missing_required = true;
                }
                $submitted_data['তথ্য'] = $general_info;
            }

            if ($has_missing_required) {
                $order_error = "অনুগ্রহ করে সকল আবশ্যকীয় তথ্য সঠিকভাবে পূরণ করুন!";
            } else {
                $conn->begin_transaction();
                try {
                    $up_user = $conn->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
                    $up_user->bind_param("di", $cost, $id);
                    $up_user->execute();
                    $up_user->close();

                    $input_data_string = "";
                    foreach ($submitted_data as $k => $v) {
                        $input_data_string .= $k . ": " . $v . " | ";
                    }
                    $input_data_string = rtrim($input_data_string, " | ");

                    $service_name = $service['service_name'];
                    $ins_ord = $conn->prepare("INSERT INTO orders (user_id, order_type, input_data, price, status) VALUES (?, ?, ?, ?, 'pending')");
                    $ins_ord->bind_param("issd", $id, $service_name, $input_data_string, $cost);
                    $ins_ord->execute();
                    $ins_ord->close();

                    $conn->commit();
                    $balance = $current_user_bal - $cost;
                    $order_success = "আপনার অর্ডার সফলভাবে গৃহীত হয়েছে! শীঘ্রই প্রসেস করা হবে।";
                } catch (Exception $e) {
                    $conn->rollback();
                    $order_error = "অর্ডার সম্পন্ন করতে সমস্যা হয়েছে! পুনরায় চেষ্টা করুন।";
                }
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title><?php echo htmlspecialchars($service['service_name']); ?> - <?php echo soko_e(soko_brand('site_name')); ?></title>

    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link rel="shortcut icon" href="<?php echo soko_e(soko_brand('site_favicon')); ?>">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }

        body {
            font-family: 'Hind Siliguri', 'Plus Jakarta Sans', sans-serif;
            background: #F1F5F9;
            color: #1E293B;
            min-height: 100vh;
            padding-bottom: 70px;
        }

        .top-navbar {
            background: #FFFFFF;
            padding: 10px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .header-logo {
            height: 38px;
            width: auto;
            max-width: 160px;
            object-fit: contain;
        }

        .balance-badge-top {
            background: #F59E0B;
            color: #FFFFFF;
            padding: 6px 16px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 13px;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.25);
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
        }

        .user-avatar-top {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #E2E8F0;
        }

        .page-container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 20px 14px;
        }

        .order-card {
            background: #FFFFFF;
            border-radius: 24px;
            padding: 24px 20px;
            border: 1px solid rgba(226, 232, 240, 0.95);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.03);
            margin-bottom: 24px;
        }

        .form-input-custom {
            width: 100%;
            border: 2px solid #E2E8F0;
            border-radius: 16px;
            padding: 12px 16px;
            font-size: 14px;
            outline: none;
            transition: all 0.2s ease;
            background: #FFFFFF;
        }
        .form-input-custom:focus {
            border-color: #4F46E5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
        }

        .btn-order-submit {
            background: linear-gradient(135deg, #4F46E5 0%, #6366F1 100%);
            color: #FFFFFF;
            font-weight: 700;
            font-size: 15px;
            padding: 14px;
            border-radius: 16px;
            width: 100%;
            border: none;
            cursor: pointer;
            box-shadow: 0 6px 18px rgba(79, 70, 229, 0.25);
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn-order-submit:hover {
            transform: translateY(-2px);
        }

        .history-card {
            background: #FFFFFF;
            border-radius: 24px;
            padding: 22px 18px;
            border: 1px solid rgba(226, 232, 240, 0.95);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.03);
        }

        .history-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            overflow: hidden;
            border-radius: 16px;
        }

        .history-table thead tr {
            background: #3B49B8;
            color: #FFFFFF;
        }

        .history-table th {
            padding: 12px 14px;
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
        }

        .history-table td {
            padding: 12px 14px;
            font-size: 12.5px;
            border-bottom: 1px solid #F1F5F9;
            color: #334155;
            white-space: nowrap;
            vertical-align: middle;
        }

        .history-table tbody tr:hover td {
            background: #F8FAFC;
        }

        .fab-group {
            position: fixed;
            bottom: 24px;
            right: 20px;
            z-index: 50;
        }
        .fab-join-btn {
            background: #14B8A6;
            color: #FFFFFF;
            border-radius: 30px;
            padding: 10px 18px;
            font-size: 13px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 6px 20px rgba(20, 184, 166, 0.35);
            text-decoration: none;
            transition: transform 0.2s;
        }
        .fab-join-btn:hover {
            transform: scale(1.05);
        }

        .drawer-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.45);
            z-index: 90;
            display: none;
        }
        .drawer-menu {
            position: fixed;
            top: 0;
            right: -320px;
            width: 300px;
            height: 100%;
            background: #FFFFFF;
            box-shadow: -4px 0 25px rgba(0, 0, 0, 0.15);
            z-index: 100;
            transition: right 0.3s ease;
            display: flex;
            flex-direction: column;
        }
        .drawer-menu.open { right: 0; }
        .drawer-header {
            padding: 18px;
            background: linear-gradient(135deg, #4F46E5 0%, #6366F1 100%);
            color: #FFFFFF;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .drawer-body {
            flex: 1;
            overflow-y: auto;
            padding: 10px 0;
        }
        .drawer-link {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 12px 18px;
            color: #334155;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            border-bottom: 1px solid #F8FAFC;
        }
        .drawer-link:hover {
            background: #F1F5F9;
            color: #4F46E5;
        }
        .drawer-link i {
            width: 22px;
            font-size: 16px;
            text-align: center;
        }
    </style>
</head>
<body>

    <?php include __DIR__ . '/includes/site_header.php'; ?>

    <div class="page-container">

        <div class="order-card max-w-2xl mx-auto">

            <div class="flex items-center justify-between border-b pb-4 mb-4">
                <a href="dashboard.php" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 flex items-center gap-1.5 transition">
                    <i class="fa-solid fa-arrow-left"></i> ব্যাকে যান
                </a>
                <div class="bg-indigo-50 text-indigo-700 font-extrabold text-xs px-3.5 py-1.5 rounded-full border border-indigo-100">
                    ব্যালেন্স: ৳ <?php echo number_format($balance, 2); ?>
                </div>
            </div>

            <div class="flex items-center gap-3.5 bg-slate-50 border border-slate-200/80 p-3.5 rounded-2xl mb-5">
                <div class="w-12 h-12 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-xl flex-shrink-0">
                    <?php if (!empty($service['service_image'])): ?>
                        <img src="<?php echo htmlspecialchars($service['service_image']); ?>" class="w-8 h-8 object-contain rounded-lg" alt="Icon">
                    <?php else: ?>
                        <i class="fa-solid fa-layer-group"></i>
                    <?php endif; ?>
                </div>
                <div class="flex-1">
                    <h4 class="font-extrabold text-slate-800 text-base leading-snug"><?php echo htmlspecialchars($service['service_name']); ?></h4>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="text-xs font-bold text-emerald-600">চার্জ: ৳ <?php echo floatval($service['price']); ?></span>
                        <span class="text-[11px] font-semibold text-slate-400">|</span>
                        <?php if ($service['status'] === 'active'): ?>
                            <span class="text-[11px] font-bold text-emerald-600 flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> সার্ভিস চালু আছে
                            </span>
                        <?php else: ?>
                            <span class="text-[11px] font-bold text-rose-600 flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> সার্ভিস বন্ধ আছে
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if (!empty($service['description'])): ?>
                <div class="bg-amber-50/70 border border-amber-200/80 text-amber-900 rounded-2xl p-3 mb-5 text-xs font-medium leading-relaxed">
                    <i class="fa-solid fa-circle-info text-amber-600 me-1"></i> <?php echo nl2br(htmlspecialchars($service['description'])); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($order_success)): ?>
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-2xl mb-4 text-xs font-bold flex items-center gap-2">
                    <i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i> <?php echo $order_success; ?>
                </div>
            <?php elseif (!empty($order_error)): ?>
                <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-2xl mb-4 text-xs font-bold flex items-center gap-2">
                    <i class="fa-solid fa-circle-exclamation text-rose-600 text-sm"></i> <?php echo $order_error; ?>
                </div>
            <?php endif; ?>

            <?php if (soko_is_new_birth_application($service['service_name'])): ?>
            <div class="ss-order-result">নতুন আবেদনে আগে থেকে জন্ম নিবন্ধন নম্বর লাগে না। নম্বর ইস্যু হলে নিচের হিস্টোরিতে অ্যাডমিনের দেওয়া নম্বর/সনদ পাবেন।</div>
            <?php endif; ?>
            <form action="" method="POST" enctype="multipart/form-data">

                <?php if (!empty($custom_fields)): ?>
                    <?php foreach ($custom_fields as $i => $field): 
                        $f_type = $field['type'] ?? 'text';
                        $f_title = $field['title'] ?? ('তথ্য ' . ($i + 1));
                        $is_req = !empty($field['required']);
                    ?>
                        <div class="mb-4">
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                <?php echo htmlspecialchars($f_title); ?>
                                <?php if ($is_req): ?><span class="text-rose-500">*</span><?php endif; ?>
                            </label>

                            <?php if ($f_type === 'date'): ?>
                                <input type="date" name="field_<?php echo $i; ?>" class="form-input-custom font-semibold text-slate-700" <?php echo $is_req ? 'required' : ''; ?>>
                            <?php elseif ($f_type === 'textarea'): ?>
                                <textarea name="field_<?php echo $i; ?>" rows="3" class="form-input-custom" placeholder="<?php echo htmlspecialchars($f_title); ?> লিখুন" <?php echo $is_req ? 'required' : ''; ?>></textarea>
                            <?php elseif ($f_type === 'file'): ?>
                                <input type="file" name="field_<?php echo $i; ?>" class="form-input-custom text-xs" <?php echo $is_req ? 'required' : ''; ?>>
                            <?php elseif ($f_type === 'number'): ?>
                                <input type="number" name="field_<?php echo $i; ?>" class="form-input-custom" placeholder="<?php echo htmlspecialchars($f_title); ?>" <?php echo $is_req ? 'required' : ''; ?>>
                            <?php else: ?>
                                <input type="text" name="field_<?php echo $i; ?>" class="form-input-custom" placeholder="<?php echo htmlspecialchars($f_title); ?>" <?php echo $is_req ? 'required' : ''; ?>>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="mb-4">
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            প্রয়োজনীয় তথ্য বা নম্বর দিন <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="general_info" class="form-input-custom" placeholder="যেমন: NID নম্বর / মোবাইল নম্বর" required>
                    </div>
                <?php endif; ?>

                <div class="mt-6">
                    <button type="submit" name="submit_custom_order" class="btn-order-submit">
                        <i class="fa-solid fa-circle-check"></i> অর্ডার কনফার্ম করুন (৳ <?php echo floatval($service['price']); ?>)
                    </button>
                </div>
            </form>

        </div>



    </div>


    <script>
        $('#openDrawerBtn').click(function() {
            $('#drawerOverlay').fadeIn(200);
            $('#drawerMenu').addClass('open');
        });

        $('#closeDrawerBtn, #drawerOverlay').click(function() {
            $('#drawerOverlay').fadeOut(200);
            $('#drawerMenu').removeClass('open');
        });
    </script>
<?php include __DIR__ . '/includes/site_footer.php'; ?>
</body>
</html>