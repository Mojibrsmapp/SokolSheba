<?php
session_start();
include_once("includes/configuration.php");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$feedback = "";
$registrationSuccessful = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['name'], $_POST['email'], $_POST['password'], $_POST['number'])) {
        $name = trim($_POST['name']);
        $email = strtolower(trim($_POST['email']));
        $password = trim($_POST['password']);
        $number = trim($_POST['number']);

        $checkEmail = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $checkEmail->bind_param("s", $email);
        $checkEmail->execute();
        $checkEmail->store_result();

        if ($checkEmail->num_rows > 0) {
            $feedback = "এই ইমেইলটি ইতোমধ্যে ব্যবহৃত হয়েছে।";
        } else {
            $hashed_password = md5($password);
            $token = bin2hex(random_bytes(8));

            $stmt = $conn->prepare("INSERT INTO users (name, email, password, token, mobile) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $name, $email, $hashed_password, $token, $number);

            if ($stmt->execute()) {
                session_regenerate_id(true);
                $_SESSION['user_token'] = $token;
                $_SESSION['user_id'] = base64_encode($email);
                $registrationSuccessful = true;
            } else {
                $feedback = "রেজিস্ট্রেশন ব্যর্থ হয়েছে। অনুগ্রহ করে আবার চেষ্টা করুন।";
            }
            $stmt->close();
        }
        $checkEmail->close();
    } else {
        $feedback = "অনুগ্রহ করে সব ঘর পূরণ করুন।";
    }
}

$support_wa = "";
$settings_query = "SELECT setting_value FROM site_settings WHERE setting_key = 'support_whatsapp_num' LIMIT 1";
$settings_result = mysqli_query($conn, $settings_query);
if ($settings_result && mysqli_num_rows($settings_result) > 0) {
    $row = mysqli_fetch_assoc($settings_result);
    $support_wa = $row['setting_value'];
}

?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>রেজিস্ট্রেশন - <?php echo soko_e(soko_brand('site_name')); ?></title>

    <link rel="stylesheet" href="assets/vendors/css/vendor.bundle.base.css">
    <link rel="shortcut icon" type="image/jpeg" href="<?php echo soko_e(soko_brand('site_favicon')); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Hind+Siliguri:wght@500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-page: #e8f5ec;
            --card-bg: #ffffff;
            --primary-green: #118c44;
            --dark-green: #0a4f27;
            --accent-red: #d92525;
            --border-color: #dbe7df;
            --input-bg: #ffffff;
            --text-dark: #1f3d2d;
            --text-muted: #577063;
        }

        * {
            box-sizing: border-box;
            font-family: 'Hind Siliguri', 'Plus Jakarta Sans', sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        .fa, .fas, .far, .fal, .fad, .fab, .fa-solid, .fa-regular, .fa-brands {
            font-family: "Font Awesome 6 Free", "Font Awesome 6 Brands" !important;
        }

        body { 
            margin: 0; 
            padding: 10px; 
            min-height: 100vh; 
            background-color: var(--bg-page); 
            display: flex; 
            align-items: center; 
            justify-content: center; 
        }

        .auth-container { 
            width: 100%; 
            max-width: 420px; 
            margin: auto; 
        }

        .register-card { 
            background: var(--card-bg); 
            border: 1.5px solid #d3e8db; 
            border-radius: 32px; 
            box-shadow: 0 14px 35px -10px rgba(17, 140, 68, 0.12); 
            position: relative; 
            overflow: hidden;
        }

        .card-top-header {
            background: linear-gradient(180deg, #b6eed0 0%, #e1f7ec 55%, #ffffff 100%);
            padding: 16px 16px 8px 16px;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 96px;
        }

        .logo-avatar-wrap {
            width: 82px;
            height: 82px;
            border-radius: 50%;
            background: #ffffff;
            padding: 4px;
            box-shadow: 0 6px 16px rgba(17, 140, 68, 0.16);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logo-avatar-wrap img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }

        .top-info-badge {
            position: absolute;
            right: 14px;
            top: 14px;
            background: #0f6c34;
            color: #ffffff;
            padding: 5px 10px;
            border-radius: 10px;
            font-size: 10.5px;
            font-weight: 700;
            line-height: 1.3;
            text-align: center;
            box-shadow: 0 4px 8px rgba(15, 108, 52, 0.2);
        }

        .card-body-content {
            padding: 4px 20px 14px 20px;
        }

        .form-group-custom { 
            margin-bottom: 9px; 
        }

        .input-wrapper { 
            position: relative; 
            display: flex; 
            align-items: center; 
        }

        .input-icon-box {
            position: absolute;
            left: 10px;
            width: 30px;
            height: 30px;
            border-radius: 8px;
            background: #eef4f0;
            color: #456151;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13.5px;
            z-index: 2;
            pointer-events: none;
        }

        .smooth-input { 
            width: 100%; 
            height: 44px;
            padding: 0 40px 0 48px; 
            border: 1.5px solid var(--border-color); 
            border-radius: 13px; 
            outline: none; 
            background: #ffffff; 
            color: #173223; 
            font-size: 13.5px; 
            font-weight: 600; 
            transition: all .2s ease; 
        }

        .smooth-input::placeholder { 
            color: #8fa597; 
            font-weight: 500; 
        }

        .smooth-input:focus { 
            border-color: var(--primary-green); 
            background: #ffffff; 
            box-shadow: 0 0 0 3px rgba(17, 140, 68, 0.14); 
        }

        .toggle-pwd { 
            position: absolute; 
            right: 14px; 
            z-index: 2; 
            color: #7b9485; 
            cursor: pointer; 
            font-size: 14px; 
            padding: 4px;
        }

        .toggle-pwd:hover { 
            color: var(--primary-green); 
        }

        .terms-row {
            display: flex;
            align-items: center;
            gap: 7px;
            margin: 8px 0 12px 0;
            font-size: 12px;
            color: #274938;
            font-weight: 700;
        }

        .terms-row input[type="checkbox"] {
            width: 15px;
            height: 15px;
            accent-color: var(--primary-green);
            cursor: pointer;
            margin: 0;
        }

        .btn-register-submit { 
            width: 100%; 
            height: 44px;
            border: 0; 
            border-radius: 22px; 
            background: linear-gradient(135deg, #18984c 0%, #0d7938 100%); 
            color: #ffffff; 
            font-size: 15px; 
            font-weight: 800; 
            cursor: pointer; 
            box-shadow: 0 6px 14px rgba(17, 140, 68, 0.28); 
            transition: all .25s ease; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            gap: 8px; 
        }

        .btn-register-submit:hover { 
            transform: translateY(-1px); 
            box-shadow: 0 8px 18px rgba(17, 140, 68, 0.36); 
        }

        .btn-register-submit:disabled { 
            opacity: .8; 
            cursor: wait; 
            transform: none; 
        }

        .divider-wrapper {
            position: relative;
            text-align: center;
            margin: 10px 0 8px 0;
        }

        .divider-wrapper::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #e1ebe4;
            z-index: 1;
        }

        .divider-wrapper span {
            position: relative;
            background: #ffffff;
            padding: 0 10px;
            font-size: 11.5px;
            color: #889f92;
            font-weight: 700;
            z-index: 2;
        }

        .btn-google-signup {
            width: 100%;
            height: 42px;
            border-radius: 21px;
            border: 1.5px solid #d9e3dc;
            background: #ffffff;
            color: #243f30;
            font-size: 13.5px;
            font-weight: 700;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s ease;
        }

        .btn-google-signup:hover {
            background: #f7faf8;
            border-color: #bccdc3;
        }

        .btn-google-signup svg {
            width: 16px;
            height: 16px;
        }

        .login-footer-link {
            text-align: center;
            margin-top: 10px;
            font-size: 12.5px;
            color: #3b5547;
            font-weight: 700;
        }

        .login-footer-link a {
            color: var(--accent-red);
            text-decoration: none;
            font-weight: 800;
            margin-left: 3px;
        }

        .login-footer-link a:hover {
            text-decoration: underline;
        }

        .footer-features-bar {
            background: #0f6c34;
            padding: 10px 14px;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            border-bottom-left-radius: 30px;
            border-bottom-right-radius: 30px;
            border-top: 2.5px solid #b61c1c;
        }

        .feature-item {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            color: #ffffff;
            text-decoration: none;
            position: relative;
        }

        .feature-item:not(:last-child)::after {
            content: '';
            position: absolute;
            right: 0;
            top: 15%;
            height: 70%;
            width: 1px;
            background: rgba(255, 255, 255, 0.2);
        }

        .feature-item i {
            font-size: 16px;
            color: #e5fbf0;
        }

        .feature-item .text-wrap {
            display: flex;
            flex-direction: column;
            line-height: 1.1;
        }

        .feature-item .title {
            font-size: 12px;
            font-weight: 800;
            color: #ffffff;
        }

        .feature-item .subtitle {
            font-size: 10px;
            font-weight: 600;
            color: #bdf3d5;
        }

        .custom-swal-popup {
            background: #ffffff !important;
            border: 1.5px solid #d8e8de !important;
            border-radius: 20px !important;
            box-shadow: 0 16px 35px rgba(15, 108, 52, 0.15) !important;
        }

        .custom-swal-title {
            color: var(--dark-green) !important;
            font-weight: 800 !important;
        }

        .custom-swal-text {
            color: var(--text-muted) !important;
            font-weight: 600 !important;
        }
    </style>
<style>body{display:block!important;padding:0!important}.auth-container{margin:24px auto!important;padding:0 12px}</style>
</head>
<body>
<?php include __DIR__ . '/includes/site_header.php'; ?>

    <div class="auth-container">
        <div class="register-card">

            <div class="card-top-header">
                <div class="logo-avatar-wrap">
                    <img src="<?php echo soko_e(soko_brand('site_logo')); ?>" alt="সকল সেবা">
                </div>
                <div class="top-info-badge">
                    সহজে রেজিস্ট্রেশন করুন এবং<br>উপভোগ করুন সকল সেবা
                </div>
            </div>

            <div class="card-body-content">
                <form method="POST" action="" id="registerForm">

                    <div class="form-group-custom">
                        <div class="input-wrapper">
                            <div class="input-icon-box">
                                <i class="fa-solid fa-user"></i>
                            </div>
                            <input type="text" name="name" id="name" class="smooth-input" placeholder="আপনার নাম লিখুন" value="<?php echo htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required autocomplete="name">
                        </div>
                    </div>

                    <div class="form-group-custom">
                        <div class="input-wrapper">
                            <div class="input-icon-box">
                                <i class="fa-solid fa-phone"></i>
                            </div>
                            <input type="tel" name="number" id="number" class="smooth-input" placeholder="মোবাইল নাম্বার" value="<?php echo htmlspecialchars($_POST['number'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required autocomplete="tel" inputmode="numeric" maxlength="11">
                        </div>
                    </div>

                    <div class="form-group-custom">
                        <div class="input-wrapper">
                            <div class="input-icon-box">
                                <i class="fa-solid fa-envelope"></i>
                            </div>
                            <input type="email" name="email" id="email" class="smooth-input" placeholder="ইমেইল লিখুন" value="<?php echo htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required autocomplete="email">
                        </div>
                    </div>

                    <div class="form-group-custom">
                        <div class="input-wrapper">
                            <div class="input-icon-box">
                                <i class="fa-solid fa-lock"></i>
                            </div>
                            <input type="password" name="password" id="password" class="smooth-input" placeholder="পাসওয়ার্ড দিন" required autocomplete="new-password">
                            <i class="fa-solid fa-eye-slash toggle-pwd" id="togglePassword"></i>
                        </div>
                    </div>

                    <div class="form-group-custom">
                        <div class="input-wrapper">
                            <div class="input-icon-box">
                                <i class="fa-solid fa-lock"></i>
                            </div>
                            <input type="password" id="confirmPassword" class="smooth-input" placeholder="পাসওয়ার্ড নিশ্চিত করুন" required autocomplete="new-password">
                            <i class="fa-solid fa-eye-slash toggle-pwd" id="toggleConfirmPassword"></i>
                        </div>
                    </div>

                    <div class="terms-row">
                        <input type="checkbox" id="agreeTerms" required checked>
                        <label for="agreeTerms">আমি সকল শর্তাবলী ও গোপনীয়তা নীতি মেনে নিচ্ছি</label>
                    </div>

                    <button type="submit" id="registerBtn" class="btn-register-submit">
                        <i class="fa-solid fa-user-plus"></i>
                        <span>রেজিস্ট্রেশন করুন</span>
                    </button>

                    <div class="divider-wrapper">
                        <span>অথবা</span>
                    </div>

                    <div>
                        <a href="#" class="btn-google-signup">
                            <svg viewBox="0 0 24 24">
                                <path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.82-2.4 3.68v3.05h3.88c2.27-2.09 3.66-5.17 3.66-9.17z"/>
                                <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.05c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.1-6.72-4.93H1.25v3.15C3.26 21.36 7.34 24 12 24z"/>
                                <path fill="#FBBC05" d="M5.28 14.27c-.25-.72-.38-1.49-.38-2.27s.13-1.55.38-2.27V6.58H1.25C.45 8.16 0 9.97 0 12s.45 3.84 1.25 5.42l4.03-3.15z"/>
                                <path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.34 0 3.26 2.64 1.25 6.58l4.03 3.15c.95-2.83 3.6-4.98 6.72-4.98z"/>
                            </svg>
                            <span>Google দিয়ে সাইন আপ করুন</span>
                        </a>
                    </div>

                    <div class="login-footer-link">
                        <span>ইতিমধ্যে অ্যাকাউন্ট আছে?</span>
                        <a href="index.php">লগইন করুন &rarr;</a>
                    </div>
                </form>
            </div>

            <div class="footer-features-bar">
                <div class="feature-item">
                    <i class="fa-regular fa-circle-check"></i>
                    <div class="text-wrap">
                        <span class="title">নিরাপদ</span>
                        <span class="subtitle">সেবা</span>
                    </div>
                </div>

                <div class="feature-item">
                    <i class="fa-solid fa-bolt" style="color: #ffdc34;"></i>
                    <div class="text-wrap">
                        <span class="title">দ্রুত</span>
                        <span class="subtitle">সেবা</span>
                    </div>
                </div>

                <?php if (!empty($support_wa)): ?>
                <a href="https://wa.me/88<?php echo htmlspecialchars($support_wa); ?>" target="_blank" class="feature-item" style="cursor: pointer;">
                    <i class="fa-solid fa-headset"></i>
                    <div class="text-wrap">
                        <span class="title">সর্বদা</span>
                        <span class="subtitle">সাপোর্ট</span>
                    </div>
                </a>
                <?php else: ?>
                <div class="feature-item">
                    <i class="fa-solid fa-headset"></i>
                    <div class="text-wrap">
                        <span class="title">সর্বদা</span>
                        <span class="subtitle">সাপোর্ট</span>
                    </div>
                </div>
                <?php endif; ?>
            </div>

        </div>
    </div>

    <script src="assets/vendors/js/vendor.bundle.base.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const togglePassword = document.getElementById('togglePassword');
        const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirmPassword');
        const registerForm = document.getElementById('registerForm');
        const registerBtn = document.getElementById('registerBtn');
        const numberInput = document.getElementById('number');

        togglePassword.addEventListener('click', function () {
            const isPassword = passwordInput.type === 'password';
            passwordInput.type = isPassword ? 'text' : 'password';
            this.classList.toggle('fa-eye', isPassword);
            this.classList.toggle('fa-eye-slash', !isPassword);
        });

        toggleConfirmPassword.addEventListener('click', function () {
            const isPassword = confirmPasswordInput.type === 'password';
            confirmPasswordInput.type = isPassword ? 'text' : 'password';
            this.classList.toggle('fa-eye', isPassword);
            this.classList.toggle('fa-eye-slash', !isPassword);
        });

        numberInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });

        registerForm.addEventListener('submit', function (e) {
            const pwd = passwordInput.value.trim();
            const cpwd = confirmPasswordInput.value.trim();
            const phone = numberInput.value.trim();

            if (phone.length !== 11 || !phone.startsWith('01')) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'সঠিক নম্বর দিন',
                    text: 'অনুগ্রহ করে ১১ ডিজিটের সঠিক মোবাইল নম্বর লিখুন (যেমন: 01XXXXXXXXX)।',
                    confirmButtonColor: '#118c44',
                    customClass: { popup: 'custom-swal-popup', title: 'custom-swal-title', htmlContainer: 'custom-swal-text' }
                });
                return;
            }

            if (pwd !== cpwd) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'পাসওয়ার্ড মিলেনি',
                    text: 'উভয় ঘরে একই পাসওয়ার্ড প্রদান করুন।',
                    confirmButtonColor: '#118c44',
                    customClass: { popup: 'custom-swal-popup', title: 'custom-swal-title', htmlContainer: 'custom-swal-text' }
                });
                return;
            }

            registerBtn.disabled = true;
            registerBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i><span>প্রসেস হচ্ছে...</span>';
        });

        <?php if ($registrationSuccessful): ?>
        Swal.fire({
            icon: 'success',
            title: 'রেজিস্ট্রেশন সফল হয়েছে!',
            text: 'আপনাকে ড্যাশবোর্ডে নেওয়া হচ্ছে...',
            showConfirmButton: false,
            timer: 1600,
            timerProgressBar: true,
            allowOutsideClick: false,
            allowEscapeKey: false,
            customClass: { popup: 'custom-swal-popup', title: 'custom-swal-title', htmlContainer: 'custom-swal-text' }
        }).then(function () {
            window.location.replace('dashboard.php');
        });
        <?php elseif ($feedback !== ''): ?>
        Swal.fire({
            icon: 'error',
            title: 'রেজিস্ট্রেশন ব্যর্থ!',
            text: <?php echo json_encode($feedback, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>,
            confirmButtonColor: '#d92525',
            customClass: { popup: 'custom-swal-popup', title: 'custom-swal-title', htmlContainer: 'custom-swal-text' }
        });
        <?php endif; ?>
    </script>
<?php include __DIR__ . '/includes/site_footer.php'; ?>
</body>
</html>