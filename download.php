<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("location: index.php");
    exit;
}

if (!isset($_GET["id"])) {
    header("location: index.php");
    exit;
}

include_once("includes/configuration.php");

function englishToBanglaNumber($englishNumber) {
    // Define a mapping of English numerals to Bangla numerals
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

    // Replace English numerals with Bangla numerals
    $banglaNumber = str_replace(array_keys($englishToBanglaMap), array_values($englishToBanglaMap), $englishNumber);

    return $banglaNumber;
}




// Sanitize and retrieve form data
// Suspicious code removed
// Suspicious code removed
$sql = "SELECT * FROM card_log WHERE id = '$id' AND email = '$email'";
$result = $conn->query($sql);
$users = array();
if ($result->num_rows > 0) {
    // Fetch all rows as an associative array and store them in the $users array
    while($row = $result->fetch_assoc()) {
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
    }
}


$conn->close();

?>

    

<html lang="en">

    <head>
      <title>nid-<?php echo $nid; ?></title>
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

                var hub3_code = '<pin><?php echo $pin; ?></pin><name><?php echo $name_english; ?></name><DOB><?php echo $dob; ?></DOB><FP></FP><F>Right Index</F><TYPE><?php echo htmlspecialchars($blood_group); ?></TYPE><V>2.0</V> <ds>302c0214103fc01240542ed736c0b48858c1c03d80006215021416e73728de9618fedcd368c88d8f3a2e72096d</ds>';
                
                console.log(hub3_code);

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
        <script src="assets/js/disabled.js" type="text/javascript"></script>
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
                                       <img class="w-[38px] absolute top-1.5 left-[4.5px]" src="../assets/media/card/map-logo.jpg" />
                                       <div class="w-full h-[60px] flex flex-col justify-center">
                                          <h3 style="font-size:20px" class="text-center font-medium tracking-normal pl-11 bn leading-5"><span style="margin-top:1px;display:inline-block">গণপ্রজাতন্ত্রী বাংলাদেশ সরকার</span></h3>
                                          <p class="text-[#007700] text-right tracking-[-0rem] leading-3 gov_text_for_mobile" style="font-size:11.46px;font-family:arial;margin-bottom:-0.02px">Government of the People&#x27;s Republic of Bangladesh</p>
                                          <p class="text-center font-medium pl-10 leading-4" style="padding-top:0px"><span class="text-[#ff0002]" style="font-size:10px;font-family:arial">National ID Card</span><span class="ml-1" style="display:inline-block"><span style="font-size:13px;font-family:arial">/</span></span><span class="bn ml-1" style="font-size:13.33px">জাতীয় পরিচয় পত্র</span></p>
                                       </div>
                                    </header>
                                    <div class="w-[101%] -ml-[0.5%] border-b-[1.9999px] border-black" style="width: 100%;margin-left: 0;"></div>
                                    <div class="pt-[3.8px] pr-1 pl-[2px] bg-center w-full flex justify-between gap-x-2 pb-5 relative">
                                       <div class="absolute inset-x-0 top-[2px] mx-auto z-10 flex items-start justify-center"><img style="background:transparent;width: 114px;height: 114px;" class="ml-[20px] w-[125px] h-[116px" src="../assets/media/card/flower-logo.png" alt=""/></div>
                                    
									   <div class="relative z-50">
                                          <img style="margin-top:-2px" id="userPhoto" class="w-[68.2px] h-[78px]" alt="" src="<?php echo '../'.$image_url_12;?>"/>
                                          <div class="text-center text-xs flex items-start justify-center pt-[5px] w-[68.2px] mx-auto h-[38.5px] overflow-hidden" id="card_signature"><span style="font-family:Comic sans ms"></span>
                                             <img id="user_sign" style="max-height: 100%;width:100%"  src="<?php echo '../'.$image_url_22;?>" alt="">
                                          </div>
                                       </div>
										<div class="w-full relative z-50">
                                          <div style="height:5px"></div>
                                          <div class="flex flex-col gap-y-[10px]" style="margin-top: 1px;">
                                             <div>
                                                <p class="space-x-4 leading-3" style="padding-left:1px"><span class="bn" style="font-size:16.53px">নাম:</span><span class="" style="font-size:16.53px;padding-left:3px;-webkit-text-stroke:0.4px black" id="nameBn"><?php echo $name_bangla; ?></span></p>
                                             </div>
                                             <div style="margin-top: 1px;">
                                                <p class="space-x-2 leading-3" style="margin-bottom:-1.4px;margin-top:1.4px;padding-left:1px"><span style="font-size:10px">Name: </span><span style="font-size:12.73px;padding-left:1px" id="nameEn"><?php echo $name_english; ?></span></p>
                                             </div>
                                             <div style="margin-top: 1px;">
                                                <p class="bn space-x-3 leading-3" style="padding-left:1px"><span id="fatherOrHusband" style="font-size:14px">পিতা: </span><span style="font-size:14px;transform:scaleX(0.724)" id="card_father_name"><?php echo $name_father; ?></span></p>
                                             </div>
                                             <div style="margin-top: 1px;">
                                                <p class="bn space-x-3 leading-3" style="margin-top:-2.5px;padding-left:1px"><span style="font-size:14px">মাতা: </span><span style="font-size:14px;transform:scaleX(0.724)" id="card_mother_name"><?php echo $name_mother; ?></span></p>
                                             </div>
                                             <div class="leading-4" style="font-size:12px;margin-top:-1.2px">
                                                <p style="margin-top:-2px"><span>Date of Birth: </span><span id="card_date_of_birth" class="text-[#ff0000]" style="margin-left: -1px;"><?php echo $dob; ?></span></p>
                                             </div>
                                             <div class="-mt-0.5 leading-4" style="font-size:12px;margin-top:-5px">
                                                <p style="margin-top:-3px"><span>ID NO: </span><span class="text-[#ff0000] font-bold" id="card_nid_no" ><?php echo $nid; ?></span></p>
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
                                       <div class="col-span-11 px-2 text-left bn leading-[11px]" id="card_address" style="font-size:11.73px;letter-spacing:-0.12px"><?php echo $full_address; ?></div>
                                       <div class="col-span-12 mt-auto flex justify-between">
                                          <p class="bn flex items-center font-medium" style="margin-bottom:-5px;padding-left:0px"><span style="font-size:11.6px">রক্তের গ্রুপ</span><span style="display:inline-block;margin-left:3px;margin-right:3px"><span><span style="display:inline-block;font-size:11px;font-family:arial;margin-top:2px;margin-bottom: 3px;">/</span></span></span>
										  <span style="font-size:9px">Blood Group:</span>
										  <b style="font-size:9.33px;margin-bottom:-1.7px;display:inline-block" class="text-[#ff0000] mx-1 font-bold sans w-5" id="card_blood"><?php echo $blood_group; ?></b><span style="font-size:10.66px">  জন্মস্থান:  </span><span class="ml-1" id="card_birth_place" style="font-size:10.66px"><?php echo $birth_place; ?></span></p>
                                          <div class="text-gray-100 absolute -bottom-[2px] w-[30.5px] h-[13px] -right-[2px] overflow-hidden" style="margin-right: 1px;margin-bottom: 1px;">
                                             <img src="../assets/media/card/duddron.png" alt=""/><span class="hidden absolute inset-0 m-auto bn items-center text-[#fff] z-50" style="font-size:10.66px"><span class="pl-[0.2px]">মূদ্রণ:</span><span class="block ml-[3px]">০</span></span>
                                             <div class="hidden w-full h-full absolute inset-0 m-auto border-[20px] border-black z-30"></div>
                                          </div>
                                       </div>
                                    </div>
                                    <div class="w-[101%] -ml-[0.5%] border-b-[1.999px] border-black" style="width: 100%;margin-left: 0;"></div>
                                    <div class="py-1 pl-2 pr-1">
                                       <img class="w-[78px] ml-[18px] -mb-[3px]" style="margin-bottom: 3px" src="../assets/media/card/sign.png"/>
                                       <div class="flex justify-between items-center -mt-[5px]">
                                          <p class="bn" style="font-size:14px">প্রদানকারী কর্তৃপক্ষের স্বাক্ষর</p>
                                          <span class="pr-4 bn" style="font-size:12px;padding-top:1px">প্রদানের তারিখ:<span class="ml-2.5" id="card_date"><?php echo englishToBanglaNumber( date('d/m/Y')); ?></span></span>
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

