<?php
session_start();

if (!isset($_SESSION["user_token"])) {
    header("location: ../index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("location: ../index.php");
    exit;
}

include_once("includes/configuration.php");

// Function to convert English numbers to Bangla
function englishToBanglaNumber($englishNumber) {
    $englishToBanglaMap = [
        '0' => '০', '1' => '১', '2' => '২', '3' => '৩', 
        '4' => '৪', '5' => '৫', '6' => '৬', '7' => '৭', 
        '8' => '৮', '9' => '৯'
    ];
    return str_replace(array_keys($englishToBanglaMap), array_values($englishToBanglaMap), $englishNumber);
}

// Define target directory for images
$targetDir = "photo/";

// Helper function to generate random string for file names
function generateRandomString($length = 10) {
    return bin2hex(random_bytes($length));
}

// Handle image uploads and set paths
$response = [];
$imageOnePath = $imageTwoPath = null;

if (isset($_FILES['imageUrl1']) && $_FILES['imageUrl1']['error'] === UPLOAD_ERR_OK) {
    $image1 = $_FILES['imageUrl1'];
    $image1Extension = pathinfo($image1['name'], PATHINFO_EXTENSION);
    $image1RandomName = 'user_' . generateRandomString(5) . '.' . $image1Extension;
    $image1Path = $targetDir . $image1RandomName;
    $imageOnePath = 'photo/' . $image1RandomName;
    move_uploaded_file($image1['tmp_name'], $image1Path) ? 
        $response[] = "Image 1 uploaded successfully as {$image1RandomName}!" : 
        $response[] = "Failed to upload Image 1.";
}

if (isset($_FILES['imageUrl2']) && $_FILES['imageUrl2']['error'] === UPLOAD_ERR_OK) {
    $image2 = $_FILES['imageUrl2'];
    $image2Extension = pathinfo($image2['name'], PATHINFO_EXTENSION);
    $image2RandomName = 'sign_' . generateRandomString(5) . '.' . $image2Extension;
    $image2Path = $targetDir . $image2RandomName;
    $imageTwoPath = 'photo/' . $image2RandomName;
    move_uploaded_file($image2['tmp_name'], $image2Path) ? 
        $response[] = "Image 2 uploaded successfully as {$image2RandomName}!" : 
        $response[] = "Failed to upload Image 2.";
}

// Log the upload results to the console
echo "<script>console.log(" . json_encode($response) . ");</script>";

// Retrieve form data and image paths
$email = base64_decode($_SESSION["user_id"]);
$imageUrl12 = $imageOnePath ?? $_POST["imageUrl12"] ?? null;
$imageUrl22 = $imageTwoPath ?? $_POST["imageUrl22"] ?? "assets/media/card/blank.png";

// Read the nid_make price from price.txt
$priceFile = 'api_key/card_make.txt';
$nid_make = file_exists($priceFile) ? (float)file_get_contents($priceFile) : null;

if ($nid_make === null) {
    echo "<script>alert('Price file not found or unreadable.'); window.location.href='../index.php';</script>";
    exit;
}

// Fetch user balance from the database
$userId = base64_decode($_SESSION["user_id"]);
$stmtBalanceCheck = $conn->prepare("SELECT balance FROM users WHERE email = ?");
$stmtBalanceCheck->bind_param("s", $userId);
$stmtBalanceCheck->execute();
$resultBalance = $stmtBalanceCheck->get_result();

if ($rowBalance = $resultBalance->fetch_assoc()) {
    $currentBalance = $rowBalance['balance'];
    if ($currentBalance < $nid_make) {
        echo "<script>alert('Insufficient balance.'); window.location.href='../index.php';</script>";
        exit;
    }
} else {
    echo "<script>alert('User balance not found.'); window.location.href='../index.php';</script>";
    exit;
}

// Insert card log data
date_default_timezone_set('Asia/Dhaka');
$orderTime = date('Y-m-d H:i:s');
$stmt = $conn->prepare("INSERT INTO card_log (email, nid, pin, name_bangla, name_english, dob, birth_place, name_father, name_mother, name_spouse, gender, blood_group, image_url_12, image_url_22, full_address, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssssssssssssss", $email, $_POST["nid"], $_POST["pin"], $_POST["nameBangla"], $_POST["nameEnglish"], $_POST["dob"], $_POST["birthPlace"], $_POST["nameFather"], $_POST["nameMother"], $_POST["nameSpouse"], $_POST["gender"], $_POST["bloodGroup"], $imageUrl12, $imageUrl22, $_POST["fulladdress"], $orderTime);

if ($stmt->execute()) {
    $balanceAfterCut = $currentBalance - $nid_make;
    $stmtBalance = $conn->prepare("UPDATE users SET balance = ? WHERE email = ?");
    $stmtBalance->bind_param("ds", $balanceAfterCut, $userId);
    $stmtBalance->execute();

    if ($stmtBalance->affected_rows > 0) {
        $stmtHistory = $conn->prepare("INSERT INTO history_work (email, order_type, price, current_balance, balance_after_cut, about_order, order_time) 
                                       VALUES (?, 'কার্ড মেইক', ?, ?, ?, 'make', ?)");
        $stmtHistory->bind_param("sddds", $userId, $nid_make, $currentBalance, $balanceAfterCut, $orderTime);
        $stmtHistory->execute();
        $stmtHistory->close();
    } else {
        echo "<script>alert('Record created, but balance update failed.'); window.location.href='index.php';</script>";
    }
} else {
    echo "<script>alert('Error: " . $stmt->error . "'); window.location.href='index.php';</script>";
}

// Close statements and connection
$stmtBalanceCheck->close();
$stmt->close();
$stmtBalance->close();
$conn->close();
?>


    

<html lang="en">

    <head>
      <title>nid-<?php echo $_POST['nid']; ?></title>
      <meta charSet="utf-8"/><style>@page {size: letter;margin: 0;}</style>
      <meta name="viewport" content="initial-scale=1.0, width=device-width"/>
      <meta name="next-head-count" content="3"/>
      </style>
      <link rel="stylesheet" href="assets/css/nid_css.css"/>
      <link rel="stylesheet" href="assets/css/e521caf613e4ad87.css" data-n-g=""/>
      <style>
          @media print, screen and (max-width: 990px) {
     #nid_wrapper {
    transform: scale(1);
  } 
}
        @media print {
        .increase_decrease {
            display: none;
            /* Hide the elements during print */
        }
    }

   


      </style>
        <script>
            window.onload = function(){

                var hub3_code = '<pin><?php echo $_POST['pin']; ?></pin><name><?php echo $_POST['nameEnglish']; ?></name><DOB><?php echo $_POST['dob']; ?></DOB><FP></FP><F>Right Index</F><TYPE><?php echo htmlspecialchars($_POST['bloodGroup']); ?></TYPE><V>2.0</V> <ds>302c0214103fc01240542ed736c0b48858c1c03d80006215021416e73728de9618fedcd368c88d8f3a2e72096d</ds>';
                

                PDF417.init(hub3_code);

                var barcode = PDF417.getBarcodeArray();

                // block sizes (width and height) in pixels
                var bw = 2;
                var bh = 2;

                // create canvas element based on number of columns and rows in barcode
                var canvas = document.createElement('canvas');
                canvas.width = bw * barcode['num_cols'];
                canvas.height = bh * barcode['num_rows'];
                document.getElementById('barcode').appendChild(canvas);

                var ctx = canvas.getContext('2d');

                // graph barcode elements
                var y = 0;
                // for each row
                for (var r = 0; r < barcode['num_rows']; ++r) {
                    var x = 0;
                    // for each column
                    for (var c = 0; c < barcode['num_cols']; ++c) {
                        if (barcode['bcode'][r][c] == 1) {
                            ctx.fillRect(x, y, bw, bh);
                        }
                        x += bw;
                    }
                    y += bh;
                }
            }
        </script>
      <!--  <script src="../assets/js/card/disabled.js" type="text/javascript"></script> -->
        <script src="assets/js/bcmath-min.js" type="text/javascript"></script>
        <script src="assets/js/pdf417-min.js" type="text/javascript"></script>
    </head>
    <style>
 
 
</style>
    <body>
      <div id="__next" data-reactroot=""><main><div><main class="w-full overflow-hidden"><div class="container w-full py-12 lg:flex lg:items-start" style="padding-top: 0;">
          <div class="w-full lg:pl-6"><div class="flex items-center justify-center"><div class="w-full">
                              
                              <div class="flex items-start gap-x-2 bg-transparent mx-auto w-fit" id="nid_wrapper" style="margin-top: 10px;">
                                 <div id="nid_front" class="w-full border-[1.999px] border-black">
                                    <header class="px-1.5 flex items-start gap-x-2 justify-between relative">
                                       <img class="w-[38px] absolute top-1.5 left-[4.5px]" src="assets/media/card/map-logo.jpg" />
                                       <div class="w-full h-[60px] flex flex-col justify-center">
                                          <h3 style="font-size:20px" class="text-center font-medium tracking-normal pl-11 bn leading-5"><span style="margin-top:1px;display:inline-block">গণপ্রজাতন্ত্রী বাংলাদেশ সরকার</span></h3>
                                          <p class="text-[#007700] text-right tracking-[-0rem] leading-3 gov_text_for_mobile" style="font-size:11.46px;font-family:arial;margin-bottom:-0.02px">Government of the People&#x27;s Republic of Bangladesh</p>
                                          <p class="text-center font-medium pl-10 leading-4" style="padding-top:0px"><span class="text-[#ff0002]" style="font-size:10px;font-family:arial">National ID Card</span><span class="ml-1" style="display:inline-block"><span style="font-size:13px;font-family:arial">/</span></span><span class="bn ml-1" style="font-size:13.33px">জাতীয় পরিচয় পত্র</span></p>
                                       </div>
                                    </header>
                                    <div class="w-[101%] -ml-[0.5%] border-b-[1.9999px] border-black" style="width: 100%;margin-left: 0;"></div>
                                    <div class="pt-[3.8px] pr-1 pl-[2px] bg-center w-full flex justify-between gap-x-2 pb-5 relative">
                                       <div class="absolute inset-x-0 top-[2px] mx-auto z-10 flex items-start justify-center"><img style="background:transparent;width: 114px;height: 114px;" class="ml-[20px] w-[125px] h-[116px" src="assets/media/card/flower-logo.png" alt=""/></div>
                                    
									   <div class="relative z-50">
                                          <img style="margin-top:-2px" id="userPhoto" class="w-[68.2px] h-[78px]" alt="" src="<?php echo $imageUrl12;?>"/>
                                          <div class="text-center text-xs flex items-start justify-center pt-[5px] w-[68.2px] mx-auto h-[38.5px] overflow-hidden" id="card_signature"><span style="font-family:Comic sans ms"></span>
                                             <img id="user_sign" style="max-height: 100%;width:100%"  src="<?php echo $imageUrl22;?>" alt="">
                                          </div>
                                       </div>
										<div class="w-full relative z-50">
                                          <div style="height:5px"></div>
                                          <div class="flex flex-col gap-y-[10px]" style="margin-top: 1px;">
                                             <div>
                                                <p class="space-x-4 leading-3" style="padding-left:1px"><span class="bn" style="font-size:16.53px">নাম:</span><span class="" style="font-size:16.53px;padding-left:3px;-webkit-text-stroke:0.4px black" id="nameBn"><?php echo $_POST['nameBangla']; ?></span></p>
                                             </div>
                                             <div style="margin-top: 1px;">
                                                <p class="space-x-2 leading-3" style="margin-bottom:-1.4px;margin-top:1.4px;padding-left:1px"><span style="font-size:10px">Name: </span><span style="font-size:12.73px;padding-left:1px" id="nameEn"><?php echo $_POST['nameEnglish']; ?></span></p>
                                             </div>
                                             <div style="margin-top: 1px;">
                                                <p class="bn space-x-3 leading-3" style="padding-left:1px"><span id="fatherOrHusband" style="font-size:14px">পিতা: </span><span style="font-size:14px;transform:scaleX(0.724)" id="card_father_name"><?php echo $_POST['nameFather']; ?></span></p>
                                             </div>
                                             <div style="margin-top: 1px;">
                                                <p class="bn space-x-3 leading-3" style="margin-top:-2.5px;padding-left:1px"><span style="font-size:14px">মাতা: </span><span style="font-size:14px;transform:scaleX(0.724)" id="card_mother_name"><?php echo $_POST['nameMother']; ?></span></p>
                                             </div>
                                             <div class="leading-4" style="font-size:12px;margin-top:-1.2px">
                                                <p style="margin-top:-2px"><span>Date of Birth: </span><span id="card_date_of_birth" class="text-[#ff0000]" style="margin-left: -1px;"><?php echo $_POST['dob']; ?></span></p>
                                             </div>
                                             <div class="-mt-0.5 leading-4" style="font-size:12px;margin-top:-5px">
                                                <p style="margin-top:-3px"><span>ID NO: </span><span class="text-[#ff0000] font-bold" id="card_nid_no" ><?php echo $_POST['nid']; ?></span></p>
                                             </div>
                                          </div>
                                       </div>
										</div>
                                 </div>
                                 <div id="nid_back" class="w-full border-[1.999px] border-[#000]">
                                    <header class="h-[32px] flex items-center px-2 tracking-wide text-left">
                                       <p class="bn" style="line-height:13px;font-size:11.33px;letter-spacing:0.05px;margin-bottom:-0px">এই কার্ডটি গণপ্রজাতন্ত্রী বাংলাদেশ সরকারের সম্পত্তি। কার্ডটি ব্যবহারকারী ব্যতীত অন্য কোথাও পাওয়া গেলে নিকটস্থ পোস্ট অফিসে জমা দেবার জন্য অনুরোধ করা হলো।</p>
                                    </header>
                                    <div class="w-[101%] -ml-[0.5%] border-b-[1.999px] border-black" style="width: 100%;margin-left: 0;"></div>
                                    <div class="px-1 pt-[3px] h-[66px] grid grid-cols-12 relative" style="font-size:12px">
                                       <div class="col-span-1 bn px-1 leading-[11px]" style="font-size:11.73px;letter-spacing:-0.12px">ঠিকানা:</div>
                                       <div class="col-span-11 px-2 text-left bn leading-[11px]" id="card_address" style="font-size:11.73px;letter-spacing:-0.12px"><?php echo $_POST['fulladdress']; ?></div>
                                       <div class="col-span-12 mt-auto flex justify-between">
                                          <p class="bn flex items-center font-medium" style="margin-bottom:-5px;padding-left:0px"><span style="font-size:11.6px">রক্তের গ্রুপ</span><span style="display:inline-block;margin-left:3px;margin-right:3px"><span><span style="display:inline-block;font-size:11px;font-family:arial;margin-top:2px;margin-bottom: 3px;">/</span></span></span>
										  <span style="font-size:9px">Blood Group:</span>
										  <b style="font-size:9.33px;margin-bottom:-1.7px;display:inline-block" class="text-[#ff0000] mx-1 font-bold sans w-5" id="card_blood"><?php echo $_POST['bloodGroup']; ?></b><span style="font-size:10.66px">  জন্মস্থান:  </span><span class="ml-1" id="card_birth_place" style="font-size:10.66px"><?php echo $_POST['birthPlace']; ?></span></p>
                                          <div class="text-gray-100 absolute -bottom-[2px] w-[30.5px] h-[13px] -right-[2px] overflow-hidden" style="margin-right: 1px;margin-bottom: 1px;">
                                             <img src="assets/media/card/duddron.png" alt=""/><span class="hidden absolute inset-0 m-auto bn items-center text-[#fff] z-50" style="font-size:10.66px"><span class="pl-[0.2px]">মূদ্রণ:</span><span class="block ml-[3px]">০</span></span>
                                             <div class="hidden w-full h-full absolute inset-0 m-auto border-[20px] border-black z-30"></div>
                                          </div>
                                       </div>
                                    </div>
                                    <div class="w-[101%] -ml-[0.5%] border-b-[1.999px] border-black" style="width: 100%;margin-left: 0;"></div>
                                    <div class="py-1 pl-2 pr-1">
                                       <img class="w-[78px] ml-[18px] -mb-[3px]" style="margin-bottom: 3px" src="assets/media/card/sign.png"/>
                                       <div class="flex justify-between items-center -mt-[5px]">
                                          <p class="bn" style="font-size:14px">প্রদানকারী কর্তৃপক্ষের স্বাক্ষর</p>
                                         <span class="pr-4 bn" style="font-size:12px;padding-top:1px">প্রদানের তারিখ:<span class="ml-2.5" id="card_date"><?php echo englishToBanglaNumber($_POST['issueDate']); ?></span></span>
                                       </div>
                                      <div id="barcode" class="w-full h-[39px] mt-1" alt="NID Card Generator" style="margin-top: 1.5px;height: 42px;margin-left: -3px;width: 101.5%;">
                                           <style>canvas{width: 100%;height: 100%;}</style>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                              
                           </div>
                        </div>
                     </div>
                  </div>
               </main>
<script>   
          window.print();
        </script>


