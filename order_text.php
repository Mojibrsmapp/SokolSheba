<?php
session_start();
include_once("includes/configuration.php");

if (!isset($_SESSION['user_token'])) {
    header("Location: index.php");
    exit();
}

$token_user = $_SESSION['user_token'];

$stmt = $conn->prepare("SELECT id, name, email, balance, mobile FROM users WHERE token = ?");
$stmt->bind_param("s", $token_user);
$stmt->execute();
$res_user = $stmt->get_result();
if ($res_user && $res_user->num_rows > 0) {
    $user = $res_user->fetch_assoc();
    $id = (int)$user['id'];
    $name = $user['name'];
    $email = $user['email'];
    $balance = (float)$user['balance'];
} else {
    unset($_SESSION['user_token'], $_SESSION['user_id']);
    header("Location: index.php");
    exit();
}
$stmt->close();

$order_msg = "";
$order_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_text_order'])) {
    $service_id = intval($_POST['service_id'] ?? 0);
    $text_data = trim($_POST['text_data'] ?? '');
    $phone_number = trim($_POST['contact_phone'] ?? '');

    if (empty($text_data)) {
        $order_err = "প্রয়োজনীয় তথ্য বা নম্বর লিখুন!";
    } else {
        $chk = $conn->prepare("SELECT service_title, price, status FROM order_text_services WHERE id = ? LIMIT 1");
        $chk->bind_param("i", $service_id);
        $chk->execute();
        $srv = $chk->get_result()->fetch_assoc();
        $chk->close();

        if (!$srv) {
            $order_err = "সার্ভিসটি পাওয়া যায়নি!";
        } elseif ($srv['status'] !== 'active') {
            $order_err = "এই সার্ভিসটি বর্তমানে সাময়িকভাবে বন্ধ আছে!";
        } else {
            $cost = floatval($srv['price']);

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

                    $full_input = $text_data . (!empty($phone_number) ? " (ফোন: " . $phone_number . ")" : "");
                    $order_type = "(Text) " . $srv['service_title'];

                    $ins = $conn->prepare("INSERT INTO orders (user_id, order_type, input_data, price, status) VALUES (?, ?, ?, ?, 'pending')");
                    $ins->bind_param("issd", $id, $order_type, $full_input, $cost);
                    $ins->execute();
                    $ins->close();

                    $conn->commit();
                    $balance = $cur_bal - $cost;
                    $order_msg = "অর্ডার সফলভাবে সম্পন্ন হয়েছে! শীঘ্রই প্রসেস করা হবে।";
                } catch (Exception $e) {
                    $conn->rollback();
                    $order_err = "অর্ডার সম্পন্ন করতে সমস্যা হয়েছে!";
                }
            }
        }
    }
}

$text_services = [];
$res_t = $conn->query("SELECT * FROM order_text_services ORDER BY id ASC");
if ($res_t && $res_t->num_rows > 0) {
    while ($r = $res_t->fetch_assoc()) {
        $text_services[] = $r;
    }
}

$recent_orders = [];
$match_sql = "%(Text)%";
$h_stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? AND order_type LIKE ? ORDER BY id DESC LIMIT 20");
if ($h_stmt) {
    $h_stmt->bind_param("is", $id, $match_sql);
    $h_stmt->execute();
    $h_res = $h_stmt->get_result();
    if ($h_res) {
        while ($hr = $h_res->fetch_assoc()) {
            $recent_orders[] = $hr;
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
    <title>অর্ডার টেক্সট সার্ভিস - <?php echo soko_e(soko_brand('site_name')); ?></title>

    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="shortcut icon" type="image/jpeg" href="<?php echo soko_e(soko_brand('site_favicon')); ?>">

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
            font-family: 'Hind Siliguri', 'Plus Jakarta Sans', sans-serif; 
            background: var(--bg-page); 
            color: var(--paragraph-color); 
            min-height: 100vh;
            padding-bottom: 75px;
        }

        .top-navbar { 
            background: #FFFFFF; 
            padding: 10px 18px; 
            display: flex; 
            align-items: center; 
            justify-content: space-between; 
            box-shadow: 0 2px 12px rgba(7, 139, 56, 0.05); 
            position: sticky; 
            top: 0; 
            z-index: 50; 
            border-bottom: 1.5px solid var(--border-color);
        }

        .header-logo { 
            height: 40px; 
            width: 40px; 
            object-fit: cover;
            border-radius: 50%;
            border: 1.5px solid var(--brand-green);
        }

        .balance-badge-top { 
            background: linear-gradient(135deg, var(--brand-green) 0%, var(--dark-green) 100%); 
            color: #FFFFFF; 
            padding: 6px 16px; 
            border-radius: 20px; 
            font-weight: 700; 
            font-size: 13px; 
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(7, 139, 56, 0.25);
        }

        .service-select-item { 
            border: 1.5px solid var(--border-color); 
            border-radius: 14px; 
            padding: 12px 14px; 
            cursor: pointer; 
            transition: all 0.2s; 
            background: #FFFFFF; 
        }

        .service-select-item:hover { 
            border-color: var(--brand-green); 
        }

        .service-select-item.selected { 
            border-color: var(--brand-green); 
            background: #EAF6EE; 
            box-shadow: 0 4px 12px rgba(7, 139, 56, 0.15);
        }

        .custom-input-box { 
            width: 100%; 
            border: 1.5px solid var(--border-color) !important; 
            border-radius: 14px; 
            padding: 12px 16px; 
            font-size: 14px; 
            font-weight: 600; 
            outline: none; 
            transition: all 0.2s; 
            background: #FAFCFA !important; 
            color: #1E293B;
        }

        .custom-input-box:focus { 
            border-color: var(--brand-green) !important; 
            box-shadow: 0 0 0 3px rgba(7, 139, 56, 0.12); 
            background: #FFFFFF !important;
        }

        .btn-submit-green {
            width: 100%;
            background: linear-gradient(135deg, var(--brand-green) 0%, var(--dark-green) 100%);
            border: none;
            border-radius: 14px;
            padding: 13px 20px;
            font-weight: 800;
            font-size: 15px;
            color: #FFFFFF;
            box-shadow: 0 6px 18px rgba(7, 139, 56, 0.28);
            transition: all 0.25s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            cursor: pointer;
        }

        .btn-submit-green:hover {
            transform: translateY(-1.5px);
            box-shadow: 0 8px 22px rgba(7, 139, 56, 0.38);
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

    <?php include __DIR__ . '/includes/site_header.php'; ?>

    <div class="max-w-4xl mx-auto px-3 py-6">

        <div class="bg-white rounded-3xl p-6 border border-[#DFEBE1] shadow-sm mb-6">

            <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-4">
                <a href="dashboard.php" class="text-xs font-bold text-[#53665A] hover:text-[#078B38] flex items-center gap-1.5">
                    <i class="fa-solid fa-arrow-left"></i> ড্যাশবোর্ডে ফিরুন
                </a>
                <span class="text-xs font-extrabold bg-[#EAF6EE] text-[#078B38] px-3.5 py-1.5 rounded-full border border-[#C8E3CF]">
                    ব্যালেন্স: ৳ <?php echo number_format($balance, 2); ?>
                </span>
            </div>

            <div class="mb-5">
                <h3 class="text-lg font-black text-[#124D2B] flex items-center gap-2">
                    <i class="fa-solid fa-pen-to-square text-[#078B38]"></i> অর্ডার টেক্সট সার্ভিসসমূহ
                </h3>
                <p class="text-xs text-[#53665A] mt-1">পাসপোর্ট, জন্মনিবন্ধন, TIN ও বিভিন্ন অনলাইন সংক্রান্ত টেক্সট আবেদন সেবা</p>
            </div>

            <?php if (!empty($order_msg)): ?>
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-2xl mb-4 text-xs font-bold flex items-center gap-2">
                    <i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i> <?php echo $order_msg; ?>
                </div>
            <?php elseif (!empty($order_err)): ?>
                <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-2xl mb-4 text-xs font-bold flex items-center gap-2">
                    <i class="fa-solid fa-circle-exclamation text-rose-600 text-sm"></i> <?php echo $order_err; ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <input type="hidden" name="service_id" id="selectedServiceId" value="<?php echo !empty($text_services) ? $text_services[0]['id'] : 0; ?>">

                <div class="mb-5">
                    <label class="block text-xs font-bold text-[#124D2B] mb-2">সেবা নির্বাচন করুন *</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2 max-h-80 overflow-y-auto p-1 border border-[#DFEBE1] rounded-2xl bg-[#FAFCFA]">
                        <?php foreach ($text_services as $idx => $ts): 
                            $is_sel = ($idx === 0);
                            $is_active = ($ts['status'] === 'active');
                        ?>
                        <div class="service-select-item flex items-center justify-between <?php echo $is_sel ? 'selected' : ''; ?>" 
                             data-id="<?php echo $ts['id']; ?>" 
                             data-price="<?php echo intval($ts['price']); ?>"
                             data-placeholder="<?php echo htmlspecialchars($ts['placeholder']); ?>"
                             onclick="selectTextService(this, <?php echo $ts['id']; ?>)">
                            <div class="flex-1 pr-2">
                                <h6 class="text-xs font-bold text-slate-800"><?php echo htmlspecialchars($ts['service_title']); ?></h6>
                                <?php if (!$is_active): ?>
                                    <span class="text-[10px] text-rose-500 font-bold">(সাময়িকভাবে বন্ধ)</span>
                                <?php endif; ?>
                            </div>
                            <span class="text-xs font-black text-[#078B38] bg-[#EAF6EE] border border-[#C8E3CF] px-2.5 py-1 rounded-lg shrink-0">
                                ৳ <?php echo intval($ts['price']); ?>
                            </span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-xs font-bold text-[#124D2B] mb-1.5" id="inputBoxLabel">প্রয়োজনীয় তথ্য/নম্বর দিন *</label>
                    <textarea name="text_data" id="textDataInput" rows="3" class="custom-input-box" placeholder="প্রয়োজনীয় তথ্য অথবা নম্বর লিখুন" required style="resize: none;"></textarea>
                </div>

                <div class="mb-5">
                    <label class="block text-xs font-bold text-[#124D2B] mb-1.5">যোগাযোগের মোবাইল নম্বর (ঐচ্ছিক)</label>
                    <input type="text" name="contact_phone" class="custom-input-box" placeholder="017XXXXXXXX">
                </div>

                <button type="submit" name="submit_text_order" class="btn-submit-green" id="submitBtn">
                    <i class="fa-solid fa-paper-plane"></i> <span id="submitBtnText">অর্ডার কনফার্ম করুন (৳ <?php echo !empty($text_services) ? intval($text_services[0]['price']) : 100; ?>)</span>
                </button>
            </form>

        </div>

        <div class="bg-white rounded-3xl p-6 border border-[#DFEBE1] shadow-sm">
            <h5 class="font-bold text-[#078B38] text-sm mb-3.5 flex items-center gap-2">
                <i class="fa-solid fa-clock-rotate-left"></i> আপনার সাম্প্রতিক টেক্সট অর্ডারসমূহ
            </h5>

            <div class="overflow-x-auto rounded-xl border border-[#DFEBE1]">
                <table class="w-full text-left text-xs">
                    <thead style="background-color: var(--footer-color);" class="text-white">
                        <tr>
                            <th class="p-3">#</th>
                            <th class="p-3">সেবার ধরন</th>
                            <th class="p-3">প্রদত্ত তথ্য</th>
                            <th class="p-3">স্ট্যাটাস</th>
                            <th class="p-3">ফলাফল</th>
                            <th class="p-3">চার্জ</th>
                            <th class="p-3">সময়</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($recent_orders)): ?>
                            <tr><td colspan="7" class="p-6 text-center text-slate-400">কোনো অর্ডারের রেকর্ড পাওয়া যায়নি।</td></tr>
                        <?php else: ?>
                            <?php $sl = 1; foreach ($recent_orders as $ord): ?>
                            <tr class="hover:bg-slate-50">
                                <td class="p-3 font-bold"><?php echo $sl++; ?></td>
                                <td class="p-3 font-bold text-slate-800"><?php echo htmlspecialchars($ord['order_type']); ?></td>
                                <td class="p-3 text-slate-600 max-w-xs truncate"><?php echo htmlspecialchars($ord['input_data']); ?></td>
                                <td class="p-3">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?php echo ($ord['status'] === 'success') ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200'; ?>">
                                        <?php echo htmlspecialchars($ord['status']); ?>
                                    </span>
                                </td>
                                <td class="p-3">
                                    <?php if (!empty($ord['result_file'])): ?>
                                        <a href="<?php echo htmlspecialchars($ord['result_file']); ?>" target="_blank" class="text-[#078B38] font-bold hover:underline">ডাউনলোড</a>
                                    <?php else: ?>
                                        <span class="text-slate-400"><?php echo htmlspecialchars($ord['result_text'] ?? 'অপেক্ষমান'); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-3 font-bold text-[#078B38]">৳ <?php echo number_format($ord['price'], 2); ?></td>
                                <td class="p-3 text-slate-400 text-[11px]"><?php echo date('d M Y, h:i A', strtotime($ord['created_at'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>


    <script>
        function selectTextService(el, srvId) {
            $('.service-select-item').removeClass('selected');
            $(el).addClass('selected');
            $('#selectedServiceId').val(srvId);
            const price = $(el).data('price');
            const ph = $(el).data('placeholder');
            $('#textDataInput').attr('placeholder', ph).focus();
            $('#submitBtnText').text(`অর্ডার কনফার্ম করুন (৳ ${price})`);
        }
    </script>
<?php include __DIR__ . '/includes/site_footer.php'; ?>
</body>
</html>