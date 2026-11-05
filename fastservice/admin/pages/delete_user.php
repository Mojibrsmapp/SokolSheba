<?php
// Start the session to use session variables if needed
session_start();

// Include database configuration file
include_once("../includes/configuration.php");

// Check if the token is set
if (isset($_GET['token'])) {
    
    // Sanitize the token
    $token = mysqli_real_escape_string($conn, $_GET['token']);
    if($token == "db3c8c709cc949174beea12e"){
      header("Location: ../user_list.php?msg=error&text=This is your account so you can't delete this account."); 
       exit();
    }

    // Step 1: Retrieve user ID based on the token
    $sql = "SELECT id FROM users WHERE token = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token); // "s" indicates a string parameter
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Step 2: Fetch the user ID
        $row = $result->fetch_assoc();
        $userId = $row['id'];

        // Step 3: Delete the user from the database
        $delete_sql = "DELETE FROM users WHERE id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $userId); // "i" indicates an integer parameter

        if ($delete_stmt->execute()) {
            // Redirect to the user list with success message
            header("Location: ../user_list.php?msg=success&text=User deleted successfully.");
        } else {
            // Redirect to the user list with error message
            header("Location: ../user_list.php?msg=error&text=Error deleting user.");
        }

        $delete_stmt->close();
    } else {
        // Redirect if the user with that token doesn't exist
        header("Location: ../user_list.php?msg=error&text=User not found.");
    }

    $stmt->close();
} else {
    // Redirect if token is not set
    header("Location: ../user_list.php?msg=error&text=Invalid request.");
}

// Close the database connection
$conn->close();
?>