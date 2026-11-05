<?php include_once("header.php"); ?>

<!-- FontAwesome & Google Fonts -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
    * {
        font-family: 'Hind Siliguri', sans-serif;
    }

    /* Preloader Styling */
    #loddingPdf {
        width: 100%;
        height: 100%;
        position: fixed;
        background: rgba(255, 255, 255, 0.85);
        top: 0;
        left: 0;
        z-index: 10000;
        backdrop-filter: blur(8px);
        display: none;
    }

    .loader-container {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
    }

    .custom-loader {
        width: 50px;
        height: 50px;
        border: 5px solid #eef2f7;
        border-top: 5px solid #4e73df;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
        margin: 0 auto 15px;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Card & Glassmorphism Design */
    .custom-card {
        background: #ffffff;
        border: none;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
    }

    /* Upload Box & Animations */
    .upload-box {
        border: 2px dashed #4e73df;
        border-radius: 20px;
        padding: 30px 20px;
        background: linear-gradient(135deg, rgba(78,115,223,0.05) 0%, rgba(54,185,204,0.05) 100%);
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .upload-box:hover {
        background: rgba(78, 115, 223, 0.08);
        border-color: #2e59d9;
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(78, 115, 223, 0.15);
    }

    /* Icon Animation Effects */
    .upload-icon-anim {
        width: 100px;
        transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        animation: floatIcon 3s ease-in-out infinite;
    }

    @keyframes floatIcon {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-8px); }
    }

    .upload-box:hover .upload-icon-anim {
        animation: none;
        transform: scale(1.15) rotate(3deg);
    }

    /* Form Labels & Animated Inputs */
    .form-group-custom {
        position: relative;
    }

    .form-label-custom {
        font-weight: 600;
        color: #36b9cc;
        margin-bottom: 6px;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .form-control-custom {
        border-radius: 12px;
        border: 1.5px solid #e3e6f0;
        padding: 12px 16px;
        font-size: 14px;
        transition: all 0.3s ease;
        background-color: #f8f9fc;
    }

    .form-control-custom:focus {
        border-color: #4e73df;
        background-color: #ffffff;
        box-shadow: 0 0 12px rgba(78, 115, 223, 0.2);
        outline: none;
        transform: translateY(-1px);
    }

    /* Image Preview Container */
    .preview-box {
        border: 2px dashed #eaecf4;
        border-radius: 14px;
        padding: 12px;
        background: #ffffff;
        display: flex;
        align-items: center;
        gap: 15px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.02);
    }

    .preview-img {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 10px;
        border: 2px solid #e3e6f0;
        background: #f8f9fc;
    }

    /* Gradient Button & Pulse Effect */
    .btn-gradient {
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
        color: #ffffff;
        border: none;
        border-radius: 12px;
        padding: 14px 40px;
        font-size: 16px;
        font-weight: 700;
        box-shadow: 0 6px 18px rgba(78, 115, 223, 0.35);
        transition: all 0.3s ease;
    }

    .btn-gradient:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(78, 115, 223, 0.45);
        color: #ffffff;
    }

    .charge-badge {
        background: rgba(246, 194, 62, 0.15);
        color: #d68100;
        font-weight: 600;
        padding: 8px 20px;
        border-radius: 25px;
        display: inline-block;
        font-size: 14px;
    }
</style>

<!-- Preloader -->
<div id="loddingPdf">
    <div class="loader-container">
        <div class="custom-loader"></div>
        <h6 class="text-primary fw-bold mt-2"><i class="fas fa-file-pdf me-2"></i>PDF প্রসেস করা হচ্ছে, অপেক্ষা করুন...</h6>
    </div>
</div>

<div class="container-fluid px-3 py-2">
    <!-- Marquee Banner -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card custom-card">
                <?php
                    include 'includes/marquee.php';
                    $id = 10;
                    $marquee = new Marquee($conn, $id);$marquee->display();
                ?>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card custom-card p-4">
                
                <!-- Animated Drag & Drop PDF Upload Box -->
                <div class="text-center mb-4">
                    <form id="frmHtmlToPdfmake" method="POST" accept-charset="utf-8" enctype="multipart/form-data">
                        <label id="upload_label" for="pdf" class="upload-box w-100">
                            <img id="uploadAnimation" class="upload-icon-anim" src="assets/images/upload_animation.gif" alt="Upload Animation">
                            <h5 class="fw-bold text-primary mt-3 mb-1"><i class="fas fa-cloud-upload-alt me-2"></i>অটো ফিল করতে PDF ক্লিক বা ড্রাগ করুন</h5>
                            <p class="text-muted small mb-0">ফাইল নির্বাচন করতে এখানে চাপুন (PDF Only)</p>
                        </label>
                        <input id="pdf" type="file" name="pdf" accept="application/pdf" class="d-none">
                    </form>
                </div>

                <!-- Main Form -->
                <form action="nid-bn.php" method="post" class="pb-1" enctype="multipart/form-data" target="_blank">
                    
                    <!-- Image Upload Fields -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="preview-box">
                                <div class="flex-grow-1">
                                    <label class="form-label-custom" for="imageUrl1"><i class="fas fa-camera text-primary"></i> ছবি আপলোড</label>
                                    <input class="form-control form-control-custom" type="file" id="imageUrl1" name="imageUrl1" accept="image/*">
                                </div>
                                <img id="imageUrl1_url" class="preview-img" src="assets/images/placeholder.png" alt="Profile">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="preview-box">
                                <div class="flex-grow-1">
                                    <label class="form-label-custom" for="imageUrl2"><i class="fas fa-file-signature text-info"></i> স্বাক্ষর আপলোড</label>
                                    <input class="form-control form-control-custom" type="file" id="imageUrl2" name="imageUrl2" accept="image/*">
                                </div>
                                <img id="imageUrl2_url" class="preview-img" src="assets/images/placeholder.png" alt="Signature">
                            </div>
                        </div>
                    </div>

                    <!-- Hidden Image Inputs -->
                    <input type="text" hidden name="imageUrl12" id="imageUrl12" class="userImgClear">
                    <input type="text" hidden name="imageUrl22" id="imageUrl22" class="userSignClear"> 

                    <!-- Data Fields -->
                    <div class="row g-3">
                        <div class="col-md-6 form-group-custom">
                            <label class="form-label-custom" for="nid"><i class="fas fa-id-card text-primary"></i> এন আইডি নাম্বার</label>
                            <input type="text" class="form-control form-control-custom" name="nid" id="nid" placeholder="NID নম্বর">
                        </div>

                        <div class="col-md-6 form-group-custom">
                            <label class="form-label-custom" for="pin"><i class="fas fa-key text-warning"></i> পিন নাম্বার</label>
                            <input type="text" class="form-control form-control-custom" name="pin" id="pin" placeholder="PIN নম্বর">
                        </div>

                        <div class="col-md-6 form-group-custom">
                            <label class="form-label-custom" for="nameBangla"><i class="fas fa-user text-success"></i> নাম (বাংলা)</label>
                            <input type="text" class="form-control form-control-custom" name="nameBangla" id="nameBangla" placeholder="বাংলা নাম">
                        </div>

                        <div class="col-md-6 form-group-custom">
                            <label class="form-label-custom" for="nameEnglish"><i class="fas fa-user-tag text-info"></i> নাম (ইংরেজি)</label>
                            <input type="text" class="form-control form-control-custom" name="nameEnglish" id="nameEnglish" placeholder="English Name">
                        </div>

                        <div class="col-md-6 form-group-custom">
                            <label class="form-label-custom" for="dob"><i class="fas fa-calendar-alt text-danger"></i> জন্ম তারিখ</label>
                            <input type="text" class="form-control form-control-custom" name="dob" id="dob" placeholder="YYYY-MM-DD">
                        </div>

                        <div class="col-md-6 form-group-custom">
                            <label class="form-label-custom" for="birthPlace"><i class="fas fa-map-marker-alt text-secondary"></i> জন্মস্থান</label>
                            <input type="text" class="form-control form-control-custom" name="birthPlace" id="birthPlace" placeholder="জেলা/স্থান">
                        </div>

                        <div class="col-md-6 form-group-custom">
                            <label class="form-label-custom" for="fatherName"><i class="fas fa-user-friends text-dark"></i> বাবার নাম</label>
                            <input type="text" class="form-control form-control-custom" name="nameFather" id="fatherName" placeholder="বাবার নাম">
                        </div>

                        <div class="col-md-6 form-group-custom">
                            <label class="form-label-custom" for="motherName"><i class="fas fa-female text-primary"></i> মায়ের নাম</label>
                            <input type="text" class="form-control form-control-custom" name="nameMother" id="motherName" placeholder="মায়ের নাম">
                        </div>

                        <div class="col-md-4 form-group-custom">
                            <label class="form-label-custom" for="gender"><i class="fas fa-venus-mars text-purple"></i> লিঙ্গ</label>
                            <input type="text" class="form-control form-control-custom" name="gender" id="gender" placeholder="পুরুষ/মহিলা">
                        </div>

                        <div class="col-md-4 form-group-custom">
                            <label class="form-label-custom" for="bloodGroup"><i class="fas fa-tint text-danger"></i> রক্তের গ্রুপ</label>
                            <input type="text" class="form-control form-control-custom" name="bloodGroup" id="bloodGroup" placeholder="A+, B+, O+...">
                        </div>

                        <div class="col-md-4 form-group-custom">
                            <label class="form-label-custom" for="issueDate"><i class="fas fa-clock text-info"></i> ইস্যুর তারিখ</label>
                            <input type="text" class="form-control form-control-custom" id="issueDate" name="issueDate" value="<?php echo date('d/m/Y'); ?>">
                        </div>

                        <div class="col-12 form-group-custom">
                            <label class="form-label-custom" for="fulAddress"><i class="fas fa-home text-success"></i> ঠিকানা</label>
                            <textarea class="form-control form-control-custom" id="fulAddress" name="fulladdress" rows="3" placeholder="বাসা/হোল্ডিং, গ্রাম/রাস্তা, ডাকঘর, উপজেলা, জেলা" spellcheck="false"></textarea>
                        </div> 
                    </div>

                    <!-- Charge Notice -->
                    <div class="text-center mt-4">
                        <span class="charge-badge">
                            <i class="fas fa-coins me-1"></i> কার্ড তৈরির চার্জ: <strong><?php echo file_get_contents('api_key/card_make.txt'); ?> ৳</strong>
                        </span>
                    </div>

                    <!-- Submit Button -->
                    <div class="text-center mt-3">
                        <button class="btn btn-gradient" type="submit">
                            <i class="fas fa-download me-2"></i>ডাউনলোড কার্ড
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<?php include_once("footer.php");?> 

<!-- JavaScript Handlers -->
<script type="text/javascript">
$(document).ready(function() {
    $('.nav-item').removeClass('active');$('.dashborad').addClass('active');

    // Auto Upload PDF Handling
    document.getElementById('pdf').addEventListener('change', function(event) {
        event.preventDefault();
        
        const file = event.target.files[0];
        if (!file) return;
        
        if (file.type !== 'application/pdf') {
            Swal.fire({
                icon: 'error',
                title: 'ভুল ফাইল!',
                text: 'অনুগ্রহ করে শুধুমাত্র PDF ফাইল সিলেক্ট করুন'
            });
            return;
        }

        const formData = new FormData();
        formData.append('pdf', file);
        
        $("#loddingPdf").fadeIn();

        fetch('api_proxy_one.php', {
            method: 'POST',
            body: formData
        })
        .then(async response => {
            const text = await response.text();
            try {
                const data = JSON.parse(text);
                if (!response.ok) throw new Error('Network response failure');
                return data;
            } catch (e) {
                throw new Error(`JSON Error: ${e.message}`);
            }
        })
        .then(data => {
            $("#loddingPdf").fadeOut();
            if (data.success && data.data) {
                const apiData = data.data;
                
                const fields = {
                    'nid': 'nationalId',
                    'pin': 'pin',
                    'nameBangla': 'nameBangla',
                    'nameEnglish': 'nameEnglish',
                    'dob': 'dateOfBirth',
                    'birthPlace': 'birthPlace',
                    'fatherName': 'fatherName',
                    'motherName': 'motherName',
                    'bloodGroup': 'bloodGroup',
                    'gender': 'gender',
                    'fulAddress': 'address'
                };

                Object.entries(fields).forEach(([elementId, apiField]) => {
                    const element = document.getElementById(elementId);
                    if (element) {
                        element.value = apiData[apiField] || '';
                    }
                });

                if (apiData.userIMG) {
                    document.getElementById('imageUrl12').value = apiData.userIMG;
                    document.getElementById('imageUrl1_url').src = apiData.userIMG;
                }
                if (apiData.signIMG) {
                    document.getElementById('imageUrl22').value = apiData.signIMG;
                    document.getElementById('imageUrl2_url').src = apiData.signIMG;
                }

                const Toast = Swal.mixin({
                    toast: true,
                    position: "top-end",
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true,
                    background: "#28a745",
                    color: "white"
                });

                Toast.fire({
                    icon: "success",
                    title: "তথ্য অটো-ফিল হয়ে গেছে!"
                });
            } else {
                throw new Error(data.message || 'PDF থেকে ডাটা পাওয়া যায়নি');
            }
        })
        .catch(error => {
            $("#loddingPdf").fadeOut();
            Swal.fire({
                icon: 'error',
                title: 'সমস্যা দেখা দিয়েছে',
                text: error.message || 'প্রসেস করা সম্ভব হয়নি।'
            });
        });
    });

    // Image Preview Instant Event
    $('#imageUrl1').on('change', function(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = e => $('#imageUrl1_url').attr('src', e.target.result);
            reader.readAsDataURL(file);
        }
    });

    $('#imageUrl2').on('change', function(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = e => $('#imageUrl2_url').attr('src', e.target.result);
            reader.readAsDataURL(file);
        }
    });
});
</script>