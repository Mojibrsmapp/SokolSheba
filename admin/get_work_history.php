<?php
include 'includes/configuration.php'; 
session_start();
// Suspicious code removed

// Get the number of days to filter by, default to 1 (today) if not specified
$days = isset($_GET['days']) && $_GET['days'] !== 'all' ? (int)$_GET['days'] : 'all';

// Prepare SQL query based on the selected days filter
if ($days === 'all') {
    // Query to get all records
    $sql = "SELECT * FROM history_work ORDER BY id DESC";
} else {
    // Query to get records within the last specified days
    $sql = "SELECT * FROM history_work WHERE order_time >= DATE_SUB(CURDATE(), INTERVAL ? DAY) ORDER BY id DESC";
}

// Use prepared statements for security
$stmt = $conn->prepare($sql);
if ($days !== 'all') {
    $stmt->bind_param("i", $days);  // Bind the days parameter if it's a number
}
$stmt->execute();
$result = $stmt->get_result();

$data = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $data[] = [
            'id' => $row["id"],
            'email' => $row["email"],
            'orderType' => $row["order_type"],
            'price' => $row["price"],
            'beforeBalance' => $row["current_balance"],
            'afterBalance' => $row["balance_after_cut"],
            'orderTime' => $row["order_time"]
        ];
    }
}

// Return the data as JSON
header('Content-Type: application/json');
echo json_encode($data);
?>