<?php
header('Content-Type: application/json');
include_once("../../includes/configuration.php");

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['id']) && isset($data['status'])) {
    $orderId = $data['id'];
    $status = "cancel";

    // Step 1: Retrieve the order details (email, user_type, order_type, status, price) from order_list
    $sql = "SELECT email, user_type, order_type, status, price FROM order_list WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param('i', $orderId);
        $stmt->execute();
        $stmt->bind_result($email, $userType, $orderType, $currentStatus, $price);
        if ($stmt->fetch()) {
            $stmt->close();

            // Check if the current status is "pending" before processing the cancellation
            if ($currentStatus === "pending") {
                
                // Step 2: Retrieve the user's current balance
                $balanceQuery = "SELECT balance FROM users WHERE email = ?";
                if ($balanceStmt = $conn->prepare($balanceQuery)) {
                    $balanceStmt->bind_param('s', $email);
                    $balanceStmt->execute();
                    $balanceStmt->bind_result($currentBalance);
                    if ($balanceStmt->fetch()) {
                        $balanceStmt->close();

                        // Calculate balance after adding the refunded amount
                        $newBalance = $currentBalance + $price;

                        // Step 3: Update the user's balance
                        $updateBalanceQuery = "UPDATE users SET balance = ? WHERE email = ?";
                        if ($updateBalanceStmt = $conn->prepare($updateBalanceQuery)) {
                            $updateBalanceStmt->bind_param('ds', $newBalance, $email);
                            $updateBalanceStmt->execute();
                            $updateBalanceStmt->close();
                        }

                        // Step 4: Update the order status to 'cancel'
                       $updateOrderStatus = "UPDATE order_list SET status = ?, complete_time = NOW() WHERE id = ?";
                        if ($updateStmt = $conn->prepare($updateOrderStatus)) {
                            $updateStmt->bind_param('si', $status, $orderId);
                            
                            if ($updateStmt->execute()) {
                                // Step 5: Insert record into history_work
                                $orderTime = date("Y-m-d H:i:s"); // Current time
                                $orderTypeInsert = "ক্যানসেল হয়েছেঃ ".$orderType." ".$userType;
                                $historyQuery = "INSERT INTO history_work (email, order_type, price, current_balance, balance_after_cut, order_time) VALUES (?, ?, ?, ?, ?, ?)";
                                if ($historyStmt = $conn->prepare($historyQuery)) {
                                    $historyStmt->bind_param('ssdsss', $email, $orderTypeInsert, $price, $currentBalance, $newBalance, $orderTime);
                                    $historyStmt->execute();
                                    $historyStmt->close();
                                }

                                echo json_encode(['status' => 'success', 'message' => 'Order canceled, price refunded, and history updated successfully.']);
                            } else {
                                echo json_encode(['status' => 'error', 'message' => 'Failed to cancel order.']);
                            }

                            $updateStmt->close();
                        }
                    }
                }
            } else {
                // If the status is not "pending", prevent further cancellation attempts
                echo json_encode(['status' => 'error', 'message' => 'Order is already canceled or not eligible for cancellation.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Order not found.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error.']);
    }

    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input data.']);
}
?>
