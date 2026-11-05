<?php
include '../../includes/configuration.php';
session_start();
$user = base64_decode($_SESSION["user_id"]);

// Get the start date from the request; default to today's date if not set
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d');  // Default to today's date

// List of specific order_types to show
$specificOrderTypes = ['কার্ড মেইক', 'সার্ভার কপি', 'অটো টিন', 'সার্ভার কপি ১'];

// Prepare the SQL query for the specific date filter (no date range) for the `history_work` table
$sql_history_work = "
    SELECT order_type, COUNT(*) AS totalOrders, SUM(price) AS totalPrice 
    FROM history_work 
    WHERE order_time >= ? 
      AND order_time < DATE_ADD(?, INTERVAL 1 DAY)
      AND order_type IN (?, ?, ?, ?)  -- Filter by specific order types
    GROUP BY order_type
";

$stmt_history_work = $conn->prepare($sql_history_work);
$stmt_history_work->bind_param(
    "ssssss",
    $start_date,
    $start_date,
    $specificOrderTypes[0],
    $specificOrderTypes[1],
    $specificOrderTypes[2],
    $specificOrderTypes[3]
);
$stmt_history_work->execute();
$result_history_work = $stmt_history_work->get_result();

// Fetch the order type data (counts and total price) from `history_work` table
$orderSummary = [];
while ($row = $result_history_work->fetch_assoc()) {
    $orderSummary[] = [
        'order_type' => $row['order_type'],
        'totalOrders' => (int)$row['totalOrders'],
        'totalPrice' => (float)$row['totalPrice']
    ];
}

// Query for the overall total from `history_work` table (no grouping by order_type)
$sql_total_history_work = "
    SELECT COUNT(*) AS totalOrders, SUM(price) AS totalPrice 
    FROM history_work 
    WHERE order_time >= ? 
      AND order_time < DATE_ADD(?, INTERVAL 1 DAY) 
      AND order_type IN (?, ?, ?, ?)
";

$stmt_total_history_work = $conn->prepare($sql_total_history_work);
$stmt_total_history_work->bind_param(
    "ssssss",
    $start_date,
    $start_date,
    $specificOrderTypes[0],
    $specificOrderTypes[1],
    $specificOrderTypes[2],
    $specificOrderTypes[3]
);
$stmt_total_history_work->execute();
$result_total_history_work = $stmt_total_history_work->get_result();
$summaryTotal = $result_total_history_work->fetch_assoc();

// Default values if no results
$totalOrdersHistoryWork = $summaryTotal['totalOrders'] ?? 0;
$totalPriceHistoryWork = $summaryTotal['totalPrice'] ?? 0;

// Prepare the SQL query for the `order_list` table to count orders based on status
$sql_order_list = "
    SELECT 
        order_type, 
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending_count,
        SUM(CASE WHEN status = 'pending' THEN price ELSE 0 END) AS pending_price,
        SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) AS success_count,
        SUM(CASE WHEN status = 'success' THEN price ELSE 0 END) AS success_price,
        SUM(CASE WHEN status NOT IN ('pending', 'success') THEN 1 ELSE 0 END) AS other_count,
        SUM(CASE WHEN status NOT IN ('pending', 'success') THEN price ELSE 0 END) AS other_price
    FROM order_list 
    WHERE order_time >= ? AND order_time < DATE_ADD(?, INTERVAL 1 DAY)
    GROUP BY order_type
";

$stmt_order_list = $conn->prepare($sql_order_list);
$stmt_order_list->bind_param("ss", $start_date, $start_date);
$stmt_order_list->execute();
$result_order_list = $stmt_order_list->get_result();

// Fetch counts and prices from `order_list`
$orderListCounts = [];
while ($row = $result_order_list->fetch_assoc()) {
    $orderListCounts[$row['order_type']] = [
        'pending_count' => (int)$row['pending_count'],
        'pending_price' => (float)$row['pending_price'],
        'success_count' => (int)$row['success_count'],
        'success_price' => (float)$row['success_price'],
        'other_count' => (int)$row['other_count'],
        'other_price' => (float)$row['other_price']
    ];
}

// Close the database connection
$conn->close();

// Return the combined results as JSON
header('Content-Type: application/json');
echo json_encode([
    'historyWorkTotal' => [
        'totalOrders' => $totalOrdersHistoryWork,
        'totalPrice' => $totalPriceHistoryWork
    ],
    'historyWorkSummary' => $orderSummary,
    'orderListCounts' => $orderListCounts
]);
?>
