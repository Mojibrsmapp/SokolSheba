<?php 
include_once("header.php");

$bkash_number = get_site_setting($conn, "bkash_number", "");
$nagad_number = get_site_setting($conn, "nagad_number", "");
$support_wa   = get_site_setting($conn, "support_whatsapp_num", "");
$tg_link      = get_site_setting($conn, "telegram_link", "#");
$wa_group     = get_site_setting($conn, "whatsapp_group", "#");

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_deposit'])) {
    $amount = floatval($_POST['amount'] ?? 0);
    $method = trim($_POST['method'] ?? '');
    $trx_id = trim($_POST['trx_id'] ?? '');
    
    if ($amount < 100) {
        $_SESSION['Error'] = "সর্বনিম্ন ১০০ টাকা রিচার্জ করতে হবে।";
    } elseif (empty($method) || !in_array($method, ['bkash', 'nagad'])) {
        $_SESSION['Error'] = "সঠিক পেমেন্ট গেটওয়ে নির্বাচন করুন।";
    } elseif (empty($trx_id)) {
        $_SESSION['Error'] = "সঠিক ট্রানজেকশন আইডি (TrxID) দিন।";
    } else {
        $checkStmt = $conn->prepare("SELECT id FROM deposit_requests WHERE trx_id = ?");
        $checkStmt->bind_param("s", $trx_id);
        $checkStmt->execute();
        $checkRes = $checkStmt->get_result();

        if ($checkRes->num_rows > 0) {
            $_SESSION['Error'] = "এই ট্রানজেকশন আইডিটি ইতোমধ্যে ব্যবহার করা হয়েছে!";
        } else {
            $screenshotPath = null;
            if (isset($_FILES['screenshot']) && $_FILES['screenshot']['error'] === 0) {
                $uploadDir = "uploads/deposits/";
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $ext = strtolower(pathinfo($_FILES["screenshot"]["name"], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'webp'];
                if (in_array($ext, $allowed)) {
                    $newFilename = "dep_" . $id . "_" . time() . "." . $ext;
                    $targetFile = $uploadDir . $newFilename;
                    if (move_uploaded_file($_FILES["screenshot"]["tmp_name"], $targetFile)) {
                        $screenshotPath = $targetFile;
                    }
                }
            }

            $insStmt = $conn->prepare("INSERT INTO deposit_requests (user_id, gateway, method, trx_id, amount, screenshot, status) VALUES (?, 'manual', ?, ?, ?, ?, 'pending')");
            $insStmt->bind_param("issds", $id, $method, $trx_id, $amount, $screenshotPath);
            
            if ($insStmt->execute()) {
                $_SESSION['Success'] = "পেমেন্ট সফলভাবে প্রসেস করা হয়েছে! কিছুক্ষণের মধ্যে ব্যালেন্স যুক্ত হবে।";
            } else {
                $_SESSION['Error'] = "সার্ভারে ত্রুটি হয়েছে। অনুগ্রহ করে পুনরায় চেষ্টা করুন।";
            }
            $insStmt->close();
        }
        $checkStmt->close();
    }

    echo "<script>window.location.href='recharge.php';</script>";
    exit();
}
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

<style>
    .content-wrapper {
        padding-top: 10px !important;
        margin-top: 58px !important;
    }
    @media (min-width: 768px) {
        .content-wrapper {
            margin-top: 65px !important;
        }
    }

    body {
        background: #F8FAFC !important;
        color: #1E293B;
        font-family: 'Hind Siliguri', 'Plus Jakarta Sans', sans-serif;
    }

    .gateway-box {
        max-width: 480px;
        margin: 0 auto 16px;
        background: #FFFFFF;
        border-radius: 24px;
        border: 1px solid rgba(226, 232, 240, 0.9);
        box-shadow: 0 10px 30px rgba(124, 58, 237, 0.05);
        padding: 22px 20px;
    }

    .steps-nav {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
        position: relative;
    }
    .steps-nav::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 20%;
        right: 20%;
        height: 2px;
        background: #E2E8F0;
        z-index: 1;
        transform: translateY(-50%);
    }
    .step-node {
        position: relative;
        z-index: 2;
        background: #FFFFFF;
        width: 34px;
        height: 34px;
        border-radius: 50%;
        border: 2px solid #E2E8F0;
        color: #94A3B8;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 13px;
        transition: all 0.3s ease;
    }
    .step-node.active {
        border-color: #7C3AED;
        background: #7C3AED;
        color: #FFFFFF;
        box-shadow: 0 4px 12px rgba(124, 58, 237, 0.35);
    }
    .step-node.done {
        border-color: #10B981;
        background: #10B981;
        color: #FFFFFF;
    }

    .method-tile {
        border: 2px solid #E2E8F0;
        border-radius: 18px;
        padding: 14px 10px;
        cursor: pointer;
        transition: all 0.25s ease;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
        background: #F8FAFC;
    }
    .method-tile:hover {
        border-color: #A855F7;
        transform: translateY(-2px);
    }
    .method-tile.selected {
        border-color: #7C3AED;
        background: #F5F3FF;
        box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.15);
    }
    .method-tile img {
        height: 32px;
        object-fit: contain;
    }

    .copy-banner {
        background: #F5F3FF;
        border: 1.5px dashed #A855F7;
        border-radius: 18px;
        padding: 14px 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin: 14px 0;
    }

    .copy-btn-solid {
        background: #7C3AED !important;
        color: #FFFFFF !important;
        border: none !important;
        border-radius: 12px;
        padding: 8px 16px;
        font-size: 12px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        cursor: pointer;
        box-shadow: 0 3px 10px rgba(124, 58, 237, 0.3);
    }
    .copy-btn-solid:hover {
        background: #6D28D9 !important;
    }

    .quick-pill {
        border: 1px solid #E2E8F0;
        background: #FFFFFF;
        border-radius: 12px;
        padding: 6px 14px;
        font-size: 13px;
        font-weight: 700;
        color: #475569;
        cursor: pointer;
        transition: 0.2s;
    }
    .quick-pill:hover {
        border-color: #7C3AED;
        color: #7C3AED;
        background: #F5F3FF;
    }

    .gateway-btn {
        background: linear-gradient(135deg, #7C3AED 0%, #9333EA 100%);
        color: #FFFFFF;
        border: none;
        border-radius: 16px;
        padding: 13px;
        width: 100%;
        font-weight: 700;
        font-size: 15px;
        box-shadow: 0 6px 18px rgba(124, 58, 237, 0.25);
        transition: transform 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    .gateway-btn:hover {
        transform: translateY(-2px);
        color: #FFFFFF;
    }
    .gateway-back-btn {
        background: #F1F5F9;
        color: #475569;
        border: none;
        border-radius: 16px;
        padding: 13px;
        width: 100%;
        font-weight: 700;
        font-size: 14px;
    }

    .contact-card {
        max-width: 480px;
        margin: 0 auto 24px;
        background: #FFFFFF;
        border-radius: 20px;
        border: 1px solid rgba(226, 232, 240, 0.9);
        padding: 18px 16px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.03);
    }
    .support-btn-group {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        justify-content: center;
        margin-top: 10px;
    }
    .support-link-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        border-radius: 30px;
        font-size: 12px;
        font-weight: 700;
        text-decoration: none !important;
        color: #FFFFFF !important;
        box-shadow: 0 3px 8px rgba(0,0,0,0.08);
        transition: opacity 0.2s, transform 0.2s;
    }
    .support-link-btn:hover {
        opacity: 0.92;
        transform: translateY(-1px);
        color: #FFFFFF !important;
    }
    .btn-wa { background: #25D366 !important; }
    .btn-tg { background: #0088CC !important; }
    .btn-ch { background: #7C3AED !important; }
</style>

<div class="container px-2 px-md-3" style="max-width: 960px;">

    <?php if(isset($_SESSION['Error'])): ?>
        <div class="alert alert-danger text-center fw-bold rounded-4 shadow-sm" style="max-width: 480px; margin: 0 auto 12px;" role="alert">
            <i class="fa-solid fa-circle-exclamation me-1"></i> <?php echo $_SESSION['Error']; unset($_SESSION['Error']); ?>
        </div>
    <?php elseif(isset($_SESSION['Success'])): ?>
        <div class="alert alert-success text-center fw-bold rounded-4 shadow-sm" style="max-width: 480px; margin: 0 auto 12px;" role="alert">
            <i class="fa-solid fa-circle-check me-1"></i> <?php echo $_SESSION['Success']; unset($_SESSION['Success']); ?>
        </div>
    <?php endif; ?>

    <div class="gateway-box">
        
        <div class="text-center mb-3">
            <h4 class="font-black text-slate-800 text-lg md:text-xl flex items-center justify-center gap-2">
                <i class="fa-solid fa-bolt text-purple-600"></i> অটোমেটিক ব্যালেন্স অ্যাড
            </h4>
            <p class="text-slate-400 text-xs mt-0.5">সহজ ও দ্রুত পেমেন্ট গেটওয়ে সার্ভিস (২৪/৭ চালু)</p>
        </div>

        <div class="steps-nav">
            <div class="flex items-center gap-2">
                <div class="step-node active" id="node1">১</div>
                <span class="text-xs font-bold text-slate-700">অ্যামাউন্ট</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="step-node" id="node2">২</div>
                <span class="text-xs font-bold text-slate-400" id="labelStep2">পেমেন্ট তথ্য</span>
            </div>
        </div>

        <form action="" method="POST" enctype="multipart/form-data" id="depositForm">
            <input type="hidden" name="method" id="selectedMethodInput" value="bkash">

            <div id="tab1">
                <div class="mb-3">
                    <label class="form-label fw-bold text-xs text-slate-600 mb-1">টাকার পরিমাণ নির্বাচন করুন (সর্বনিম্ন ১০০)</label>
                    <input type="number" min="100" step="1" class="form-control rounded-3 py-2 text-center text-xl font-black border-2 border-purple-100" id="amount" name="amount" placeholder="৳ 100" required>
                    
                    <div class="flex gap-2 justify-center mt-2">
                        <span class="quick-pill" onclick="setAmount(100)">৳ ১০০</span>
                        <span class="quick-pill" onclick="setAmount(200)">৳ ২০০</span>
                        <span class="quick-pill" onclick="setAmount(500)">৳ ৫০০</span>
                        <span class="quick-pill" onclick="setAmount(1000)">৳ ১,০০০</span>
                    </div>
                </div>

                <label class="form-label fw-bold text-xs text-slate-600 mb-2">পেমেন্ট মেথড সিলেক্ট করুন</label>
                <div class="grid grid-cols-2 gap-3 mb-4">
                    <div class="method-tile selected" onclick="selectMethod('bkash', '<?php echo htmlspecialchars($bkash_number); ?>')">
                        <img src="lib/img/bkash.svg" alt="bKash" onerror="this.src='https://fastservice.totthohub.com/assets/logo.jpg'">
                        <span class="text-xs font-bold text-slate-700">বিকাশ (Send Money)</span>
                    </div>
                    <div class="method-tile" onclick="selectMethod('nagad', '<?php echo htmlspecialchars($nagad_number); ?>')">
                        <img src="lib/img/nagad.png" alt="Nagad" onerror="this.src='https://fastservice.totthohub.com/assets/logo.jpg'">
                        <span class="text-xs font-bold text-slate-700">নগদ (Send Money)</span>
                    </div>
                </div>

                <button type="button" class="gateway-btn" onclick="goToStep2()">
                    পরবর্তী ধাপ <i class="fa-solid fa-arrow-right text-xs"></i>
                </button>
            </div>

            <div id="tab2" style="display: none;">
                
                <div class="copy-banner">
                    <div>
                        <span class="text-[11px] text-purple-700 font-bold block" id="methodNameText">বিকাশ পার্সোনাল নম্বর:</span>
                        <span class="font-black text-purple-950 text-base md:text-lg tracking-wider" id="targetNumber"><?php echo htmlspecialchars($bkash_number); ?></span>
                    </div>
                    <button type="button" class="copy-btn-solid" onclick="copyNumber()">
                        <i class="fa-solid fa-copy"></i> কপি করুন
                    </button>
                </div>

                <div class="bg-amber-50 border border-amber-200 rounded-xl p-2.5 mb-3 text-[11px] text-amber-800 font-semibold flex items-center gap-2">
                    <i class="fa-solid fa-circle-info text-amber-600 text-sm"></i>
                    <span>উপরের নাম্বারে <b id="displayPayAmount">৳ ১০০</b> সেন্ড মানি করে TrxID প্রদান করুন।</span>
                </div>

                <div class="mb-3">
                    <label for="trx_id" class="form-label fw-bold text-xs text-slate-600">ট্রানজেকশন আইডি (TrxID)</label>
                    <input type="text" class="form-control rounded-3 py-2 text-center uppercase tracking-widest font-black text-sm" id="trx_id" name="trx_id" placeholder="যেমন: BK105953 / 7A8B9C">
                </div>

                <div class="mb-4">
                    <label for="screenshot" class="form-label fw-bold text-xs text-slate-600">পেমেন্ট স্ক্রিনশট (ঐচ্ছিক)</label>
                    <input type="file" class="form-control rounded-3 text-xs py-2" id="screenshot" name="screenshot" accept="image/*">
                </div>

                <div class="grid grid-cols-3 gap-2">
                    <button type="button" class="gateway-back-btn col-span-1" onclick="goToStep1()">
                        <i class="fa-solid fa-arrow-left me-1"></i> ব্যাকে যান
                    </button>
                    <button type="submit" name="submit_deposit" class="gateway-btn col-span-2">
                        <i class="fa-solid fa-circle-check me-1"></i> কনফার্ম করুন
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div class="contact-card">
        <div class="text-center mb-2">
            <h6 class="font-extrabold text-slate-800 text-xs md:text-sm flex items-center justify-center gap-1.5 mb-0.5">
                <i class="fa-solid fa-headset text-purple-600"></i> পেমেন্টে কোনো সমস্যা হলে যোগাযোগ করুন
            </h6>
            <p class="text-[11px] text-slate-500 mb-0">যেকোনো সহায়তায় আমাদের হেল্পলাইন ২৪/৭ খোলা আছে</p>
        </div>
        <div class="support-btn-group">
            <a href="https://wa.me/88<?php echo htmlspecialchars($support_wa); ?>" target="_blank" class="support-link-btn btn-wa">
                <i class="fa-brands fa-whatsapp text-sm"></i> WhatsApp: <?php echo htmlspecialchars($support_wa); ?>
            </a>
            <a href="<?php echo htmlspecialchars($tg_link); ?>" target="_blank" class="support-link-btn btn-tg">
                <i class="fa-brands fa-telegram text-sm"></i> Telegram
            </a>
            <a href="<?php echo htmlspecialchars($wa_group); ?>" target="_blank" class="support-link-btn btn-ch">
                <i class="fa-solid fa-bullhorn text-xs"></i> Join Group
            </a>
        </div>
    </div>

    <div class="card w-100 border-0 shadow-sm rounded-4 max-w-[960px] mx-auto">
        <div class="card-body p-3 p-md-4">
            <h5 class="card-title fw-bold text-slate-800 mb-3 text-sm md:text-base">পূর্ববর্তী লেনদেন হিস্টোরি</h5>
            <div class="table-responsive">
                <table class="table text-nowrap mb-0 align-middle text-sm">
                    <thead class="bg-slate-50 text-slate-600">
                        <tr>
                            <th>আইডি</th>
                            <th>মেথড ও ট্রানজেকশন</th>
                            <th>সময়</th>
                            <th>পরিমাণ</th>
                            <th>স্ট্যাটাস</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $history = [];
                        if (!empty($id)) {
                            $histStmt = $conn->prepare("SELECT id, method, trx_id, amount, status, created_at FROM deposit_requests WHERE user_id = ? ORDER BY created_at DESC LIMIT 50");
                            $histStmt->bind_param("i", $id);
                            $histStmt->execute();
                            $histRes = $histStmt->get_result();
                            while ($row = $histRes->fetch_assoc()) {
                                $history[] = $row;
                            }
                            $histStmt->close();
                        }

                        if (empty($history)):
                        ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">কোনো লেনদেন পাওয়া যায়নি</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($history as $row):
                            $statusLabel = [
                                'pending'   => 'প্রসেসিং',
                                'completed' => 'সফল',
                                'failed'    => 'বাতিল',
                                'cancelled' => 'বাতিল',
                            ][$row['status']] ?? ucfirst($row['status']);

                            $badgeClass = [
                                'pending'   => 'bg-amber-100 text-amber-800 border border-amber-200',
                                'completed' => 'bg-emerald-100 text-emerald-800 border border-emerald-200',
                                'failed'    => 'bg-rose-100 text-rose-800 border border-rose-200',
                                'cancelled' => 'bg-slate-100 text-slate-800',
                            ][$row['status']] ?? 'bg-light text-dark';
                        ?>
                        <tr>
                            <td class="fw-bold">#<?php echo htmlspecialchars($row['id']); ?></td>
                            <td>
                                <span class="badge bg-purple-50 text-purple-700 border border-purple-200 uppercase font-bold"><?php echo htmlspecialchars($row['method']); ?></span>
                                <div class="font-mono text-xs text-slate-500 mt-0.5"><?php echo htmlspecialchars($row['trx_id']); ?></div>
                            </td>
                            <td class="text-slate-500 text-xs"><?php echo htmlspecialchars(date('d M Y, h:i A', strtotime($row['created_at']))); ?></td>
                            <td class="fw-bold text-slate-800">৳ <?php echo htmlspecialchars($row['amount']); ?></td>
                            <td>
                                <span class="badge <?php echo $badgeClass; ?> rounded-pill px-3 py-1 text-xs"><?php echo $statusLabel; ?></span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<script>
let currentNumber = "<?php echo htmlspecialchars($bkash_number); ?>";

function selectMethod(method, number) {
    $('.method-tile').removeClass('selected');
    event.currentTarget.classList.add('selected');
    
    $('#selectedMethodInput').val(method);
    currentNumber = number;
    $('#targetNumber').text(number);
    
    if (method === 'bkash') {
        $('#methodNameText').text('বিকাশ পার্সোনাল নম্বর:');
    } else {
        $('#methodNameText').text('নগদ পার্সোনাল নম্বর:');
    }
}

function setAmount(val) {
    $('#amount').val(val).focus();
}

function copyNumber() {
    navigator.clipboard.writeText(currentNumber).then(() => {
        Swal.fire({
            icon: 'success',
            title: 'কপি সফল!',
            text: currentNumber,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 1500
        });
    });
}

function goToStep2() {
    let amount = parseFloat($('#amount').val());
    if (isNaN(amount) || amount < 100) {
        Swal.fire({
            icon: 'warning',
            title: 'অ্যামাউন্ট দিন',
            text: 'অনুগ্রহ করে সর্বনিম্ন ১০০ টাকা লিখুন।'
        });
        return;
    }

    $('#displayPayAmount').text('৳ ' + amount);
    
    $('#tab1').fadeOut(150, function() {
        $('#tab2').fadeIn(150);
        $('#node1').removeClass('active').addClass('done');
        $('#node2').addClass('active');
        $('#labelStep2').removeClass('text-slate-400').addClass('text-slate-700');
    });
}

function goToStep1() {
    $('#tab2').fadeOut(150, function() {
        $('#tab1').fadeIn(150);
        $('#node2').removeClass('active');
        $('#node1').removeClass('done').addClass('active');
        $('#labelStep2').removeClass('text-slate-700').addClass('text-slate-400');
    });
}
</script>

<?php include_once("footer.php"); ?>