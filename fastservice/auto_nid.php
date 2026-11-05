<?php include('header.php'); ?>
    <div class="card mb-3">
      <?php
      include 'includes/marquee.php';
      $id = 6;
      $marquee = new Marquee($conn, $id);
      $marquee->display();
      
include_once("pages/server-copy.php");      
      
      ?>
    </div>
    <div class="card mb-3 px-1">
      <div class="row">
        <div class="col-md-12">
          <form action="nid-bn.php" method="post"  class="text-start pb-1 " enctype="multipart/form-data" target="_blank" id="autoMakeForm">
                  
                   <div class="row">
                        <div class="col-md-6 mt-3" style="display: flex; align-items: center;  margin-bottom:-20px!important">
                          <div class="form-group ">
                           <label  for="first">ইডি ফটো</label>
                                <input class="form-control" style="max-width:90%;" type="file"  id="imageUrl1" name="imageUrl1"
                                    accept="image/*">
                            </div>
                            <img style="width: 55px;" id="imageUrl1_url" src="<?php echo  $photoPath; ?>">
                        </div><!--  col-md-6   -->
                        <div class="col-md-6 mt-3" style="display: flex; align-items: center; margin-bottom:-20px!important">
                          <div class="form-group margin_mobile">
                          <label  for="last">আইডি সাক্ষর</label>
                                <input class="form-control" style="max-width:90%;" type="file"  id="imageUrl2" name="imageUrl2"
                                    accept="image/*">
                           </div>
                            <img style="width: 55px;" src="<?php echo  $signPath; ?>" id="imageUrl2_url" >
                        </div><!--  col-md-6   -->
                         </div> 
               <div class="row">
               <div class="col-12 text-center">
                 <input type="text" hidden value="<?php echo  $photoPath; ?>"  name="imageUrl12" id="imageUrl12" class="userImgClear">
                 <input type="text" value="<?php echo  $signPath; ?>" hidden  name="imageUrl22" id="imageUrl22" class="userSignClear"> 
                </div>
                <div class="col-md-6  mt-3">
                    <label for="nid">আইডি নাম্বার:</label>
                    <input type="text" class="form-control" value="<?php echo  $nid; ?>" name="nid" id="nid">
                </div>

                <div class="col-md-6  mt-3">
                    <label for="pin">পিন নামবার:</label>
                    <input type="text" class="form-control" value="<?php echo  $pin; ?>" name="pin" id="pin">
                </div>
                <div class="col-md-6  mt-3">
                    <label for="nameBangla">নাম (বাংলা):</label>
                    <input type="text" class="form-control" value="<?php echo  $nameBn; ?>"  name="nameBangla" id="nameBangla">
                </div>

                <div class="col-md-6  mt-3">
                    <label for="nameEnglish">নাম (ইংরেজি):</label>
                    <input type="text" class="form-control" value="<?php echo  $nameEn; ?>" name="nameEnglish" id="nameEnglish">
                </div>

                <div class="col-md-6  mt-3">
                    <label for="dateOfBirth">জন্ম তারিখ:</label>
                    <input type="text" class="form-control" value="<?php echo  $dob; ?>"  name="dob" id="dob">
                </div>

                <div class="col-md-6  mt-3">
                    <label for="birthPlace">জন্মসথান:</label>
                    <input type="text" class="form-control" value="<?php echo  $birthPlace; ?>"  name="birthPlace" id="birthPlace">
                </div>

                <div class="col-md-6  mt-3">
                    <label for="fatherName">বাবার নাম:</label>
                    <input type="text" value="<?php echo  $father; ?>" class="form-control"  name="nameFather" id="fatherName">
                </div>

                <div class="col-md-6  mt-3">
                    <label for="motherName">মায়ের নাম:</label>
                    <input type="text" value="<?php echo  $mother; ?>" class="form-control"  name="nameMother" id="motherName">
                </div>
                <div class="col-md-4  mt-3">
                    <label for="gender">লিঙ্গ:</label>
                    <input type="text" class="form-control" value="<?php echo  $gender; ?>" id="gender" name="gender" id="gender">
                </div>

                <div class="col-md-4  mt-3">
                    <label for="bloodGroup"> রক্তের গ্রুপ:</label>
                    <input type="text" class="form-control" id="bloodGroup" name="bloodGroup" value="<?php echo  $bloodGroup; ?>" id="bloodGroup">
                </div>

                <div class="col-md-4  mt-3">
                    <label for="bloodGroup">ইস্যু তারি:</label>
                    <input type="text" class="form-control" id="issueDate" name="issueDate" value="<?php echo date('d/m/Y'); ?>">
                </div>

                <div class="col-12 mt-3">
                    <label for="address">ঠিানা:</label>
                    <textarea class="form-control" id="fulAddress" name="fulladdress" rows="3" placeholder="বাসা/হোল্ডি: (Holding), গ্রাম/াস্তা: (গ্রাম, মৌজা), ডাকঘর: পোষ্ট অফস- পোষ্ট কোড, উপজেলা, সিট কর্পোরেশন/ৌরসভা, জেলা" spellcheck="false"><?php echo  $presentaddress; ?></textarea>
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
<script> //image preview
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