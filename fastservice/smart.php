<?php include_once("header.php"); ?>
<style>
img#uploadAnimation {
	margin: 0;
	padding: 0;
	width: 210px;
	margin-top: -20px;
	margin-bottom: -30px;
}

.loader {
	--d: 22px;
	width: 4px;
	height: 4px;
	border-radius: 50%;
	color: #1F3BB3;
	box-shadow: calc(1*var(--d))      calc(0*var(--d))     0 0,
    calc(0.707*var(--d))  calc(0.707*var(--d)) 0 1px,
    calc(0*var(--d))      calc(1*var(--d))     0 2px,
    calc(-0.707*var(--d)) calc(0.707*var(--d)) 0 3px,
    calc(-1*var(--d))     calc(0*var(--d))     0 4px,
    calc(-0.707*var(--d)) calc(-0.707*var(--d))0 5px,
    calc(0*var(--d))      calc(-1*var(--d))    0 6px;
	animation: l27 1s infinite steps(8);
}

@keyframes l27 {
	100% {
		transform: rotate(1turn);
	}
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
	display: none;
}

#loader {
	animation: l27 1s infinite steps(8);
	top: 50%;
	left: 50%;
	transform: translate(-50%, -50%);
	position: absolute;
}
/* Upload Section */
.upload-section {
	position: relative;
	border: 2px dashed #1F3BB3;
	border-radius: 15px;
	padding: 35px 20px;
	background: linear-gradient(145deg, #f0f4ff, #e6ebff);
	text-align: center;
	transition: all 0.3s ease;
	margin: 0 auto;
	max-width: 400px;
 /* smaller card */;
}

.upload-section:hover {
	border-color: #0d6efd;
	box-shadow: 0 0 20px rgba(31,59,179,0.3);
	background: linear-gradient(145deg, #e6ebff, #dce4ff);
}

/* Animated corners */
.upload-section::before,
.upload-section::after {
	content: '';
	position: absolute;
	width: 15px;
	height: 15px;
	border: 2px solid #1F3BB3;
	border-radius: 50%;
	animation: rotateCorners 4s linear infinite;
}

.upload-section::before {
	top: -10px;
	left: -10px;
}

.upload-section::after {
	bottom: -10px;
	right: -10px;
}

@keyframes rotateCorners {
	0% {
		transform: rotate(0deg);
	}

	50% {
		transform: rotate(180deg);
	}

	100% {
		transform: rotate(360deg);
	}
}

/* Upload Image */
#upload_label {
	cursor: pointer;
	display: block;
}

#uploadAnimation {
	width: 150px;
	transition: transform 0.3s;
}

#upload_label:hover #uploadAnimation {
	transform: scale(1.1) rotate(10deg);
}

/* Upload Text */
.upload-title {
	font-size: 18px;
	font-weight: 600;
	color: #1F3BB3;
	margin-bottom: 5px;
}

.upload-sub {
	font-size: 13px;
	color: #555;
	margin-bottom: 12px;
}

/* Upload Button */
#uploadBtn {
	min-width: 180px;
	font-weight: 600;
	border-radius: 25px;
	padding: 8px 20px;
	font-size: 15px;
	box-shadow: 0 4px 12px rgba(0,0,0,0.1);
	transition: all 0.3s;
}

#uploadBtn:hover {
	transform: translateY(-2px);
	box-shadow: 0 7px 18px rgba(0,0,0,0.15);
}

/* Smart Card Preview */
#smartCardPreview {
	margin: 20px auto;
	max-width: 350px;
	border-radius: 12px;
	box-shadow: 0 6px 18px rgba(0,0,0,0.15);
	padding: 15px;
	background: #fff;
	text-align: center;
	font-size: 13px;
	line-height: 1.3;
}

#smartCardPreview img {
	max-width: 80px;
	margin-bottom: 5px;
	border-radius: 5px;
}
</style>
<!--preloader -->
<div id="loddingPdf">
	<div id="loader">
		<div class="loader"></div>
	</div>
</div>
<div class="container mt-4">
	<div class="card mb-4">
		<?php
            include 'includes/marquee.php';
            $id = 10;
            $marquee = new Marquee($conn, $id);
            $marquee->display();
        ?>
	</div>
	<div class="card">
		<div class="card-body">
			<!-- PDF Upload Section -->
			<div class="upload-section mb-3">
				<div class="upload-title"> স্মার্ট কার্ড তৈরির জন্য সাইন কপি আপলোড করুন। </div>
				<div class="upload-sub"> PDF আপলোড করলে স্বয়ংক্রিয়ভাবে তথ্য পূরণ হবে </div>
				<form id="frmHtmlToPdfmake" method="POST" accept-charset="utf-8" enctype="multipart/form-data">
					<label id="upload_label" for="pdf"> <img id="uploadAnimation" src="assets/images/upload_animation.gif" alt="Upload PDF"> </label>
					<div style="font-size:14px;color:#777;margin-top:8px;"> PDF নির্বাচন করতে ছবিতে ক্লিক করুন </div>
					<input id="pdf" type="file" name="pdf" accept="application/pdf" style="display:none"> </form>
				<button id="uploadBtn" class="btn btn-success mt-3" disabled> <i class="fas fa-upload me-2"></i> আপলোড করুন </button>
			</div>
			<!-- Smart Card Form -->
			<form action="smart_bn.php" method="post" enctype="multipart/form-data" target="_blank" id="smartCardForm">
				<!-- Photo & Signature -->
				<div class="row mb-3">
					<div class="col-md-6">
						<label>ছবি</label>
						<input type="file" id="imageUrl1" name="imageUrl1" accept="image/*" class="form-control"> <img id="imageUrl1_url" style="width:55px; margin-top:5px;">
						<input type="hidden" name="userPhoto" id="imageUrl12"> </div>
					<div class="col-md-6">
						<label>স্বাক্ষর</label>
						<input type="file" id="imageUrl2" name="imageUrl2" accept="image/*" class="form-control"> <img id="imageUrl2_url" style="width:55px; margin-top:5px;">
						<input type="hidden" name="userSign" id="imageUrl22"> </div>
				</div>
				<!-- NID & PIN -->
				<div class="row mb-3">
					<div class="col-md-6">
						<label>NID</label>
						<input type="text" id="nid" name="nid" class="form-control"> </div>
					<div class="col-md-6">
						<label>PIN</label>
						<input type="text" id="pin" name="pin" class="form-control"> </div>
				</div>
				<!-- Name -->
				<div class="row mb-3">
					<div class="col-md-6">
						<label>নাম (বাংলা)</label>
						<input type="text" id="nameBangla" name="nameBangla" class="form-control"> </div>
					<div class="col-md-6">
						<label>নাম (ইংরেজি)</label>
						<input type="text" id="nameEnglish" name="nameEnglish" class="form-control"> </div>
				</div>
				<!-- DOB & BirthPlace -->
				<div class="row mb-3">
					<div class="col-md-6">
						<label>জন্ম তারিখ</label>
						<input type="text" id="dob" name="dob" class="form-control"> </div>
					<div class="col-md-6">
						<label>জন্মস্থান</label>
						<input type="text" id="birthPlace" name="birthPlace" class="form-control"> </div>
				</div>
				<!-- Parents -->
				<div class="row mb-3">
					<div class="col-md-6">
						<label>বাবার নাম</label>
						<input type="text" id="fatherName" name="fatherName" class="form-control"> </div>
					<div class="col-md-6">
						<label>মায়ের নাম</label>
						<input type="text" id="motherName" name="motherName" class="form-control"> </div>
				</div>
				<!-- Gender, Blood Group, Issue Date -->
				<div class="row mb-3">
					<div class="col-md-4">
						<label>রক্তের গ্রুপ</label>
						<input type="text" id="bloodGroup" name="bloodGroup" class="form-control"> </div>
					<div class="col-md-4">
						<label>লিঙ্গ</label>
						<input type="text" id="gender" name="gender" class="form-control"> </div>
					<div class="col-md-4">
						<label>ইস্যুর তারিখ</label>
						<input type="text" id="issueDate" name="issueDate" class="form-control" value="26 May 2024"> </div>
				</div>
				<!-- Address -->
				<div class="mb-3">
					<label>ঠিকানা</label>
					<textarea id="fulAddress" name="fulAddress" class="form-control" rows="3"></textarea>
				</div>
				<div class="text-center">
					<button type="submit" class="btn btn-primary"> <i class="fas fa-download"></i> ডাউনলোড কার্ড </button>
				</div>
			</form>
		</div>
	</div>
</div>

<?php include_once("footer.php"); ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
	let selectedFile = null;
// Enable upload button when file selected
document.getElementById('pdf').addEventListener('change', function(e) {
	selectedFile = e.target.files[0];
	const uploadBtn = document.getElementById('uploadBtn');
	uploadBtn.disabled = !selectedFile;
	uploadBtn.innerHTML = selectedFile ? `<i class="fas fa-upload"></i> আপলোড করুন (${selectedFile.name})` : `<i class="fas fa-upload"></i> আপলোড করুন`;
});
// Format NID
function formatNID(value) {
	value = value.replace(/\D/g, '').slice(0, 10);
	return value.replace(/(\d{3})(\d{3})(\d{4})/, '$1 $2 $3').trim();
}
document.getElementById('nid').addEventListener('input', e => e.target.value = formatNID(e.target.value));
// Set API data
function setAPIData(apiData) {
	const fields = {
		'nid': 'nationalId',
		'pin': 'pin',
		'nameBangla': 'nameBangla',
		'nameEnglish': 'nameEnglish',
		'dob': 'dateOfBirth',
		'birthPlace': 'birthPlaceEn',
		'fatherName': 'fatherName',
		'motherName': 'motherName',
		'bloodGroup': 'bloodGroup',
		'gender': 'gender',
		'fulAddress': 'address'
	};
	Object.entries(fields).forEach(([id, key]) => {
		const el = document.getElementById(id);
		if(el && apiData[key] !== undefined) el.value = id === 'nid' ? formatNID(apiData[key]) : apiData[key];
	});
	if(apiData.userIMG) {
		document.getElementById('imageUrl12').value = apiData.userIMG;
		document.getElementById('imageUrl1_url').src = apiData.userIMG;
	}
	if(apiData.signIMG) {
		document.getElementById('imageUrl22').value = apiData.signIMG;
		document.getElementById('imageUrl2_url').src = apiData.signIMG;
	}
}
// PDF Upload Handler with Confirmation
document.getElementById('uploadBtn').addEventListener('click', function() {
	if(!selectedFile) return;
	if(selectedFile.type !== 'application/pdf') {
		Swal.fire({
			icon: 'error',
			title: 'ভুল ফাইল',
			text: 'দয়া করে একটি PDF ফাইল আপলোড করুন'
		});
		return;
	}
	// SweetAlert confirmation
	Swal.fire({
		title: 'আপনি কি নিশ্চিত?',
		html: `ফাইল <b>${selectedFile.name}</b> আপলোড করতে যাচ্ছেন।`,
		icon: 'question',
		showCancelButton: true,
		confirmButtonColor: '#667eea',
		cancelButtonColor: '#d33',
		confirmButtonText: 'হ্যাঁ, আপলোড করুন',
		cancelButtonText: 'বাতিল'
	}).then(result => {
		if(result.isConfirmed) {
			const formData = new FormData();
			formData.append('pdf', selectedFile);
			$("#loddingPdf").fadeIn();
			fetch('api_proxy_smart.php', {
				method: 'POST',
				body: formData
			}).then(async res => {
				const text = await res.text();
				try {
					const data = JSON.parse(text);
					if(!res.ok || !data.success) {
						throw new Error(data.message || 'আপনার অ্যাকাউন্ট/এপিআই কি ঠিক নেই, স্মার্ট কার্ড তৈরি করা সম্ভব হলো না।');
					}
					return data;
				} catch(e) {
					throw new Error(`JSON parse error: ${e.message}\nRaw response: ${text}`);
				}
			}).then(data => {
				$("#loddingPdf").fadeOut();
				setAPIData(data.data);
				// Success alert
				Swal.fire({
					toast: true,
					position: 'top-end',
					showConfirmButton: false,
					timer: 1500,
					icon: 'success',
					title: 'ডাটা সফলভাবে সংগ্রহ করা হয়েছে'
				});
				// Reset upload input
				document.getElementById('pdf').value = '';
				selectedFile = null;
				const uploadBtn = document.getElementById('uploadBtn');
				uploadBtn.disabled = true;
				uploadBtn.innerHTML = `<i class="fas fa-upload"></i> আপলোড করুন`;
			}).catch(err => {
				console.error(err);
				$("#loddingPdf").fadeOut();
				Swal.fire({
					icon: 'error',
					title: 'PDF প্রসেস করতে সমস্যা হয়েছে',
					text: err.message
				});
			});
		}
	});
});
document.getElementById('smartCardForm').addEventListener('submit', function(e) {
	e.preventDefault();

	fetch('api_key/smartret.txt')
	.then(res => res.text())
	.then(amount => {

		amount = amount.trim();

		Swal.fire({
			title: 'আপনি কি নিশ্চিত?',
			text: `স্মার্ট কার্ড তৈরি করতে আপনার অ্যাকাউন্ট থেকে ${amount} টাকা কেটে নেওয়া হবে`,
			icon: 'warning',
			showCancelButton: true,
			confirmButtonText: 'হ্যাঁ',
			cancelButtonText: 'না'
		}).then((result) => {

			if(result.isConfirmed) {

				const formData = new FormData(document.getElementById('smartCardForm'));

				Swal.fire({
					title: 'কার্ড তৈরি হচ্ছে',
					text: 'দয়া করে অপেক্ষা করুন...',
					allowOutsideClick: false,
					didOpen: () => {
						Swal.showLoading();
					}
				});

				fetch('smart_bn.php', {
					method: 'POST',
					body: formData
				})
				.then(res => res.json()) // ✅ গুরুত্বপূর্ণ
				.then(data => {

					if(!data.success){
						throw new Error(data.message);
					}

					Swal.fire({
						icon: 'success',
						title: 'সফল হয়েছে',
						text: data.message,
						timer: 1500,
						showConfirmButton: false
					}).then(() => {
						window.location.href = "smart_list.php";
					});

				})
				.catch(err => {
					Swal.fire({
						icon: 'error',
						title: 'সমস্যা হয়েছে',
						text: err.message || 'কার্ড তৈরি করা যায়নি'
					});
				});
			}
		});
	});
});
</script>
