<?php
include_once("../../includes/configuration.php");

$data = json_decode(file_get_contents("php://input"), true);

$token = $data['metadata']['token'] ?? null;
$amount = isset($data['amount']) ? floatval($data['amount']) : null;
$trxID = $data['trxID'] ?? '';
$paymentID = $data['paymentID'] ?? '';
$status = strtolower($data['transactionStatus'] ?? '');
$executeTime = $data['paymentExecuteTime'] ?? date('Y-m-d H:i:s');
$invoice = $data['merchantInvoiceNumber'] ?? '';
$msisdn = $data['customerMsisdn'] ?? '';

if ($token && $amount && $status === 'success') {
    $check = $conn->prepare("SELECT id FROM transactions WHERE paymentID = ?");
    $check->bind_param("s", $paymentID);
    $check->execute();
    $check->store_result();

    if ($check->num_rows === 0) {
        $update = $conn->prepare("UPDATE users SET balance = balance + ? WHERE token = ?");
        $update->bind_param("ds", $amount, $token);
        $update->execute();

        $insert = $conn->prepare("INSERT INTO transactions (token, paymentID, trxID, transactionStatus, paymentExecuteTime, merchantInvoiceNumber, customerMsisdn, amount)
        VALUES (?, ?, ?, 'success', ?, ?, ?, ?)");
        $insert->bind_param("ssssssd", $token, $paymentID, $trxID, $executeTime, $invoice, $msisdn, $amount);
        $insert->execute();
    }
}

http_response_code(200);
echo "OK";
?>
