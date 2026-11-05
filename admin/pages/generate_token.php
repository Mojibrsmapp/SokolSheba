<?php
session_start();
if (isset($_SESSION["user_id"])){
// Include the database connection
include('../includes/configuration.php'); 

// Check if the email parameter is set
if (isset($_GET['token'])) {
    $email = "";

    // Generate a new token (you can change this to any method of token generation)
    $newToken = bin2hex(random_bytes(5)); // Generates a 32-character token

    // Update the user's token in the database
    $sql = "UPDATE admin_user SET reg_token = ? WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $newToken, $email); // "ss" for two strings

    if ($stmt->execute()) {
        // Success - redirect to the edit page with the email parameter
        header("Location: ../createLink.php?msg=success&text=New link generate successfully");
        exit;
    } else {
        // Handle error
        header("Location: ../index.php");
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
} else {
    // Handle the case where the email is not provided
    echo "Email not provided.";
}
} else {
    header("Location: ../index.php");
}
?>