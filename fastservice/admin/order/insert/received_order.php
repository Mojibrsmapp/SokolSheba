<?php
// update-order-status.php
header('Content-Type: application/json');
include '../../includes/configuration.php'; // Include your database connection file

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the raw POST data
    $data = json_decode(file_get_contents('php://input'), true);

    // Validate the input
    if (isset($data['id']) && isset($data['received'])) {
        $orderId = intval($data['id']);
        $receivedStatus = intval($data['received']);

        // Prepare the SQL statement
        $stmt = $conn->prepare("UPDATE order_list SET received = ? WHERE id = ?");
        $stmt->bind_param("ii", $receivedStatus, $orderId);

        // Execute the query and check for success
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Order status has been updated.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update order status.']);
        }

        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid input.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}

$conn->close();
?>
