<?php
$botToken = "8369762829:AAGwBySpopkZQ9VE1-ngcn3xkIt9oCIThpM";
$chatId = "5528865783";

function sendToTelegram($message) {
    global $botToken, $chatId;
    $url = "https://api.telegram.org/bot".$botToken."/sendMessage";
    $data = ['chat_id' => $chatId, 'text' => $message, 'parse_mode' => "HTML"];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}

session_start();
$feedback = '';
$autoAmount = $_GET['amount'] ?? '';
$canSubmit = true;
$waitMinutes = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sender = $_POST['sender'] ?? '';
    $trxid = $_POST['trxid'] ?? '';
    $amount = $_POST['amount'] ?? '';
    $email = $_POST['email'] ?? '';

    if (!empty($sender) && !empty($trxid) && !empty($amount)) {
        $lastSubmit = $_SESSION['last_submit'][$trxid] ?? 0;
        $now = time();

        if ($now - $lastSubmit < 600) {
            $canSubmit = false;
            $waitMinutes = ceil((600 - ($now - $lastSubmit)) / 60);
            $feedback = "wait";
        } else {
            $_SESSION['last_submit'][$trxid] = $now;

            $message = "<b>New bKash Payment Received</b>\n";
            $message .= "From: " . htmlspecialchars($sender) . "\n";
            $message .= "Amount: " . htmlspecialchars($amount) . "\n";
            $message .= "Transaction ID: " . htmlspecialchars($trxid) . "\n";
            $message .= "Email: " . htmlspecialchars($email) . "\n";
            $message .= "Time: " . date('Y-m-d H:i:s');
            $result = sendToTelegram($message);
            $feedback = $result !== false ? "success" : "error";
        }
    } else {
        $feedback = "empty";
    }
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title>bKash পেমেন্ট</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- endinject -->
     <link rel="shortcut icon" href="assets/images/f.png" />
    <style>
        body {
            background: #f0f2f5;
            font-family: 'Segoe UI', sans-serif;
            padding: 20px;
        }
        .container {
            max-width: 480px;
            margin: auto;
            background: #e2136e;
            color: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
        }
        .instructions {
            background: rgba(255, 255, 255, 0.1);
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 20px;
        }
        .step {
            margin-bottom: 10px;
        }
        label {
            display: block;
            margin-top: 12px;
            margin-bottom: 6px;
            font-weight: 500;
        }
        .copy-section {
            background: #fff;
            color: #111827;
            padding: 10px;
            border-radius: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .copy-btn {
            background: #3b82f6;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            width: 65px;
            margin-top: -2px;
        }
        input, button {
            width: 96%;
            padding: 12px;
            font-size: 16px;
            border-radius: 8px;
            border: none;
            margin-top: 6px;
        }
        button{
            width:100%;
            cursor:pointer;
        }
        button[type="submit"] {
            background: #10b981;
            color: white;
        }
        #cancelButton {
            background: #ef4444;
            color: white;
        }
        .note {
            font-size: 14px;
            color: #ffe9f4;
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>bKash পেমেন্ট ভেরিফিকেশন</h2>

    <div class="instructions">
        <div class="step">১. bKash অ্যাপ বা *247# থেকে "Send Money" করুন।</div>
        <div class="step">২. নিচের নাম্বারে টাকা পাঠান:</div>
        <div class="copy-section">
            <span id="number">01341803993</span>
            <button class="copy-btn" type="button" data-copy="01341803993">Copy</button>
        </div>
        <div class="step">৩. TrxID ও নাম্বার নিচে দিন।</div>
    </div>

    <form method="post">
        <label for="amount">পরিমাণ (৳):</label>
        <input type="number" name="amount" id="amount" placeholder="যেমনঃ 200" required value="<?php echo htmlspecialchars($autoAmount); ?>">

        <label for="sender">আপনার bKash নাম্বার:</label>
        <input type="text" name="sender" id="sender" placeholder="01XXXXXXXXX" required>

        <label for="trxid">TrxID:</label>
        <input type="text" name="trxid" id="trxid" placeholder="যেমনঃ TX1234567" required>

        <label for="email">আপনার ইমেল:</label>
        <input type="email" name="email" id="email" placeholder="example@mail.com" required>

        <button type="submit" class="align-center">VERIFY</button>
        <button type="button" id="cancelButton">CANCEL</button>
    </form>

    <p class="note">সকল তথ্য সঠিকভাবে পূরণ করুন, কোন সমস্যা হলে সাপোর্টে যোগাযোগ করুন।</p>
</div>

<script>
document.querySelectorAll(".copy-btn").forEach(btn => {
    btn.addEventListener("click", function () {
        const text = this.dataset.copy;
        navigator.clipboard.writeText(text).then(() => {
            Swal.fire({
                title: 'Copied!',
                text: 'Bkash Number copied successfully',
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'Copied!',
                showConfirmButton: false,
                timer: 1500
            });
        });
    });
});

document.getElementById("cancelButton").addEventListener("click", function () {
    Swal.fire({
        icon: 'error',
        title: 'পেমেন্ট বাতিল?',
        text: 'আপনি কি রিচার্জ বাতিল করতে চান?',
        showCancelButton: true,
        confirmButtonText: 'ঠিক আছে',
        cancelButtonText: 'না',
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "https://goriberseba.top/recharge.php";
        }
    });
});

<?php if ($feedback === "success"): ?>
Swal.fire({
    icon: 'success',
    title: 'Success!',
    text: 'আপনার তথ্য সফলভাবে সাবমিট হয়েছে।',
    confirmButtonText: 'OK'
}).then(() => {
    Swal.fire({
        title: 'Loading...',
        html: 'দয়া করে অপেক্ষা করুন...',
        timer: 3000,
        didOpen: () => Swal.showLoading(),
        willClose: () => {
            window.location.href = 'https://goriberseba.top/recharge.php';
        }
    });
});
<?php elseif ($feedback === "error"): ?>
Swal.fire({ icon: 'error', title: 'Error!', text: 'তথ্য পাঠানো ব্যর্থ।' });
<?php elseif ($feedback === "empty"): ?>
Swal.fire({ icon: 'warning', title: 'Missing Info', text: 'সব ঘর পূরণ করুন।' });
<?php elseif ($feedback === "wait"): ?>
Swal.fire({
    icon: 'info',
    title: 'আবার অপেক্ষা করুন',
    html: 'এই TrxID দিয়ে আবার <b><?php echo $waitMinutes; ?> মিনিট</b> পরে সাবমিট করতে পারবেন।',
    confirmButtonColor: '#3b82f6'
});
<?php endif; ?>
</script>
</body>
</html>
