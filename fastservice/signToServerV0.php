<?php
include_once("pages/sign_to_server.php");
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo "V1_".$nameEn; ?></title>
    <meta name="viewport" content="initial-scale=1.0, width=device-width"/>
    <link rel="stylesheet" href="assets/css/serverCopy.css"/>
    <meta property="og:image" content="">
    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
    <link href="https://fonts.maateen.me/solaiman-lipi/font.css" rel="stylesheet">
    <meta charSet="utf-8"/>
    <style>
        @media print {
            html, body {
                height:100%; 
                margin: 0 !important; 
                padding: 0 !important;
                overflow: hidden;
            }
        }
    </style>
</head>
<body oncontextmenu="return false;" style="text-align: center;">
    <div class="background">
        <img class="crane" src="assets/images/new-min-one.jpg" height="4725px" width="3126px">
        <div style="position: absolute; left: 30%; top: 8%;width: auto;font-size: 16px; color: rgb(255 224 0);"><b>National Identity Registration Wing (NIDW)</b></div>
        <div style="position: absolute; left: 37%; top: 11%;width: auto;font-size: 14px; color: rgb(255, 47, 161);"><b>Select Your Search Category</b></div>
        <div style="position: absolute; left: 45%; top: 12.8%;width: auto;font-size: 12px; color: rgb(8, 121, 4);">Search By NID / Voter No.</div>
        <div style="position: absolute; left: 45%; top: 14.3%;width: auto;font-size: 12px; color: rgb(7, 119, 184);">Search By Form No.</div>
        <div style="position: absolute; left: 30%; top: 16.9%;width: auto;font-size: 12px; color: rgb(252, 0, 0);"><b>NID or Voter No*</b></div>
        <div style="position: absolute; left: 45%; top: 16.9%; width: auto; font-size: 12px; color: rgb(143, 143, 143);"><?php echo $nid;?></div>
        <div style="position: absolute;left: 62.9%;top: 17.1%;width: auto;font-size: 11px;color: rgb(255 255 255);">Submit</div>
        <div style="position: absolute;left: 89%;top: 11.55%;width: auto;font-size: 11px;color: #fff;">Home</div>
        
        <!-- National Identity Information -->
        <div style="position: absolute; left: 37.5%; top: 27.2%; width: auto; font-size: 16px; color: rgb(7, 7, 7);font-family: 'SolaimanLipi', sans-serif;"><b>জাতীয় পরিচিতি তথ্য</b></div>
        <div style="position: absolute; left: 37.3%; top: 30%; width: auto; font-size: 13px; color: rgb(7, 7, 7);font-family: 'SolaimanLipi', sans-serif;">জাতীয় পরিচয় পত্র নম্বর</div>
        <div id="nid_no" style="position: absolute; left: 55%; top: 30%; width: auto; font-size: 14px; color: rgb(7, 7, 7);"><?php echo $nid;?></div>
        
        <div style="position: absolute; left: 37.3%; top: 32.8%; width: auto; font-size: 13px; color: rgb(7, 7, 7);font-family: 'SolaimanLipi', sans-serif;">পিন নম্বর</div>
        <div id="nid_father" style="position: absolute; left: 55%; top: 32.8%; width: auto; font-size: 14px; color: rgb(7, 7, 7);"><?php echo $pin;?></div>
        
        <div style="position: absolute; left: 37.3%; top: 35.3%; width: auto; font-size: 13px; color: rgb(7, 7, 7);font-family: 'SolaimanLipi', sans-serif;">ভোটার নাম্বার</div>
        <div id="nid_father" style="position: absolute; left: 55%; top: 35.3%; width: auto; font-size: 14px; color: rgb(7, 7, 7);"><?php echo $voter_no;?></div>
        
        <div style="position: absolute; left: 37.3%; top: 40.3%; width: auto; font-size: 13px; color: rgb(7, 7, 7);font-family: 'SolaimanLipi', sans-serif;">ভোটার এলাকা</div>
        <div id="nid_father" style="position: absolute; left: 55%; top: 40.3%; width: auto; font-size: 14px; color: rgb(7, 7, 7);"><?php echo $voterArea;?></div>        

        <div style="position: absolute; left: 37.3%; top: 37.8%; width: auto; font-size: 13px; color: rgb(7, 7, 7);font-family: 'SolaimanLipi', sans-serif;">ভোটার সিরিয়াল নং</div>
        <div id="nid_father" style="position: absolute; left: 55%; top: 37.8%; width: auto; font-size: 14px; color: rgb(7, 7, 7);"><?php echo $sl_no;?></div>        

        
        <!-- Personal Information -->
        <div style="position: absolute; left: 37.5%; top: 43.3%; width: auto; font-size: 16px; color: rgb(7, 7, 7);font-family: 'SolaimanLipi', sans-serif;"><b>ব্যক্তিগত তথ্য</b></div>
        
        <div style="position: absolute; left: 37.3%; top: 46%; width: auto; font-size: 14px; color: rgb(7, 7, 7);font-family: 'SolaimanLipi', sans-serif;">নাম (বাংলা)</div>
        <div id="name_bn" style="position: absolute; font-weight: bold; left: 55%; top: 46%; width: auto; font-size: 14px; color: rgb(7, 7, 7);font-family: 'SolaimanLipi', sans-serif;"><b><?php echo $nameBn ?></b></div>
        
        <div style="position: absolute; left: 37.3%; top: 48.8%; width: auto; font-size: 14px; color: rgb(7, 7, 7);font-family: 'SolaimanLipi', sans-serif;">নাম (ইংরেজি)</div>
        <div id="name_en" style="position: absolute; left: 55%; top:48.8%; width: auto; font-size: 14px; color: rgb(7, 7, 7);"><?php echo $nameEn ?></div>
        
        <div style="position: absolute; left: 37.3%; top: 51.5%; width: auto; font-size: 14px; color: rgb(7, 7, 7);font-family: 'SolaimanLipi', sans-serif;">জন্ম তারিখ</div>
        <div id="dob" style="position: absolute; left: 55%; top: 51.5%; width: auto; font-size: 14px; color: rgb(7, 7, 7);"><?php echo $dob;?></div>
        
        <div style="position: absolute; left: 37.3%; top: 54.1%; width: auto; font-size: 14px; color: rgb(7, 7, 7);font-family: 'SolaimanLipi', sans-serif;">পিতার নাম</div>
        <div id="fathers_name" style="position: absolute; left: 55%; top: 54.1%; width: auto; font-size: 14px; color: rgb(7, 7, 7);font-family: 'SolaimanLipi', sans-serif;"><?php echo $father;?></div>
        
        <div style="position: absolute; left: 37.3%; top: 56.7%; width: auto; font-size: 14px; color: rgb(7, 7, 7);font-family: 'SolaimanLipi', sans-serif;">মাতার নাম</div>
        <div id="mothers_name" style="position: absolute; left: 55%; top: 56.7%; width: auto; font-size: 14px; color: rgb(7, 7, 7);font-family: 'SolaimanLipi', sans-serif;"><?php echo $mother;?></div>
        
        <div style="position: absolute; left: 37.3%; top: 59.2%; width: auto; font-size: 14px; color: rgb(7, 7, 7);font-family: 'SolaimanLipi', sans-serif;">স্বামী/স্ত্রীর নাম</div>
        <div id="mothers_name" style="position: absolute; left: 55%; top: 59.2%; width: auto; font-size: 14px; color: rgb(7, 7, 7);font-family: 'SolaimanLipi', sans-serif;"><?php echo empty($spouse) ? "N/A" : $spouse; ?></div>
        
        <!-- Other Information -->
        <div style="position: absolute; left: 37.5%; top: 62%; width: auto; font-size: 16px; color: rgb(7, 7, 7);font-family: 'SolaimanLipi', sans-serif;"><b>অন্যান্য তথ্য</b></div>
        
        <div style="position: absolute; left: 37.3%; top: 65.2%; width: auto; font-size: 14px; color: rgb(7, 7, 7);font-family: 'SolaimanLipi', sans-serif;">র্ধম</div>
        <div id="gender" style="position: absolute; left: 55%; top: 65.2%; width: auto; font-size: 14px; color: rgb(7, 7, 7); font-family: 'SolaimanLipi', sans-serif;"><?php echo $religion; ?></div>
        
        <div style="position: absolute; left: 37.3%; top: 68%; width: auto; font-size: 14px; color: rgb(7, 7, 7);font-family: 'SolaimanLipi', sans-serif;">লিঙ্গ</div>
        <div id="mobile_no" style="position: absolute; left: 55%; top: 68%; width: auto; font-size: 14px; color: rgb(7, 7, 7);"><?php echo $gender;?></div>
        
        <div style="position: absolute; left: 37.3%; top: 70.7%; width: auto; font-size: 14px; color: rgb(7, 7, 7);font-family: 'SolaimanLipi', sans-serif;">পেশা</div>
        <div id="blood_grp" style="position: absolute; left: 55%; top: 70.7%; width: auto; font-size: 14px; color: rgb(7, 7, 7); font-family: 'SolaimanLipi', sans-serif;"><?php echo $occupation;?></div>
        
        <div style="position: absolute; left: 37.3%; top: 73.2%; width: auto; font-size: 14px; color: rgb(7, 7, 7);font-family: 'SolaimanLipi', sans-serif;">জন্মস্থান</div>
        <div id="birth_place" style="position: absolute; left: 55%; top: 73.2%; width: auto; font-size: 14px; color: rgb(7, 7, 7);font-family: 'SolaimanLipi', sans-serif;"><?php echo $birthPlace;?></div>
        
        
        <!-- Address Information -->
        <div style="font-family: 'SolaimanLipi'; position: absolute; left: 37.5%; top: 75.5%; width: auto; font-size: 16px; color: rgb(7, 7, 7);"><b>বর্তমান ঠিকানা</b></div>
        <div id="presentAddress" style="font-family: 'SolaimanLipi';position: absolute; left: 37%; top: 78.7%; width: 48%; font-size: 12.5px; color: rgb(7, 7, 7);"><?php echo $presentAddress; ?></div>
        
        <div style="font-family: 'SolaimanLipi';position: absolute; left: 37.5%; top: 86.5%; width: auto; font-size: 16px; color: rgb(7, 7, 7);"><b>স্থায়ী ঠিকানা</b></div>
        <div id="permanentAddress" style="font-family: 'SolaimanLipi';position: absolute; left: 37%; top: 89%; width: 48%; font-size: 12.5px; color: rgb(7, 7, 7);"><?php echo $permanentAddress; ?></div>
        
        <!-- Footer Notes -->
        <div style="position: absolute;top: 95%;width: 100%;font-size: 12px;text-align: center;color: rgb(255, 0, 0);font-family: 'SolaimanLipi', sans-serif;">উপরে প্রদর্শিত তথ্যসমূহ জাতীয় পরিচয়পত্র সংশ্লিষ্ট, ভোটার তালিকার সাথে সরাসরি সম্পর্কযুক্ত নয়।</div>
        <div style="position: absolute;top: 96.5%;width: 100%;text-align: center;font-size: 12px;color: rgb(3, 3, 3);">This is Software Generated Report From Bangladesh Election Commission, Signature &amp; Seal Aren't Required.</div>
        
        <!-- Photo and QR Code -->
        <div style="position: absolute; left: 16%; top: 25.7%; width: auto; font-size: 12px; color: rgb(3, 3, 3);">
            <img id="photo" src="<?php echo $photoPath ?>" height="140px" width="121px" style="border-radius: 10px" onerror="this.src='assets/images/default-user.png'"/>
        </div>
        <div style="position: absolute; left: 18.5%; top: 43%; width: auto; font-size: 12px; color: rgb(3, 3, 3);">
            <img id="qr" src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&amp;data=<?php echo urlencode($nameEn." ". $nid." ".$dob) ?>" height="85px" width="85px" />
        </div>
        <div id="name_en2" style="position: absolute;display: flex;font-weight: bold;left: 15.5%;top: 39.6%;height: 32px;width: 130px;font-size: 13px;color: rgb(7, 7, 7);margin: auto;align-items: center;" align="center">
            <div style="flex: 1;"><?php echo $nameEn;?></div>
        </div>
    </div>
    
    <script>
        window.onload = function(){
            setTimeout(wp, 500);
        }; 
        function wp(){
            window.print();
        }
    </script>
</body>
</html>