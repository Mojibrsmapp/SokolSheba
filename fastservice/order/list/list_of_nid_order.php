<?php
session_start();
$email = base64_decode($_SESSION["user_id"]);
header('Content-Type: application/json');
include_once("../../includes/configuration.php");

// Fetch data from the order_list table
$sql = "SELECT order_type, nid, bio_totto, price, status, received, opinion, order_time, file_one 
        FROM order_list 
        WHERE email = '$email' 
          AND user_type = 'file' 
          AND order_type ='NID PDF' 
        ORDER BY id DESC";
  // Assuming you're showing 'type' and 'nid' columns
$result = $conn->query($sql);

$orders = array();

if ($result->num_rows > 0) {
    // Fetch all rows into an array
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
}

// Return data as JSON
echo json_encode($orders);

// Close the connection
$conn->close();
?>