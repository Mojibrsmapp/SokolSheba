<?php
session_start();

header('Content-Type: application/json');

// ---------------------------
// Auth check
// ---------------------------
if (!isset($_SESSION["user_token"])) {
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
    exit;
}

include_once("includes/configuration.php");

// ---------------------------
// Helper Functions
// ---------------------------

// Save image from FORM or API
function saveImageFromAnySource($fileInput = null, $apiImageUrl = null) {
    $uploadDir = "smart/image/";
    $dbPrefix  = "../smart/image/";

    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // FORM upload
    if ($fileInput && isset($_FILES[$fileInput]) && $_FILES[$fileInput]['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES[$fileInput]['name'], PATHINFO_EXTENSION);
        $fileName = uniqid("img_") . "." . $ext;
        if (move_uploaded_file($_FILES[$fileInput]['tmp_name'], $uploadDir . $fileName)) {
            return $dbPrefix . $fileName;
        }
    }

    // API image
    if ($apiImageUrl) {
        $imageData = @file_get_contents($apiImageUrl);
        if ($imageData !== false) {
            $fileName = uniqid("img_") . ".jpg";
            file_put_contents($uploadDir . $fileName, $imageData);
            return $dbPrefix . $fileName;
        }
    }

    return null;
}

// Gender normalize
function firstLetterGender($gender) {
    $gender = trim($gender);
    if ($gender === '') return null;
    return strtoupper($gender[0]);
}

// ---------------------------
// Image Handling
// ---------------------------
$imagePath1 = saveImageFromAnySource('imageUrl1', $_POST['api_image1'] ?? null);
$imagePath1 = $imagePath1 ?? $_POST['userPhoto'] ?? '../assets/media/card/blank.png';

$imagePath2 = saveImageFromAnySource('imageUrl2', $_POST['api_image2'] ?? null);
$imagePath2 = $imagePath2 ?? $_POST['userSign'] ?? '../assets/media/card/blank.png';

// ---------------------------
// Card price
// ---------------------------
$priceFile = 'api_key/smartret.txt';
$nid_make = file_exists($priceFile) ? (float) file_get_contents($priceFile) : null;

if ($nid_make === null) {
    echo json_encode(["success" => false, "message" => "মূল্য ফাইল পাওয়া যায়নি"]);
    exit;
}

// ---------------------------
// Get user balance
// ---------------------------
$email = base64_decode($_SESSION["user_id"]);

$stmt = $conn->prepare("SELECT balance FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$res = $stmt->get_result();

if (!$res->num_rows) {
    echo json_encode(["success" => false, "message" => "ইউজার পাওয়া যায়নি"]);
    exit;
}

$user = $res->fetch_assoc();
$currentBalance = $user['balance'];

// ---------------------------
// Balance check
// ---------------------------
if ($currentBalance < $nid_make) {
    echo json_encode([
        "success" => false,
        "message" => "পর্যাপ্ত ব্যালেন্স নেই"
    ]);
    exit;
}

// ---------------------------
// Normalize gender
// ---------------------------
$gender = firstLetterGender($_POST['gender'] ?? '');

if ($gender !== 'M' && $gender !== 'F') {
    echo json_encode([
        "success" => false,
        "message" => "অবৈধ জেন্ডার"
    ]);
    exit;
}

// ---------------------------
// Insert card
// ---------------------------
date_default_timezone_set('Asia/Dhaka');
$orderTime = date("Y-m-d H:i:s");

$insert = $conn->prepare("
    INSERT INTO smart 
    (email, nid, pin, name_bangla, name_english, dob, birth_place, name_father, name_mother, gender, blood_group, issueDate, image_url_12, image_url_22, full_address, created_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$insert->bind_param(
    "ssssssssssssssss",
    $email,
    $_POST['nid'],
    $_POST['pin'],
    $_POST['nameBangla'],
    $_POST['nameEnglish'],
    $_POST['dob'],
    $_POST['birthPlace'],
    $_POST['fatherName'],
    $_POST['motherName'],
    $gender,
    $_POST['bloodGroup'],
    $_POST['issueDate'],
    $imagePath1,
    $imagePath2,
    $_POST['fulAddress'],
    $orderTime
);

// ---------------------------
// Execute
// ---------------------------
if ($insert->execute()) {

    // Deduct balance
    $newBalance = $currentBalance - $nid_make;

    $update = $conn->prepare("UPDATE users SET balance = ? WHERE email = ?");
    $update->bind_param("ds", $newBalance, $email);
    $update->execute();

    // Add history
    $log = $conn->prepare("
        INSERT INTO history_work 
        (email, order_type, price, current_balance, balance_after_cut, about_order, order_time)
        VALUES (?, 'স্মার্ট কার্ড মেইক', ?, ?, ?, 'make', ?)
    ");

    $log->bind_param("sddds", $email, $nid_make, $currentBalance, $newBalance, $orderTime);
    $log->execute();

    echo json_encode([
        "success" => true,
        "message" => "কার্ড সফলভাবে তৈরি হয়েছে"
    ]);
    exit;

} else {
    echo json_encode([
        "success" => false,
        "message" => "তথ্য সেভ করতে সমস্যা হয়েছে"
    ]);
    exit;
}

// ---------------------------
$conn->close();
?>