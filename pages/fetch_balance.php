<?php
session_start();
include_once(__DIR__ . '/../includes/configuration.php');
// Return JSON response
header('Content-Type: application/json');
// Decode the email from session
$email = base64_decode($_SESSION["user_id"]);

// Fetch the balance for the user
$sql = "SELECT * FROM users WHERE email='$email'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    // Return the balance as JSON
    echo json_encode(array('balance' => $row['balance'],'name'=>$row['name'],'email'=>$row['email']));
} else {
    echo json_encode(array('balance' => 0)); // Fallback if user not found
}

$conn->close();
?>
