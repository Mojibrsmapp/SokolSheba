<?php
session_start();
$email = base64_decode($_SESSION["user_id"]);
include_once("../../includes/configuration.php");

// Fetch data from the order_list table
$sql = "SELECT id, email, order_type, user_type, nid, bio_totto, received, order_time, complete_time FROM order_list WHERE  user_type='bio' AND status='cancel'";
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
