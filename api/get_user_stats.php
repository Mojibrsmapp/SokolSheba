<?php
header('Content-Type: application/json; charset=utf-8');
session_start();

include_once("../includes/configuration.php");

if (!isset($_SESSION['user_token'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$token = $_SESSION['user_token'];
$stmt = $conn->prepare("SELECT id, balance, email FROM users WHERE token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$res = $stmt->get_result();

if ($res && $user = $res->fetch_assoc()) {
    $uid = (int)$user['id'];
    $uemail = $conn->real_escape_string($user['email']);
    
    $total = 0;
    $today = 0;

    $q1 = @$conn->query("SELECT COUNT(*) as total, SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as today FROM orders WHERE user_id = $uid");
    if ($q1 && $r1 = $q1->fetch_assoc()) {
        $total += (int)($r1['total'] ?? 0);
        $today += (int)($r1['today'] ?? 0);
    }

    $q2 = @$conn->query("SELECT COUNT(*) as total, SUM(CASE WHEN DATE(order_time) = CURDATE() THEN 1 ELSE 0 END) as today FROM order_list WHERE email = '$uemail'");
    if ($q2 && $r2 = $q2->fetch_assoc()) {
        $total += (int)($r2['total'] ?? 0);
        $today += (int)($r2['today'] ?? 0);
    }

    echo json_encode([
        'success' => true,
        'user_balance' => (string)$user['balance'],
        'total_orders' => $total,
        'completed_orders' => $today
    ]);
} else {
    echo json_encode(['success' => false]);
}