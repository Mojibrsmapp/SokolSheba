<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
$id = $_SESSION["user_token"];
if (strlen($id) == 0) {
    header('location:index.php');
    exit;
}

include('includes/db.php'); // Ensure this file contains a valid connection in $link
include 'execute.php'; // Assuming this fetches the payment data

if (isset($_GET['status'])) {
    if ($_GET['status'] == 'success') {
        $result_data = execute($_GET['paymentID']);
        $response = json_decode($result_data, true);
        
        if (isset($response['statusCode']) && $response['statusCode'] != '0000') {
            header("Location: recharge.php?statusMessage=" . $response['statusMessage']);
            exit;
        } else {
            // Database insert operation to store $response data
            $deposit = $response['amount'];
            
            // Update user balance
            $sql2 = "UPDATE users SET balance = balance + '$deposit' WHERE token = '$id'";
            
            // Check if update is successful
            if (mysqli_query($link, $sql2)) {
                // Prepare insert query for transaction
                $insertQuery = "INSERT INTO transactions 
                    (token, paymentID, trxID, transactionStatus, paymentExecuteTime, merchantInvoiceNumber, customerMsisdn, amount) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                    
                if ($stmt = mysqli_prepare($link, $insertQuery)) {
                    // Bind variables to the prepared statement as parameters
                    mysqli_stmt_bind_param($stmt, "sssssssd", 
                        $_SESSION["user_token"], 
                        $response['paymentID'], 
                        $response['trxID'], 
                        $response['transactionStatus'], 
                        $response['paymentExecuteTime'], 
                        $response['merchantInvoiceNumber'], 
                        $response['customerMsisdn'], 
                        $response['amount']
                    );

                    // Execute the statement
                    if (mysqli_stmt_execute($stmt)) {
                        header("Location: recharge.php?trxID=" . $response['trxID']);
                    } else {
                        header("Location: recharge.php?trxID=" . $response['trxID']);
                    }

                    // Close the prepared statement
                    mysqli_stmt_close($stmt);
                } else {
                    header("Location: recharge.php?trxID=Connection faield");
                }

            } else {
                header("Location: recharge.php?trxID=" . $response['transactionStatus']);
            }

            // Close the database connection
            mysqli_close($link); 
            exit;
        }
    } else {
        header("Location: recharge.php");
        exit;
    }
}
?>