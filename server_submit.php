<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("location: ../index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("location: ../index.php");
    exit;
}

include_once("includes/configuration.php");

function englishToBanglaNumber($englishNumber) {
    $englishToBanglaMap = [
        '0' => '০',
        '1' => '১',
        '2' => '২',
        '3' => '৩',
        '4' => '৪',
        '5' => '৫',
        '6' => '৬',
        '7' => '৭',
        '8' => '৮',
        '9' => '৯'
    ];

    return str_replace(array_keys($englishToBanglaMap), array_values($englishToBanglaMap), $englishNumber);
}

$targetDir = "photo/";
$response = [];

function generateRandomString($length = 10) {
    return bin2hex(random_bytes($length));
}

// Handle image uploads
$imageOnePath = null;
$imageTwoPath = null;

if (isset($_FILES['imageUrl1']) && $_FILES['imageUrl1']['error'] === UPLOAD_ERR_OK) {
    $image1 = $_FILES['imageUrl1'];
    $image1Extension = pathinfo($image1['name'], PATHINFO_EXTENSION);
    $image1RandomName = 'user_' . generateRandomString(5) . '.' . $image1Extension;
    $image1Path = $targetDir . $image1RandomName;

    if (move_uploaded_file($image1['tmp_name'], $image1Path)) {
        $imageOnePath = 'photo/' . $image1RandomName;
        $response[] = "Image 1 uploaded successfully as {$image1RandomName}!";
    } else {
        $response[] = "Failed to upload Image 1.";
    }
}

if (isset($_FILES['imageUrl2']) && $_FILES['imageUrl2']['error'] === UPLOAD_ERR_OK) {
    $image2 = $_FILES['imageUrl2'];
    $image2Extension = pathinfo($image2['name'], PATHINFO_EXTENSION);
    $image2RandomName = 'sign_' . generateRandomString(5) . '.' . $image2Extension;
    $image2Path = $targetDir . $image2RandomName;

    if (move_uploaded_file($image2['tmp_name'], $image2Path)) {
        $imageTwoPath = 'photo/' . $image2RandomName;
        $response[] = "Image 2 uploaded successfully as {$image2RandomName}!";
    } else {
        $response[] = "Failed to upload Image 2.";
    }
}

echo "<script>console.log(" . json_encode($response) . ");</script>";

// Retrieve form data
$email = base64_decode($_SESSION["user_id"]);
$nid = $_POST["nid"] ?? null;
$pin = $_POST["pin"] ?? null;
$nameBangla = $_POST["nameBangla"] ?? null;
$nameEnglish = $_POST["nameEnglish"] ?? null;
$dob = $_POST["dob"] ?? null;
$birthPlace = $_POST["birthPlace"] ?? null;
$nameFather = $_POST["nameFather"] ?? null;
$nameMother = $_POST["nameMother"] ?? null;
$gender = $_POST["gender"] ?? null;
$bloodGroup = $_POST["bloodGroup"] ?? null;
$fulladdress = $_POST["fulladdress"] ?? null;
$issueDate  = $_POST["issueDate"] ?? null;
$nameSpouse = $_POST["spouseName"] ?? null;
$education = $_POST["education"] ?? null;
$formNo = $_POST["formNo"] ?? null;
$mobile = $_POST["mobile"] ?? null;
$mother_nid = $_POST["mother_nid"] ?? null;
$father_nid = $_POST["father_nid"] ?? null;
$permanentAddress = $_POST["permanentAddress"] ?? null;
$presentAddress = $_POST["presentAddress"] ?? null;
$religion = $_POST["religion"] ?? null;
$sl_no = $_POST["sl_no"] ?? null;
$voterNo = $_POST["voterNo"] ?? null;
$voterArea = $_POST["voterArea"] ?? null;
$occupation = $_POST["occupation"] ?? null;




$imageUrl12 = !empty($imageOnePath) ? $imageOnePath : ($_POST["imageUrl12"] ?? null);
$imageUrl22 = !empty($imageTwoPath) ? $imageTwoPath : ($_POST["imageUrl22"] ?? "assets/media/card/blank.png");

$priceFile = 'api_key/signToServer.txt';
$nid_make = file_exists($priceFile) ? (float)file_get_contents($priceFile) : 3;

// Fetch user balance and NID make price from `users` table
$stmtBalanceCheck = $conn->prepare("SELECT balance FROM users WHERE email = ?");
$stmtBalanceCheck->bind_param("s", $email);
$stmtBalanceCheck->execute();
$resultBalance = $stmtBalanceCheck->get_result();

if ($rowBalance = $resultBalance->fetch_assoc()) {
    $currentBalance = $rowBalance['balance'];

    if ($currentBalance < $nid_make) {
        echo "<script>alert('Insufficient balance. Your balance is too low to proceed.'); window.location.href='../index.php';</script>";
        exit;
    }
} else {
    echo "<script>alert('User balance not found.'); window.location.href='../index.php';</script>";
    exit;
}
$dob = null;
if (!empty($_POST["dob"])) {
    $dobObj = DateTime::createFromFormat('d M Y', $_POST["dob"]);
    if ($dobObj) {
        $dob = $dobObj->format('Y-m-d');
    }
}

$stmtBalanceCheck->close();

// Process and deduct balance
date_default_timezone_set('Asia/Dhaka');
$orderTime = date('Y-m-d H:i:s');

$stmt = $conn->prepare("INSERT INTO sign_to_server (
    email, nid, pin, name_bangla, name_english, dob, birth_place, name_father, name_mother, name_spouse, gender, blood_group, image_url_12, image_url_22, full_address, education, formNo, mobile, mother_nid, father_nid, permanentAddress, presentAddress, religion, sl_no, voterNo, voterArea, occupation, created_at
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->bind_param(
    "ssssssssssssssssssssssssssss", 
    $email, $nid, $pin, $nameBangla, $nameEnglish, $dob, $birthPlace, $nameFather, $nameMother, $nameSpouse, $gender, $bloodGroup, $imageUrl12, $imageUrl22, $_POST['fulladdress'], 
    $education, $formNo, $mobile, $mother_nid, $father_nid, $permanentAddress, $presentAddress, $religion, $sl_no, $voterNo, $voterArea, $occupation, $created_at
);

if ($stmt->execute()) {
    $balanceAfterCut = $currentBalance - $nid_make;

    $stmtBalance = $conn->prepare("UPDATE users SET balance = ? WHERE email = ?");
    $stmtBalance->bind_param("ds", $balanceAfterCut, $email);
    $stmtBalance->execute();

    if ($stmtBalance->affected_rows > 0) {
        $orderType = "সাইন টু সার্ভার";
        $aboutOrder = "make";

        $stmtHistory = $conn->prepare("INSERT INTO history_work (email, order_type, price, current_balance, balance_after_cut, about_order, order_time) 
                                        VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmtHistory->bind_param("sssddss", $email, $orderType, $nid_make, $currentBalance, $balanceAfterCut, $aboutOrder, $orderTime);
      
                 if ($stmtHistory->execute()) {
                      $stmtHistory->close();
                      echo "<script>
                          alert('সফলভাবে সার্ভার কপি তৈরি করা হয়েছে। এখন ফাইল লিষ্ট থেকে ডাউনলোড করে নিন যেকোনো ভার্সনে।');
                          window.location.href = 'signToserverList.php';
                      </script>";
                      exit();
                  } else {
                      echo "Error executing statement.";
                  }


    } else {
        echo "<script>alert('Record created, but balance update failed.'); window.location.href='index.php';</script>";
    }

    $stmtBalance->close();
} else {
    echo "<script>alert('Error: " . $stmt->error . "'); window.location.href='index.php';</script>";
}

$stmt->close();
$conn->close();
?>

    
