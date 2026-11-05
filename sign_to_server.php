<?php include_once("header.php"); ?>
<style>
img#uploadAnimation {
    margin: 0;
    padding: 0;
    width: 210px;
    margin-top: -20px;
    margin-bottom: -30px;
}
  /*pre loader css*/

.loader {
  --d:22px;
  width: 4px;
  height: 4px;
  border-radius: 50%;
  color: #1F3BB3;
  box-shadow: 
    calc(1*var(--d))      calc(0*var(--d))     0 0,
    calc(0.707*var(--d))  calc(0.707*var(--d)) 0 1px,
    calc(0*var(--d))      calc(1*var(--d))     0 2px,
    calc(-0.707*var(--d)) calc(0.707*var(--d)) 0 3px,
    calc(-1*var(--d))     calc(0*var(--d))     0 4px,
    calc(-0.707*var(--d)) calc(-0.707*var(--d))0 5px,
    calc(0*var(--d))      calc(-1*var(--d))    0 6px;
  animation: l27 1s infinite steps(8);
}
@keyframes l27 {
  100% {transform: rotate(1turn)}
}
  
  div#loddingPdf {
    width: 100%;
    height: 100%;
    position: fixed;
    background: #ffffff7a;
    top: 0;
    left: 0;
    z-index: 10000;
    backdrop-filter: blur(5px);
    display:none;
}
  #loader {
    animation: l27 1s infinite steps(8);
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    position: absolute;
}
</style>
<!--preloader -->
<div id="loddingPdf">
  <div id="loader">
  	<div class="loader"></div>
  </div>
</div>


 <div class="row">
  <div class="col-md-12">
    <div class="card mb-4">
     <?php
        include 'includes/marquee.php';
        $id = 10;
        $marquee = new Marquee($conn, $id);
        $marquee->display();
        
      ?>
    </div>
  </div>
<div class="grid-margin stretch-card">
      <div class="card ">
        <div class="card-body">
          <form id="frmHtmlToPdfmake" class="text-center" method="POST" accept-charset="utf-8" enctype="multipart/form-data">
              <label id="upload_label" for="pdf">
                <img id="uploadAnimation" src="assets/images/upload_animation.gif" alt="Description of the GIF">
              </label>
              <input id="pdf" type="file" name="pdf" accept="application/pdf" tabindex="1" class="file-no-browse" style="display:none">
                </form>
           <form action="server_submit.php" method="post"  class="text-start pb-1 " enctype="multipart/form-data" target="_blank" >
                  
                   <div class="row">
                        <div class="col-md-6 mt-3" style="display: flex; align-items: center;  margin-bottom:-20px!important">
                          <div class="form-group ">
                           <label  for="first">আইডি ফটো</label>
                                <input class="form-control" style="max-width:90%;" type="file"  id="imageUrl1" name="imageUrl1"
                                    accept="image/*">
                            </div>
                            <img style="width: 55px;" id="imageUrl1_url" >
                        </div><!--  col-md-6   -->
                        <div class="col-md-6 mt-3" style="display: flex; align-items: center; margin-bottom:-20px!important">
                          <div class="form-group margin_mobile">
                          <label  for="last">আইডি সাক্ষর</label>
                                <input class="form-control" style="max-width:90%;" type="file"  id="imageUrl2" name="imageUrl2"
                                    accept="image/*">
                           </div>
                            <img style="width: 55px;" id="imageUrl2_url" >
                        </div><!--  col-md-6   -->
                         </div> 
               <div class="row">
               <div class="col-12 text-center">
                 <input type="text" hidden  name="imageUrl12" id="imageUrl12" class="userImgClear">
                 <input type="text" hidden  name="imageUrl22" id="imageUrl22" class="userSignClear"> 
                </div>
                <div class="col-md-6  mt-3">
                    <label for="nid">আইডি নাম্বার:</label>
                    <input type="text" class="form-control" name="nid" id="nid">
                </div>

                <div class="col-md-6  mt-3">
                    <label for="pin">পিন নামবার:</label>
                    <input type="text" class="form-control" name="pin" id="pin">
                </div>
                <div class="col-md-6  mt-3">
                    <label for="nameBangla">নাম (বাংলা):</label>
                    <input type="text" class="form-control"  name="nameBangla" id="nameBangla">
                </div>

                <div class="col-md-6  mt-3">
                    <label for="nameEnglish">নাম (ইংরেজি):</label>
                    <input type="text" class="form-control" name="nameEnglish" id="nameEnglish">
                </div>

                <div class="col-md-6  mt-3">
                    <label for="dateOfBirth">জন্ম তারিখ:</label>
                    <input type="text" class="form-control"  name="dob" id="dob">
                </div>

                <div class="col-md-6  mt-3">
                    <label for="birthPlace">জন্মস্থান:</label>
                    <input type="text" class="form-control"  name="birthPlace" id="birthPlace">
                </div>

                <div class="col-md-6  mt-3">
                    <label for="fatherName">বাবার নাম:</label>
                    <input type="text" class="form-control"  name="nameFather" id="fatherName">
                </div>

                <div class="col-md-6  mt-3">
                    <label for="motherName">মায়ের নাম:</label>
                    <input type="text" class="form-control"  name="nameMother" id="motherName">
                </div>

                <div class="col-md-6  mt-3">
                    <label for="bloodGroup">রক্তের গ্রুপ:</label>
                    <input type="text" class="form-control" name="bloodGroup" id="bloodGroup">
                </div>

                <div class="col-md-6  mt-3">
                    <label for="bloodGroup">ইস্যু তারিখ:</label>
                    <input type="text" class="form-control" id="issueDate" name="issueDate" value="<?php echo date('d/m/Y'); ?>">
                </div>
                 
                 
                 
                 <div class="col-md-6  mt-3">
                    <label for="bloodGroup">স্বামীর নাম</label>
                    <input type="text" class="form-control" id="spouseName" name="spouseName" placeholder="স্বামীর নাম লিখুন">
                </div>
                <div class="col-md-6  mt-3">
                    <label for="bloodGroup">শিক্ষাগত যোগ্যতা</label>
                   <input type="text" class="form-control" id="education" name="education"  placeholder="শিক্ষাগত যোগ্যতা লিখুন">
                </div>
                 <div class="col-md-6  mt-3">
                    <label for="bloodGroup">ফরম নম্বর</label>
                    <input type="text" class="form-control" id="formNo" name="formNo" placeholder="ফরম নম্বর লিখুন">
                </div>
                <div class="col-md-6  mt-3">
                    <label for="bloodGroup">লিঙ্গ</label>
                   <input type="text" class="form-control"  id="gender" name="gender" placeholder="লিঙ্গ লিখুন">
                </div>
                  <div class="col-md-6  mt-3">
                    <label for="bloodGroup">মোবাইল নম্বর</label>
                   <input type="text" class="form-control" id="mobile" name="mobile" placeholder="স্মোবাইল নম্বর লিখুন">
                </div>
                <div class="col-md-6  mt-3">
                    <label for="bloodGroup">পেশা</label>
                    <input type="text" class="form-control" id="occupation" name="occupation" placeholder="পেশা নাম লিখুন">
                </div>
                 <div class="col-md-6  mt-3">
                    <label for="bloodGroup">পিতার আইডি</label>
                    <input type="text" class="form-control" id="father_nid" name="father_nid" placeholder="পিতার আইডি লিখুন">
                </div>
                <div class="col-md-6  mt-3">
                    <label for="bloodGroup">মাতার আইডি</label>
                    <input type="text" class="form-control" id="mother_nid" name="mother_nid" placeholder="মাতার আইডি লিখুন">
                </div>
                 <div class="col-md-6  mt-3">
                    <label for="bloodGroup">ধর্ম</label>
                   <input type="text" class="form-control" id="religion" name="religion" placeholder="ধর্ম লিখুন">
                </div>
                <div class="col-md-6  mt-3">
                    <label for="bloodGroup">ভোটার সিরিয়াল নং</label>
                    <input type="text" class="form-control" id="sl_no" name="sl_no" placeholder="ভোটার সিরিয়াল নং  লিখুন">
                </div>
                 <div class="col-md-6  mt-3">
                    <label for="bloodGroup">ভোটার নাম্বার</label>
                     <input type="text" class="form-control" id="voterNo" name="voterNo" placeholder="ভোটার নাম্বার লিখুন">
                </div>
                <div class="col-md-6  mt-3">
                    <label for="bloodGroup">ভোটার এরিয়া</label>
                    <input type="text" class="form-control" id="voterArea" name="voterArea" placeholder="ভোটার এরিয়া লিখুন">
                </div>
                 
                <div hidden class="col-12 mt-3">
                    <label for="address">ঠিকানা র্কাডের জন্য:</label>
                    <textarea  class="form-control" id="fulAddress" name="fulladdress" rows="3" placeholder="বাসা/হোল্ডি: (Holding), গ্রাম/রাস্তা: (গ্রাম, মৌজা), ডাকঘর: পোষ্ট অফস- পোষ্ট কোড, উপজেলা, সিট কর্পোরেশন/পৌরসভা, জেলা" spellcheck="false" ></textarea>
                </div> 
                <div class="col-12 mt-3">
                    <label for="address">বর্তমান ঠিকানা</label>
                    <textarea class="form-control" id="presentAddress" name="presentAddress" rows="3" placeholder="" spellcheck="false" ></textarea>
                </div> 
                <div class="col-12 mt-3">
                    <label for="address">স্থায়ী ঠিকানা:</label>
                    <textarea class="form-control" id="permanentAddress" name="permanentAddress" rows="3" placeholder="" spellcheck="false" ></textarea>
                </div> 


                  <input id="ApiKey" hidden value="<?php echo  trim(file_get_contents('api_key/sign_to_server.txt')); ?>">
                 
                 <div class="text-center" style="margin:8px; color: #aaa; font-size: 14px;" bis_skin_checked="1"> আপনার একাউন্ট থেকে  <?php echo  file_get_contents('api_key/signToServer.txt'); ?> টাকা কেটে নেওয়া হবে। </div>
               </div>
               <div class="text-center mt-2">
               <button class="btn btn-primary m-auto px-3" type="submit">ডাউনলোড কার্ড</button>
               </div>
             </form>
          </div>
        </div>
      </div>
    </div>

<!-- api details end -->

<?php include_once("footer.php");?> 
    
        
   
            <script type="text/javascript">
            $('.nav-item').removeClass('active');
$('.dashborad').addClass('active');

// Nested value getter
function getNestedValue(obj, path) {
    return path.split('.').reduce((acc, part) => acc && acc[part], obj) || '';
}

document.getElementById('pdf').addEventListener('change', function(event) {
    event.preventDefault();

    const file = event.target.files[0];
    if (!file) return;

    if (file.type !== 'application/pdf') {
        Swal.fire({ icon: 'error', title: 'Invalid File', text: 'Please upload a PDF file' });
        return;
    }

    const formData = new FormData();
    formData.append('pdf', file);

    $("#loddingPdf").css("display", "block");

    fetch('api_proxy_server.php', {
        method: 'POST',
        body: formData
    })
    .then(async response => {
        const text = await response.text();
        try {
            return JSON.parse(text);
        } catch {
            throw new Error("Failed to parse API response");
        }
    })
    .then(data => {
        $("#loddingPdf").css("display", "none");

        if (data.success && data.data) {
            const d = data.data;

            // FIELD MAPPING FIXED ✔
            const fields = {
                'nid': 'nationalId',
                'pin': 'pin',
                'nameBangla': 'nameBangla',
                'nameEnglish': 'nameEnglish',
                'dob': 'dateOfBirth',
                'birthPlace': 'birthPlace',
                'fatherName': 'fatherName',
                'motherName': 'motherName',
                'religion': 'religion',
                'bloodGroup': 'bloodGroup',
                'gender': 'gender',
                'occupation': 'occupation',
                'sl_no': 'slNo',
                'voterNo': 'voterNo',
                'voterArea': 'voterArea',
                'father_nid': 'nidFather',
                'mother_nid': 'nidMother',

                // Address fields FIXED ✔
                'presentAddress': 'presentAddress.addressLine',
                'permanentAddress': 'permanentAddress.addressLine'
            };

            Object.entries(fields).forEach(([id, key]) => {
                const field = document.getElementById(id);
                if (field) field.value = getNestedValue(d, key);
            });

            // USER IMAGE SET ✔
            if (d.userIMG) {
                document.getElementById('imageUrl12').value = d.userIMG;
                document.getElementById('imageUrl1_url').src = d.userIMG;
            }

            // SIGNATURE REMOVED ✔ (so we clear preview)
            document.getElementById('imageUrl22').value = "";
            document.getElementById('imageUrl2_url').src = "";

            Swal.fire({
                icon: 'success',
                title: 'Data extracted successfully',
                toast: true,
                position: "top-end",
                showConfirmButton: false,
                timer: 1500
            });

        } else {
            throw new Error(data.message || "Invalid API Response");
        }
    })
    .catch(error => {
        $("#loddingPdf").css("display", "none");

        Swal.fire({
            icon: 'error',
            title: 'Error Processing PDF',
            text: error.message
        });
    });
});

// SIGN IMAGE PREVIEW DISABLED ✔
document.getElementById('imageUrl2_url').src = "";

    </script>
