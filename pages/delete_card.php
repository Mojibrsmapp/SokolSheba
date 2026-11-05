<?php
session_start();
if(!isset($_SESSION['user_id'])){
header("location: ../index.php");
exit;
}
include('../includes/configuration.php');
if (isset($_GET['id']) && isset($_SESSION['user_id'])) {
    // Decode the base64-encoded ID
// Suspicious code removed
// Suspicious code removed
    
    // Sanitize the ID to prevent SQL injection
    $id = mysqli_real_escape_string($conn, $id);
    $email = mysqli_real_escape_string($conn, $email);
  
    // Write the SQL query to delete the record with the provided ID
    $sql = "DELETE FROM card_log WHERE id = '$id' AND email = '$email'";
    
    // Execute the query
    if (mysqli_query($conn, $sql)) {
        // Success message
        header("location: ../list.php?msg=success&text=ডাটা ডিলেট হয়েছে সফলভাবে। ");
    } else {
        // Error message
        header("location: ../list.php?msg=error&text=এই ফাইলটি আপনি ডিলিট করতে পারবেন না");
    }
} else {
    // If 'id' is not provided in the URL, redirect to the list page
   header("location: ../index.php");
}

// Close the database connection
mysqli_close($conn);
?>