<?php
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

$sql = "SELECT * FROM server_copy WHERE nationalId = ? AND id = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) { die("Prepare failed: " . htmlspecialchars($conn->error)); }
$stmt->bind_param("si", $nid, $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $data = $result->fetch_assoc();
} else {
    echo "No records found for the given NID and ID.";
    exit;
}
$stmt->close();
$conn->close();

/* ============ Extract (DB Column names) ============ */
$nameBn           = $data['nameBn']              ?? '';
$nameEn           = $data['nameEn']              ?? '';
$nid              = $data['nationalId']          ?? '';
$pin              = $data['pin']                 ?? '';
$sl_no            = $data['sl_no']               ?? '';
$voter_aria_code  = $data['voter_aria_code']     ?? '';  // এটাই এখন voterArea
$voter_no         = $data['voter_no']            ?? '';
$dob              = $data['dateOfBirth']         ?? '';
$father           = $data['father']              ?? '';
$mother           = $data['mother']              ?? '';
$gender           = $data['gender']              ?? '';
$bloodGroup       = $data['bloodGroup']          ?? '';
$permanentaddress = $data['permanentAddressLine']?? '';
$presentaddress   = $data['presentAddressLine']  ?? '';
$occupation       = $data['occupation1']          ?? '';
$birthPlace       = $data['birthPlace']          ?? '';
$religion         = $data['religion']            ?? '';
$signPath         = $data['sign_path']           ?? '';
$photoPath        = $data['photo']               ?? '';

/* ============ Photo Local Save ============ */
$displayPhoto = 'assets/images/no-photo.png';
if (!empty($photoPath)) {
    if (preg_match('/^https?:\/\//i', $photoPath)) {
        $localDir = 'uploads/photos/';
        if (!is_dir($localDir)) @mkdir($localDir, 0777, true);

        $cleanNid  = preg_replace('/[^0-9]/', '', $nid);
        $localFile = $localDir . 'nid_' . $cleanNid . '.jpg';

        if (file_exists($localFile) && filesize($localFile) > 1000) {
            $displayPhoto = $localFile;
        } else {
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
        }
    } else {
        $displayPhoto = $photoPath;
    }
}

/* ============ V1 / V2 Detection ============ */
$s_v = "V1";
if (!is_numeric($voter_no)) { $s_v = "V2"; }
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo "V1_" . htmlspecialchars($nameEn); ?></title>
    <meta name="viewport" content="initial-scale=1.0, width=device-width"/>
    <link rel="stylesheet" href="assets/css/serverCopy.css"/>
    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
    <link href="https://fonts.maateen.me/solaiman-lipi/font.css" rel="stylesheet">
    <meta charSet="utf-8"/>
    <style>
        @media print {
            html, body { height: 100%; margin: 0 !important; padding: 0 !important; overflow: hidden; }
        }
        img#photo { box-shadow: 1px 1px 5px 0px #858585; }
    </style>
</head>
<body oncontextmenu="return false;" style="text-align: center;">
    <div class="background">
        <img class="crane" src="assets/images/new_server_one.jpg" height="4725px" width="3126px">
        <div style="border-top: 1px solid #858585; opacity: 0.5; top: 168px; left: 33px; width: 89%; position: absolute;"></div>

        <div style="position: absolute; left: 43%; top: 9%; font-size: 14px; color: black; font-family: 'SolaimanLipi', sans-serif;"><b>বাংলাদেশ নির্বাচন কমিশন</b></div>
        <div style="position: absolute; left: 43.2%; top: 11.3%; font-size: 14px; color: black; font-family: 'SolaimanLipi', sans-serif;"><b>নির্বাচন কমিশন সচিবালয়</b></div>
        <div style="position: absolute; left: 41.1%; top: 13.5%; font-size: 14px; color: black; font-family: 'SolaimanLipi', sans-serif;"><b>জাতীয় পরিচয় নিবন্ধন অনুবিভাগ</b></div>

        <div style="position: absolute; left: 14.3%; top: 33%; font-size: 15px; color: rgb(7,7,7); font-family: 'SolaimanLipi', sans-serif;"><b>জাতীয় পরিচিতি তথ্য</b></div>

        <div style="position: absolute; left: 14.3%; top: 35.6%; font-size: 14px; font-family: 'SolaimanLipi', sans-serif;">জাতীয় পরিচয় পত্র নম্বর</div>
        <div id="nid_no" style="position: absolute; left: 33.5%; top: 35.6%; font-size: 14px;"><?php echo htmlspecialchars($nid); ?></div>

        <div style="position: absolute; left: 14.3%; top: 38%; font-size: 14px; font-family: 'SolaimanLipi', sans-serif;"><?php echo ($s_v=="V2") ? "পিতার এনআইডি" : "পিন নম্বর"; ?></div>
        <div id="nid_father" style="position: absolute; left: 33.5%; top: 38%; font-size: 14px;"><?php echo htmlspecialchars($pin); ?></div>

        <div style="position: absolute; left: 14.3%; top: 40.5%; font-size: 14px; font-family: 'SolaimanLipi', sans-serif;"><?php echo ($s_v=="V2") ? "মাতার এনআইডি" : "ভোটার নম্বর"; ?></div>
        <div id="nid_mother" style="position: absolute; left: 33.5%; top: 40.5%; font-size: 14px;">
            <?php echo ($s_v=="V2") ? htmlspecialchars($sl_no) : htmlspecialchars($voter_no); ?>
        </div>

        <div style="position: absolute; left: 14.3%; top: 43%; font-size: 14px; font-family: 'SolaimanLipi', sans-serif;"><?php echo ($s_v=="V2") ? "ভোটার এলাকা" : "সিরিয়াল নম্বর"; ?></div>
        <div id="spouse" style="position: absolute; left: 33.5%; top: 43%; font-size: 14px; font-family: 'SolaimanLipi', sans-serif;">
            <?php echo ($s_v=="V2") ? htmlspecialchars($voter_aria_code) : htmlspecialchars($sl_no); ?>
        </div>

        <!-- ✅ এরিয়া কোড → ভোটার এলাকা -->
        <div style="position: absolute; left: 14.3%; top: 45.5%; font-size: 14px; font-family: 'SolaimanLipi', sans-serif;">ভোটার এলাকা</div>
        <div id="voter_area" style="position: absolute; left: 33.5%; top: 45.5%; width: 60%; font-size: 14px; font-family: 'SolaimanLipi', sans-serif;"><?php echo htmlspecialchars($voter_aria_code); ?></div>

        <div style="position: absolute; left: 14.3%; top: 48.1%; font-size: 14px; font-family: 'SolaimanLipi', sans-serif;"><b>ব্যক্তিগত তথ্য</b></div>

        <div style="position: absolute; left: 14.3%; top: 50.5%; font-size: 14px; font-family: 'SolaimanLipi', sans-serif;">নাম (বাংলা)</div>
        <div id="name_bn" style="position: absolute; left: 33.5%; top: 50.5%; font-size: 14px; font-family: 'SolaimanLipi', sans-serif;"><b><?php echo htmlspecialchars($nameBn); ?></b></div>

        <div style="position: absolute; left: 14.3%; top: 53%; font-size: 14px; font-family: 'SolaimanLipi', sans-serif;">নাম (ইংরেজি)</div>
        <div id="name_en" style="position: absolute; left: 33.5%; top: 53%; font-size: 14px;"><?php echo htmlspecialchars($nameEn); ?></div>

        <div style="position: absolute; left: 14.3%; top: 55.6%; font-size: 14px; font-family: 'SolaimanLipi', sans-serif;">জন্ম তারিখ</div>
        <div id="dob" style="position: absolute; left: 33.5%; top: 55.6%; font-size: 14px; font-family: 'SolaimanLipi', sans-serif;"><?php echo htmlspecialchars($dob); ?></div>

        <div style="position: absolute; left: 14.3%; top: 58%; font-size: 14px; font-family: 'SolaimanLipi', sans-serif;">পিতার নাম</div>
        <div id="fathers_name" style="position: absolute; left: 33.5%; top: 58%; font-size: 14px; font-family: 'SolaimanLipi', sans-serif;"><?php echo htmlspecialchars($father); ?></div>

        <div style="position: absolute; left: 14.3%; top: 60.5%; font-size: 14px; font-family: 'SolaimanLipi', sans-serif;">মাতার নাম</div>
        <div id="mothers_name" style="position: absolute; left: 33.5%; top: 60.5%; font-size: 14px; font-family: 'SolaimanLipi', sans-serif;"><?php echo htmlspecialchars($mother); ?></div>

        <div style="position: absolute; left: 14.3%; top: 63%; font-size: 14px; font-family: 'SolaimanLipi', sans-serif;"><b>অন্যান্য তথ্য</b></div>

        <div style="position: absolute; left: 14.3%; top: 65.6%; font-size: 14px; font-family: 'SolaimanLipi', sans-serif;">লিঙ্গ</div>
        <div id="gender" style="position: absolute; left: 33.5%; top: 65.6%; font-size: 14px; font-family: 'SolaimanLipi', sans-serif;"><?php echo htmlspecialchars($gender); ?></div>

        <div style="position: absolute; left: 14.3%; top: 68%; font-size: 14px; font-family: 'SolaimanLipi', sans-serif;">পেশা</div>
        <div id="mobile_no" style="position: absolute; left: 33.5%; top: 68%; font-size: 14px; font-family: 'SolaimanLipi', sans-serif;"><?php echo htmlspecialchars($occupation1); ?></div>

        <div style="position: absolute; left: 14.3%; top: 70.4%; font-size: 14px; font-family: 'SolaimanLipi', sans-serif;">জন্মস্থান</div>
        <div id="blood_grp" style="position: absolute; left: 33.5%; top: 70.4%; font-size: 14px; font-family: 'SolaimanLipi', sans-serif;"><?php echo htmlspecialchars($birthPlace); ?></div>

        <div style="font-family: 'SolaimanLipi'; position: absolute; left: 14.3%; top: 73%; font-size: 14px;"><b>বর্তমান ঠিকানা</b></div>
        <div id="present_addr" style="font-family: 'SolaimanLipi'; position: absolute; left: 14.3%; top: 75.5%; width: 72%; font-size: 14px;"><?php echo htmlspecialchars($presentaddress); ?></div>

        <div style="font-family: 'SolaimanLipi'; position: absolute; left: 14.3%; top: 81.1%; font-size: 14px;"><b>স্থায়ী ঠিকানা</b></div>
        <div id="permanent_addr" style="font-family: 'SolaimanLipi'; position: absolute; left: 14.3%; top: 83.5%; width: 72%; font-size: 14px;"><?php echo htmlspecialchars($permanentaddress); ?></div>

        <div style="position: absolute; top: 88.5%; width: 100%; font-size: 13px; text-align: center; color: rgb(255,0,0); font-family: 'SolaimanLipi', sans-serif; font-weight: bold;">উপরে প্রদর্শিত তথ্যসমূহ জাতীয় পরিচয়পত্র সংশ্লিষ্ট, ভোটার তালিকার সাথে সরাসরি সম্পর্কযুক্ত নয়।</div>
        <div style="position: absolute; top: 90.1%; width: 100%; text-align: center; font-size: 10.5px; font-weight: bold;">This is Software Generated Report From Bangladesh Election Commission, Signature &amp; Seal Aren't Required.</div>

        <div style="position: absolute; left: 42.4%; top: 16.8%;">
            <img id="photo" src="<?php echo htmlspecialchars($displayPhoto); ?>"
                 referrerpolicy="no-referrer"
                 onerror="this.onerror=null; this.src='assets/images/no-photo.png';"
                 height="140px" width="121px" style="border-radius: 10px"/>
        </div>
    </div>

    <script>
        window.onload = function () { setTimeout(wp, 500); };
        function wp() { window.print(); }
    </script>
</body>
</html>