<?php
session_start(); // Start session
if (!isset($_SESSION['user_id'])) {
    header("location: index.php");
    exit();
}
include_once("includes/configuration.php");

if (isset($_POST['tin'])) {
    header('Content-Type: application/json'); // JSON response
    $tin = $_POST['tin'];
    $user_email = base64_decode($_SESSION['user_id']);

    // Fetch user info and balance
    $sql = "SELECT id, balance FROM users WHERE email = ?";
    $stmtUser = $conn->prepare($sql);
    $stmtUser->bind_param("s", $user_email);
    $stmtUser->execute();
    $resultUser = $stmtUser->get_result();

    if ($resultUser->num_rows > 0) {
        $row = $resultUser->fetch_assoc();
        $userId = $row['id'];
        $balance = $row['balance'];
    } else {
        echo json_encode(['status' => 'error', 'message' => 'User not found.']);
        exit();
    }
    $stmtUser->close();

    // Get deduction amount and convert to float
    $deductionAmount = (float) file_get_contents('api_key/auto_tin.txt');

    if ($balance < $deductionAmount) {
        echo json_encode(['status' => 'error', 'message' => 'আপনার পর্যাপ্ত ব্যাল্যান্স নাই']);
        exit();
    }

    // API call
    $api_url = "https:/key=AD-bHjAcjqNTPO8&tin=$tin";
    $response = @file_get_contents($api_url);
    $data = json_decode($response, true);

    if (isset($data['data']['tin'])) {
        $data = $data['data']; // move to actual data section

        // Save QR image
        $qrUrl = $data['qr_code'];
        $randomName = uniqid('qr_code_', true) . '.png';
        $savePath = 'qr_photo/' . $randomName;
        $imageData = @file_get_contents($qrUrl);
        if ($imageData !== false) {
            file_put_contents($savePath, $imageData);
        } else {
            $savePath = ''; // fallback
        }

        // Placeholder values for unused fields
        $law = $law1 = $law2 = $law3 = "N/A";

        // Extract nested tax_office data
        $taxOffice = $data['tax_office'];
        $circle = $taxOffice['circle'] ?? 'N/A';
        $zone = $taxOffice['zone'] ?? 'N/A';
        $phone = $taxOffice['phone'] ?? '';
        $address = $taxOffice['address'] ?? '';
        $full_address = $taxOffice['full_address'] ?? '';
        $zone1 = $taxOffice['zone1'] ?? '';

        // Insert into tin_data
        $stmt = $conn->prepare("INSERT INTO tin_data (tin, name, father_name, mother_name, current_address, permanent_address, previous_tin, status, date, circle, zone, law, law1, law2, law3, address, phone, full_address, zone1, qr_code, user_email)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->bind_param(
            "sssssssssssssssssssss",
            $data['tin'],
            $data['name'],
            $data['father_name'],
            $data['mother_name'],
            $data['current_address'],
            $data['permanent_address'],
            $data['previous_tin'],
            $data['status'],
            $data['date'],
            $circle,
            $zone,
            $law,
            $law1,
            $law2,
            $law3,
            $address,
            $phone,
            $full_address,
            $zone1,
            $savePath,
            $user_email
        );

        if ($stmt->execute()) {
            // Deduct balance
            $newBalance = $balance - $deductionAmount;
            $updateBalanceSql = "UPDATE users SET balance = ? WHERE id = ?";
            $stmtUpdate = $conn->prepare($updateBalanceSql);
            $stmtUpdate->bind_param("di", $newBalance, $userId);
            $stmtUpdate->execute();
            $stmtUpdate->close();

            // Insert into history
            $order_type = "অটো টিন";
            $about_order = "auto_tin";
            $order_time = date("Y-m-d H:i:s");

            $stmtHistory = $conn->prepare("INSERT INTO history_work (email, order_type, price, current_balance, balance_after_cut, about_order, order_time)
                                            VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmtHistory->bind_param("sssddss", $user_email, $order_type, $deductionAmount, $balance, $newBalance, $about_order, $order_time);

            if ($stmtHistory->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Data inserted successfully, balance updated, and history recorded.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Error inserting history record: ' . $stmtHistory->error]);
            }

            $stmtHistory->close();
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error inserting data: ' . $stmt->error]);
        }

        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to retrieve data from external API.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'TIN is required and user email must be set in the session.']);
}

$conn->close();
?>