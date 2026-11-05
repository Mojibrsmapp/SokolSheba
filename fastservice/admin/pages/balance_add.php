<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if (isset($_POST["submit"])) {
    include_once("../includes/configuration.php");
    
    // Sanitize the inputs
    $id = mysqli_real_escape_string($conn, $_POST["id"]);
    $balance = mysqli_real_escape_string($conn, $_POST["balance"]);

    // Ensure $balance is a numeric value
    $balance = floatval($balance); // or use intval($balance) if it's an integer

    // Step 1: Retrieve the current balance
    $sql = "SELECT email, balance FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id); // "i" indicates an integer parameter
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $currentBalance = $row['balance'];
        $email = $row['email'];

        // Step 2: Add the new balance to the current balance
        $newBalance = $currentBalance + $balance;

        // Step 3: Update the balance in the database
        $update_sql = "UPDATE users SET balance = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("di", $newBalance, $id); // "di" indicates double and integer params

        if ($update_stmt->execute()) {
            // Step 4: Log the balance change in add_remove_balance table
            $action = ($balance >= 0) ? "add" : "remove";
            $log_sql = "INSERT INTO add_remove_balance (user, amount, action, update_at) VALUES (?, ?, ?, NOW())";
            $log_stmt = $conn->prepare($log_sql);
            $log_stmt->bind_param("sds", $email, $balance, $action); // "sds" indicates string, double, string params
            $log_stmt->execute();
            $log_stmt->close();

            header("location: ../user_list.php?msg=success&text=" . $balance . " tk Balance added successfully for " . $email);
            exit();
        } else {
            header("location: ../user_list.php?msg=error&text=Error found when adding balance");
            exit();
        }

        $update_stmt->close();
    } else {
        header("location: ../index.php");
        exit();
    }
}
?>