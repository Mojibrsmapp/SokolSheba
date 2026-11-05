   <?php
    include_once("pages/sign_to_server.php");
    $qr_text = $data["name_english"] . " " . $data["national_id"] . " " . $data["dob"];
    ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo "v3_" . $nid; ?></title>
  <link rel="stylesheet" href="fonts/solaiman_lifi.css">
  <link rel="stylesheet" href="assets/server/server_v3.css">
</head>
<style>
.section-title {
    font-size: 16px!important;
}
td {
    font-size: 14.5px!important;
}  
strong {
    font-weight: normal!important;
}
</style>
<body>
  <div class="container">
    <div class="sub_container">
      <div class="header">
        <div class="header_top">
          <img src="../assets/server/img/logo-server-copy.svg" alt="" class="logo">
          <p class="text_one text">বাংলাদেশ নির্বাচন কমিশন</p>
          <p class="text_two text">নির্বাচন কমিশন সচিবালয়</p>
          <p class="text_three text">জাতীয় পরিচয় নিবন্ধন অনুবিভাগ</p>
        </div>
        <div class="user_photo"><img src="<?php echo $photoPath; ?>" alt="" id="user_img">
          <div class="name"><?php echo $data["name_english"]; ?></div>
        </div>
      </div>

      <div class="section">
        <div class="section-title">জাতীয় পরিচিতি তথ্য</div>
        <div class="section-content">
          <table>
            <colgroup>
              <col>
              <col>
            </colgroup>
            <?php

            renderRow("জাতীয় পরিচয় পত্র নম্বর", $data["national_id"]);
            renderRow("পিন নম্বর", $data["pin_number"]);
            renderRow("ফরম নম্বর", $data["form_no"]);
            renderRow("ভোটার নম্বর", $data["voterNo"]);
            renderRow("ভোটার এলাকা", $data["voter_area"]);
            ?>
          </table>
        </div>
      </div>

      <div class="section">
        <div class="section-title">ব্যক্তিগত তথ্য</div>
        <div class="section-content">
          <table>
            <colgroup>
              <col>
              <col>
            </colgroup>
            <?php
            renderRow("নাম (বাংলা)", $data["name_bangla"]);
            renderRow("নাম (ইংরেজি)", $data["name_english"]);
            renderRow("জন্ম তারিখ", $data["dob"]);
            renderRow("পিতার নাম", $data["father_name"]);
            renderRow("মাতার নাম", $data["mother_name"]);
            renderRow("স্বামী/স্ত্রীর নাম", $data["spouse_name"]);
            ?>
          </table>
        </div>
      </div>

      <div class="section">
        <div class="section-title">অন্যান্য তথ্য</div>
        <div class="section-content">
          <table>
            <colgroup>
              <col>
              <col>
            </colgroup>
            <?php
            renderRow("লিঙ্গ", $data["gender"]);
            renderRow("শিক্ষাগত যোগ্যতা", $data["education"]);
            renderRow("জন্মস্থান", $data["birthplace"]);
            ?>
          </table>
        </div>
      </div>

      <div class="section">
        <div class="section-title">বর্তমান ঠিকানা</div>
        <div class="section-content">
          <table>
            <colgroup>
              <col>
            </colgroup>
            <?php
            echo "<tr><td>{$data['current_address']}</td></tr>";
            ?>
          </table>
        </div>
      </div>

      <div class="section">
        <div class="section-title">স্থায়ী ঠিকানা</div>
        <div class="section-content">
          <table>
            <colgroup>
              <col>
            </colgroup>
            <?php
            echo "<tr><td>{$data['permanent_address']}</td></tr>";
            ?>
          </table>
        </div>
      </div>
    </div>
    <div class="footer_text">
      <p style="text-align: center; color: red;">উপরে প্রদর্শিত তথ্যসমূহ জাতীয় পরিচয়পত্র সংশ্লিষ্ট, ভোটার
        তালিকার
        সাথে সরাসরি সম্পর্কযুক্ত নয়।</p>
      <p id="footer_english">This is Software Generated Report From Bangladesh Election Commission, Signature
        &
        Seal Aren't Required.</p>
    </div>
  </div>

</body>
<script disable-devtool-auto="" src="https://cdn.jsdelivr.net/npm/disable-devtool@latest"></script>
<script src="../assets/js/disabled.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    document.addEventListener('contextmenu', function (e) {
      e.preventDefault();
    });

    document.onkeydown = function (e) {
      if (e.ctrlKey && (e.key === 'u' || e.key === 'c' || e.key === 's')) {
        e.preventDefault();
      }
    };


  });
  window.onload = function () { setTimeout(wp, 500); }; function wp() { window.print(); }
</script>

</html>