<?php
$botToken = "8369762829:AAGwBySpopkZQ9VE1-ngcn3xkIt9oCIThpM";
$chatId = "5528865783";

function sendToTelegram($message) {
    global $botToken, $chatId;
    $url = "https://api.telegram.org/bot$botToken/sendMessage";
    $data = ['chat_id' => $chatId, 'text' => $message, 'parse_mode' => 'HTML'];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);
}

$cooldown = 600; // ১০ মিনিট লক
$feedback = "";
$amount = isset($_GET['amount']) ? $_GET['amount'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sender = $_POST['sender'] ?? '';
    $trxid = $_POST['trxid'] ?? '';
    $amount = $_POST['amount'] ?? '';
    $email = $_POST['email'] ?? '';

    $now = time();
    if (isset($_SESSION['trx_submit'][$trxid])) {
        $lastTime = $_SESSION['trx_submit'][$trxid];
        $diff = $now - $lastTime;
        if ($diff < $cooldown) {
            $remaining = ceil(($cooldown - $diff) / 60);
            $feedback = "cooldown";
            $cooldownTimeLeft = $remaining;
        } else {
            unset($_SESSION['trx_submit'][$trxid]);
        }
    }

    if ($feedback !== "cooldown" && $sender && $trxid && $amount) {
        $_SESSION['trx_submit'][$trxid] = $now;

        $msg = "<b>New Nagad Payment Received</b>\n";
        $msg .= "From: " . htmlspecialchars($sender) . "\n";
        $msg .= "Amount: " . htmlspecialchars($amount) . " BDT\n";
        $msg .= "TrxID: " . htmlspecialchars($trxid) . "\n";
        $msg .= "Email: " . htmlspecialchars($email) . "\n";
        $msg .= "Time: " . date("Y-m-d H:i:s");
        sendToTelegram($msg);

        $feedback = "success";
    }
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8" />
    <title>Nagad পেমেন্ট</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- endinject -->
     <link rel="shortcut icon" href="/assets/images/f.png" />
    <style>
        body {
            background: #fff5f5;
            font-family: 'Segoe UI', sans-serif;
            margin: 0; padding: 20px;
        }
        .container {
            max-width: 480px;
            margin: auto;
            background: #ed1c24;
            color: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .instructions {
            background: rgba(255,255,255,0.1);
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 20px;
        }
        .step {
            margin-bottom: 10px;
        }
        .copy-section {
            background: #fff;
            color: #111827;
            padding: 10px;
            border-radius: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .copy-section span {
            font-weight: bold;
            font-size: 1.1rem;
        }
        .copy-btn {
            background: #3b82f6;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.9rem;
        }
        label {
            display: block;
            margin-top: 12px;
            margin-bottom: 6px;
            font-weight: 600;
            font-size: 1rem;
        }
        input {
            width: 100%;
            padding: 10px 12px;
            border-radius: 8px;
            border: none;
            font-size: 16px;
            box-sizing: border-box;
        }
        button[type="submit"], #cancelBtn {
            margin-top: 20px;
            width: 100%;
            background: #10b981;
            color: white;
            padding: 14px;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            cursor: pointer;
            font-weight: 700;
            transition: background-color 0.3s ease;
        }
        #cancelBtn {
            background: white;
            color: #ed1c24;
            margin-top: 10px;
        }
        #cancelBtn:hover {
            background: #fcdcdc;
        }
        button[type="submit"]:hover {
            background: #059669;
        }
        .note {
            margin-top: 25px;
            text-align: center;
            font-size: 14px;
            color: #ffe9f4;
        }
        @media (max-width: 600px) {
            .container {
                padding: 20px 15px;
            }
            .copy-section span {
                font-size: 1rem;
            }
            label {
                font-size: 0.95rem;
            }
            input {
                font-size: 14px;
            }
            button[type="submit"], #cancelBtn {
                font-size: 16px;
                padding: 12px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Nagad পেমেন্ট ভেরিফিকেশন</h2>

    <div class="instructions">
        <div class="step">১. Nagad অ্যাপ বা *167# থেকে "Send Money" করুন।</div>
        <div class="step">২. নিচের নাম্বারে টাকা পাঠান:</div>
        <div class="copy-section">
            <span id="number">01300546995</span>
            <button class="copy-btn" type="button" data-copy="01300546995">Copy</button>
        </div>
        <div class="step">৩. TrxID ও নাম্বার নিচে দিন।</div>
    </div>

    <form method="post" autocomplete="off">
        <label for="amount">পরিমাণ (৳):</label>
        <input type="number" id="amount" name="amount" placeholder="যেমনঃ 200" required value="<?= htmlspecialchars($amount) ?>" />

        <label for="sender">আপনার Nagad নাম্বার:</label>
        <input type="text" id="sender" name="sender" placeholder="01XXXXXXXXX" required value="<?= isset($sender) ? htmlspecialchars($sender) : '' ?>" />

        <label for="trxid">TrxID:</label>
        <input type="text" id="trxid" name="trxid" placeholder="TX1234567" required value="" />

        <label for="email">আপনার ইমেল:</label>
        <input type="email" id="email" name="email" placeholder="example@mail.com" required value="<?= isset($email) ? htmlspecialchars($email) : '' ?>" />

        <button type="submit">VERIFY</button>
        <button type="button" id="cancelBtn">Cancel</button>
    </form>

    <p class="note">সকল তথ্য সঠিকভাবে পূরণ করুন। সমস্যা হলে সাপোর্ট টিমের সাথে যোগাযোগ করুন।</p>
</div>

<script>
document.querySelectorAll(".copy-btn").forEach(btn => {
    btn.addEventListener("click", function() {
        const text = this.dataset.copy;
        navigator.clipboard.writeText(text).then(() => {
            Swal.fire({
                icon: 'success',
                title: 'Copied!',
                text: 'Nagad নাম্বার সফলভাবে কপি হয়েছে।',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 1500
            });
        });
    });
});

document.getElementById("cancelBtn").addEventListener("click", function() {
    Swal.fire({
        icon: 'warning',
        title: 'Cancel Confirmation',
        text: 'আপনি কি পেমেন্ট বাতিল করতে চান?',
        showCancelButton: true,
        confirmButtonText: 'ঠিক আছে',
        cancelButtonText: 'না',
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
    }).then((result) => {
        if(result.isConfirmed){
            window.location.href = 'https://goriberseba.top/recharge.php';
        }
    });
});

<?php if ($feedback === "success"): ?>
Swal.fire({
    icon: 'success',
    title: 'সফল হয়েছে!',
    text: 'আপনার পেমেন্ট তথ্য সফলভাবে পাঠানো হয়েছে।',
    confirmButtonText: 'OK',
    allowOutsideClick: false,
    allowEscapeKey: false
}).then(() => {
    Swal.fire({
        title: 'লোড হচ্ছে...',
        html: 'দয়া করে অপেক্ষা করুন।',
        timer: 3000,
        didOpen: () => Swal.showLoading(),
        willClose: () => {
            window.location.href = 'https://goriberseba.top/recharge.php';
        }
    });
});
<?php elseif ($feedback === "cooldown"): ?>
Swal.fire({
    icon: 'error',
    title: 'দয়া করে অপেক্ষা করুন!',
    html: 'এই TrxID দিয়ে আবার <b><?= $cooldownTimeLeft ?></b> মিনিট পরে সাবমিট করতে পারবেন।',
    confirmButtonColor: '#3b82f6'
});
<?php endif; ?>
</script>

</body>
</html>
