<?php
/**
 * Sokol Sheba — JSON status of an order payment (used by payment_processing.php).
 * GET payment_status.php?order=<id>
 */
session_start();
include_once("includes/configuration.php");
include_once("includes/payment_flow.php");

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$user = soko_current_user($conn);
if (!$user) {
    http_response_code(401);
    echo json_encode(array('state' => 'auth', 'message' => 'লগইন প্রয়োজন'));
    exit;
}

$order_id = isset($_GET['order']) ? (int)$_GET['order'] : 0;
$order = soko_order_by_id($conn, $order_id, (int)$user['id']);

if (!$order) {
    http_response_code(404);
    echo json_encode(array('state' => 'not_found', 'message' => 'অর্ডার পাওয়া যায়নি'));
    exit;
}

$state = soko_order_payment_state($conn, $order);
$payment = soko_pay_latest($conn, (int)$order['id']);

echo json_encode(array(
    'state' => $state,
    'order_id' => (int)$order['id'],
    'order_status' => (string)$order['status'],
    'payment_status' => $payment ? (string)$payment['status'] : '',
    'trx_id' => $payment ? (string)$payment['trx_id'] : '',
    'redirect' => ($state === 'verified' || $state === 'wallet') ? ('receipt.php?order=' . (int)$order['id'] . '&paid=1') : '',
));
