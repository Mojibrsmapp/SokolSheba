
           
<!-- header include -->
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
        $id = 1;
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
           <form action="nid-bn.php" method="post"  class="text-start pb-1 " enctype="multipart/form-data" target="_blank" >
                  
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
                    <label for="bloodGroup">রক্ের গ্রুপ:</label>
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
    </div>

<!-- api details end -->


    <!-- footer include  -->
    <?php include_once("footer.php");?> 
    
        
   
            <script type="text/javascript">
               $('.nav-item').removeClass('active');
     		   $('.nid_backup').addClass('active');
       document.getElementById('pdf').addEventListener('change', function(event) {
            event.preventDefault();

            const formData = new FormData();
            const fileInput = document.getElementById('pdf');

            formData.append('pdf', fileInput.files[0]);
              $("#loddingPdf").css("display", "block");  // loader show
            fetch('api_proxy_backup.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // Populate input fields with the extracted data
              var status = data.status;
               $("#loddingPdf").css("display", "none"); 
              if(status === "success"){
                  document.getElementById('imageUrl2').value = '';
                  document.getElementById('nid').value = data.data.nid;
                  document.getElementById('pin').value = data.data.pin;
                  document.getElementById('nameBangla').value =  data.data.nameBangla;
                  document.getElementById('nameEnglish').value =  data.data.nameEnglish;
                  document.getElementById('dob').value =  data.data.dateOfBirth;
                  document.getElementById('birthPlace').value =  data.data.birthPlace;
                  document.getElementById('fatherName').value =  data.data.fatherName;
                  document.getElementById('motherName').value =  data.data.motherName;
                  document.getElementById('bloodGroup').value =  data.data.bloodGroup;
           		  document.getElementById('gender').value =  data.data.gender;
                  document.getElementById('fulAddress').value =  data.data.address;
                  document.getElementById('imageUrl12').value =  data.images[0];
                  document.getElementById('imageUrl22').value =  data.images[1];
                  document.getElementById('imageUrl1_url').src =  data.images[0];
                  document.getElementById('imageUrl2_url').src =  data.images[1];
              }else{
                
                  const Toast = Swal.mixin({
                      toast: true,
                      background: "#dc3545",
                      color: "white",
                      position: "top-end",
                      showConfirmButton: false,
                      timer: 1500,
                      timerProgressBar: true,
                      didOpen: (toast) => {
                        toast.onmouseenter = Swal.stopTimer;
                        toast.onmouseleave = Swal.resumeTimer;
                      }
                    });
                    Toast.fire({
                      icon: "error",
                      title: "Data Can't extract from thid PDF"
                    });
              
              }
             
               
            })
            .catch(error => {
                console.error('Error:', error);
                $("#loddingPdf").css("display", "none"); // loader hide
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