<?php
class OrderHandler
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function placeOrder($email, $type, $orderType, $nid, $name, $birthDate, $aboutOrder)
    {
        // Check if work is available for the given order type
        if (!$this->isWorkAvailable($type)) {
            return "Error: এই অর্ডারের কাজটি বর্তমানে বন্ধ রয়েছে। দয়া করে পরে চেষ্টা করুন।";
        }

        // Fetch price based on order type
        $price = $this->getPrice($type);

        if ($price === null) {
            return "Error: আপনি ফ্রন্টইন্ড কোড ইডিট করে অর্ডার করার চেষ্টা করেছেন। আবার এমন করলে একাউন্ট থেকে ১০০ টাকা কেটে নেয়া হবে জরিমানাস্বরুপ";
        }

        // Check if the user has enough balance
        $currentBalance = $this->getCurrentBalance($email);
        if ($currentBalance < $price) {
            return "Error: আপনার পর্যাপ্ত ব্যালেন্স নেই। প্রথমে রিচার্জ করুন অর্ডার করতে। ধন্যবাদ!";
        }

        // Insert order into the order_list
        $sql = "INSERT INTO order_list (email, user_type, order_type, nid, price, user_name, user_date, details) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssssssss", $email, $orderType, $type, $nid, $price, $name, $birthDate, $aboutOrder);

        if ($stmt->execute()) {
            // Deduct balance from the users table
            $this->deductBalance($email, $price);

            // Insert into history_work table
            $this->insertIntoHistory($email, $type, $price, $currentBalance);

            // Send Telegram notification
            $chatId = "8433591638";
            $current_time = date('h:i:s A');
            $messageText2 = "ইউজার নামঃ {$email}.\nঅর্ডারটির তথ্যঃ {$nid}\nঅর্ডারটির মূল্যঃ " . $this->convertToBengaliNumber($price) . " টাকা। \nঅর্ডাটির সময়ঃ" . $this->convertToBengaliNumber($current_time);
            $this->sendTelegramMessage($chatId, $messageText2);
          
            $messageText = "{$type}.\n{$nid}";
            $this->sendTelegramMessage($chatId, $messageText);

            return "সফলভাবে অর্ডার সম্পূর্ণ হয়েছে! ব্যালেন্স কেটে নেয়া হয়েছেঃ " . $this->convertToBengaliNumber($price) . " টাকা।";
        } else {
            return "Error: " . $stmt->error;
        }

        // Close the statement
        $stmt->close();
    }

    private function isWorkAvailable($orderType)
    {
        $sql = "SELECT work_status FROM prices WHERE orderName = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $orderType);
        $stmt->execute();
        $stmt->bind_result($work_status);
        $stmt->fetch();
        $stmt->close();

        return $work_status == 1; // Returns true if work_status is "1" (on), false otherwise
    }

    private function getPrice($orderType)
    {
        $sql = "SELECT price FROM prices WHERE orderName = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $orderType);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $row = $result->fetch_assoc()) {
            return $row['price'];
        }

        return null;
    }

    private function getCurrentBalance($email)
    {
        $sql = "SELECT balance FROM users WHERE email=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->bind_result($balance);
        $stmt->fetch();
        $stmt->close();

        return $balance;
    }

    private function deductBalance($email, $price)
    {
        $sql = "UPDATE users SET balance = balance - ? WHERE email=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ds", $price, $email);
        $stmt->execute();
        $stmt->close();
    }

    private function insertIntoHistory($email, $orderType, $price, $currentBalance)
    {
        $balanceAfterCut = $currentBalance - $price;
        date_default_timezone_set('Asia/Dhaka');
        $orderTime = date('Y-m-d H:i:s');

        $sql = "INSERT INTO history_work (email, order_type, price, current_balance, balance_after_cut, order_time) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssdsss", $email, $orderType, $price, $currentBalance, $balanceAfterCut, $orderTime);
        $stmt->execute();
        $stmt->close();
    }

    private function sendTelegramMessage($chatId, $messageText)
{
    $botToken = "8547512624:AAEgBfyVyKrcncTDpYgRmhH1PbP9gqq0ra8";
    $url = "https://api.telegram.org/bot{$botToken}/sendMessage";

    $postData = [
        'chat_id' => $chatId,
        'text' => $messageText,
        'parse_mode' => 'HTML'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        error_log("Telegram API Error: " . curl_error($ch));
        curl_close($ch);
        return false;
    }

    curl_close($ch);
    return true;
}


    private function convertToBengaliNumber($number)
    {
        $englishNumbers = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $bengaliNumbers = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];

        return str_replace($englishNumbers, $bengaliNumbers, $number);
    }
}
?>
