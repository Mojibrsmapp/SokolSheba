<?php
session_start();
if (empty($_SESSION['user_token'])) { header('Location: login.php'); exit; }
require_once __DIR__ . '/includes/configuration.php';
require_once __DIR__ . '/includes/customer_history.php';
header('Cache-Control: private, no-store');
$order_id = (int)($_GET['order'] ?? 0);
try {
    $stmt = $conn->prepare('SELECT o.* FROM orders o INNER JOIN users u ON u.id = o.user_id WHERE o.id = ? AND u.token = ? LIMIT 1');
    if (!$stmt) throw new RuntimeException('Result unavailable');
    $stmt->bind_param('is', $order_id, $_SESSION['user_token']);
    if (!$stmt->execute()) { $stmt->close(); throw new RuntimeException('Result unavailable'); }
    $res = $stmt->get_result();
    $order = $res ? $res->fetch_assoc() : null;
    $stmt->close();
} catch (Throwable $error) {
    error_log('Customer order result could not be loaded.');
    http_response_code(503);
    exit('রেজাল্ট এখন ডাউনলোড করা যাচ্ছে না।');
}
$file = $order && soko_order_result_has_file($order) ? soko_order_result_path($order['result_file']) : null;
if (!$file) { http_response_code(404); exit('রেজাল্ট ফাইল পাওয়া যায়নি। সাপোর্টে যোগাযোগ করুন।'); }
$extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
$mime = ['pdf' => 'application/pdf', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp', 'zip' => 'application/zip', 'txt' => 'text/plain'];
header('X-Content-Type-Options: nosniff');
header('Content-Type: ' . ($mime[$extension] ?? 'application/octet-stream'));
header('Content-Disposition: attachment; filename="order-' . $order_id . '-result.' . $extension . '"');
header('Content-Length: ' . filesize($file));
session_write_close();
readfile($file);
