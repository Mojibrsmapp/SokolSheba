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
        // Fetch price based on order type
        $price = $this->getPrice($type);

        if ($price === null) {
            return "Error: Invalid order type.";
        }

        // Check if the user has enough balance
        $currentBalance = $this->getCurrentBalance($email);
        if ($currentBalance < $price) {
            return "আপনার পর্যাপ্ত ব্যালেন্স নেই। প্রথমে রিচার্জ করুন অর্ডার করতে। ধন্যবাদ!";
        }

        // Insert order into the order_list
        $sql = "INSERT INTO order_list (email, user_type, order_type, nid, user_name, user_date, details) VALUES ( ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssssss", $email, $orderType, $type, $nid, $name, $birthDate, $aboutOrder);

        if ($stmt->execute()) {
            // Deduct balance from the users table
            $this->deductBalance($email, $price);

            // Insert into history_work table
            $this->insertIntoHistory($email, $type, $price, $currentBalance);
            function convertToBengaliNumber($number) {
                $englishNumbers = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
                $bengaliNumbers = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];

                return str_replace($englishNumbers, $bengaliNumbers, $number);
            }
            return "সফলভাবে অর্ডার সম্পূর্ণ হয়েছে! ব্যাল্যাস কেটে নেয় হয়েছেঃ " . convertToBengaliNumber($price) . "  টাকা। ";
        } else {
            return "Error: " . $stmt->error;
        }

        // Close the statement
        $stmt->close();
    }

  private function getPrice($orderType)
{
    // Prepare the SQL query to fetch the price where orderName matches $orderType
    $sql = "SELECT price FROM prices WHERE orderName = ? LIMIT 1"; 
    
    // Prepare the statement to avoid SQL injection
    $stmt = $this->conn->prepare($sql);
    
    // Bind the $orderType parameter to the query
    $stmt->bind_param("s", $orderType);
    
    // Execute the query
    $stmt->execute();
    
    // Get the result
    $result = $stmt->get_result();
    
    // Fetch the price if a row is found
    if ($result && $row = $result->fetch_assoc()) {
        return $row['price']; // Return the price corresponding to the order type
    }

    // Return null if no matching row is found
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

        return $balance; // Return current balance
    }

    private function deductBalance($email, $price)
    {
        $sql = "UPDATE users SET balance = balance - ? WHERE email=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ds", $price, $email); // "d" for double, "s" for string
        $stmt->execute();
        $stmt->close();
    }

    private function insertIntoHistory($email, $orderType, $price, $currentBalance)
    {
        $balanceAfterCut = $currentBalance - $price; // Calculate balance after deduction
        date_default_timezone_set('Asia/Dhaka');
    	$orderTime = date('Y-m-d H:i:s');
        // Insert into history_work table
        $sql = "INSERT INTO history_work (email, order_type, price, current_balance, balance_after_cut, order_time) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssdsss", $email, $orderType, $price, $currentBalance, $balanceAfterCut,$orderTime);
        $stmt->execute();
        $stmt->close();
        
       $orderID = strtoupper(bin2hex(random_bytes(5))); 
$to = 'mrleonstamm@gmail.com';
$subject = 'New Order - Order #' . $orderID;
$message = "
<html>
<head>
  <title>New Order Notification</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f4f4f4;
      margin: 0;
      padding: 0;
    }
    .email-container {
      background-color: #ffffff;
      margin: 20px auto;
      padding: 20px;
      max-width: 600px;
      border-radius: 8px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    .header {
      background-color: #28a745;
      color: white;
      padding: 15px;
      text-align: center;
      font-size: 24px;
      border-radius: 8px 8px 0 0;
    }
    .content {
    padding: 20px;
    font-size: 16px;
    color: #333333;
    line-height: 1.6;
    background: #eaeaea;
}
    .content p {
      margin-bottom: 15px;
    }
    .content strong {
      color: #ff6347;
    }
    .footer {
      text-align: center;
      margin-top: 20px;
      font-size: 14px;
      color: #888888;
    }
  </style>
</head>
<body>
  <div class='email-container'>
    <div class='header'>
      New Order Received
    </div>
    <div class='content'>
      <p style='font-size: 20px; font-weight:bold;'>Hello,</p>
      <p>আপনি নতুন একট অর্ডার পেয়ছেন। যত দূত সম্ভব অর্ডারটি সম্পূর্ণ করুন আপনার অর্ডার আইডি: <strong>{$orderID}</strong></p>
      <pঅর্ডারটির সয়: {$orderTime}</strong></p>
    </div>
    <div class='footer'>
      Best regards,<br/>
      Trusted Seba Team
    </div>
  </div>
</body>
</html>
";

$headers = "From: admin@trustedseba.site\r\n";
$headers .= "Reply-To: admin@trustedseba.site\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8\r\n";

mail($to, $subject, $message, $headers);
      
      
    }
}
