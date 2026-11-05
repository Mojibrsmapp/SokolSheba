<?php
session_start();
$email = base64_decode($_SESSION["user_id"]);
include_once("../../includes/configuration.php");

// Get the order_type from GET parameters, if provided
$order_type_filter = isset($_GET['order_type']) ? $_GET['order_type'] : '';

// Fetch distinct order_type values
$orderTypesSql = "SELECT DISTINCT order_type FROM order_list WHERE user_type='bio' AND status='pending'";
$orderTypesResult = $conn->query($orderTypesSql);
$orderTypes = array();

if ($orderTypesResult->num_rows > 0) {
    while ($row = $orderTypesResult->fetch_assoc()) {
        $orderTypes[] = $row['order_type'];
    }
}

// Fetch orders based on the selected order_type filter
$sql = "SELECT id, email, order_type, user_type, nid, bio_totto, status, received, opinion, order_time FROM order_list WHERE user_type='bio' AND status='pending'";

if (!empty($order_type_filter) && $order_type_filter !== 'all') {
    $sql .= " AND order_type = '$order_type_filter'";  // Add the filter condition
}

$result = $conn->query($sql);

$orders = array();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
}
header('Content-Type: application/json');
echo json_encode(['orderTypes' => $orderTypes, 'orders' => $orders]);

// Close the connection
$conn->close();
?>
