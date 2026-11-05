<?php
// Database connection
include_once("../includes/configuration.php");
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the request is for inserting data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['name'])) {
    $name = $_POST['name'];
    $orderName = $_POST['orderName'];
    $price = $_POST['price'];
    $typeOfOrder = $_POST['typeOforder'];
    $workStatus = $_POST['work_status'];
    $field_height = $_POST['field_height'];
    $field_placeholder = $_POST['field_placeholder'];

    // Prepare the SQL statement
    $sql = "INSERT INTO prices (name, orderName, price, typeOforder, work_status, field_placeholder, field_height) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    // Bind parameters
    $stmt->bind_param("ssdssss", $name, $orderName, $price, $typeOfOrder, $workStatus, $field_placeholder, $field_height);

    // Execute the statement and return a success or error message
    if ($stmt->execute()) {
        echo "Data inserted successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close the statement
    $stmt->close();
}

// Close the connection
$conn->close();
?>