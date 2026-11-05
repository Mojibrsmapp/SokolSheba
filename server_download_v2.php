<?php
// server_download_v2.php  (V2 এর জন্য)
session_start();
error_reporting(0);

if (strlen($_SESSION['user_id']) == 0) {
    header('location:index.php');
    exit;
}

if (!isset($_GET["nid"]) || !isset($_GET["id"])) {
    echo "NID or ID not set.";
    exit;
}

$nid = $_GET["nid"];
$id  = $_GET["id"];

include_once("includes/configuration.php");

/* ================= Fetch Data ================= */

$sql = "SELECT * FROM server_copy WHERE nationalId = ? AND id = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("Prepare failed: " . htmlspecialchars($conn->error));
}

$stmt->bind_param("si", $nid, $id);
$stmt->execute();
$result = $stmt->get_result();

$data = null;
if ($result->num_rows > 0) {
    $data = $result->fetch_assoc();
} else {
    echo "No records found for the given NID and ID.";
    exit;
}

$stmt->close();
$conn->close();

/* ================= Extract Variables ================= */

$nameBn             = $data['nameBn']              ?? '';
$nameEn             = $data['nameEn']              ?? '';
$nid                = $data['nationalId']          ?? '';
$pin                = $data['pin']                 ?? '';
$sl_no              = $data['sl_no']               ?? '';
$voter_aria_code    = $data['voter_aria_code']     ?? '';
$voter_no           = $data['voter_no']            ?? '';
$dob                = $data['dateOfBirth']         ?? '';
$father             = $data['father']              ?? '';
$mother             = $data['mother']              ?? '';
$spouse             = $data['spouse']              ?? '';
$gender             = $data['gender']              ?? '';
$photoPath          = $data['photo']               ?? '';
$bloodGroup         = $data['bloodGroup']          ?? '';
$permanentaddress   = $data['permanentAddressLine'] ?? '';
$presentaddress     = $data['presentAddressLine']   ?? '';
$occupation         = $data['occupation']          ?? '';
$birthPlace         = $data['birthPlace']          ?? '';
$religion           = $data['religion']            ?? '';
$signPath           = $data['sign_path']           ?? '';

/* ================= Photo Local Save ================= */

$displayPhoto = 'assets/images/no-photo.png';

if (!empty($photoPath)) {
    if (preg_match('/^https?:\/\//', $photoPath)) {
        $localDir = 'uploads/photos/';
        if (!is_dir($localDir)) {
            @mkdir($localDir, 0777, true);
        }
        $localFile = $localDir . 'nid_' . preg_replace('/[^0-9]/', '', $nid) . '.jpg';

        if (!file_exists($localFile) || filesize($localFile) < 1000) {
            $imgCh = curl_init($photoPath);
            curl_setopt_array($imgCh, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT        => 20,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_USERAGENT      => 'Mozilla/5.0'
            ]);
            $imgData  = curl_exec($imgCh);
            $httpCode = curl_getinfo($imgCh, CURLINFO_HTTP_CODE);
            curl_close($imgCh);

            if ($imgData !== false && $httpCode === 200 && strlen($imgData) > 1000) {
                file_put_contents($localFile, $imgData);
                $displayPhoto = $localFile;
            } else {
                $displayPhoto = $photoPath;
            }
        } else {
            $displayPhoto = $localFile;
        }
    } else {
        $displayPhoto = $photoPath;
    }
}

/* ================= Signature Local Save ================= */

$displaySign = '';
if (!empty($signPath)) {
    if (preg_match('/^https?:\/\//', $signPath)) {
        $localDirS = 'uploads/signs/';
        if (!is_dir($localDirS)) {
            @mkdir($localDirS, 0777, true);
        }
        $localSign = $localDirS . 'sign_' . preg_replace('/[^0-9]/', '', $nid) . '.jpg';

        if (!file_exists($localSign) || filesize($localSign) < 500) {
            $sCh = curl_init($signPath);
            curl_setopt_array($sCh, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT        => 20,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_USERAGENT      => 'Mozilla/5.0'
            ]);
            $sData = curl_exec($sCh);
            $sCode = curl_getinfo($sCh, CURLINFO_HTTP_CODE);
            curl_close($sCh);

            if ($sData !== false && $sCode === 200 && strlen($sData) > 500) {
                file_put_contents($localSign, $sData);
                $displaySign = $localSign;
            } else {
                $displaySign = $signPath;
            }
        } else {
            $displaySign = $localSign;
        }
    } else {
        $displaySign = $signPath;
    }
}

/* ================= Force V2 ================= */
$s_v = "V2";
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo "V2_" . htmlspecialchars($nameEn); ?></title>
    <meta name="viewport" content="initial-scale=1.0, width=device-width"/>
    <link rel="stylesheet" href="assets/css/serverCopy.css"/>
    <meta property="og:image" content="">
    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
    <link href="https://fonts.maateen.me/solaiman-lipi/font.css" rel="stylesheet">
    <meta charSet="utf-8"/>
    <style>
        @media print {
            html, body {
                height: 100%;
                margin: 0 !important;
                padding: 0 !important;
                overflow: hidden;
            }
        }
        img#photo {
            box-shadow: 1px 1px 5px 0px #858585;
        }
    </style>
</head>
<body oncontextmenu="return false;" style="text-align: center;">
    <div class="background">
        <img class="crane" src="assets/images/new-min-one.jpg" height="4725px" width="3126px">

        <div style="position: absolute; left: 30%; top: 8%; width: auto; font-size: 16px; color: rgb(255 224 0);"><b>National Identity Registration Wing (NIDW)</b></div>
        <div style="position: absolute; left: 37%; top: 11%; width: auto; font-size: 14px; color: rgb(255, 47, 161);"><b>Select Your Search Category</b></div>
        <div style="position: absolute; left: 45%; top: 12.8%; width: auto; font-size: 12px; color: rgb(8, 121, 4);">Search By NID / Voter No.</div>
        <div style="position: absolute; left: 45%; top: 14.3%; width: auto; font-size: 12px; color: rgb(7, 119, 184);">Search By Form No.</div>
        <div style="position: absolute; left: 30%; top: 16.9%; width: auto; font-size: 12px; color: rgb(252, 0, 0);"><b>NID or Voter No*</b></div>
        <div style="position: absolute; left: 45%; top: 16.9%; width: auto; font-size: 12px; color: rgb(143, 143, 143);">NID</div>
        <div style="position: absolute; left: 62.9%; top: 17.1%; width: auto; font-size: 11px; color: rgb(255 255 255);">Submit</div>
        <div style="position: absolute; left: 89%; top: 11.55%; width: auto; font-size: 11px; color: #fff;">Home</div>

        <div style="position: absolute; left: 37.5%; top: 27.2%; width: auto; font-size: 16px; color: rgb(7, 7, 7); font-family: 'SolaimanLipi', sans-serif;"><b>জাতীয় পরিচিতি তথ্য</b></div>

        <div style="position: absolute; left: 37.3%; top: 30%; width: auto; font-size: 13px; color: rgb(7, 7, 7); font-family: 'SolaimanLipi', sans-serif;">জাতীয় পরিচয় পত্র নম্বর</div>
        <div id="nid_no" style="position: absolute; left: 55%; top: 30%; width: auto; font-size: 14px; color: rgb(7, 7, 7);"><?php echo htmlspecialchars($nid); ?></div>

        <div style="position: absolute; left: 37.3%; top: 32.8%; width: auto; font-size: 13px; color: rgb(7, 7, 7); font-family: 'SolaimanLipi', sans-serif;"><?php echo ($s_v == "V2") ? "পিন নম্বর" : "পিন নম্বর"; ?></div>
        <div id="nid_father" style="position: absolute; left: 55%; top: 32.8%; width: auto; font-size: 14px; color: rgb(7, 7, 7);"><?php echo htmlspecialchars($pin); ?></div>

        <div style="position: absolute; left: 37.3%; top: 35.3%; width: auto; font-size: 13px; color: rgb(7, 7, 7); font-family: 'SolaimanLipi', sans-serif;"><?php echo ($s_v == "V2") ? "ভোটার নম্বর" : "ভোটার নম্বর"; ?></div>
        <div id="nid_mother" style="position: absolute; left: 55%; top: 35.3%; width: auto; font-size: 14px; color: rgb(7, 7, 7);"><?php echo ($s_v == "V2") ? htmlspecialchars($sl_no) : htmlspecialchars($voter_no); ?></div>

        <div style="position: absolute; left: 37.3%; top: 37.8%; width: auto; font-size: 14px; color: rgb(7, 7, 7); font-family: 'SolaimanLipi', sans-serif;"><?php echo ($s_v == "V2") ? "সিরিয়াল নম্বর" : "সিরিয়াল নম্বর"; ?></div>
        <div id="spouse" style="position: absolute; left: 55%; top: 37.8%; width: auto; font-size: 14px; color: rgb(7, 7, 7); font-family: 'SolaimanLipi', sans-serif;"><?php echo ($s_v == "V2") ? htmlspecialchars($voter_no) : htmlspecialchars($sl_no); ?></div>

        <div style="position: absolute; left: 37.3%; top: 40.5%; width: auto; font-size: 14px; color: rgb(7, 7, 7); font-family: 'SolaimanLipi', sans-serif;"> ভোটার এলাকা </div>
        <div id="voter_area" style="position: absolute; left: 55%; top: 40.5%; width: auto; font-size: 14px; color: rgb(7, 7, 7); font-family: 'SolaimanLipi', sans-serif;"><?php echo htmlspecialchars($voter_aria_code); ?></div>

        <div style="position: absolute; left: 37.5%; top: 43.3%; width: auto; font-size: 16px; color: rgb(7, 7, 7); font-family: 'SolaimanLipi', sans-serif;"><b>ব্যক্তিগত তথ্য</b></div>

        <div style="position: absolute; left: 37.3%; top: 46%; width: auto; font-size: 14px; color: rgb(7, 7, 7); font-family: 'SolaimanLipi', sans-serif;">নাম (বাংলা)</div>
        <div id="name_bn" style="position: absolute; font-weight: bold; left: 55%; top: 46%; width: auto; font-size: 14px; color: rgb(7, 7, 7); font-family: 'SolaimanLipi', sans-serif;"><b><?php echo htmlspecialchars($nameBn); ?></b></div>

        <div style="position: absolute; left: 37.3%; top: 48.8%; width: auto; font-size: 14px; color: rgb(7, 7, 7); font-family: 'SolaimanLipi', sans-serif;">নাম (ইংরেজি)</div>
        <div id="name_en" style="position: absolute; left: 55%; top: 48.8%; width: auto; font-size: 14px; color: rgb(7, 7, 7);"><?php echo htmlspecialchars($nameEn); ?></div>

        <div style="position: absolute; left: 37.3%; top: 51.5%; width: auto; font-size: 14px; color: rgb(7, 7, 7); font-family: 'SolaimanLipi', sans-serif;">জন্ম তারিখ</div>
        <div id="dob" style="position: absolute; left: 55%; top: 51.5%; width: auto; font-size: 14px; color: rgb(7, 7, 7);"><?php echo htmlspecialchars($dob); ?></div>

        <div style="position: absolute; left: 37.3%; top: 54.1%; width: auto; font-size: 14px; color: rgb(7, 7, 7); font-family: 'SolaimanLipi', sans-serif;">পিতার নাম</div>
        <div id="fathers_name" style="position: absolute; left: 55%; top: 54.1%; width: auto; font-size: 14px; color: rgb(7, 7, 7); font-family: 'SolaimanLipi', sans-serif;"><?php echo htmlspecialchars($father); ?></div>

        <div style="position: absolute; left: 37.3%; top: 56.7%; width: auto; font-size: 14px; color: rgb(7, 7, 7); font-family: 'SolaimanLipi', sans-serif;">মাতার নাম</div>
        <div id="mothers_name" style="position: absolute; left: 55%; top: 56.7%; width: auto; font-size: 14px; color: rgb(7, 7, 7); font-family: 'SolaimanLipi', sans-serif;"><?php echo htmlspecialchars($mother); ?></div>

        <!-- স্বামী/স্ত্রীর নাম ফাঁকা -->
        <div style="position: absolute; left: 37.3%; top: 59.2%; width: auto; font-size: 14px; color: rgb(7, 7, 7); font-family: 'SolaimanLipi', sans-serif;"></div>
        <div id="spouse_name" style="position: absolute; left: 55%; top: 59.2%; width: auto; font-size: 14px; color: rgb(7, 7, 7); font-family: 'SolaimanLipi', sans-serif;"></div>

        <div style="position: absolute; left: 37.5%; top: 62%; width: auto; font-size: 16px; color: rgb(7, 7, 7); font-family: 'SolaimanLipi', sans-serif;"><b>অন্যান্য তথ্য</b></div>

        <!-- জাতীয়তা -->
        <div style="position: absolute; left: 37.3%; top: 65.2%; width: auto; font-size: 14px; color: rgb(7, 7, 7); font-family: 'SolaimanLipi', sans-serif;">জাতীয়তা</div>
        <div id="nationality" style="position: absolute; left: 55%; top: 65.2%; width: auto; font-size: 14px; color: rgb(7, 7, 7); font-family: 'SolaimanLipi', sans-serif;">বাংলাদেশী</div>

        <div style="position: absolute; left: 37.3%; top: 68%; width: auto; font-size: 14px; color: rgb(7, 7, 7); font-family: 'SolaimanLipi', sans-serif;">লিঙ্গ </div>
        <div id="gender" style="position: absolute; left: 55%; top: 68%; width: auto; font-size: 14px; color: rgb(7, 7, 7);"><?php echo htmlspecialchars($gender); ?></div>

        <!-- জন্মস্থান ekhon uporer ghore boshano holo -->
        <div style="position: absolute; left: 37.3%; top: 70.7%; width: auto; font-size: 14px; color: rgb(7, 7, 7); font-family: 'SolaimanLipi', sans-serif;">জন্মস্থান</div>
        <div id="birth_place" style="position: absolute; left: 55%; top: 70.7%; width: auto; font-size: 14px; color: rgb(7, 7, 7); font-family: 'SolaimanLipi', sans-serif;"><?php echo htmlspecialchars($birthPlace); ?></div>

        <!-- nicher ghor ti khali kora holo -->
        <div style="position: absolute; left: 37.3%; top: 73.2%; width: auto; font-size: 14px; color: rgb(7, 7, 7); font-family: 'SolaimanLipi', sans-serif;"></div>
        <div id="empty_row" style="position: absolute; left: 55%; top: 73.2%; width: auto; font-size: 14px; color: rgb(7, 7, 7); font-family: 'SolaimanLipi', sans-serif;"></div>

        <div style="font-family: 'SolaimanLipi'; position: absolute; left: 37.5%; top: 75.8%; width: auto; font-size: 16px; color: rgb(7, 7, 7);"><b>বর্তমান ঠিকানা</b></div>
        <div id="present_addr" style="font-family: 'SolaimanLipi'; position: absolute; left: 37%; top: 78.2%; width: 48%; font-size: 12.5px; color: rgb(7, 7, 7);"><?php echo htmlspecialchars($presentaddress); ?></div>

        <div style="font-family: 'SolaimanLipi'; position: absolute; left: 37.5%; top: 86.5%; width: auto; font-size: 16px; color: rgb(7, 7, 7);"><b>স্থায়ী ঠিকানা</b></div>
        <div id="permanent_addr" style="font-family: 'SolaimanLipi'; position: absolute; left: 37%; top: 88.7%; width: 48%; font-size: 12.5px; color: rgb(7, 7, 7);"><?php echo htmlspecialchars($permanentaddress); ?></div>

        <div style="position: absolute; top: 95%; width: 100%; font-size: 12px; text-align: center; color: rgb(255, 0, 0); font-family: 'SolaimanLipi', sans-serif;">উপরে প্রদর্শিত তথ্যসমূহ জাতীয় পরিচয়পত্র সংশ্লিষ্ট, ভোটার তালিকার সাথে সরাসরি সম্পর্কযুক্ত নয়।</div>
        <div style="position: absolute; top: 96.5%; width: 100%; text-align: center; font-size: 12px; color: rgb(3, 3, 3);">This is Software Generated Report From Bangladesh Election Commission, Signature &amp; Seal Aren't Required.</div>

        <!-- ===== PHOTO ===== -->
        <div style="position: absolute; left: 16%; top: 25.7%; width: auto; font-size: 12px; color: rgb(3, 3, 3);">
            <img id="photo"
                 src="<?php echo htmlspecialchars($displayPhoto); ?>"
                 referrerpolicy="no-referrer"
                 onerror="this.onerror=null; this.src='assets/images/no-photo.png';"
                 height="140px" width="121px"
                 style="border-radius: 10px"/>
        </div>

        <!-- ===== NAME (ছবির নিচে, আরও নিচে) ===== -->
        <div id="name_en2"
             style="position: absolute;
                    left: 16%;
                    top: 40%;
                    width: 121px;
                    height: 16px;
                    font-size: 8px;
                    color: rgb(7, 7, 7);
                    font-weight: bold;
                    background: #ffffff;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    text-align: center;
                    overflow: hidden;
                    line-height: 1;
                    white-space: nowrap;
                    z-index: 3;">
            <?php echo htmlspecialchars($nameEn); ?>
        </div>

        <!-- ===== QR CODE (নামের নিচে) ===== -->
        <div style="position: absolute; left: 16%; top: 42.2%; width: 121px; text-align: center; z-index: 1;">
            <img id="qr"
                 src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&amp;data=<?php echo urlencode($nameEn . " " . $nid . " " . $dob); ?>"
                 height="90px" width="90px" />
        </div>

        <!-- ===== SIGNATURE (QR এর নিচে) ===== -->
        <?php if (!empty($displaySign)) { ?>
        <div style="position: absolute; left: 16%; top: 48.2%; width: 121px; height: 40px; text-align: center; z-index: 2;">
            <img id="sign"
                 src="<?php echo htmlspecialchars($displaySign); ?>"
                 referrerpolicy="no-referrer"
                 onerror="this.style.display='none';"
                 style="max-height: 40px; max-width: 121px; display: inline-block;" />
        </div>
        <?php } ?>

    </div>
    <script>
        window.onload = function () {
            setTimeout(wp, 500);
        };
        function wp() {
            window.print();
        }
    </script>
</body>
</html>