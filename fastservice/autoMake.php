<?php include('header.php'); ?>
<link rel="stylesheet" href="assets/css/server.css">
<style>
  #autoMakeForm{
  display:none;
  }
</style>
<div id="loddingPdf">
  <div id="progress_main">
    <svg>
      <text x="50%" y="50%" dy=".35em" text-anchor="middle">
        তথ্য খোজা হচ্ছে
      </text>
    </svg>
  </div>
</div>
    <div class="card mb-3">
      <?php
      include 'includes/marquee.php';
      $id = 6;
      $marquee = new Marquee($conn, $id);
      $marquee->display();
      ?>
    </div>
    <div class="card mb-3 px-1">
      <div class="row">
        <div class="col-md-4"></div>
        <div class="col-md-5">
          <div class="">
            <form id="server_form" method="post" class="py-5 text-center">
              <h2> অটো মেইক </h2>
              <div class="inputIcon">
                <label for="nidNumber">NID number (10 or 17 digits)</label> <i class="s_icon fa fa-id-card"></i>
                <input type="number" name="nid" id="nidNumber" required placeholder="Type Nid Number">
              </div>
              <div class="inputIcon">
                <label for="DateOfBirth">Date of Birth (YYYY-MM-DD)</label> <i class="s_icon fa fa-calendar"></i>
                <input type="text" name="dob" required id="DateOfBirth" placeholder="2000-12-21">
              </div>
              <input type="text" hidden value="" name="email">
              <?php
                if ($balance > 5) {
                  echo '<button type="submit" class="btn btn-primary w-100 mt-3" name="submit">তথ্য অনুসন্ধান</button>';
                } else {
                  echo '<button class="btn btn-primary w-100 mt-3" disabled name="submit">Recharge Please</button>';
                }
              ?>
            </form>
          </div>
        </div>
        <div class="col-md-3"></div>
        <div class="col-md-12">
          <form action="nid-bn.php" method="post"  class="text-start pb-1 " enctype="multipart/form-data" target="_blank" id="autoMakeForm">
                  
                   <div class="row">
                        <div class="col-md-6 mt-3" style="display: flex; align-items: center;  margin-bottom:-20px!important">
                          <div class="form-group ">
                           <label  for="first">ইডি ফটো</label>
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
                    <label for="birthPlace">জন্মসথান:</label>
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
                <div class="col-md-4  mt-3">
                    <label for="gender">লিঙ্গ:</label>
                    <input type="text" class="form-control" id="gender" name="gender" id="gender">
                </div>

                <div class="col-md-4  mt-3">
                    <label for="bloodGroup"> রক্তের গ্রুপ:</label>
                    <input type="text" class="form-control" id="bloodGroup" name="bloodGroup" id="bloodGroup">
                </div>

                <div class="col-md-4  mt-3">
                    <label for="bloodGroup">ইস্যু তারি:</label>
                    <input type="text" class="form-control" id="issueDate" name="issueDate" value="<?php echo date('d/m/Y'); ?>">
                </div>

                <div class="col-12 mt-3">
                    <label for="address">ঠিানা:</label>
                    <textarea class="form-control" id="fulAddress" name="fulladdress" rows="3" placeholder="বাসা/হোল্ডি: (Holding), গ্রাম/াস্তা: (গ্রাম, মৌজা), ডাকঘর: পোষ্ট অফস- পোষ্ট কোড, উপজেলা, সিট কর্পোরেশন/ৌরসভা, জেলা" spellcheck="false" ></textarea>
                </div> 
               </div>
               <div class="text-center mt-3">
               <button class="btn btn-primary m-auto px-3" type="submit">ডাউনলোড কর্ড</button>
               </div>
             </form>
        </div>
      </div>
    </div>

<!-- Loading Scripts -->
<?php include('footer.php'); ?>
<script>
$('#server_form').on('submit', function(event) {
    event.preventDefault();
    var nid = $('#nidNumber').val();
    var dob = $('#DateOfBirth').val();
    var requestUrl = 'insert_auto_make.php';

    Swal.fire({
        title: 'Are you sure?',
        html: `আপনি কি তথ্য NID: ${'<b>' + nid} Dob: ${dob + '</b>'} সাবমিট করতে চান?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, submit it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $("#loddingPdf").css("display", "block");

            $.ajax({
                url: requestUrl,
                type: 'POST',
                data: { nid: nid, dob: dob },
                dataType: 'json',
                success: function(response) {
                    $("#loddingPdf").css("display", "none");
                    
                    // Log the entire response to see its structure
                    console.log("Response Data:", response);

                    if (response.status === 'success') {
                        $("#autoMakeForm").css("display", "block");
                        data = response.data;
                        document.getElementById('nid').value = data.nationalId;
                        document.getElementById('pin').value = data.pin;
                        document.getElementById('nameBangla').value = data.nameBn;
                        document.getElementById('nameEnglish').value =  data.nameEn;
                        document.getElementById('dob').value =  data.dateOfBirth;
                        document.getElementById('birthPlace').value =  data.birthPlace;
                        document.getElementById('fatherName').value =  data.father;
                        document.getElementById('motherName').value =  data.mother;
                        //document.getElementById('bloodGroup').value =  data.bloodGroup;
                        document.getElementById('gender').value =  data.gender;
                        document.getElementById('fulAddress').value =  data.presentAddr;
                        document.getElementById('imageUrl12').value =  data.photo_path;
                        document.getElementById('imageUrl1_url').src = data.photo_path;


                      
                       
                    } else {
                        console.error("Error message:", response.message);
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function() {
                    $("#loddingPdf").css("display", "none");
                    console.log("Error: Request could not be sent");
                    Swal.fire('Error', "An error occurred while sending the request", 'error');
                }
            });
        }
    });
});
              //image preview
      const imageInput = document.getElementById('imageUrl2');
    const imagePreview = document.getElementById('imageUrl2_url');
    const hiddenInput = document.getElementById('imageUrl22');

    imageInput.addEventListener('change', function(event) {
        const file = event.target.files[0];
        
        if (file) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                imagePreview.src = e.target.result;
            };

            reader.readAsDataURL(file);
        } else {
            imagePreview.src = '';
        }
    });
 
</script>