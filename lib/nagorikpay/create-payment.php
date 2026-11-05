<?php
session_start();
include_once("../../includes/configuration.php");

$amount = $_POST['amount'] ?? 0;
$token = $_SESSION['user_token'] ?? null;

if (!$amount || !$token) {
  http_response_code(400);
  echo "Invalid request";
  exit;
}

$payload = json_encode([
  "success_url" => "https://grameenseba.xyz/success.php?token=" . urlencode($token),
  "cancel_url" => "https://grameenseba.xyz/recharge.php",
  "webhook_url" => "https://grameenseba.xyz/lib/nagorikpay/webhook.php",
  "metadata" => [ "token" => $token ],
  "amount" => $amount
]);

$curl = curl_init();
curl_setopt_array($curl, [
  CURLOPT_URL => 'https://secure-pay.nagorikpay.com/api/payment/create',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS => $payload,
  CURLOPT_HTTPHEADER => [
    'API-KEY: VNdbg4P58ty2xmhai1wqNmQUdbmzAiOkrdJYCadIjb0e0gt9c0',
    'Content-Type: application/json'
  ],
]);
$response = curl_exec($curl);
curl_close($curl);

$data = json_decode($response, true);
if (isset($data['payment_url'])) {
  echo $data['payment_url'];
} else {
  echo "error";
}
?>
