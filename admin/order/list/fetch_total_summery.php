<?php
include '../../includes/configuration.php';
session_start();
$user = base64_decode($_SESSION["user_id"]);

// Get the start date from the request; default to today's date if not set
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d');  // Default to today's date

// List of specific order_types to show
$specificOrderTypes = ['কার্ড মেইক', 'সার্ভার কপি', 'অটো টিন'];

// Build the SQL query for the specific date filter (no date range)
$sql = "SELECT order_type, COUNT(*) AS totalOrders, SUM(price) AS totalPrice 
        FROM history_work 
        WHERE order_time >= ? 
          AND order_time < DATE_ADD(?, INTERVAL 1 DAY)
          AND order_type IN (?, ?, ?)  -- Filter by specific order types
        GROUP BY order_type";

$stmt = $conn->prepare($sql);

// Bind parameters for the selected date and the specific order types
$stmt->bind_param("sssss", $start_date, $start_date, $specificOrderTypes[0], $specificOrderTypes[1], $specificOrderTypes[2]);

$stmt->execute();
$result = $stmt->get_result();

// Fetch the order type data (counts and total price)
$orderSummary = [];
while ($row = $result->fetch_assoc()) {
    $orderSummary[] = [
        'order_type' => $row['order_type'],
        'totalOrders' => (int)$row['totalOrders'],
        'totalPrice' => (float)$row['totalPrice']
    ];
}

// Query for the overall total (no grouping by order_type)
$sqlTotal = "SELECT COUNT(*) AS totalOrders, SUM(price) AS totalPrice 
             FROM history_work 
             WHERE order_time >= ? 
               AND order_time < DATE_ADD(?, INTERVAL 1 DAY) 
               AND order_type IN (?, ?, ?)";

$stmtTotal = $conn->prepare($sqlTotal);
$stmtTotal->bind_param("sssss", $start_date, $start_date, $specificOrderTypes[0], $specificOrderTypes[1], $specificOrderTypes[2]);
$stmtTotal->execute();
$resultTotal = $stmtTotal->get_result();
$summaryTotal = $resultTotal->fetch_assoc();

// Default values if no results
$totalOrders = $summaryTotal['totalOrders'] ?? 0;
$totalPrice = $summaryTotal['totalPrice'] ?? 0;

// Return both overall total and per order_type totals
header('Content-Type: application/json');
echo json_encode([
    'totalOrders' => $totalOrders,
    'totalPrice' => $totalPrice,
    'orderSummary' => $orderSummary
]);
?>
