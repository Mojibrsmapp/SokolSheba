<?php 
session_start();
include_once("includes/configuration.php");

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

$conn->query("CREATE TABLE IF NOT EXISTS `number_to_server_services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `operator_key` varchar(50) NOT NULL UNIQUE,
  `operator_name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 90.00,
  `logo_url` text NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$order_msg = "";
$order_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_operator_order'])) {
    $op_id = intval($_POST['operator_id'] ?? 0);
    $phone_number = trim($_POST['phone_number'] ?? '');

    if (empty($phone_number) || strlen($phone_number) < 11) {
        $order_err = "সঠিক ১১ ডিজিটের মোবাইল নম্বর প্রদান করুন!";
    } else {
        $op_stmt = $conn->prepare("SELECT operator_name, price, status FROM number_to_server_services WHERE id = ? LIMIT 1");
        $op_stmt->bind_param("i", $op_id);
        $op_stmt->execute();
        $op_info = $op_stmt->get_result()->fetch_assoc();
        $op_stmt->close();

        if (!$op_info) {
            $order_err = "অপারেটর নির্বাচন সঠিক নয়!";
        } elseif ($op_info['status'] !== 'active') {
            $order_err = "এই অপারেটরের সার্ভিস বর্তমানে সাময়িকভাবে বন্ধ আছে!";
        } else {
            $cost = floatval($op_info['price']);

            $u_stmt = $conn->prepare("SELECT balance FROM users WHERE id = ? LIMIT 1");
            $u_stmt->bind_param("i", $id);
            $u_stmt->execute();
            $cur_bal = floatval($u_stmt->get_result()->fetch_assoc()['balance'] ?? 0);
            $u_stmt->close();

            if ($cur_bal < $cost) {
                $order_err = "আপনার অ্যাকাউন্টে পর্যাপ্ত ব্যালেন্স নেই! রিচার্জ করুন।";
            } else {
                $conn->begin_transaction();
                try {
                    $up_b = $conn->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
                    $up_b->bind_param("di", $cost, $id);
                    $up_b->execute();
                    $up_b->close();

                    $order_title = $op_info['operator_name'] . " নাম্বার টু সার্ভার কপি";
                    $ins = $conn->prepare("INSERT INTO orders (user_id, order_type, input_data, price, status) VALUES (?, ?, ?, ?, 'pending')");
                    $ins->bind_param("issd", $id, $order_title, $phone_number, $cost);
                    $ins->execute();
                    $ins->close();

                    $conn->commit();
                    $balance = $cur_bal - $cost;
                    $order_msg = "অর্ডার সফলভাবে সম্পন্ন হয়েছে! শীঘ্রই ফলাফল দেওয়া হবে।";
                } catch (Exception $e) {
                    $conn->rollback();
                    $order_err = "অর্ডার সম্পন্ন করতে সমস্যা হয়েছে!";
                }
            }
        }
    }
}

$operators = [];
$res_op = $conn->query("SELECT * FROM number_to_server_services ORDER BY id ASC");
if ($res_op && $res_op->num_rows > 0) {
    while ($orow = $res_op->fetch_assoc()) {
        $operators[] = $orow;
    }
}

$recent_orders = [];
$match_sql = "%নাম্বার টু সার্ভার কপি%";
$h_stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? AND order_type LIKE ? ORDER BY id DESC LIMIT 20");
$h_stmt->bind_param("is", $id, $match_sql);
$h_stmt->execute();
$h_res = $h_stmt->get_result();
while ($hr = $h_res->fetch_assoc()) {
    $recent_orders[] = $hr;
}
$h_stmt->close();
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>নাম্বার টু সার্ভার কপি - FAST SERVICE</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
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

        .main-container {
            max-width: 1020px;
            margin: 0 auto;
            padding: 24px 14px;
        }

        .service-form-card {
            background: #FFFFFF;
            border-radius: 26px;
            padding: 26px 22px;
            border: 1px solid rgba(226, 232, 240, 0.95);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.03);
            margin-bottom: 24px;
        }

        .operator-card {
            background: #FFFFFF;
            border: 2px solid #E2E8F0;
            border-radius: 20px;
            padding: 16px 12px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.25s ease;
            position: relative;
        }
        .operator-card:hover {
            border-color: #CBD5E1;
            transform: translateY(-2px);
        }
        .operator-card.selected {
            border-color: #E11D48;
            background: #FFF1F2;
            box-shadow: 0 4px 18px rgba(225, 29, 72, 0.12);
        }

        .operator-logo-wrap {
            width: 46px;
            height: 46px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 8px;
        }
        .operator-logo-wrap img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .phone-input-custom {
            width: 100%;
            border: 2px solid #E2E8F0;
            border-radius: 18px;
            padding: 14px 18px;
            font-size: 15px;
            font-weight: 600;
            outline: none;
            transition: all 0.2s ease;
            background: #FFFFFF;
        }
        .phone-input-custom:focus {
            border-color: #E11D48;
            box-shadow: 0 0 0 4px rgba(225, 29, 72, 0.1);
        }

        .btn-submit-order {
            background: linear-gradient(135deg, #E11D48 0%, #BE123C 100%);
            color: #FFFFFF;
            font-weight: 700;
            font-size: 15px;
            padding: 15px;
            border-radius: 18px;
            width: 100%;
            border: none;
            cursor: pointer;
            box-shadow: 0 6px 20px rgba(225, 29, 72, 0.28);
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        .btn-submit-order:hover {
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

    <header class="top-navbar">
        <div class="flex items-center gap-3">
            <button type="button" id="openDrawerBtn" class="text-slate-700 text-xl px-1 hover:text-indigo-600">
                <i class="fa-solid fa-bars"></i>
            </button>
            <a href="dashboard.php" class="flex items-center">
                <img src="https://fastservice.totthohub.com/assets/logo_nav.png" alt="FAST SERVICE Logo" class="header-logo">
            </a>
        </div>

        <div class="flex items-center gap-2 sm:gap-3">
            <div class="balance-badge-top" onclick="location.href='recharge.php'">
                <i class="fa-solid fa-wallet text-xs"></i>
                <span><?php echo htmlspecialchars($balance); ?> tk</span>
            </div>
            <a href="profile.php">
                <img src="https://fastservice.totthohub.com/assets/logo.jpg" alt="Profile" class="user-avatar-top">
            </a>
        </div>
    </header>

    <div class="main-container">

        <div class="service-form-card">
            
            <div class="flex items-center justify-between border-b pb-4 mb-5">
                <a href="dashboard.php" class="text-xs font-bold text-slate-500 hover:text-rose-600 flex items-center gap-1.5 transition">
                    <i class="fa-solid fa-arrow-left"></i> ব্যাকে যান
                </a>
                <div class="bg-rose-50 text-rose-700 font-extrabold text-xs px-3.5 py-1.5 rounded-full border border-rose-100">
                    ব্যালেন্স: ৳ <?php echo number_format($balance, 2); ?>
                </div>
            </div>

            <div class="mb-6">
                <h3 class="text-lg md:text-xl font-extrabold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-fingerprint text-rose-600"></i> মোবাইল নাম্বার → বায়োমেট্রিক ইনফো
                </h3>
                <p class="text-xs text-slate-500 mt-1">সিম কার্ড কার নামে নিবন্ধিত তার নাম, এনআইডি নম্বর, পিতা-মাতা ও ঠিকানা তথ্য বের করুন।</p>
            </div>

            <?php if (!empty($order_msg)): ?>
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-2xl mb-5 text-xs font-bold flex items-center gap-2">
                    <i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i> <?php echo $order_msg; ?>
                </div>
            <?php elseif (!empty($order_err)): ?>
                <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-2xl mb-5 text-xs font-bold flex items-center gap-2">
                    <i class="fa-solid fa-circle-exclamation text-rose-600 text-sm"></i> <?php echo $order_err; ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                
                <div class="mb-5">
                    <label class="block text-xs font-bold text-slate-700 mb-2.5">
                        সিম অপারেটর নির্বাচন করুন <span class="text-rose-500">*</span>
                    </label>

                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                        <?php foreach ($operators as $idx =>$op): 
                            $is_sel = ($idx === 0);
                        ?>
                        <label class="operator-card <?php echo $is_sel ? 'selected' : ''; ?>" onclick="selectOp(this)">
                            <input type="radio" name="operator_id" value="<?php echo $op['id']; ?>" <?php echo $is_sel ? 'checked' : ''; ?> class="hidden">
                            <div class="operator-logo-wrap">
                                <img src="<?php echo htmlspecialchars($op['logo_url']); ?>" alt="<?php echo htmlspecialchars($op['operator_name']); ?>">
                            </div>
                            <span class="text-xs font-bold text-slate-800 text-center leading-tight mb-1">
                                <?php echo htmlspecialchars($op['operator_name']); ?>
                            </span>
                            <span class="text-xs font-black text-rose-600">
                                ৳ <?php echo intval($op['price']); ?>
                            </span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-xs font-bold text-slate-700 mb-2">
                        মোবাইল নম্বর <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="phone_number" class="phone-input-custom" placeholder="যেমন: 017XXXXXXXX বা 019XXXXXXXX" required>
                </div>

                <button type="submit" name="submit_operator_order" class="btn-submit-order">
                    <i class="fa-solid fa-fingerprint text-lg"></i> বায়োমেট্রিক তথ্য অনুসন্ধান করুন
                </button>
            </form>

        </div>

        <div class="history-card">
            <div class="flex items-center gap-2 mb-3.5">
                <i class="fa-solid fa-table-list text-sky-500 text-base"></i>
                <h5 class="font-bold text-sky-500 text-sm md:text-base">আপনার সাম্প্রতিক অর্ডারসমূহ</h5>
            </div>

            <div class="overflow-x-auto rounded-xl">
                <table class="history-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>অর্ডারের ধরন</th>
                            <th>তথ্য</th>
                            <th>রিসিভ স্ট্যাটাস</th>
                            <th>ডাউনলোড / স্ট্যাটাস</th>
                            <th>রেট</th>
                            <th>সময়</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recent_orders)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-6 text-slate-400 text-xs">
                                    কোনো অর্ডারের রেকর্ড পাওয়া যায়নি।
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $serial = 1; foreach ($recent_orders as$ord): 
                                $st_badge = 'bg-amber-50 text-amber-700 border border-amber-200';$st_text = 'পেন্ডিং';
                                if ($ord['status'] === 'success') {
                                    $st_badge = 'bg-emerald-50 text-emerald-700 border border-emerald-200';$st_text = 'সফল';
                                } elseif ($ord['status'] === 'cancel') {
                                    $st_badge = 'bg-rose-50 text-rose-700 border border-rose-200';$st_text = 'বাতিল';
                                }
                            ?>
                            <tr>
                                <td class="font-bold text-slate-700"><?php echo $serial++; ?></td>
                                <td class="font-bold text-slate-800"><?php echo htmlspecialchars($ord['order_type']); ?></td>
                                <td class="font-semibold text-slate-700"><?php echo htmlspecialchars($ord['input_data']); ?></td>
                                <td>
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold <?php echo $st_badge; ?>">
                                        <?php echo $st_text; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($ord['result_file'])): ?>
                                        <a href="<?php echo htmlspecialchars($ord['result_file']); ?>" target="_blank" class="text-xs font-bold text-indigo-600 hover:underline flex items-center gap-1">
                                            <i class="fa-solid fa-file-arrow-down"></i> ডাউনলোড
                                        </a>
                                    <?php elseif (!empty($ord['result_text'])): ?>
                                        <span class="text-xs text-slate-700 font-semibold"><?php echo htmlspecialchars($ord['result_text']); ?></span>
                                    <?php else: ?>
                                        <span class="text-slate-400 text-xs">অপেক্ষমান</span>
                                    <?php endif; ?>
                                </td>
                                <td class="font-bold text-emerald-600">৳ <?php echo number_format($ord['price'], 2); ?></td>
                                <td class="text-slate-400 text-xs">
                                    <?php echo date('d M Y, h:i A', strtotime($ord['created_at'])); ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <div class="fab-group">
        <a href="<?php echo htmlspecialchars(get_site_setting($conn, "whatsapp_group", "#")); ?>" target="_blank" class="fab-join-btn">
            <i class="fa-brands fa-whatsapp text-lg"></i>
            <span>Join Group</span>
        </a>
    </div>

    <div class="drawer-overlay" id="drawerOverlay"></div>
    <div class="drawer-menu" id="drawerMenu">
        <div class="drawer-header">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-grid-2"></i>
                <h5 class="font-bold text-sm">সকল সার্ভিস তালিকা</h5>
            </div>
            <button id="closeDrawerBtn" class="text-white text-lg"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="drawer-body">
            <a href="dashboard.php" class="drawer-link"><i class="fa-solid fa-house text-indigo-500"></i> হোম ড্যাশবোর্ড</a>
            <a href="serverCopy.php" class="drawer-link"><i class="fa-solid fa-server text-sky-500"></i> অটো সার্ভার কপি</a>
            <a href="sign_to_server.php" class="drawer-link"><i class="fa-solid fa-sim-card text-rose-500"></i> নাম্বার টু সার্ভার কপি</a>
            <a href="recharge.php" class="drawer-link"><i class="fa-solid fa-wallet text-emerald-600"></i> ব্যালেন্স রিচার্জ</a>
            <a href="work_history.php" class="drawer-link"><i class="fa-solid fa-clock-rotate-left text-violet-500"></i> অর্ডার হিস্টোরি</a>
            <a href="profile.php" class="drawer-link"><i class="fa-solid fa-user-gear text-slate-600"></i> আমার প্রোফাইল</a>
            <a href="logout.php" class="drawer-link text-red-600"><i class="fa-solid fa-arrow-right-from-bracket text-red-600"></i> লগআউট</a>
        </div>
    </div>

    <script>
        function selectOp(element) {
            $('.operator-card').removeClass('selected');
            $(element).addClass('selected');$(element).find('input[type="radio"]').prop('checked', true);
        }

        $('#openDrawerBtn').click(function() {
            $('#drawerOverlay').fadeIn(200);
            $('#drawerMenu').addClass('open');
        });

        $('#closeDrawerBtn, #drawerOverlay').click(function() {
            $('#drawerOverlay').fadeOut(200);
            $('#drawerMenu').removeClass('open');
        });
    </script>
</body>
</html>