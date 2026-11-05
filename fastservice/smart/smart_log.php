<?php
session_start();

// ইউজার লগইন চেক
if (!isset($_SESSION["user_id"])) {
    header("location: ./index.php");
    exit;
}

// GET প্যারামিটার চেক
if (!isset($_GET["id"])) {
    header("location: index.php");
    exit;
}

include_once("../includes/configuration.php");

// বাংলা নম্বর রূপান্তর ফাংশন
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

// GET থেকে আইডি ডিকোড
$id = base64_decode($_GET["id"]);
$email = base64_decode($_SESSION["user_id"]);

// Prepare statement দিয়ে কোয়েরি চালানো (SQL Injection প্রতিরোধে)
$sql = "SELECT * FROM smart WHERE id = ? AND email = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("is", $id, $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // একেকটি রেকর্ড ফেচ করা
    $row = $result->fetch_assoc();
    
    $nid = $row["nid"];
    $name_bangla = $row["name_bangla"];
    $name_english = $row["name_english"];
    $pin = $row["pin"];
    $dob = $row["dob"];
    $birth_place = $row["birth_place"];
    $name_father = $row["name_father"];
    $name_mother = $row["name_mother"];
    $name_spouse = $row["name_spouse"];
    $gender = $row["gender"];
    $blood_group = $row["blood_group"];
    $image_url_12 = $row["image_url_12"];
    $image_url_22 = $row["image_url_22"];
    $full_address = $row["full_address"];
    $issueDate = $row["issueDate"];
} else {
    // রেকর্ড না থাকলে অন্য পেজে রিডাইরেক্ট
    header("location: index.php");
    exit;
}

$stmt->close();
$conn->close();

// ---------------- MRZ ফাংশন ----------------
function generateMrz($dob, $name_english, $nid, $gender) {
    // Clean NID (ফাঁকা জায়গা বাদ)
    $nid_clean = preg_replace('/[^0-9]/', '', $nid);

    // MRZ NID: প্রথম 9 digit + < + last digit + random 1 digit
    $randomDigit = rand(0, 9);
    $nid_mrz = substr($nid_clean, 0, 9) . '<' . substr($nid_clean, -1) . $randomDigit;

    // Line 1
    $line1 = 'I<BGD' . $nid_mrz;
    $line1 = str_pad($line1, 30, '<'); // Total 30 char

    // Line 2
    $dob_obj = date_create($dob);
    $yy = $dob_obj->format('y');
    $mm = $dob_obj->format('m');
    $dd = $dob_obj->format('d');
    $dateCode = $yy . $mm . $dd;

    $serial = rand(1000000, 9999999);
    $line2 = $dateCode . strtoupper($gender) . $serial . 'BGD';
    $line2 = str_pad($line2, 29, '<');
    $line2 .= rand(0, 9);

    // Line 3
    $name_clean = strtoupper($name_english);
    $name_clean = preg_replace('/[^A-Z ]/', '', $name_clean);
    $name_clean = preg_replace('/\s+/', ' ', $name_clean);
    $name_clean = trim($name_clean);
    
    // name split
    $nameParts = explode(' ', $name_clean);
    
    if (count($nameParts) == 1) {
        $surname = $nameParts[0];
        $givenNames = '';
    } else {
        $surname = array_pop($nameParts);
        $givenNames = implode('<', $nameParts);
    }
    
    $line3 = $givenNames ? $surname . '<<' . $givenNames : $surname;
    $line3 = str_pad($line3, 30, '<');

    return [$line1, $line2, $line3];
}

// MRZ render ফাংশন
function renderMrzLine($line, $iconImgUrl, $addForLastClass = false) {
    $html = '';
    $chars = preg_split('//u', $line, -1, PREG_SPLIT_NO_EMPTY);
    foreach ($chars as $char) {
        $classes = 'f_line_icon';
        if ($addForLastClass) $classes .= ' for_last';

        if ($char === '<') {
            $html .= '<div class="' . $classes . '"><img src="' . htmlspecialchars($iconImgUrl) . '" alt="icon"></div>';
        } else {
            $html .= '<div class="' . $classes . '">' . htmlspecialchars($char) . '</div>';
        }
    }
    return $html;
}

$iconImgUrl = 'https://i.ibb.co/4czxGGy/smart-card-back-icon.png';
list($line1, $line2, $line3) = generateMrz($dob, $name_english, $nid, $gender);

?>
<style>
    img#user_img {
    left: 269px;
    top: 37px;
    width: 20px;
    opacity: 0.5;
    filter: blur(0.2px) grayscale(1);
}
</style>
<!DOCTYPE html>
<html lang="bn">
<head>
    <title>smart_nid_<?php echo $nid; ?></title>
    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale=1.0, width=device-width">
    <link rel="stylesheet" href="assets/css/smart.css">
    <link rel="stylesheet" href="assets/css/e521caf613e4ad87.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:ital,wght@0,100..900;1,100..900&family=Roboto+Mono:ital,wght@0,100..700;1,100..700&family=Roboto+Slab:wght@100..900&display=swap" rel="stylesheet">
    <link href="fonts/font.css" rel="stylesheet">
</head>

<body id="design" style="background: white; color: black !important">
<div id="__next" data-reactroot="" style="width: 800px">
    <div class="flex" style="padding-left: 70px; padding-right: 70px">

        <!-- FRONT SIDE -->
        <div id="front_side" class="id_side" style="display: inline-block">
            <div id="font_text" class="absolute">
                <div class="nameBan title font_family">নাম</div>
                <div class="nameBan main_text font_family"><?php echo $name_bangla; ?></div>
                <div class="nameEn title">Name</div>
                <div class="nameEn main_text"><?php echo $name_english; ?></div>
                <div class="fatherName title font_family">পিতা</div>
                <div class="fatherName main_text font_family"><?php echo $name_father; ?></div>
                <div class="motherName title font_family">মাতা</div>
                <div class="motherName main_text font_family"><?php echo $name_mother; ?></div>
                <div class="dateOfBirth">
                    <div class="date_title en_title">Date Of Birth</div>
                    <div class="date_number en_title"><?php echo $dob; ?></div>
                </div>
                <div class="nid">
                    <div class="nid_title en_title">NID No.</div>
                    <div class="nid_number en_title"><?php echo $nid; ?></div>
                </div>
            </div>
            <img class="test_img" src="smart/test.svg" alt="">
            <div id="user_img">
                <img class="user_img" src="<?= htmlspecialchars($image_url_12) ?>" alt="">
                <img id="user_img" class="user_img" src="<?= htmlspecialchars($image_url_12) ?>" alt="">
                <div class="overflow_dob"><?php echo $dob; ?></div>
            </div>
            <div id="sing_img_div">
                <img id="sign_img" class="sign_img" src="<?= htmlspecialchars($image_url_22) ?>" alt="">
            </div>
            <div id="front_img">
                <img id="overflow_img" src="upload-image.click/upload/overflow.svg" alt="">
                <img class="side_img" src="upload-image.click/upload/fronts.svg" alt="">
            </div>
        </div>

        <!-- BACK SIDE -->
        <div id="back_side" class="id_side" style="display: inline-block">
            <img id="user_img_two" class="user_img" src="<?= htmlspecialchars($image_url_12) ?>" alt="">
            <div id="back_img">
                <img class="side_img" src="upload-image.click/upload/back.svg" alt="">
                <img class="overflow_back" src="upload-image.click/upload/overflow_back.svg" alt="">
                <div class="address" style="font-size: 10px; line-height: 10px">
                    ঠিকানা: <?php echo $full_address; ?>
                </div>
                <div class="back_text_one">
                    <span class="fist_line_one back_line_one" style="top: 2.5px;">
                        Blood Group: <span class="result_one bloodGroup"><?php echo $blood_group; ?></span>
                    </span>
                    <span class="second_line_one back_line_one">
                        Place of Birth: <span class="result_one place_of_birth"><?php echo $birth_place; ?></span>
                    </span>
                    <span class="third_line_one back_line_one">
                        Issue Date: <span class="result_one date_of_issue"><?php echo $issueDate; ?></span>
                    </span>
                </div>
                <div class="back_text">
                    <div class="first_line back_line"><?php echo renderMrzLine($line1, $iconImgUrl, true); ?></div>
                    <div class="second_line back_line"><?php echo renderMrzLine($line2, $iconImgUrl, true); ?></div>
                    <div class="third_line back_line"><?php echo renderMrzLine($line3, $iconImgUrl, true); ?></div>
                </div>
                <div style="position: absolute; top: 13px; left: 20px; transform: rotate(180deg); width: 290px; height: 38px">
                    <canvas style="width: 100%; height: 100%" id="barcode"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bwip-js/4.5.2/bwip-js-min.js"></script>
<script>
var hub3_code = `<pin><?= $pin ?></pin><name><?php echo $name_english; ?></name><DOB><?php echo $dob; ?></DOB><FP></FP><F>Right Index</F><TYPE></TYPE><V>2.0</V><ds>302d02150094b24c767848fa73cf4adb3fc75635d9c1148871a5950aa1d4204650651ec74c60b597eceea4b1c43c3a</ds>`;
const canvas = document.getElementById("barcode");
const ctx = canvas.getContext("2d");
canvas.width = 300;
canvas.height = 60;

try {
    bwipjs.toCanvas(canvas, {
        bcid: "pdf417",
        text: hub3_code,
        scale: 2,
        columns: 13,
        eclevel: 5,
        rowheight: 4,
        paddingwidth: 0,
        paddingheight: 0,
        includetext: false,
    });
    ctx.imageSmoothingEnabled = false;
    ctx.drawImage(canvas, 0, 0);
} catch (e) {
    console.error("Error:", e);
}

let rotationAngle = 0;
function rotateImage() {
    rotationAngle += 90;
    document.getElementById("sign_img").style.transform = `rotate(${rotationAngle}deg) scale(${document.getElementById('scaleRange').value})`;
}
function applyStyles() {
    const topValue = document.getElementById('topRange').value;
    const paddingValue = document.getElementById('paddingRange').value;
    const scaleValue = document.getElementById('scaleRange').value;
    document.getElementById('topValueLabel').textContent = topValue;
    document.getElementById('paddingValueLabel').textContent = paddingValue;
    document.getElementById('scaleValueLabel').textContent = scaleValue;

    const div = document.getElementById('sing_img_div');
    const img = document.getElementById("sign_img");
    div.style.top = `${topValue}px`;
    div.style.padding = `${paddingValue}px`;
    img.style.transform = `rotate(${rotationAngle}deg) scale(${scaleValue})`;
}
applyStyles();
</script>

<div class="hidden_when_print" style="margin-top: 30px;">
    <label for="topRange">সিগনেচার উপর নিচঃ</label>
    <input type="range" id="topRange" min="100" max="190" value="165" oninput="applyStyles()">
    <span id="topValueLabel">170</span>
    <br>
    <label for="paddingRange">Padding (px):</label>
    <input type="range" id="paddingRange" min="0" max="25" value="0" oninput="applyStyles()">
    <span id="paddingValueLabel">5</span>
    <br>
    <label for="scaleRange">Zoom (Scale):</label>
    <input type="range" id="scaleRange" min="0.5" max="2" step="0.1" value="1" oninput="applyStyles()">
    <span id="scaleValueLabel">1</span>
    <br><br>
    <button style="padding: 5px; color: white; background: orange" onclick="rotateImage()">সিগনেচার ঘুরান</button>
    <button style="padding: 5px; color: white; background: blue;" onclick="window.print()">প্রিন্ট করুন</button>
</div>
</body>
</html>
