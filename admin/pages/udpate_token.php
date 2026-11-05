<?php
if (isset($_POST["token_submit"])) {
    include_once("../includes/configuration.php");
    if($_POST["id"] == 1){
        header("location: ../user_list.php?message=you can not update this beacuse this is admin");
        exit;
    }
    // Get form values
    $id = $_POST["id"];
    $old_token = $_POST["old_token"];
    $token = bin2hex(random_bytes(12));
    // Ensure values are sanitized
    $id = mysqli_real_escape_string($conn, $id);
    $token = mysqli_real_escape_string($conn, $token);
    $old_token = mysqli_real_escape_string($conn, $old_token);

    // Update query
    $update_query = "UPDATE users SET token = '$token' WHERE id = '$id' AND token = '$old_token'";

    // Execute the query
    if (mysqli_query($conn, $update_query)) {
        header("location: ../user_list.php?msg=success&text= New Token Generated successfully");

        // Optionally, you can redirect or trigger a SweetAlert response
    } else {
        header("location: ../user_list.php?msg=error&text=Some have wrong here");
        // Handle error with SweetAlert or redirection if necessary
    }

} else {
    header("Location: ../dashboard.php");
    exit;
}
?>