<?php
include 'includes/configuration.php';
session_start();
// Suspicious code removed

// Get the number of days from the request, defaulting to 1 (today)
$days = isset($_GET['days']) && $_GET['days'] !== 'all' ? (int)$_GET['days'] : 'all';

if ($days === 'all') {
    // Query to get all records
    $sql = "SELECT COUNT(*) AS totalOrders, SUM(price) AS totalPrice FROM history_work";
} else {
    // Query to get records within the last specified days
    $sql = "SELECT COUNT(*) AS totalOrders, SUM(price) AS totalPrice FROM history_work WHERE order_time >= DATE_SUB(CURDATE(), INTERVAL ? DAY)";
}

$stmt = $conn->prepare($sql);
if ($days !== 'all') {
    $stmt->bind_param("i", $days);
}
$stmt->execute();
$result = $stmt->get_result();
$summary = $result->fetch_assoc();

// Default values if no results
$totalOrders = $summary['totalOrders'] ?? 0;
$totalPrice = $summary['totalPrice'] ?? 0;

header('Content-Type: application/json');
echo json_encode(['totalOrders' => $totalOrders, 'totalPrice' => $totalPrice]);
?>