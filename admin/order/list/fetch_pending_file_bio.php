<?php
include_once("../../includes/configuration.php");

// Prepare the SQL queries to count pending orders
$sql_bio_count = "SELECT COUNT(*) AS count FROM order_list WHERE user_type = 'bio' AND status = 'pending'";
$sql_file_count = "SELECT COUNT(*) AS count FROM order_list WHERE user_type = 'file' AND status = 'pending'";

$result_bio_count = $conn->query($sql_bio_count);
$result_file_count = $conn->query($sql_file_count);

$counts = [
    'bio' => 0,
    'file' => 0
];

// Fetch bio count
if ($result_bio_count->num_rows > 0) {
    $row = $result_bio_count->fetch_assoc();
    $counts['bio'] = (int)$row['count'];
}

// Fetch file count
if ($result_file_count->num_rows > 0) {
    $row = $result_file_count->fetch_assoc();
    $counts['file'] = (int)$row['count'];
}

// Close connection
$conn->close();

// Return results as JSON
header('Content-Type: application/json');
echo json_encode($counts);
?>
