<?php
session_start();
if(isset($_SESSION["user_id"])){



// fetch_operators.php
include('../../includes/configuration.php'); 

// Fetch operators from the database
$query = "SELECT * FROM prices WHERE  work_status = 1 AND orderName ='sign_copy'";
$result = $conn->query($query);

$operators = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $operators[] = $row;
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($operators);
  
  
}else{
header("location: ../../index.php");
}  
?>