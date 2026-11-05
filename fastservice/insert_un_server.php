<?php
session_start();
error_reporting(0);

if (!isset($_SESSION['user_id'])) {
    header("location: index.php");
    exit();
}

include_once("includes/configuration.php");

function safe_get($array, $key, $default = null) {
    if (!is_array($array)) return $default;
    $value = isset($array[$key]) ? trim($array[$key]) : null;
    return ($value !== '') ? $value : $default;
}
function null_if_empty($value) {
    return ($value === null || trim($value) === '') ? null : $value;
}

if (!isset($_POST['nid']) || !isset($_POST['dob'])) {
    echo json_encode(['status' => 'error', 'message' => 'NID এবং জন্ম তারিখ আবশ্যক।']);
    exit;
}

$nid = trim($_POST['nid']);
$dob = trim($_POST['dob']);
$user_mail = base64_decode($_SESSION['user_id']);

$deductionAmount = floatval(file_get_contents('api_key/deduction_amount.txt'));

$balanceStmt = $conn->prepare("SELECT balance FROM users WHERE email = ?");
$balanceStmt->bind_param("s", $user_mail);
$balanceStmt->execute();
$balanceResult = $balanceStmt->get_result();

if ($balanceResult->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'User not found.']);
    exit;
}
$userBalance = $balanceResult->fetch_assoc()['balance'];

if ($userBalance < $deductionAmount) {
    echo json_encode(['status' => 'error', 'message' => 'আপনার পর্যাপ্ত ব্যালেন্স নাই।']);
    exit;
}

/* ============ API CALL ============ */
$apiKey = 'th_93d5c103fc4581565d3ec876';
$apiUrl = "https://api1.totthohub.com/api/v1?key={$apiKey}&nid={$nid}&dob={$dob}";

$ch = curl_init($apiUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_TIMEOUT        => 30,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'
]);
$response = curl_exec($ch);
if ($response === false) {
    echo json_encode(['status' => 'error', 'message' => 'cURL Error: ' . curl_error($ch)]);
    curl_close($ch); exit;
}
curl_close($ch);

$data = json_decode($response, true);
if (empty($data['success']) || $data['success'] !== true) {
    echo json_encode(['status' => 'error', 'message' => 'কোনো তথ্য পাওয়া যায়নি।']);
    exit;
}
$res = $data['data-Info'] ?? null;
if (empty($res) || empty($res['nationalId'])) {
    echo json_encode(['status' => 'error', 'message' => 'API response এ কোনো ডাটা নেই।']);
    exit;
}

/* ============ Extract ============ */
$nameBangla  = safe_get($res, 'nameBangla');
$nameEnglish = safe_get($res, 'nameEnglish');
$nationalId  = safe_get($res, 'nationalId');
$dateOfBirth = safe_get($res, 'dateOfBirth');

$fatherName  = null_if_empty(safe_get($res, 'fatherName'));
$motherName  = null_if_empty(safe_get($res, 'motherName'));
$spouseName  = null_if_empty(safe_get($res, 'spouseName'));
$gender      = null_if_empty(safe_get($res, 'gender'));
$religion    = null_if_empty(safe_get($res, 'religion'));
$occupation  = null_if_empty(safe_get($res, 'occupation'));
$birthPlace  = null_if_empty(safe_get($res, 'birthPlace'));
$bloodGroup  = null_if_empty(safe_get($res, 'bloodGroup'));
$photo       = null_if_empty(safe_get($res, 'photo'));

$voterNumber = null_if_empty(safe_get($res, 'voterNumber'));
$voterArea   = null_if_empty(safe_get($res, 'voterArea'));   // ✅ ভোটার এলাকা
$pin         = null_if_empty(safe_get($res, 'pin'));
$oldId       = null_if_empty(safe_get($res, 'oldId'));

/* ✅ sl_no = formNumber, voter_aria_code = voterArea */
$slNo          = null_if_empty(safe_get($res, 'formNumber'));
$voterAreaCode = $voterArea;   // ✅ voterArea এখানে সেভ করছি
$sign          = null;
$nidFather     = null;
$nidMother     = null;

/* ============ Address ============ */
$presentFullAddress   = safe_get($res['preAddress'] ?? [], 'addressLine');
$permanentFullAddress = safe_get($res['perAddress'] ?? [], 'addressLine');

/* ============ INSERT ============ */
$sql = "INSERT INTO server_copy (
    user_mail, nameBn, nameEn, nationalId, gender, pin, bloodGroup, dateOfBirth,
    birthplace, sl_no, voter_aria_code, voter_no, father, mother, spouse,
    religion, occupation, photo, permanentAddressLine, presentAddressLine,
    fatherNID, sign_path, motherNID
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "sssssssssssssssssssssss",
    $user_mail, $nameBangla, $nameEnglish, $nationalId, $gender, $pin,
    $bloodGroup, $dateOfBirth, $birthPlace, $slNo, $voterAreaCode,
    $voterNumber, $fatherName, $motherName, $spouseName, $religion,
    $occupation, $photo, $permanentFullAddress, $presentFullAddress,
    $nidFather, $sign, $nidMother
);

if (!$stmt->execute()) {
    echo json_encode(['status' => 'error', 'message' => 'Data insertion failed: ' . $stmt->error]);
    exit;
}

/* ============ Balance Update ============ */
$updateStmt = $conn->prepare("UPDATE users SET balance = balance - ? WHERE email = ?");
$updateStmt->bind_param("ds", $deductionAmount, $user_mail);
$updateStmt->execute();

/* ============ History ============ */
$currentBalance  = $userBalance;
$balanceAfterCut = $userBalance - $deductionAmount;
$orderTime       = date("Y-m-d H:i:s");

$stmtHistory = $conn->prepare("INSERT INTO history_work
    (email, order_type, price, current_balance, balance_after_cut, about_order, order_time)
    VALUES (?, 'সার্ভার কপি', ?, ?, ?, 'make', ?)");
$stmtHistory->bind_param("sddds", $user_mail, $deductionAmount, $currentBalance, $balanceAfterCut, $orderTime);
$stmtHistory->execute();

echo json_encode([
    'status' => 'success',
    'message' => 'সফলভাবে ডাটা তৈরি হয়েছে। এখন ফাইল লিস্ট থেকে যেকোনো ভার্সনে ডাউনলোড করুন।'
]);
$conn->close();