<?php
include_once("../../includes/configuration.php");

// Get start and end date from the request
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

// Ensure both dates are provided
if ($start_date && $end_date) {
    // Prepare the SQL query to count orders grouped by order_type and status, and calculate total price for each
    $sql_order_count = "
        SELECT 
            order_type, 
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending_count,
            SUM(CASE WHEN status = 'pending' THEN price ELSE 0 END) AS pending_price,
            SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) AS success_count,
            SUM(CASE WHEN status = 'success' THEN price ELSE 0 END) AS success_price,
            SUM(CASE WHEN status NOT IN ('pending', 'success') THEN 1 ELSE 0 END) AS other_count,
            SUM(CASE WHEN status NOT IN ('pending', 'success') THEN price ELSE 0 END) AS other_price
        FROM order_list 
        WHERE order_time BETWEEN '$start_date' AND '$end_date'
        GROUP BY order_type
    ";
} else {
    // Default query to fetch all data
    $sql_order_count = "
        SELECT 
            order_type, 
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending_count,
            SUM(CASE WHEN status = 'pending' THEN price ELSE 0 END) AS pending_price,
            SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) AS success_count,
            SUM(CASE WHEN status = 'success' THEN price ELSE 0 END) AS success_price,
            SUM(CASE WHEN status NOT IN ('pending', 'success') THEN 1 ELSE 0 END) AS other_count,
            SUM(CASE WHEN status NOT IN ('pending', 'success') THEN price ELSE 0 END) AS other_price
        FROM order_list 
        GROUP BY order_type
    ";
}

$result_order_count = $conn->query($sql_order_count);
$counts = [];

// Fetch count and total price for each order_type
if ($result_order_count->num_rows > 0) {
    while ($row = $result_order_count->fetch_assoc()) {
        $counts[$row['order_type']] = [
            'pending_count' => (int)$row['pending_count'],
            'pending_price' => (float)$row['pending_price'],
            'success_count' => (int)$row['success_count'],
            'success_price' => (float)$row['success_price'],
            'other_count' => (int)$row['other_count'],
            'other_price' => (float)$row['other_price']
        ];
    }
} else {
    // If no data found, return empty data
    $counts = [];
}

// Close connection
$conn->close();

// Return results as JSON
header('Content-Type: application/json');
echo json_encode($counts);
?>
