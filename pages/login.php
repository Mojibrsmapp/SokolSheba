<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
include_once("../includes/configuration.php"); 
include_once('../includes/db.php'); // Ensure this file connects to DB

// Get user's IP address
function getUserIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

$ipAddress = getUserIP();

// IPInfo API
$token = "98d526ace2a693"; // Replace with your actual ipinfo token
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_RETURNTRANSFER => 1,
    CURLOPT_CONNECTTIMEOUT => 2,
    CURLOPT_TIMEOUT => 3,
    CURLOPT_URL => "https://ipinfo.io/{$ipAddress}?token={$token}",
]);
$response = curl_exec($curl);

if ($response === false) {
    $response = '{}';
}
curl_close($curl);

$locationData = json_decode($response, true);

$ip = $locationData['ip'] ?? 'N/A';
$city = $locationData['city'] ?? 'N/A';
$region = $locationData['region'] ?? 'N/A';
$country = $locationData['country'] ?? 'N/A';
$loc = $locationData['loc'] ?? 'N/A';
$org = $locationData['org'] ?? 'N/A';
$timezone = $locationData['timezone'] ?? 'N/A';

if ($loc !== 'N/A') {
    list($latitude, $longitude) = explode(',', $loc);
    $latitude = floatval($latitude);
    $longitude = floatval($longitude);
} else {
    $latitude = NULL;
    $longitude = NULL;
}


// Login Handling
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = strtolower(trim($_POST['email']));
    $password = md5($_POST['password']); // Consider using password_hash() instead of MD5 in production

    // Skip logging for this specific email
    if ($email !== "infonidwork@gmail.com") {
        $sql = "INSERT INTO user_login_activity 
                (user_mail, ip_address, city, region, country, latitude, longitude, org, timezone, login_time)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssss", $email, $ip, $city, $region, $country, $latitude, $longitude, $org, $timezone);
        $stmt->execute();
    }

    // Authenticate user
    $sql = "SELECT * FROM users WHERE email = ? AND password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if ($user['status'] == 0) {
            echo json_encode(['status' => 'error', 'message' => 'Your account is not active']);
        } else {
            session_regenerate_id(true);
            $_SESSION['user_token'] = $user['token'];
            $_SESSION['user_id'] = base64_encode($user['email']);
            echo json_encode(['status' => 'success', 'message' => 'Login successful']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid email or password']);
    }

    $stmt->close();
    $conn->close();
}
?>