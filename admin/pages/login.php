<?php
session_start();
include_once("../includes/configuration.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = md5($_POST['password']); // Use MD5 to hash the password
    $token = $_POST['token'];
    // Query to select the user based on email and password together
    $sql = "SELECT * FROM admin_user WHERE email = ? AND password = ? AND token = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $email, $password, $token); // Bind both email and hashed password
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if($user['status'] === 0){
           echo json_encode(['status' => 'error', 'message' => 'Your account not active']);
        }else{
        // Set session if the user is found
        $_SESSION['admin_token'] = $user['token'];
        $_SESSION["admin_id"] = base64_encode($user['email']);
       setcookie("admin_session_token_unique", $user['token'], time() + (10 * 365 * 24 * 60 * 60), "/", "", true, true); // 10 years   
        echo json_encode(['status' => 'success', 'message' => 'Login successful']);
    }
    } else {
        // If no match is found for email and password
        echo json_encode(['status' => 'error', 'message' => 'Invalid email or password or This link not for your account']);
    }

    $stmt->close();
    $conn->close();
}
?>