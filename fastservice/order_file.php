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

$order_msg = "";
$order_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_file_order'])) {
    $service_id = intval($_POST['service_id'] ?? 0);
    $text_data = trim($_POST['text_data'] ?? '');
    $contact_phone = trim($_POST['contact_phone'] ?? '');

    $file_path = "";
    if (isset($_FILES['order_attachment']) && $_FILES['order_attachment']['error'] === 0) {
        $upDir = "uploads/orders/";
        if (!is_dir($upDir)) {
            mkdir($upDir, 0755, true);
        }
        $ext = strtolower(pathinfo($_FILES["order_attachment"]["name"], PATHINFO_EXTENSION));
        $newFilename = "file_" . time() . "_" . rand(100, 999) . "." . $ext;
        $targetPath = $upDir . $newFilename;
        if (move_uploaded_file($_FILES["order_attachment"]["tmp_name"], $targetPath)) {
            $file_path = $targetPath;
        }
    }

    if (empty($text_data) && empty($file_path)) {
        $order_err = "অনুগ্রহ করে প্রয়োজনীয় তথ্য অথবা ফাইল আপলোড করুন!";
    } else {
        $chk = $conn->prepare("SELECT service_title, price, status FROM order_file_services WHERE id = ? LIMIT 1");
        $chk->bind_param("i", $service_id);
        $chk->execute();
        $srv = $chk->get_result()->fetch_assoc();
        $chk->close();

        if (!$srv) {$order_err = "সার্ভিসটি পাওয়া যায়নি!";
        } elseif ($srv['status'] !== 'active') {$order_err = "এই সার্ভিসটি বর্তমানে সাময়িকভাবে বন্ধ আছে!";
        } else {
            $cost = floatval($srv['price']);

            $u_stmt =$conn->prepare("SELECT balance FROM users WHERE id = ? LIMIT 1");
            $u_stmt->bind_param("i", $id);
            $u_stmt->execute();$cur_bal = floatval($u_stmt->get_result()->fetch_assoc()['balance'] ?? 0);$u_stmt->close();

            if ($cur_bal < $cost) {$order_err = "আপনার অ্যাকাউন্টে পর্যাপ্ত ব্যালেন্স নেই! রিচার্জ করুন।";
            } else {
                $conn->begin_transaction();
                try {
                    $up_b =$conn->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
                    $up_b->bind_param("di", $cost,$id);
                    $up_b->execute();$up_b->close();

                    $full_input =$text_data;
                    if (!empty($contact_phone)) {
                        $full_input .= " | ফোন: " . $contact_phone;
                    }
                    if (!empty($file_path)) {
                        $full_input .= " | ফাইল: " . $file_path;
                    }

                    $order_type = "(File) " . $srv['service_title'];

                    $ins =$conn->prepare("INSERT INTO orders (user_id, order_type, input_data, price, status) VALUES (?, ?, ?, ?, 'pending')");
                    $ins->bind_param("issd", $id,$order_type, $full_input,$cost);
                    $ins->execute();$ins->close();

                    $conn->commit();$balance = $cur_bal -$cost;
                    $order_msg = "ফাইল অর্ডার সফলভাবে সম্পন্ন হয়েছে! শীঘ্রই প্রসেস করা হবে।";
                } catch (Exception $e) {
                    $conn->rollback();$order_err = "অর্ডার সম্পন্ন করতে সমস্যা হয়েছে!";
                }
            }
        }
    }
}

$file_services = [];
$res_f =$conn->query("SELECT * FROM order_file_services ORDER BY id ASC");
if ($res_f &&$res_f->num_rows > 0) {
    while ($r =$res_f->fetch_assoc()) {
        $file_services[] =$r;
    }
}

$recent_orders = [];$match_sql = "%(File)%";
$h_stmt =$conn->prepare("SELECT * FROM orders WHERE user_id = ? AND order_type LIKE ? ORDER BY id DESC LIMIT 20");
if ($h_stmt) {
    $h_stmt->bind_param("is", $id, $match_sql);$h_stmt->execute();
    $h_res =$h_stmt->get_result();
    if ($h_res) {
        while ($hr =$h_res->fetch_assoc()) {
            $recent_orders[] =$hr;
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
    <title>অর্ডার ফাইল সার্ভিস - FAST SERVICE</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="shortcut icon" href="https://fastservice.totthohub.com/assets/logo.jpg">
    
    <style>
        body { font-family: 'Hind Siliguri', 'Plus Jakarta Sans', sans-serif; background: #F1F5F9; color: #1E293B; }
        .top-navbar { background: #FFFFFF; padding: 10px 18px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 2px 12px rgba(0,0,0,0.04); position: sticky; top: 0; z-index: 50; }
        .header-logo { height: 38px; width: auto; }
        .balance-badge-top { background: #F59E0B; color: #FFFFFF; padding: 6px 16px; border-radius: 20px; font-weight: 700; font-size: 13px; }
        .service-select-item { border: 1.5px solid #E2E8F0; border-radius: 14px; padding: 12px 14px; cursor: pointer; transition: all 0.2s; background: #FFFFFF; }
        .service-select-item:hover { border-color: #059669; }
        .service-select-item.selected { border-color: #059669; background: #ECFDF5; }
        .custom-input-box { width: 100%; border: 2px solid #E2E8F0; border-radius: 14px; padding: 12px 16px; font-size: 14px; font-weight: 600; outline: none; transition: all 0.2s; background: #FFFFFF; }
        .custom-input-box:focus { border-color: #059669; box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.12); }
    </style>
</head>
<body>

    <header class="top-navbar">
        <div class="flex items-center gap-3">
            <a href="dashboard.php">
                <img src="https://fastservice.totthohub.com/assets/logo_nav.png" alt="Logo" class="header-logo">
            </a>
        </div>
        <div class="balance-badge-top" onclick="location.href='recharge.php'">
            <i class="fa-solid fa-wallet text-xs"></i> <span><?php echo htmlspecialchars($balance); ?> tk</span>
        </div>
    </header>

    <div class="max-w-4xl mx-auto px-3 py-6">

        <div class="bg-white rounded-3xl p-6 border border-slate-200/90 shadow-sm mb-6">
            
            <div class="flex items-center justify-between border-b pb-4 mb-4">
                <a href="dashboard.php" class="text-xs font-bold text-slate-500 hover:text-emerald-600 flex items-center gap-1.5">
                    <i class="fa-solid fa-arrow-left"></i> ড্যাশবোর্ডে ফিরুন
                </a>
                <span class="text-xs font-extrabold bg-emerald-50 text-emerald-700 px-3.5 py-1.5 rounded-full border border-emerald-100">
                    ব্যালেন্স: ৳ <?php echo number_format($balance, 2); ?>
                </span>
            </div>

            <div class="mb-5">
                <h3 class="text-lg font-black text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-file-arrow-up text-emerald-600"></i> অর্ডার ফাইল সার্ভিসসমূহ
                </h3>
                <p class="text-xs text-slate-500 mt-1">IMEI, MFS, বায়োমেট্রিক ও লোকেশন ফাইল ভিত্তিক বিশেষ অনুসন্ধান সেবা</p>
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

            <form action="" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="service_id" id="selectedServiceId" value="<?php echo !empty($file_services) ?$file_services[0]['id'] : 0; ?>">

                <div class="mb-5">
                    <label class="block text-xs font-bold text-slate-700 mb-2">সেবা নির্বাচন করুন *</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2 max-h-80 overflow-y-auto p-1 border rounded-2xl">
                        <?php foreach ($file_services as $idx =>$fs): 
                            $is_sel = ($idx === 0);
                            $is_active = ($fs['status'] === 'active');
                        ?>
                        <div class="service-select-item flex items-center justify-between <?php echo $is_sel ? 'selected' : ''; ?>" 
                             data-id="<?php echo $fs['id']; ?>" 
                             data-price="<?php echo intval($fs['price']); ?>"
                             data-placeholder="<?php echo htmlspecialchars($fs['placeholder']); ?>"
                             onclick="selectFileService(this, <?php echo $fs['id']; ?>)">
                            <div class="flex-1 pr-2">
                                <h6 class="text-xs font-bold text-slate-800"><?php echo htmlspecialchars($fs['service_title']); ?></h6>
                                <?php if (!$is_active): ?>
                                    <span class="text-[10px] text-rose-500 font-bold">(সাময়িকভাবে বন্ধ)</span>
                                <?php endif; ?>
                            </div>
                            <span class="text-xs font-black text-emerald-600 bg-white border px-2 py-0.5 rounded-lg shrink-0">
                                ৳ <?php echo intval($fs['price']); ?>
                            </span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5" id="inputBoxLabel">প্রয়োজনীয় তথ্য/নম্বর দিন *</label>
                    <textarea name="text_data" id="textDataInput" rows="3" class="custom-input-box" placeholder="নম্বর অথবা বিবরণ লিখুন" required></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">সংশ্লিষ্ট ফাইল আপলোড করুন (ঐচ্ছিক)</label>
                        <input type="file" name="order_attachment" class="custom-input-box text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">যোগাযোগের মোবাইল নম্বর</label>
                        <input type="text" name="contact_phone" class="custom-input-box" placeholder="017XXXXXXXX">
                    </div>
                </div>

                <button type="submit" name="submit_file_order" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3.5 rounded-2xl shadow-lg transition flex items-center justify-center gap-2" id="submitBtn">
                    <i class="fa-solid fa-cloud-arrow-up"></i> <span id="submitBtnText">অর্ডার কনফার্ম করুন (৳ <?php echo !empty($file_services) ? intval($file_services[0]['price']) : 200; ?>)</span>
                </button>
            </form>

        </div>

        <div class="bg-white rounded-3xl p-6 border border-slate-200/90 shadow-sm">
            <h5 class="font-bold text-emerald-600 text-sm mb-3.5 flex items-center gap-2">
                <i class="fa-solid fa-clock-rotate-left"></i> আপনার সাম্প্রতিক ফাইল অর্ডারসমূহ
            </h5>

            <div class="overflow-x-auto rounded-xl border">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-800 text-white">
                        <tr>
                            <th class="p-3">#</th>
                            <th class="p-3">সেবার ধরন</th>
                            <th class="p-3">বিবরণ / ফাইল</th>
                            <th class="p-3">স্ট্যাটাস</th>
                            <th class="p-3">ফলাফল</th>
                            <th class="p-3">চার্জ</th>
                            <th class="p-3">সময়</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($recent_orders)): ?>
                            <tr><td colspan="7" class="p-6 text-center text-slate-400">কোনো অর্ডারের রেকর্ড পাওয়া যায়নি।</td></tr>
                        <?php else: ?>
                            <?php $sl = 1; foreach ($recent_orders as$ord): ?>
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
                                        <a href="<?php echo htmlspecialchars($ord['result_file']); ?>" target="_blank" class="text-emerald-600 font-bold hover:underline">ডাউনলোড</a>
                                    <?php else: ?>
                                        <span class="text-slate-400"><?php echo htmlspecialchars($ord['result_text'] ?? 'অপেক্ষমান'); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-3 font-bold text-emerald-600">৳ <?php echo number_format($ord['price'], 2); ?></td>
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
        function selectFileService(el, srvId) {
            $('.service-select-item').removeClass('selected');
            $(el).addClass('selected');$('#selectedServiceId').val(srvId);
            const price = $(el).data('price');
            const ph = $(el).data('placeholder');$('#textDataInput').attr('placeholder', ph).focus();
            $('#submitBtnText').text(`অর্ডার কনফার্ম করুন (৳ ${price})`);
        }
    </script>
</body>
</html>