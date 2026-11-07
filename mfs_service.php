<?php 
session_start();
include_once("includes/configuration.php");

if (isset($_SESSION['user_token'])) {
    $token_user = $_SESSION['user_token'];

    $stmt = $conn->prepare("SELECT id, balance, email, name, token, mobile FROM users WHERE token = ?");
    if ($stmt) {
        $stmt->bind_param("s", $token_user);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
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
} else {
    header("Location: index.php");
    exit();
}

$conn->query("CREATE TABLE IF NOT EXISTS `mfs_services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_key` varchar(50) NOT NULL UNIQUE,
  `service_name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 250.00,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `icon_bg` varchar(30) NOT NULL DEFAULT 'bg-pink-100',
  `icon_color` varchar(30) NOT NULL DEFAULT 'text-pink-600',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$init_check = $conn->query("SELECT id FROM mfs_services LIMIT 1");
if ($init_check && $init_check->num_rows == 0) {
    $conn->query("INSERT INTO `mfs_services` (`service_key`, `service_name`, `price`, `status`, `icon_bg`, `icon_color`) VALUES
    ('bkash_personal', 'বিকাশ পারসোনাল NID ইনফরমেশন', 250.00, 'active', 'bg-pink-100', 'text-pink-600'),
    ('nagad_personal', 'নগদ পারসোনাল NID ইনফরমেশন', 250.00, 'active', 'bg-orange-100', 'text-orange-600'),
    ('rocket_personal', 'রকেট পারসোনাল NID ইনফরমেশন', 250.00, 'active', 'bg-purple-100', 'text-purple-600'),
    ('bkash_merchant', 'বিকাশ মার্চেন্ট NID ইনফো', 350.00, 'active', 'bg-rose-100', 'text-rose-600')");
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) &&$_POST['action'] === 'place_mfs_order') {
    header('Content-Type: application/json');
    $service_id = intval($_POST['service_id'] ?? 0);
    $target_number = trim($_POST['target_number'] ?? '');

    if (empty($target_number) || strlen($target_number) < 11) {
        echo json_encode(['status' => 'error', 'message' => 'অনুগ্রহ করে সঠিক ১১ ডিজিটের ওয়ালেট নম্বর লিখুন!']);
        exit();
    }

    $chk_stmt =$conn->prepare("SELECT service_name, price, status FROM mfs_services WHERE id = ? LIMIT 1");
    if ($chk_stmt) {
        $chk_stmt->bind_param("i", $service_id);
        $chk_stmt->execute();$srv_info = $chk_stmt->get_result()->fetch_assoc();$chk_stmt->close();
    } else {
        $srv_info = null;
    }

    if (!$srv_info) {
        echo json_encode(['status' => 'error', 'message' => 'সার্ভিস খুঁজে পাওয়া যায়নি!']);
        exit();
    }

    if ($srv_info['status'] !== 'active') {
        echo json_encode(['status' => 'error', 'message' => 'এই MFS প্যাকেজটি বর্তমানে বন্ধ রয়েছে!']);
        exit();
    }

    $cost = floatval($srv_info['price']);

    $u_stmt =$conn->prepare("SELECT balance FROM users WHERE id = ? LIMIT 1");
    $u_stmt->bind_param("i", $id);
    $u_stmt->execute();$user_current_bal = floatval($u_stmt->get_result()->fetch_assoc()['balance'] ?? 0);$u_stmt->close();

    if ($user_current_bal <$cost) {
        echo json_encode(['status' => 'error', 'message' => 'আপনার অ্যাকাউন্টে পর্যাপ্ত ব্যালেন্স নেই! রিচার্জ করুন।']);
        exit();
    }

    $conn->begin_transaction();
    try {
        $up_bal =$conn->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
        $up_bal->bind_param("di", $cost,$id);
        $up_bal->execute();$up_bal->close();

        $order_type = "( MFS ) " . $srv_info['service_name'];
        $ins_ord =$conn->prepare("INSERT INTO orders (user_id, order_type, input_data, price, status) VALUES (?, ?, ?, ?, 'pending')");
        $ins_ord->bind_param("issd", $id,$order_type, $target_number,$cost);
        $ins_ord->execute();$ins_ord->close();

        $conn->commit();
        echo json_encode([
            'status' => 'success', 
            'message' => 'MFS NID ইনফো অনুসন্ধান অর্ডার সফলভাবে সম্পন্ন হয়েছে!', 
            'new_balance' => number_format($user_current_bal -$cost, 2)
        ]);
        exit();
    } catch (Exception $e) {$conn->rollback();
        echo json_encode(['status' => 'error', 'message' => 'অর্ডার সম্পন্ন করতে সমস্যা হয়েছে!']);
        exit();
    }
}

$mfs_items = [];
$res_items =$conn->query("SELECT * FROM mfs_services ORDER BY id ASC");
if ($res_items &&$res_items->num_rows > 0) {
    while ($r =$res_items->fetch_assoc()) {
        $mfs_items[] =$r;
    }
}

if (empty($mfs_items)) {$mfs_items = [
        ['id' => 1, 'service_key' => 'bkash_personal', 'service_name' => 'বিকাশ পারসোনাল NID ইনফরমেশন', 'price' => 250.00, 'status' => 'active'],
        ['id' => 2, 'service_key' => 'nagad_personal', 'service_name' => 'নগদ পারসোনাল NID ইনফরমেশন', 'price' => 250.00, 'status' => 'active'],
        ['id' => 3, 'service_key' => 'rocket_personal', 'service_name' => 'রকেট পারসোনাল NID ইনফরমেশন', 'price' => 250.00, 'status' => 'active'],
        ['id' => 4, 'service_key' => 'bkash_merchant', 'service_name' => 'বিকাশ মার্চেন্ট NID ইনফো', 'price' => 350.00, 'status' => 'active']
    ];
}

$url_type = isset($_GET['type']) ? trim($_GET['type']) : '';$selected_item = null;

if (!empty($url_type)) {
    foreach ($mfs_items as$item) {
        if ($item['service_key'] ===$url_type) {
            $selected_item =$item;
            break;
        }
    }
}

if (!$selected_item) {
    $selected_item =$mfs_items[0];
}

$selected_id = intval($selected_item['id'] ?? 1);
$selected_price = intval($selected_item['price'] ?? 250);

$recent_orders = [];$mfs_match = "%( MFS )%";
$h_stmt =$conn->prepare("SELECT * FROM orders WHERE user_id = ? AND (order_type LIKE ? OR order_type LIKE '%বিকাশ%' OR order_type LIKE '%নগদ%' OR order_type LIKE '%রকেট%') ORDER BY id DESC LIMIT 20");
if ($h_stmt) {
    $h_stmt->bind_param("is", $id, $mfs_match);$h_stmt->execute();
    $h_res =$h_stmt->get_result();
    if ($h_res) {
        while ($h_row =$h_res->fetch_assoc()) {
            $recent_orders[] =$h_row;
        }
    }
    $h_stmt->close();
}
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>বিকাশ / নগদ / রকেট সার্ভিস - Sokol Kaj</title>

    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
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
            max-width: 780px;
            margin: 0 auto;
            padding: 20px 14px;
        }

        .order-card {
            background: #FFFFFF;
            border-radius: 26px;
            padding: 24px 20px;
            border: 1px solid rgba(226, 232, 240, 0.95);
            box-shadow: 0 10px 28px rgba(0, 0, 0, 0.03);
            margin-bottom: 24px;
        }

        .radio-option-card {
            border: 2px solid #1E293B;
            border-radius: 16px;
            padding: 12px 16px;
            margin-bottom: 11px;
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
            background: #FFFFFF;
        }
        .radio-option-card:hover {
            border-color: #0284C7;
        }
        .radio-option-card.selected {
            border-color: #0284C7;
            background: #F0F9FF;
        }

        .radio-circle-box {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: all 0.2s ease;
        }
        .radio-circle-teal {
            border: 2px solid #0F766E;
            background: #0D9488;
        }
        .radio-circle-default {
            border: 2px solid #1E293B;
            background: #FFFFFF;
        }

        .modal-input-box {
            width: 100%;
            border: 2px solid #E2E8F0;
            border-radius: 16px;
            padding: 13px 18px;
            font-size: 14.5px;
            font-weight: 600;
            outline: none;
            transition: all 0.2s ease;
            background: #FFFFFF;
        }
        .modal-input-box:focus {
            border-color: #2563EB;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
        }

        .btn-confirm-order {
            background: #2563EB;
            color: #FFFFFF;
            font-weight: 700;
            font-size: 15px;
            padding: 14px 20px;
            border-radius: 16px;
            width: 100%;
            border: none;
            cursor: pointer;
            box-shadow: 0 6px 18px rgba(37, 99, 235, 0.25);
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn-confirm-order:hover {
            background: #1D4ED8;
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

        <div class="order-card">

            <div class="flex items-center justify-between border-b pb-3 mb-4">
                <a href="dashboard.php" class="text-xs font-bold text-slate-500 hover:text-blue-600 flex items-center gap-1.5 transition">
                    <i class="fa-solid fa-arrow-left"></i> ব্যাকে যান
                </a>
                <h4 class="font-bold text-blue-700 text-base md:text-lg leading-snug">
                    <u>( MFS ) বিকাশ / নগদ / রকেট সার্ভিস অর্ডার করুন</u>
                </h4>
                <div class="text-xs font-bold bg-blue-50 text-blue-700 px-3 py-1 rounded-full border border-blue-100">
                    ৳ <span id="userBalSpan"><?php echo number_format($balance, 2); ?></span>
                </div>
            </div>

            <form id="mfsDirectOrderForm">
                <div class="mb-5">
                    <?php foreach ($mfs_items as$index => $srv_opt):$is_selected = ($srv_opt['id'] ==$selected_id);
                        $is_active = ($srv_opt['status'] === 'active');
                    ?>
                    <div class="radio-option-card <?php echo $is_selected ? 'selected' : ''; ?>" 
                         data-id="<?php echo $srv_opt['id']; ?>" 
                         data-price="<?php echo intval($srv_opt['price']); ?>"
                         data-name="<?php echo htmlspecialchars($srv_opt['service_name']); ?>"
                         onclick="selectMfsPackage(this, <?php echo $srv_opt['id']; ?>)">

                        <div class="radio-circle-box <?php echo $is_selected ? 'radio-circle-teal' : 'radio-circle-default'; ?>"></div>

                        <div class="flex-1 text-sm font-bold <?php echo $is_selected ? 'text-teal-600' : 'text-slate-800'; ?> radio-label">
                            <?php echo htmlspecialchars($srv_opt['service_name']); ?> (<?php echo intval($srv_opt['price']); ?>tk)
                            <?php if (!$is_active): ?>
                                <span class="text-xs text-rose-500 font-bold ml-1">(বন্ধ)</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="mb-5">
                    <label class="block text-xs font-bold text-slate-600 mb-2">টার্গেট ওয়ালেট মোবাইল নম্বর দিন :</label>
                    <input type="text" id="targetWalletNumber" class="modal-input-box" placeholder="( 017XXXXXXXX / 018XXXXXXXX / 019XXXXXXXX )" required>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" id="mfsSubmitBtn" class="btn-confirm-order">
                        <i class="fa-solid fa-circle-check"></i> 
                        <span id="btnText">অর্ডার কনফার্ম করুন (৳ <?php echo $selected_price; ?>)</span>
                    </button>
                </div>
            </form>

        </div>



    </div>


    <script>
        let selectedServiceId = <?php echo $selected_id; ?>;

        function selectMfsPackage(el, srvId) {
            $('.radio-option-card').removeClass('selected');
            $('.radio-option-card .radio-circle-box').removeClass('radio-circle-teal').addClass('radio-circle-default');$('.radio-option-card .radio-label').removeClass('text-teal-600').addClass('text-slate-800');

            $(el).addClass('selected');
            $(el).find('.radio-circle-box').removeClass('radio-circle-default').addClass('radio-circle-teal');$(el).find('.radio-label').removeClass('text-slate-800').addClass('text-teal-600');

            selectedServiceId = srvId;
            const price = $(el).data('price');$('#btnText').text(`অর্ডার কনফার্ম করুন (৳ ${price})`);
            $('#targetWalletNumber').focus();
        }

        document.getElementById('mfsDirectOrderForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const walletVal = document.getElementById('targetWalletNumber').value.trim();
            if (!walletVal) {
                Swal.fire('তথ্য দিন', 'অনুগ্রহ করে ওয়ালেট নম্বর লিখুন।', 'warning');
                return;
            }

            const btn = document.getElementById('mfsSubmitBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> অর্ডার প্রসেস হচ্ছে...';

            $.ajax({
                url: 'mfs_service.php',
                type: 'POST',
                data: {
                    action: 'place_mfs_order',
                    service_id: selectedServiceId,
                    target_number: walletVal
                },
                dataType: 'json',
                success: function(res) {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa-solid fa-circle-check"></i> <span id="btnText">অর্ডার কনফার্ম করুন</span>';

                    if (res && res.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'সফল!',
                            text: res.message
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire('ত্রুটি!', res.message || 'অর্ডার সম্পন্ন হয়নি!', 'error');
                    }
                },
                error: function() {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa-solid fa-circle-check"></i> <span id="btnText">অর্ডার কনফার্ম করুন</span>';
                    Swal.fire('সার্ভার ত্রুটি', 'সার্ভারে সমস্যা হয়েছে। পুনরায় চেষ্টা করুন।', 'error');
                }
            });
        });

        $('#openDrawerBtn').click(function() {
            $('#drawerOverlay').fadeIn(200);
            $('#drawerMenu').addClass('open');
        });

        $('#closeDrawerBtn, #drawerOverlay').click(function() {
            $('#drawerOverlay').fadeOut(200);
            $('#drawerMenu').removeClass('open');
        });

        $(document).ready(function() {$('#targetWalletNumber').focus();
        });
    </script>
<?php include __DIR__ . '/includes/site_footer.php'; ?>
</body>
</html>