<?php
session_start();
include("../includes/configuration.php"); 

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = $_POST['currentPassword'];
    $newPassword = $_POST['newPassword'];
    $confirmPassword = $_POST['confirmPassword'];
    $hashedPassword = md5($currentPassword);
    $newHashPass = md5($newPassword);
// Suspicious code removed
   echo $userId;
    // Simple validation: Ensure new password and confirm password match
    if ($newPassword !== $confirmPassword) {
       header("location: ../profile.php?msg=error&text=New Password and Confirm password don't match");
        exit;
    }
  
   if($newPassword == $currentPassword){
       header("location: ../profile.php?msg=error&text=New Password And Old Password Same");
        exit;
    }
       $sql = "SELECT password FROM users WHERE email='$email'";
      $result = $conn->query($sql);

      if ($result->num_rows > 0) {
        // output data of each row
        while($row = $result->fetch_assoc()) {
          $dbPass =  $row["password"];
          
          if($dbPass   ==  $hashedPassword){
            $updateQuery = "UPDATE users SET password='$newHashPass' WHERE email='$email'";
                
                if ($conn->query($updateQuery) === TRUE) {
                    header("location: ../profile.php?msg=success&text=Password updated successfully");
                } else {
                    header("location: ../profile.php?msg=error&text=Error updating password");
                }
           
          }else{
           header("location: ../profile.php?msg=error&text=Current Password doesn't match");
          }
          
          
        }
      } else {
        header("location: ../profile.php?msg=error&text=Something have problem here");
      }


}else{
header("location: index.php");
}
?>