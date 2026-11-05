<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Your account is not logged in']);
    exit;
}

// Decode user email
// Suspicious code removed

// Database connection
require_once 'includes/configuration.php'; 

// Handle POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve NID and DOB from POST data
    $nid = $_POST["nid"] ?? '';
    $dob = $_POST["dob"] ?? '';

    // Validate inputs
    if (empty($nid) || empty($dob)) {
        echo json_encode(['status' => 'error', 'message' => 'NID and Date of Birth are required']);
        exit;
    }

    // Check if NID is 10 or 17 digits
    if (!preg_match('/^\d{10}$|^\d{17}$/', $nid)) {
        echo json_encode(['status' => 'error', 'message' => 'আপনাকে অবশ্যই ১০ ডিজিটের অথবা ১৭ ডিজিটেরে নাম্বার দিতে হবে।']);
        exit;
    }

    // Check if DOB format is correct
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dob)) {
        echo json_encode(['status' => 'error', 'message' => 'জন্ম তারিখ আপনাকে 2000-01-29 এই ফরমেটে দিতে হবে অবশ্যই']);
        exit;
    }

    // Deduction amount from text file
    $deductionAmount = file_get_contents('api_key/deduction_amount.txt');

    // Check user balance
    $balanceCheckSql = "SELECT balance FROM users WHERE email = ?";
    $balanceCheckStmt = $conn->prepare($balanceCheckSql);
    $balanceCheckStmt->bind_param("s", $user_mail);
    $balanceCheckStmt->execute();
    $balanceCheckStmt->bind_result($balance);
    $balanceCheckStmt->fetch();
    $balanceCheckStmt->close();

    if ($balance < $deductionAmount) {
        echo json_encode(['status' => 'error', 'message' => 'আপনার পর্যাপ্ত ব্যাল্যন্স নাই']);
        exit;
    }

    // Prepare API URL
    $url = " ";

    // Initialize cURL
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'Accept-Language: en-US,en;q=0.9',
        'Connection: keep-alive',
        'Referer: https://amarnamjaninavai/',
    ]);

    // Execute cURL request
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        echo json_encode(['error' => 'cURL Error: ' . curl_error($ch)]);
        curl_close($ch);
        exit;
    }
    curl_close($ch);

    // Decode JSON response
    $responseData = json_decode($response, true);

    // Check for valid response and balance deduction
    if ($responseData && $responseData['success'] == true && isset($responseData['data'])) {
        // Deduct balance
        $updateBalanceSql = "UPDATE users SET balance = balance - ? WHERE email = ?";
        $updateStmt = $conn->prepare($updateBalanceSql);
        $updateStmt->bind_param("ds", $deductionAmount, $user_mail);

        if ($updateStmt->execute()) {
            $data = $responseData['data'];

            // Extract data
            $nameBn = $data['name'] ?? 'N/A';
            $nameEn = $data['nameEn'] ?? 'N/A';
            $gender = $data['gender'] ?? 'N/A';
            $nationalId = $data['nationalId'] ?? 'N/A';
            $pin = $data['pin'] ?? 'N/A';
            $dob = $data['dateOfBirth'] ?? 'N/A';
            $fatherName = $data['father'] ?? 'N/A';
            $motherName = $data['mother'] ?? 'N/A';
            $spouse = $data['spouse'] ?? 'N/A';

            // Handle photo download
            $photo = $data['photo'] ?? '';
            $photoPath = '';
            if ($photo) {
                $photoFolder = 'photo/'; // Ensure this folder exists and is writable
                $imageName = basename($photo); // Get the image name
                $photoPath = $photoFolder . $imageName; // Local path to save the image
// Suspicious code removed
            }

            function englishToBangla($number) {
                $englishDigits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
                $bengaliDigits = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
                return str_replace($englishDigits, $bengaliDigits, $number);
            }

            // Present and permanent addresses
            $presentAddress = $data['presentAddress'] ?? [];
            $presentNID = formatAddress($presentAddress);
            $permanentAddress = $data['permanentAddress'] ?? [];
            $permanentNID = formatAddress($permanentAddress);

            // Prepare the data for response
            $responseArray = [
                'birthPlace' => $permanentAddress['district'] ?? 'N/A',
                'photo_path' => $photoPath,
                'nameBn' => $nameBn,
                'nameEn' => $nameEn,
                'gender' => $gender,
                'nationalId' => $nationalId,
                'pin' => $pin,
                'dateOfBirth' => $dob,
                'father' => $fatherName,
                'mother' => $motherName,
                'spouse' => $spouse,
                'presentAddr' => $presentNID,
                'permanentAddr' => $permanentNID 
            ];

            // Return the data as JSON
            echo json_encode(['status' => 'success', 'data' => $responseArray]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Balance deduction failed']);
        }
    } elseif ($responseData['Success'] == "False") {
        echo json_encode(['status' => 'error', 'message' => 'কোনো তথ্য পাওয়া যায় নি']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'API সমস্যা। আপাদাত সার্ভার কপির কাজ বন্ধ। খুব শিগ্রিই ঠিক করা হবে।']);
    }
}

/**
 * Helper function to format address data.
 */
function formatAddress($address) {
    $district = $address['district'] ?? '';
    $upazilla = $address['upazilla'] ?? '';
    $postOffice = englishToBangla($address['postOffice'] ?? '');
    $mouza = $address['mouzaOrMoholla'] ?? '';
    $village = $address['villageOrRoad'] ?? '';
    $holdingNo = !empty(trim($address['houseOrHoldingNo'] ?? '')) ? 'বাসা/হোল্ডিং: ' . $address['houseOrHoldingNo'] . ',' : '';
    $villageMouza = (trim($village) === trim($mouza)) ? $village : "$village, $mouza";
    return "$holdingNo গ্রাম/রাস্তা: $villageMouza, ডাকঘর: $postOffice, $upazilla, $district";
}
?>