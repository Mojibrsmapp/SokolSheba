<?php 
if(isset($_GET['token']) && isset($_GET['status'])){
    if($_GET['token'] == "db3c8c709cc949174beea12e"){
        header("location: ../user_list.php?msg=error&text=you can not Deactive this account beacuse this is your account");
        exit;
    }
    $token = $_GET['token'];
    $status = $_GET['status'];
    session_start();
    $verifToken = $_SESSION['user_token'];
            include_once("../includes/configuration.php");

        // Prepare and sanitize inputs before using in SQL
        $token = mysqli_real_escape_string($conn, $token);
        $status = mysqli_real_escape_string($conn, $status);
        
        // Toggle status: if status is 1, update to 0; if 0, update to 1
        $newStatus = ($status == '1') ? '0' : '1';
        
        // Build and execute the SQL update query
        $sql = "UPDATE users SET status = ? WHERE token = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $newStatus, $token); // "ss" indicates two strings

        if($stmt->execute()){
           
            $statusText = $status === '1' ? 'Active' : 'Deactive';
   			$newStatusText = $newStatus === '1' ? 'Active' : 'Deactive';
    		header("Location: ../user_list.php?msg=success&text=" . urlencode("Status Change Successfully $statusText to $newStatusText"));
        } else {
            header("location: ../user_list.php?msg=error&text=Error updating");
        }

        $stmt->close();
        $conn->close();

    

} else {
    header("location: ../dashboard.php");
    exit;
}
?>