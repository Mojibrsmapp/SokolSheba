<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("location: index.php");
    exit();
}
include_once("includes/configuration.php");

function safe_get($array, $key, $default = null) {
    $value = isset($array[$key]) ? trim($array[$key]) : null;
    return $value !== '' ? $value : $default;
}

function null_if_empty($value) {
    return ($value === null || trim($value) === '') ? null : $value;
}

if (isset($_POST['nid']) && isset($_POST['dob'])) {
    $nid = $_POST['nid'];
    $dob = $_POST['dob'];
    $user_mail = base64_decode($_SESSION['user_id']);

    $deductionAmount = floatval(file_get_contents('api_key/deduction_amount.txt'));

    $balanceSql = "SELECT balance FROM users WHERE email = ?";
    $balanceStmt = $conn->prepare($balanceSql);
    $balanceStmt->bind_param("s", $user_mail);
    $balanceStmt->execute();
    $balanceResult = $balanceStmt->get_result();

    if ($balanceResult->num_rows > 0) {
        $userBalance = $balanceResult->fetch_assoc()['balance'];

        if ($userBalance >= $deductionAmount) {
            $url = "https://api.totthohub.com/servercopyonaliseba754&nid=$nid&dob=$dob";
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            $response = curl_exec($ch);

            if ($response === false) {
                echo json_encode(['status' => 'error', 'message' => 'cURL Error: ' . curl_error($ch)]);
                curl_close($ch);
                exit;
            }
            curl_close($ch);

            $data = json_decode($response, true);
            $res = $data['data'] ?? null; // Fixed key

            if (($data['success'] === true || $data['success'] === true) && $res && isset($res['nationalId'])) {
                $nameBangla = safe_get($res, 'nameBn');
                $nameEnglish = safe_get($res, 'nameEn');
                $dateOfBirth = safe_get($res, 'dateOfBirth');
                $nationalId = safe_get($res, 'nationalId');
                $pin = safe_get($res, 'pin');
                $voterNumber = null_if_empty(safe_get($res, 'voterNo'));
                $voterSlNo = null_if_empty(safe_get($res, 'slNo'));
                $voterAreaCode = null_if_empty(safe_get($res, 'voterAreaCode'));
                $gender = null_if_empty(safe_get($res, 'gender', 'N/A'));
                $occupation = null_if_empty(safe_get($res, 'occupation'));
                $religion = null_if_empty(safe_get($res, 'religion'));
                $birthPlace = null_if_empty(safe_get($res, 'birthPlace'));
                $bloodGroup = null_if_empty(safe_get($res, 'bloodGroup'));
                $fatherName = null_if_empty(safe_get($res, 'father'));
                $nidFather = null_if_empty(safe_get($res, 'nidFather'));
                $motherName = null_if_empty(safe_get($res, 'mother'));
                $nidMother = null_if_empty(safe_get($res, 'nidMother'));
                $spouseName = null_if_empty(safe_get($res, 'spouse'));
                $photo = null_if_empty(safe_get($res, 'photo'));
                $sign = null_if_empty(safe_get($res, 'sign')); 
                $addressLine = safe_get($res, 'addressLine');
                $presentFullAddress = safe_get($res['presentAddress'], 'addressLine');
                $permanentFullAddress = safe_get($res['permanentAddress'], 'addressLine');

                $sql = "INSERT INTO server_copy (
                    user_mail, nameBn, nameEn, nationalId, gender, pin, bloodGroup, dateOfBirth, birthplace, 
                    sl_no, voter_aria_code, voter_no, father, mother, spouse, religion, occupation, 
                    photo, permanentAddressLine, presentAddressLine, fatherNID, sign_path, motherNID, addressLine
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

                $stmt = $conn->prepare($sql);
                $stmt->bind_param(
                    "ssssssssssssssssssssssss",
                    $user_mail, $nameBangla, $nameEnglish, $nationalId, $gender, $pin, $bloodGroup, $dateOfBirth, $birthPlace,
                    $voterSlNo, $voterAreaCode, $voterNumber, $fatherName, $motherName, $spouseName, $religion,
                    $occupation, $photo, $permanentFullAddress, $presentFullAddress, $nidFather, $sign, $nidMother, $addressLine
                );

                if ($stmt->execute()) {
                    $updateBalanceSql = "UPDATE users SET balance = balance - ? WHERE email = ?";
                    $updateStmt = $conn->prepare($updateBalanceSql);
                    $updateStmt->bind_param("ds", $deductionAmount, $user_mail);

                    if ($updateStmt->execute()) {
                        $currentBalance = $userBalance;
                        $balanceAfterCut = $userBalance - $deductionAmount;
                        $orderTime = date("Y-m-d H:i:s");

                        $stmtHistory = $conn->prepare("INSERT INTO history_work (email, order_type, price, current_balance, balance_after_cut, about_order, order_time) 
                                                       VALUES (?, 'সার্ভার কপি', ?, ?, ?, 'make', ?)");
                        $stmtHistory->bind_param("sddds", $user_mail, $deductionAmount, $currentBalance, $balanceAfterCut, $orderTime);

                        if ($stmtHistory->execute()) {
                            echo json_encode(['status' => 'success', 'message' => 'সফলভাবে ডাটা তৈরি হয়েছে। এখন ফইল লিষ্ট থেকে যেকোনো ভার্সনে ডাউনলোড করে নেন।']);
                        } else {
                            echo json_encode(['status' => 'error', 'message' => 'History ইনসার্ট করতে সমস্যা হয়েছে: ' . $stmtHistory->error]);
                        }
                        $stmtHistory->close();
                    } else {
                        echo json_encode(['status' => 'error', 'message' => 'ব্যালেন্স হালনাগাদ করতে সমস্যা হয়েছে: ' . $updateStmt->error]);
                    }
                    $updateStmt->close();
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Data ইনসার্ট করতে সমস্যা হয়েছে: ' . $stmt->error]);
                }

                $stmt->close();
            } else {
                echo json_encode(['status' => 'error', 'message' => 'কোনো তথ্য পাওয়া যায়নি। দয়া করে NID ও জন্ম তারিখ যাচাই করুন।']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'আপনার পযাপ্ত ব্যাল্যান্স নাই। কাজ করতে হলে প্রথমে রিচার্জ করুন।']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'User not found.']);
    }

    $balanceStmt->close();
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'NID এবং জন্ম তারিখ আবশ্যক।']);
}
?>