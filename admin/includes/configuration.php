<?php
$servername = "localhost";
$username = "soko_nid";
$password = "soko_nid";
$dbname = "soko_nid";

$conn = new mysqli($servername, $username, $password, $dbname);
mysqli_set_charset($conn, "utf8mb4");

if (!function_exists('get_site_setting')) {
    function get_site_setting($conn, $key, $default = '') {
        $stmt = $conn->prepare("SELECT setting_value FROM site_settings WHERE setting_key = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param("s", $key);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res && $row = $res->fetch_assoc()) {
                $val = trim($row['setting_value']);
                $stmt->close();
                return !empty($val) ? $val : $default;
            }
            $stmt->close();
        }
        return $default;
    }
}