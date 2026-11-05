<?php
session_start();
  if (!isset($_SESSION['user_id'])) {
    // Redirect to logout page or perform logout actions
    header("Location: ../");
    exit();
}
$user = base64_decode($_SESSION['user_id']);
// Database connection
include_once("includes/configuration.php"); 

// Fetch data from the database and order by id or created_at (replace 'id' with your actual timestamp or identifier)
$sql = "SELECT * FROM server_copy WHERE user_mail='$user' ORDER BY id DESC"; // Assuming 'id' is the primary key and higher IDs are newer
$result = $conn->query($sql);

$data = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

$conn->close();

// Return the data as JSON
header('Content-Type: application/json');
echo json_encode($data);
?>
