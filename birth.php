<?php 
session_start();

if (!isset($_SESSION["user_token"])) {
    header("location: ../index.php");
    exit;
}

include('header.php'); 

function saveBirthDataToDatabase($data) {
    global $conn;
    
    $email = base64_decode($_SESSION["user_id"]);
    $unique_id = uniqid('hdbfnekfn_');
    
    $query = "INSERT INTO birth_log (
        user, email, birthRegistrationNumber, officeAddressFirst, officeAddressSecond, 
        gender, dateOfRegistration, dateOfIssuance, barCode, qrLink, 
        dateOfBirth, dateOfBirthText, nameBangla, nameEnglish, 
        fatherNameBangla, fatherNameEnglish, fatherNationalityBangla, fatherNationalityEnglish, 
        motherNameBangla, motherNameEnglish, motherNationalityBangla, motherNationalityEnglish, 
        birthplaceBangla, birthplaceEnglish, permanentAddressBangla, permanentAddressEnglish,
        created_at, unique_id
    ) VALUES (
        ?, ?, ?, ?, ?, 
        ?, ?, ?, ?, ?, 
        ?, ?, ?, ?, 
        ?, ?, ?, ?, 
        ?, ?, ?, ?, 
        ?, ?, ?, ?,
        ?, ?
    )";
    
    $stmt = $conn->prepare($query);
    
    date_default_timezone_set('Asia/Dhaka');
    $created_at = date('Y-m-d H:i:s');
    
    $stmt->bind_param(
        "isssssssssssssssssssssssssss",
        $data['user_id'],
        $email,
        $data['birthRegistrationNumber'],
        $data['officeAddressFirst'],
        $data['officeAddressSecond'],
        $data['gender'],
        $data['dateOfRegistration'],
        $data['dateOfIssuance'],
        $data['barCode'],
        $data['qrLink'],
        $data['dateOfBirth'],
        $data['dateOfBirthText'],
        $data['nameBangla'],
        $data['nameEnglish'],
        $data['fatherNameBangla'],
        $data['fatherNameEnglish'],
        $data['fatherNationalityBangla'],
        $data['fatherNationalityEnglish'],
        $data['motherNameBangla'],
        $data['motherNameEnglish'],
        $data['motherNationalityBangla'],
        $data['motherNationalityEnglish'],
        $data['birthplaceBangla'],
        $data['birthplaceEnglish'],
        $data['permanentAddressBangla'],
        $data['permanentAddressEnglish'],
        $created_at,
        $unique_id
    );
    
    if ($stmt->execute()) {
        return $unique_id;
    }
    return false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nameBangla'])) {
    $email = base64_decode($_SESSION["user_id"]);
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $user_id = $user['id'] ?? 0;
    
    $formData = [
        'user_id' => $user_id,
        'birthRegistrationNumber' => $_POST['birthRegistrationNumber'],
        'officeAddressFirst' => $_POST['officeAddressFirst'],
        'officeAddressSecond' => $_POST['officeAddressSecond'],
        'gender' => $_POST['gender'],
        'dateOfRegistration' => $_POST['dateOfRegistration'],
        'dateOfIssuance' => $_POST['dateOfIssuance'],
        'barCode' => $_POST['barCode'] ?? '',
        'qrLink' => $_POST['qrLink'] ?? '',
        'dateOfBirth' => $_POST['dateOfBirth'],
        'dateOfBirthText' => $_POST['dateOfBirthText'],
        'nameBangla' => $_POST['nameBangla'],
        'nameEnglish' => $_POST['nameEnglish'],
        'fatherNameBangla' => $_POST['fatherNameBangla'],
        'fatherNameEnglish' => $_POST['fatherNameEnglish'],
        'fatherNationalityBangla' => $_POST['fatherNationalityBangla'],
        'fatherNationalityEnglish' => $_POST['fatherNationalityEnglish'],
        'motherNameBangla' => $_POST['motherNameBangla'],
        'motherNameEnglish' => $_POST['motherNameEnglish'],
        'motherNationalityBangla' => $_POST['motherNationalityBangla'],
        'motherNationalityEnglish' => $_POST['motherNationalityEnglish'],
        'birthplaceBangla' => $_POST['birthplaceBangla'],
        'birthplaceEnglish' => $_POST['birthplaceEnglish'],
        'permanentAddressBangla' => $_POST['permanentAddressBangla'],
        'permanentAddressEnglish' => $_POST['permanentAddressEnglish']
    ];
    
    $unique_id = saveBirthDataToDatabase($formData);
    if ($unique_id) {
        echo '<script>alert("Data saved successfully! Unique ID: ' . $unique_id . '");</script>';
        
        $priceFile = 'api_key/birth_make.txt';
        $birth_make = file_exists($priceFile) ? (float)file_get_contents($priceFile) : 0;
        
        if ($birth_make > 0) {
            $stmtBalance = $conn->prepare("SELECT balance FROM users WHERE email = ?");
            $stmtBalance->bind_param("s", $email);
            $stmtBalance->execute();
            $resultBalance = $stmtBalance->get_result();
            $userBalance = $resultBalance->fetch_assoc();
            $currentBalance = $userBalance['balance'];
            
            if ($currentBalance >= $birth_make) {
                $newBalance = $currentBalance - $birth_make;
                
                $stmtUpdate = $conn->prepare("UPDATE users SET balance = ? WHERE email = ?");
                $stmtUpdate->bind_param("ds", $newBalance, $email);
                $stmtUpdate->execute();
                
                date_default_timezone_set('Asia/Dhaka');
                $orderTime = date('Y-m-d H:i:s');
                $stmtHistory = $conn->prepare("INSERT INTO history_work (email, order_type, price, current_balance, balance_after_cut, about_order, order_time) 
                                             VALUES (?, 'Birth Certificate', ?, ?, ?, ?, ?)");
                $about_order = 'birth_make - ' . $unique_id;
                $stmtHistory->bind_param("sdddss", $email, $birth_make, $currentBalance, $newBalance, $about_order, $orderTime);
                $stmtHistory->execute();
            }
        }
    } else {
        echo '<script>alert("Error saving data to database!");</script>';
    }
}
?>
<link rel="stylesheet" href="assets/css/server.css">
<style>
    :root {
        --brand-green: #078B38;
        --brand-red: #E51B23;
        --dark-green: #065F2D;
        --dark-red: #B91018;
        --bg-page: #F5FAF6;
        --card-bg: #FFFFFF;
        --heading-color: #124D2B;
        --paragraph-color: #53665A;
        --border-color: #DFEBE1;
        --footer-color: #073D20;
    }

    body {
        background-color: var(--bg-page) !important;
        color: var(--paragraph-color) !important;
        padding-bottom: 75px !important;
    }

    .main-panel .content-wrapper > a[href*="whatsapp.com"],
    .content-wrapper > a[href*="whatsapp.com"],
    .main-panel .content-wrapper > div > a[href*="whatsapp.com"],
    .fixed.bottom-5.right-5 {
        display: none !important;
    }

    #brnNumber {
        width: 100% !important;
        line-height: 40px !important;
        padding-left: 45px;
        background: #f4f5f7;
        border: none;
    }

    .mobile-bottom-nav {
        display: none;
    }

    @media (max-width: 991px) {
        .mobile-bottom-nav {
            display: flex;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 64px;
            background: #FFFFFF;
            border-top: 1.5px solid var(--border-color);
            box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.08);
            z-index: 1050;
            align-items: center;
            justify-content: space-around;
            padding: 0 10px;
        }
        .m-nav-link {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-decoration: none !important;
            color: #64748B;
            font-size: 10.5px;
            font-weight: 700;
            flex: 1;
            transition: color 0.2s ease;
            gap: 3px;
        }
        .m-nav-link i {
            font-size: 18px;
        }
        .m-nav-link.active, .m-nav-link:hover {
            color: var(--brand-green);
        }
        .m-nav-center {
            position: relative;
            top: -16px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex: 1;
        }
        .m-nav-center-btn {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--brand-green) 0%, var(--dark-green) 100%);
            color: #FFFFFF;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none !important;
            font-size: 20px;
            box-shadow: 0 6px 18px rgba(7, 139, 56, 0.45);
            border: 3.5px solid #FFFFFF;
            transition: transform 0.2s ease;
        }
        .m-nav-center-btn:active {
            transform: scale(0.92);
        }
    }
</style>

<div id="loddingPdf">
  <div id="progress_main">
    <svg>
      <text x="50%" y="50%" dy=".35em" text-anchor="middle">
        তথ্য খোঁজা হচ্ছে
      </text>
    </svg>
  </div>
</div>

<div class="card mb-3 border-0 shadow-sm rounded-3">
  <?php
  include 'includes/marquee.php';
  $id = 6;
  $marquee = new Marquee($conn, $id);
  $marquee->display();
  ?>
</div>

<div class="card mb-3 px-1 border-0 shadow-sm rounded-3">
  <div class="row">
    <div class="col-md-4"></div>
    <div class="col-md-5">
      <div class="">
        <form action="" method="POST" id="autoloadform">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="brn">Birth Certificate no</label>
                        <input type="text" id="brn" name="brn" class="form-control form-neu" required placeholder="2005***********789">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="dob">Date of Birth</label>
                        <input type="text" id="dob" name="dob" class="form-control form-neu" required placeholder="YYYY-MM-DD">
                    </div>
                </div>
            </div>
            <div style="margin-top:20px;display: flex;justify-content: center;">
                <button name="submit" type="submit" class="btn btn-primary" style="background: var(--brand-green); border-color: var(--brand-green);">Get Data</button>
            </div>
        </form>
      </div>
    </div>
    <div class="col-md-3"></div>
    
    <div class="col-md-12">
      <form method="POST" action="birth-bn.php" id="BrnBN" target="_blank">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="form-group">
                        <label>Register Office Address</label>
                        <input type="text" name="officeAddressFirst" id="officeAddressFirst" class="form-control" placeholder="রেজিস্টার অফিসের ঠিকানা" value="<?php echo htmlspecialchars($officeAddressFirst ?? ''); ?>" required>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="form-group">
                        <label>Upazila/Pourashava/City Corporation, Zila</label>
                        <input type="text" name="officeAddressSecond" id="officeAddressSecond" class="form-control" placeholder="উপজেলা/পৌরসভা/সিটি কর্পোরেশন, জেলা" value="<?php echo htmlspecialchars($officeAddressSecond ?? ''); ?>" required>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="form-group">
                        <label>Birth Registration Number</label>
                        <input type="text" name="birthRegistrationNumber" id="birthRegistrationNumber" class="form-control" placeholder="XXXXXXXXXXXXXXXXX" value="<?php echo htmlspecialchars($birthRegistrationNumber ?? ''); ?>" required>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="form-group">
                        <label>Gender </label>
                        <select name="gender" id="gender" class="form-control">
                            <option value="Male" <?php echo (isset($gender) && $gender == 'Male') ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo (isset($gender) && $gender == 'Female') ? 'selected' : ''; ?>>Female</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="form-group">
                        <label>Date of Registration </label>
                        <input type="text" class="form-control" id="dateOfRegistration" name="dateOfRegistration" placeholder="DD/MM/YYYY" value="<?php echo htmlspecialchars($todayDate ?? ''); ?>" required>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="form-group">
                        <label>Date of Issuance </label>
                        <input type="text" class="form-control" name="dateOfIssuance" id="dateOfIssuance" placeholder="DD/MM/YYYY" value="<?php echo htmlspecialchars($todayDate ?? ''); ?>" required>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="form-group">
                        <label>Left Bar Code</label><br>
                        <input type="checkbox" id="barCodeInput" name="barCode" value="<?php echo htmlspecialchars($barCode ?? ''); ?>" onchange="handleBarcodeInputChange()"> <label for="barCodeInput">Input</label>
                        <input type="checkbox" id="barCode1" name="barCode" value="DZIH" onclick="autoSelect(1)"> <label for="barCode1">1</label>
                        <input type="checkbox" id="barCode2" name="barCode" value="DMIQ" onclick="autoSelect(2)"> <label for="barCode2">2</label>
                        <input type="checkbox" id="barCode3" name="barCode" value="AEMA" onclick="autoSelect(3)"> <label for="barCode3">3</label>
                        <input type="checkbox" id="barCode4" name="barCode" value="RQHQ" onclick="autoSelect(4)"> <label for="barCode4">4</label>
                        <input type="checkbox" id="barCode5" name="barCode" value="IRIA" onclick="autoSelect(5)"> <label for="barCode5">5</label>
                        <input type="checkbox" id="barCode6" name="barCode" value="ERRD" onclick="autoSelect(6)"> <label for="barCode6">6</label>
                        <input type="text" id="barcodeInputField" name="barcodeInputField" class="form-control" readonly>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="form-group">
                        <label>QR Link</label><br>
                        <input type="checkbox" id="qrLinkInput" name="qrLink" value="<?php echo htmlspecialchars($qrLink ?? ''); ?>" onchange="handleQrLinkInputChange()"> <label for="qrLinkInput">Input</label>
                        <input type="checkbox" id="qrLink1" name="qrLink" value="https://bdris.gov.bd/certificate/verify?key=Y2p7chu0b0iI8aWymonnhfkpKoOKE3J5kRsiGb0ZVOe0SqgyoCnMtFT+wx+Ikn/O" onclick="autoSelect(1)"> <label for="qrLink1">1</label>
                        <input type="checkbox" id="qrLink2" name="qrLink" value="https://bdris.gov.bd/certificate/verify?key=5gsdKUqk6MwdcGXYQrJJ3oFDeWtXYcK8sjPw9HTPyimw/CAb4Mia41mwDYBdwU/z" onclick="autoSelect(2)"> <label for="qrLink2">2</label>
                        <input type="checkbox" id="qrLink3" name="qrLink" value="https://bdris.gov.bd/certificate/verify?key=NstWvr0h0rOGf84T/VVLyG/k4N0SH4MULThJN9NKQzrcZXdnNjfrHy3f6R3cKkcP" onclick="autoSelect(3)"> <label for="qrLink3">3</label>
                        <input type="checkbox" id="qrLink4" name="qrLink" value="https://bdris.gov.bd/certificate/verify?key=KU32mvimW7KmtS/jr0hNN1VZHRwISZmcLnrzJYmadhEkpmcxFDEqLIy0exp3OVbG" onclick="autoSelect(4)"> <label for="qrLink4">4</label>
                        <input type="checkbox" id="qrLink5" name="qrLink" value="https://bdris.gov.bd/certificate/verify?key=znKwFj1Z1NAmN1Pgt4f/wcjTogYsu7aOytY8p2pk3OvTRDJkegjZjefRlv1GgJ+u" onclick="autoSelect(5)"> <label for="qrLink5">5</label>
                        <input type="checkbox" id="qrLink6" name="qrLink" value="https://bdris.gov.bd/certificate/verify?key=S/m6tkNVXq24lDXol5XRdF6zC/FRDZq327Fk7Ur9UJX94kJHLkB5tA2nX83QsVYF" onclick="autoSelect(6)"> <label for="qrLink6">6</label>
                        <input type="text" id="qrLinkInputField" name="qrLinkInputField" class="form-control" readonly>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="form-group">
                        <label>Date of Birth </label>
                        <input type="text" class="form-control" name="dateOfBirth" id="dateOfBirth" placeholder="DD/MM/YYYY" value="<?php echo htmlspecialchars($dob ?? ''); ?>" required>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="form-group">
                        <label>Date of Birth in Word</label>
                        <input type="text" class="form-control" id="dateOfBirthText" name="dateOfBirthText" placeholder="Eleventh August two thousand three" value="<?php echo htmlspecialchars($dobInWord ?? ''); ?>" required>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="form-group">
                        <label>নাম </label>
                        <input type="text" name="nameBangla" id="nameBangla" class="form-control" placeholder="সম্পূর্ণ নাম বাংলা" value="<?php echo htmlspecialchars($nameBn ?? ''); ?>" required>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" id="nameEnglish" name="nameEnglish" class="form-control" placeholder="Full Name in English" value="<?php echo htmlspecialchars($nameEn ?? ''); ?>">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="form-group">
                        <label>পিতার নাম </label>
                        <input type="text" id="fatherNameBangla" name="fatherNameBangla" class="form-control" placeholder="পিতার নাম বাংলায়" value="<?php echo htmlspecialchars($fatherName ?? ''); ?>" required>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="form-group">
                        <label>Father Name</label>
                        <input type="text" id="fatherNameEnglish" name="fatherNameEnglish" class="form-control" placeholder="Father Name in English" value="<?php echo htmlspecialchars($fatherNameEn ?? ''); ?>">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="form-group">
                        <label>পিতার জাতীয়তা</label>
                        <input type="text" class="form-control" name="fatherNationalityBangla" id="fatherNationalityBangla" placeholder="পিতার জাতীয়তা বাংলায়" value="বাংলাদেশী">
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="form-group">
                        <label>Father Nationality</label>
                        <input type="text" class="form-control" id="fatherNationalityEnglish" name="fatherNationalityEnglish" placeholder="Father Nationality in English" value="Bangladeshi">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="form-group">
                        <label>মাতার নাম </label>
                        <input type="text" id="motherNameBangla" name="motherNameBangla" class="form-control" placeholder="মাতার নাম বাংলায়" value="<?php echo htmlspecialchars($motherName ?? ''); ?>" required>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="form-group">
                        <label>Mother Name</label>
                        <input type="text" id="motherNameEnglish" name="motherNameEnglish" class="form-control" placeholder="Mother Name in English" value="<?php echo htmlspecialchars($motherNameEn ?? ''); ?>">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="form-group">
                        <label>মাতার জাতীয়তা</label>
                        <input type="text" class="form-control" id="motherNationalityBangla" name="motherNationalityBangla" placeholder="মাতার জাতীয়তা বাংলায়" value="বাংলাদেশী">
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="form-group">
                        <label>Mother Nationality</label>
                        <input type="text" class="form-control" id="motherNationalityEnglish" name="motherNationalityEnglish" placeholder="Mother Nationality in English" value="Bangladeshi">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="form-group">
                        <label>জন্মস্থান </label>
                        <input type="text" class="form-control" id="birthplaceBangla" name="birthplaceBangla" placeholder="জন্মস্থান বাংলায়" value="বাংলাদেশ">
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="form-group">
                        <label>Place of Birth</label>
                        <input type="text" class="form-control" id="birthplaceEnglish" name="birthplaceEnglish" placeholder="Place of Birth in English" value="Bangladesh">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="form-group">
                        <label>স্থায়ী ঠিকানা </label>
                        <input type="text" class="form-control" id="permanentAddressBangla" name="permanentAddressBangla" placeholder="স্থায়ী ঠিকানা বাংলায়" value="<?php echo htmlspecialchars($permanent_address ?? ''); ?>">
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="form-group">
                        <label>Permanent Address</label>
                        <input type="text" class="form-control" id="permanentAddressEnglish" name="permanentAddressEnglish" placeholder="Permanent Address in English" value="<?php echo htmlspecialchars($permanent_address_en ?? ''); ?>">
                    </div>
                </div>
            </div>
        
            <button type="submit" class="btn btn-primary text-center d-block mx-auto" style="background: var(--brand-green); border-color: var(--brand-green);">ডাউনলোড করুন</button>
      </form>
    </div>
  </div>
</div>

<div class="mobile-bottom-nav">
    <a href="dashboard.php" class="m-nav-link">
        <i class="fa-solid fa-house"></i>
        <span>হোম</span>
    </a>

    <a href="dashboard.php#all_services" class="m-nav-link active">
        <i class="fa-solid fa-shapes"></i>
        <span>সেবা</span>
    </a>

    <div class="m-nav-center">
        <a href="recharge.php" class="m-nav-center-btn" title="রিচার্জ করুন">
            <i class="fa-solid fa-bolt" style="color: #ffdd44;"></i>
        </a>
    </div>

    <a href="work_history.php" class="m-nav-link">
        <i class="fa-solid fa-wallet"></i>
        <span>হিস্টোরি</span>
    </a>

    <a href="profile.php" class="m-nav-link">
        <i class="fa-solid fa-user"></i>
        <span>প্রোফাইল</span>
    </a>
</div>

<?php include('footer.php'); ?>

<script>
$('#autoloadform').on('submit', function(event) {
    event.preventDefault();
    var brn = $('#brn').val();
    var dob = $('#dob').val();
    var requestUrl = 'insert_brn_info.php';

    Swal.fire({
        title: 'Are you sure?',
        html: `আপনি BRN: ${'<b>' + brn} Dob: ${dob + '</b>'} সবমিট করতে চান?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, submit it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $("#loddingPdf").css("display", "block");

            $.ajax({
                url: requestUrl,
                type: 'POST',
                data: { brn: brn, dob: dob },
                dataType: 'json',
                success: function(response) {
                    $("#loddingPdf").css("display", "none");

                    if (response.status === 'success') {
                        var data = response.data;

                        $('#nameBangla').val(data.name || '');
                        $('#nameEnglish').val(data.nameEn || '');
                        $('#fatherNameBangla').val(data.fatherName || '');
                        $('#fatherNameEnglish').val(data.fatherNameEn || '');
                        $('#motherNameBangla').val(data.motherName || '');
                        $('#motherNameEnglish').val(data.motherNameEn || '');
                        $('#birthRegistrationNumber').val(data.brnNo || '');
                        $('#dateOfBirth').val(data.dateOfBirth || '');
                        $('#dateOfBirthText').val(data.dateOfBirthEn || '');
                        $('#todayDate').val(data.dateOfToday || '');
                        $('#gender').val(data.gender || '');
                        $('#officeAddressFirst').val(data.registerOffice || '');
                        $('#officeAddressSecond').val(data.registerOfficeLocation || '');
                        $('#birthplaceBangla').val(data.birthPlace || '');
                        $('#birthplaceEnglish').val(data.birthPlaceEn || '');
                        $('#permanentAddressBangla').val(data.address || '');
                        $('#permanentAddressEnglish').val(data.addressEn || '');

                        $('#BrnBN').css("display", "block");

                        $('html, body').animate({
                            scrollTop: $("#BrnBN").offset().top
                        }, 1000);
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function() {
                    $("#loddingPdf").css("display", "none");
                    Swal.fire('Error', "An error occurred while sending the request", 'error');
                }
            });
        }
    });
});

const checkboxes = document.getElementsByName('barCode');
document.getElementById('BrnBN').addEventListener('submit', function (e) {
    e.preventDefault(); 
    let isChecked = false;
    for(let i = 0; i < checkboxes.length; i++){
        if(checkboxes[i].checked){
            isChecked = true;
            break;
        }
    }
    if(!isChecked){
        alert('Select a Left Bar Code or QR Link');
    }else{
        this.submit();
    }
});

function handleBarcodeInputChange() {
    var barcodeInputCheckbox = document.getElementById('barCodeInput');
    var barcodeInputField = document.getElementById('barcodeInputField');
    if (barcodeInputCheckbox.checked) {
        barcodeInputField.value = "";
        barcodeInputField.readOnly = false;
        uncheckBarcodeCheckboxes(barcodeInputCheckbox);
    } else {
        barcodeInputField.value = "";
        barcodeInputField.readOnly = true;
    }
}

function handleBarcodeCheckChange(checkbox) {
    if (checkbox.checked) {
        document.getElementById('barCodeInput').checked = false;
        document.getElementById('barcodeInputField').value = checkbox.value;
        document.getElementById('barcodeInputField').readOnly = true;
        uncheckOtherBarcodeCheckboxes(checkbox);
    }
}

function uncheckBarcodeCheckboxes(checkboxToKeep) {
    var checkboxes = document.getElementsByName('barCode');
    checkboxes.forEach(function(checkbox) {
        if (checkbox !== checkboxToKeep) {
            checkbox.checked = false;
        }
    });
}

function uncheckOtherBarcodeCheckboxes(checkboxToKeep) {
    var checkboxes = document.getElementsByName('barCode');
    checkboxes.forEach(function(checkbox) {
        if (checkbox !== checkboxToKeep && checkbox.checked) {
            checkbox.checked = false;
        }
    });
}

function handleQrLinkInputChange() {
    var qrLinkInputCheckbox = document.getElementById('qrLinkInput');
    var qrLinkInputField = document.getElementById('qrLinkInputField');
    if (qrLinkInputCheckbox.checked) {
        qrLinkInputField.value = "";
        qrLinkInputField.readOnly = false;
        uncheckQrLinkCheckboxes(qrLinkInputCheckbox);
    } else {
        qrLinkInputField.value = "";
        qrLinkInputField.readOnly = true;
    }
}

function handleQrLinkCheckChange(checkbox) {
    if (checkbox.checked) {
        document.getElementById('qrLinkInput').checked = false;
        document.getElementById('qrLinkInputField').value = checkbox.value;
        document.getElementById('qrLinkInputField').readOnly = true;
        uncheckOtherQrLinkCheckboxes(checkbox);
    }
}

function uncheckQrLinkCheckboxes(checkboxToKeep) {
    var checkboxes = document.getElementsByName('qrLink');
    checkboxes.forEach(function(checkbox) {
        if (checkbox !== checkboxToKeep) {
            checkbox.checked = false;
        }
    });
}

function uncheckOtherQrLinkCheckboxes(checkboxToKeep) {
    var checkboxes = document.getElementsByName('qrLink');
    checkboxes.forEach(function(checkbox) {
        if (checkbox !== checkboxToKeep && checkbox.checked) {
            checkbox.checked = false;
        }
    });
}

function autoSelect(value) {
    var barcodeCheckbox = document.getElementById('barCode' + value);
    if (barcodeCheckbox) {
        barcodeCheckbox.checked = true;
        handleBarcodeCheckChange(barcodeCheckbox);
    }
    
    var qrLinkCheckbox = document.getElementById('qrLink' + value);
    if (qrLinkCheckbox) {
        qrLinkCheckbox.checked = true;
        handleQrLinkCheckChange(qrLinkCheckbox);
    }
}
</script>