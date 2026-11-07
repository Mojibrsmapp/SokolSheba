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
    $user_id = (int)$user['id'];
    $user_name = $user['name'];
    $user_email = $user['email'];
    $user_balance = (float)$user['balance'];
} else {
    unset($_SESSION['user_token'], $_SESSION['user_id']);
    header("Location: index.php");
    exit();
}
$stmt->close();

$services = [];
$categories = [];
$q_serv = $conn->query("SELECT id, service_name, category, price, description, custom_fields FROM custom_services WHERE status = 'active' ORDER BY id ASC");
if ($q_serv) {
    while ($row = $q_serv->fetch_assoc()) {
        $services[] = $row;
        $cat = trim($row['category']);
        if (!empty($cat) && !in_array($cat, $categories)) {
            $categories[] = $cat;
        }
    }
}

$selected_service_name = isset($_GET['service']) ? trim($_GET['service']) : '';

$alert_msg = '';
$alert_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_order'])) {
    $service_id = (int)($_POST['service_id'] ?? 0);
    $dynamic_inputs = $_POST['dynamic_field'] ?? [];

    $input_info_arr = [];
    if (is_array($dynamic_inputs)) {
        foreach ($dynamic_inputs as $f_title => $f_val) {
            $f_val_clean = trim($f_val);
            if (!empty($f_val_clean)) {
                $input_info_arr[] = $f_title . ': ' . $f_val_clean;
            }
        }
    }
    $input_info = !empty($input_info_arr) ? implode("\n", $input_info_arr) : trim($_POST['fallback_input'] ?? '');

    $stmt_srv = $conn->prepare("SELECT service_name, price FROM custom_services WHERE id = ? AND status = 'active'");
    $stmt_srv->bind_param("i", $service_id);
    $stmt_srv->execute();
    $srv_res = $stmt_srv->get_result();

    if ($srv_res && $srv_res->num_rows > 0) {
        $srv = $srv_res->fetch_assoc();
        $service_name = $srv['service_name'];
        $charge = (float)$srv['price'];

        if (empty($input_info)) {
            $alert_msg = "অনুগ্রহ করে প্রয়োজনীয় তথ্য প্রদান করুন।";
            $alert_type = "warning";
        } elseif ($user_balance < $charge) {
            $alert_msg = "আপনার অ্যাকাউন্টে পর্যাপ্ত ব্যালেন্স নেই! চার্জ: ৳" . number_format($charge, 2) . ", বর্তমান ব্যালেন্স: ৳" . number_format($user_balance, 2);
            $alert_type = "error";
        } else {
            $uploaded_file_path = null;
            if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
                $file_tmp = $_FILES['attachment']['tmp_name'];
                $file_name = $_FILES['attachment']['name'];
                $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'pdf', 'zip', 'txt'];

                if (in_array($ext, $allowed)) {
                    $upload_dir = __DIR__ . '/uploads/orders/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    $new_file_name = 'order_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                    $dest = $upload_dir . $new_file_name;
                    if (move_uploaded_file($file_tmp, $dest)) {
                        $uploaded_file_path = 'uploads/orders/' . $new_file_name;
                    }
                }
            }

            $conn->begin_transaction();
            try {
                $new_balance = $user_balance - $charge;
                $up_user = $conn->prepare("UPDATE users SET balance = ? WHERE id = ?");
                $up_user->bind_param("di", $new_balance, $user_id);
                $up_user->execute();
                $up_user->close();

                $ins_order = $conn->prepare("INSERT INTO orders (user_id, order_type, input_data, price, status, result_file, created_at) VALUES (?, ?, ?, ?, 'pending', ?, NOW())");
                $ins_order->bind_param("issds", $user_id, $service_name, $input_info, $charge, $uploaded_file_path);
                $ins_order->execute();
                $ins_order->close();

                @$conn->query("INSERT INTO history_work (email, order_type, price, current_balance, balance_after_cut, about_order, order_time) 
                               VALUES ('$user_email', '$service_name', $charge, $user_balance, $new_balance, 'place_order', NOW())");

                $conn->commit();
                $user_balance = $new_balance;
                $alert_msg = "অর্ডার সফলভাবে গ্রহণ করা হয়েছে! কিছুক্ষণের মধ্যে সম্পন্ন করা হবে।";
                $alert_type = "success";
            } catch (Exception $e) {
                $conn->rollback();
                $alert_msg = "অর্ডার প্রক্রিয়াকরণে সমস্যা হয়েছে। অনুগ্রহ করে আবার চেষ্টা করুন।";
                $alert_type = "error";
            }
        }
    } else {
        $alert_msg = "অনুগ্রহ করে তালিকা থেকে একটি সঠিক সেবা নির্বাচন করুন।";
        $alert_type = "warning";
    }
    $stmt_srv->close();
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>কাস্টম ও ফাইল অর্ডার - <?php echo soko_e(soko_brand('site_name')); ?></title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/maateen/bangla-fonts/Nikosh/stylesheet.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="shortcut icon" href="<?php echo soko_e(soko_brand('site_favicon')); ?>">

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

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Nikosh', sans-serif !important;
            -webkit-font-smoothing: antialiased;
        }

        .fa, .fas, .far, .fab, .fa-solid, .fa-regular, .fa-brands {
            font-family: "Font Awesome 6 Free", "Font Awesome 6 Brands" !important;
            display: inline-block;
            font-style: normal;
            font-variant: normal;
            text-rendering: auto;
            line-height: 1;
        }

        body {
            background-color: var(--bg-page);
            color: var(--paragraph-color);
            min-height: 100vh;
        }

        .order-card {
            background: var(--card-bg);
            border: 1.5px solid var(--border-color);
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(7, 139, 56, 0.05);
            overflow: hidden;
        }

        .order-card-header {
            background: linear-gradient(135deg, #F5FAF6 0%, #FFFFFF 100%);
            border-bottom: 1.5px solid var(--border-color);
            padding: 16px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .order-card-header h2 {
            margin: 0;
            font-size: 17px;
            font-weight: 800;
            color: var(--heading-color);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .service-badge {
            font-size: 11px;
            font-weight: 800;
            padding: 4px 10px;
            border-radius: 20px;
            background: #EAF6EE;
            color: var(--brand-green);
            border: 1px solid #C8E3CF;
        }

        .order-card-body {
            padding: 20px;
        }

        .field-label {
            font-size: 13.5px;
            font-weight: 800;
            color: var(--heading-color);
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .tab-scroll-wrap {
            display: flex;
            gap: 8px;
            overflow-x: auto;
            padding-bottom: 8px;
            margin-bottom: 14px;
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
            padding: 6px 14px;
            border-radius: 9999px;
            font-size: 12.5px;
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

        .service-grid-box {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
            gap: 10px;
            max-height: 290px;
            overflow-y: auto;
            padding: 4px;
            margin-bottom: 16px;
            border: 1.5px solid var(--border-color);
            border-radius: 16px;
            background: #FAFCFA;
        }
        .service-grid-box::-webkit-scrollbar {
            width: 5px;
        }
        .service-grid-box::-webkit-scrollbar-thumb {
            background: #c2d9cb;
            border-radius: 4px;
        }

        .service-select-card {
            border: 1.5px solid var(--border-color);
            background: #FFFFFF;
            border-radius: 12px;
            padding: 10px 12px;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
        }
        .service-select-card:hover {
            border-color: var(--brand-green);
            transform: translateY(-1px);
        }
        .service-select-card.selected {
            border-color: var(--brand-green);
            background: #EAF6EE;
            box-shadow: 0 4px 12px rgba(7, 139, 56, 0.18);
        }
        .service-select-card .card-sname {
            font-size: 13px;
            font-weight: 800;
            color: #1E293B;
            line-height: 1.25;
            margin-bottom: 6px;
        }
        .service-select-card .card-sprice {
            font-size: 12px;
            font-weight: 900;
            color: var(--brand-green);
        }
        .service-select-card .card-check {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            border: 1.5px solid #CBD5E1;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            color: transparent;
        }
        .service-select-card.selected .card-check {
            border-color: var(--brand-green);
            background: var(--brand-green);
            color: #FFFFFF;
        }

        .service-details-box {
            background: #EAF6EE;
            border: 1.5px solid #C8E3CF;
            border-radius: 14px;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
        }

        .dynamic-fields-area {
            background: #FAFCFA;
            border: 1.5px solid var(--border-color);
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 16px;
        }

        .form-input-neu {
            width: 100%;
            border: 1.5px solid var(--border-color) !important;
            border-radius: 12px !important;
            padding: 10px 14px !important;
            font-size: 13.5px !important;
            font-weight: 600;
            background: #FFFFFF !important;
            color: #1E293B !important;
            outline: none !important;
            transition: all 0.2s ease;
        }
        .form-input-neu:focus {
            border-color: var(--brand-green) !important;
            box-shadow: 0 0 0 3px rgba(7, 139, 56, 0.12) !important;
        }

        .file-upload-box {
            border: 1.5px dashed var(--border-color);
            background: #FAFCFA;
            border-radius: 14px;
            padding: 10px 14px;
            cursor: pointer;
            transition: border-color 0.2s ease;
        }
        .file-upload-box:hover {
            border-color: var(--brand-green);
        }

        .btn-submit-order {
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
        .btn-submit-order:hover {
            transform: translateY(-1.5px);
            box-shadow: 0 8px 22px rgba(7, 139, 56, 0.38);
        }

        .mobile-bottom-nav {
            display: none;
        }

        @media (max-width: 991px) {
            body {
                padding-bottom: 75px;
            }
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

    <main class="max-w-4xl mx-auto px-4 py-5 md:py-7">

        <div class="order-card">
            <div class="order-card-header">
                <h2>
                    <i class="fa-solid fa-file-arrow-up text-[#078B38]"></i>
                    <span>কাস্টম ও ফাইল সার্ভিস অর্ডার</span>
                </h2>
                <span class="service-badge"><?php echo soko_e(soko_brand('site_name')); ?></span>
            </div>

            <div class="order-card-body">
                <form action="" method="POST" enctype="multipart/form-data" class="space-y-4">
                    <input type="hidden" name="service_id" id="service_id_input" required>

                    <div>
                        <label class="field-label">
                            <i class="fa-solid fa-shapes text-[#078B38]"></i>
                            <span>ক্যাটাগরি ও সেবা নির্বাচন করুন</span> <span class="text-[#E51B23]">*</span>
                        </label>

                        <div class="tab-scroll-wrap">
                            <button type="button" class="category-tab-btn active" data-cat="all">সকল সেবা</button>
                            <?php foreach ($categories as $c_name): ?>
                                <button type="button" class="category-tab-btn" data-cat="<?php echo htmlspecialchars($c_name); ?>">
                                    <?php echo htmlspecialchars($c_name); ?>
                                </button>
                            <?php endforeach; ?>
                        </div>

                        <div class="service-grid-box" id="serviceGrid">
                            <?php foreach ($services as $srv): 
                                $is_sel = ($selected_service_name === $srv['service_name']);
                            ?>
                                <div class="service-select-card <?php echo $is_sel ? 'selected' : ''; ?>" 
                                     data-id="<?php echo $srv['id']; ?>"
                                     data-cat="<?php echo htmlspecialchars($srv['category']); ?>"
                                     data-name="<?php echo htmlspecialchars($srv['service_name']); ?>"
                                     data-price="<?php echo $srv['price']; ?>"
                                     data-desc="<?php echo htmlspecialchars($srv['description']); ?>"
                                     data-fields='<?php echo htmlspecialchars($srv['custom_fields'] ?? '[]', ENT_QUOTES, 'UTF-8'); ?>'>
                                    <span class="card-check"><i class="fa-solid fa-check"></i></span>
                                    <div class="card-sname"><?php echo htmlspecialchars($srv['service_name']); ?></div>
                                    <div class="card-sprice">৳ <?php echo number_format($srv['price'], 2); ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div id="serviceDetailsCard" class="hidden service-details-box">
                        <div>
                            <span class="text-xs font-bold text-[#124D2B] block" id="srvDescText">বিবরণ</span>
                        </div>
                        <div class="text-right">
                            <span class="text-[10.5px] font-bold text-[#53665A] block">সার্ভিস চার্জ</span>
                            <span class="text-sm font-black text-[#078B38]" id="srvPriceText">৳0.00</span>
                        </div>
                    </div>

                    <div id="dynamicFieldsContainer" class="hidden dynamic-fields-area space-y-3">
                        <label class="field-label mb-2">
                            <i class="fa-solid fa-pen-to-square text-[#078B38]"></i>
                            <span>প্রয়োজনীয় তথ্য পূরণ করুন</span> <span class="text-[#E51B23]">*</span>
                        </label>
                        <div id="fieldsInjectArea" class="space-y-3"></div>
                    </div>

                    <div id="fallbackFieldContainer" class="space-y-1">
                        <label class="field-label">
                            <i class="fa-solid fa-circle-info text-[#078B38]"></i>
                            <span>প্রয়োজনীয় তথ্য / বিবরণ</span> <span class="text-[#E51B23]">*</span>
                        </label>
                        <textarea name="fallback_input" id="fallback_input" rows="3" placeholder="উদাহরণ: মোবাইল নম্বর বা প্রয়োজনীয় তথ্য এখানে লিখুন..." class="form-textarea-neu" style="resize: none;"></textarea>
                    </div>

                    <div>
                        <label class="field-label">
                            <i class="fa-solid fa-paperclip text-[#078B38]"></i>
                            <span>সংযুক্তি ফাইল (যদি থাকে - ঐচ্ছিক)</span>
                        </label>
                        <div class="file-upload-box">
                            <input type="file" name="attachment" accept=".jpg,.jpeg,.png,.pdf,.zip,.txt" class="w-full text-xs text-[#53665A] file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-[#EAF6EE] file:text-[#078B38] hover:file:bg-[#d8eedf] cursor-pointer">
                        </div>
                        <span class="text-[11px] font-semibold text-[#53665A] mt-1 block">অনুমোদিত ফরম্যাট: JPG, PNG, PDF, ZIP (সর্বোচ্চ ১০ মেগাবাইট)</span>
                    </div>

                    <div class="pt-2">
                        <button type="submit" name="submit_order" class="btn-submit-order">
                            <i class="fa-solid fa-cart-shopping"></i>
                            <span>অর্ডার নিশ্চিত করুন</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </main>


    <script>
        $(document).ready(function() {
            $('.category-tab-btn').on('click', function() {
                $('.category-tab-btn').removeClass('active');
                $(this).addClass('active');

                const targetCat = $(this).data('cat');

                if (targetCat === 'all') {
                    $('.service-select-card').show();
                } else {
                    $('.service-select-card').each(function() {
                        if ($(this).data('cat') === targetCat) {
                            $(this).show();
                        } else {
                            $(this).hide();
                        }
                    });
                }
            });

            $(document).on('click', '.service-select-card', function() {
                $('.service-select-card').removeClass('selected');
                $(this).addClass('selected');

                const sId = $(this).data('id');
                const sName = $(this).data('name');
                const sPrice = parseFloat($(this).data('price'));
                const sDesc = $(this).data('desc');
                const rawFields = $(this).attr('data-fields');

                $('#service_id_input').val(sId);
                $('#srvDescText').text(sDesc || sName);
                $('#srvPriceText').text('৳ ' + sPrice.toFixed(2));
                $('#serviceDetailsCard').removeClass('hidden');

                let fields = [];
                try {
                    fields = JSON.parse(rawFields);
                } catch(e) {
                    fields = [];
                }

                const injectArea = $('#fieldsInjectArea');
                injectArea.empty();

                if (Array.isArray(fields) && fields.length > 0) {
                    $('#fallbackFieldContainer').hide();
                    $('#fallback_input').removeAttr('required');
                    $('#dynamicFieldsContainer').removeClass('hidden');

                    fields.forEach(function(f) {
                        let fTitle = f.title || 'তথ্য দিন';
                        let fType = f.type || 'text';
                        let fReq = f.required ? 'required' : '';
                        let fReqMark = f.required ? '<span class="text-rose-500">*</span>' : '';

                        let inputMarkup = '';
                        if (fType === 'textarea') {
                            inputMarkup = `<textarea name="dynamic_field[${fTitle}]" class="form-input-neu" rows="2" placeholder="${fTitle} লিখুন..." ${fReq} style="resize: none;"></textarea>`;
                        } else {
                            let inputType = (fType === 'number' || fType === 'date') ? fType : 'text';
                            inputMarkup = `<input type="${inputType}" name="dynamic_field[${fTitle}]" class="form-input-neu" placeholder="${fTitle} লিখুন..." ${fReq}>`;
                        }

                        injectArea.append(`
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">${fTitle} ${fReqMark}</label>
                                ${inputMarkup}
                            </div>
                        `);
                    });
                } else {
                    $('#dynamicFieldsContainer').addClass('hidden');
                    $('#fallbackFieldContainer').show();
                    $('#fallback_input').attr('required', 'required');
                }
            });

            const initialSelected = $('.service-select-card.selected').first();
            if (initialSelected.length > 0) {
                initialSelected.trigger('click');
            } else {
                $('.service-select-card').first().trigger('click');
            }

            $('form').on('submit', function(e) {
                if (!$('#service_id_input').val()) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'সেবা নির্বাচন করুন',
                        text: 'তালিকা থেকে অনুগ্রহ করে একটি সার্ভিস কার্ডে ক্লিক করে নির্বাচন করুন।',
                        confirmButtonColor: '#078B38'
                    });
                }
            });
        });

        <?php if (!empty($alert_msg)): ?>
            Swal.fire({
                icon: '<?php echo $alert_type; ?>',
                title: '<?php echo ($alert_type === "success") ? "সফল!" : "বিজ্ঞপ্তি"; ?>',
                text: '<?php echo $alert_msg; ?>',
                confirmButtonColor: '<?php echo ($alert_type === "success") ? "#078B38" : "#E51B23"; ?>',
                confirmButtonText: 'ঠিক আছে',
                background: '#FFFFFF',
                color: '#124D2B'
            });
        <?php endif; ?>
    </script>
<?php include __DIR__ . '/includes/site_footer.php'; ?>
</body>
</html>