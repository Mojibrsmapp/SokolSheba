<?php
error_reporting(E_ALL);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
session_start();
if (isset($_SESSION['user_id'])) {
    header("location: dashboard.php");
    exit();
}

include_once("includes/configuration.php");

$query = "SELECT reg_token, whatsapp_group FROM admin_user WHERE id = 1";
$result = mysqli_query($conn, $query);
$reg_token = "";

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $reg_token = !empty($row['reg_token']) ? $row['reg_token'] : $row['whatsapp_group'];
}

$support_wa = "";
$tg_link = "";

$settings_query = "SELECT setting_key, setting_value FROM site_settings WHERE setting_key IN ('support_whatsapp_num', 'telegram_link')";
$settings_result = mysqli_query($conn, $settings_query);

if ($settings_result) {
    while ($setting_row = mysqli_fetch_assoc($settings_result)) {
        if ($setting_row['setting_key'] === 'support_whatsapp_num') {
            $support_wa = $setting_row['setting_value'];
        } elseif ($setting_row['setting_key'] === 'telegram_link') {
            $tg_link = $setting_row['setting_value'];
        }
    }
}


?>
<!DOCTYPE html>
<html lang="bn">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
  <title>লগইন - <?php echo soko_e(soko_brand('site_name')); ?></title>

  <link rel="stylesheet" href="assets/vendors/css/vendor.bundle.base.css">
  <link rel="shortcut icon" type="image/jpeg" href="<?php echo soko_e(soko_brand('site_favicon')); ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <style>
    :root {
      --bg-page: #e8f5ec;
      --card-bg: #ffffff;
      --primary-green: #118c44;
      --dark-green: #0a4f27;
      --accent-red: #d92525;
      --text-dark: #1f3d2d;
      --text-muted: #577063;
      --border-color: #dbe7df;
      --input-bg: #f5f8f6;
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
      padding: 25px 12px;
      min-height: 100vh;
      background-color: var(--bg-page);
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .auth-container {
      width: 100%;
      max-width: 440px;
      margin: auto;
    }

    .login-card {
      background: var(--card-bg);
      border-radius: 36px;
      border: 1.5px solid #d3e8db;
      box-shadow: 0 16px 40px -10px rgba(17, 140, 68, 0.12);
      overflow: hidden;
      position: relative;
    }

    .card-top-header {
      background: linear-gradient(180deg, #b6eed0 0%, #e1f7ec 55%, #ffffff 100%);
      padding: 34px 24px 18px 24px;
      text-align: center;
    }

    .logo-avatar-wrap {
      width: 105px;
      height: 105px;
      margin: 0 auto 12px auto;
      border-radius: 50%;
      background: #ffffff;
      padding: 5px;
      box-shadow: 0 8px 22px rgba(17, 140, 68, 0.16);
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

    .card-top-header h2 {
      margin: 0;
      font-size: 27px;
      font-weight: 800;
      color: var(--dark-green);
      letter-spacing: -0.01em;
    }

    .card-top-header p {
      margin: 4px 0 0 0;
      color: #3b5f4c;
      font-size: 13.5px;
      font-weight: 700;
    }

    .card-body-content {
      padding: 16px 28px 24px 28px;
    }

    .form-group-custom {
      margin-bottom: 16px;
    }

    .input-wrapper {
      position: relative;
      display: flex;
      align-items: center;
    }

    .input-icon-box {
      position: absolute;
      left: 12px;
      width: 36px;
      height: 36px;
      border-radius: 10px;
      background: #eef4f0;
      color: #456151;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 16px;
      z-index: 2;
      pointer-events: none;
    }

    .smooth-input {
      width: 100%;
      height: 52px;
      padding: 0 46px 0 58px;
      border-radius: 15px;
      border: 1.5px solid var(--border-color);
      background: #ffffff;
      color: #173223;
      font-size: 14.5px;
      font-weight: 600;
      outline: none;
      transition: all 0.2s ease;
    }

    .smooth-input::placeholder {
      color: #8fa597;
      font-weight: 500;
    }

    .smooth-input:focus {
      border-color: var(--primary-green);
      background: #ffffff;
      box-shadow: 0 0 0 3.5px rgba(17, 140, 68, 0.14);
    }

    .toggle-pwd {
      position: absolute;
      right: 16px;
      color: #7b9485;
      cursor: pointer;
      font-size: 16px;
      z-index: 2;
      padding: 4px;
    }

    .toggle-pwd:hover {
      color: var(--primary-green);
    }

    .caps-lock-warning {
      display: none;
      font-size: 12px;
      color: #d97706;
      font-weight: 700;
      margin-top: 5px;
      align-items: center;
      gap: 6px;
      padding-left: 4px;
    }

    .utility-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin: 12px 0 20px 0;
      font-size: 13px;
    }

    .remember-label {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      color: #3b5547;
      cursor: pointer;
      font-weight: 700;
      user-select: none;
    }

    .remember-label input[type="checkbox"] {
      width: 16px;
      height: 16px;
      accent-color: var(--primary-green);
      cursor: pointer;
    }

    .forgot-link {
      color: #274938;
      text-decoration: none;
      font-weight: 800;
      font-size: 12.5px;
    }

    .forgot-link:hover {
      color: var(--primary-green);
      text-decoration: underline;
    }

    .btn-login-submit {
      width: 100%;
      height: 52px;
      border-radius: 26px;
      border: none;
      background: linear-gradient(135deg, #18984c 0%, #0d7938 100%);
      color: #ffffff;
      font-size: 17px;
      font-weight: 800;
      cursor: pointer;
      box-shadow: 0 8px 18px rgba(17, 140, 68, 0.32);
      transition: all 0.25s ease;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
    }

    .btn-login-submit:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 22px rgba(17, 140, 68, 0.42);
    }

    .btn-login-submit:active {
      transform: translateY(0);
    }

    .divider-wrapper {
      position: relative;
      text-align: center;
      margin: 20px 0 16px 0;
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
      padding: 0 12px;
      font-size: 12.5px;
      color: #889f92;
      font-weight: 700;
      z-index: 2;
    }

    .btn-google-login {
      width: 100%;
      height: 48px;
      border-radius: 24px;
      border: 1.5px solid #d9e3dc;
      background: #ffffff;
      color: #243f30;
      font-size: 14.5px;
      font-weight: 700;
      text-decoration: none;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      transition: all 0.2s ease;
    }

    .btn-google-login:hover {
      background: #f7faf8;
      border-color: #bccdc3;
    }

    .btn-google-login svg {
      width: 18px;
      height: 18px;
    }

    .register-footer {
      text-align: center;
      margin-top: 18px;
      font-size: 13.5px;
      color: #3b5547;
      font-weight: 700;
    }

    .register-footer a {
      color: var(--accent-red);
      text-decoration: none;
      font-weight: 800;
      margin-left: 4px;
    }

    .register-footer a:hover {
      text-decoration: underline;
    }

    .footer-features-bar {
      background: #0f6c34;
      padding: 14px 18px;
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      border-bottom-left-radius: 34px;
      border-bottom-right-radius: 34px;
      border-top: 3px solid #b61c1c;
    }

    .feature-item {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      color: #ffffff;
      text-decoration: none;
      position: relative;
    }

    .feature-item:not(:last-child)::after {
      content: '';
      position: absolute;
      right: 0;
      top: 10%;
      height: 80%;
      width: 1px;
      background: rgba(255, 255, 255, 0.2);
    }

    .feature-item i {
      font-size: 19px;
      color: #e5fbf0;
    }

    .feature-item .text-wrap {
      display: flex;
      flex-direction: column;
      line-height: 1.15;
    }

    .feature-item .title {
      font-size: 13.5px;
      font-weight: 800;
      color: #ffffff;
    }

    .feature-item .subtitle {
      font-size: 11px;
      font-weight: 600;
      color: #bdf3d5;
    }

    .custom-swal-popup {
      background: #ffffff !important;
      border-radius: 24px !important;
      border: 1.5px solid #d8e8de !important;
      box-shadow: 0 20px 40px rgba(15, 108, 52, 0.15) !important;
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
    <div class="login-card">

      <div class="card-top-header">
        <div class="logo-avatar-wrap">
          <img src="<?php echo soko_e(soko_brand('site_logo')); ?>" alt="সকল সেবা">
        </div>
        <h2><?php echo soko_e(soko_brand('site_name')); ?></h2>
        <p><?php echo soko_e(soko_brand('site_tagline')); ?></p>
      </div>

      <div class="card-body-content">
        <form id="loginForm">

          <div class="form-group-custom">
            <div class="input-wrapper">
              <div class="input-icon-box">
                <i class="fa-solid fa-mobile-screen"></i>
              </div>
              <input type="text" class="smooth-input" id="email" placeholder="মোবাইল নম্বর বা ইমেইল" required autocomplete="username">
            </div>
          </div>

          <div class="form-group-custom">
            <div class="input-wrapper">
              <div class="input-icon-box">
                <i class="fa-solid fa-lock"></i>
              </div>
              <input type="password" class="smooth-input" id="password" placeholder="পাসওয়ার্ড লিখুন" required autocomplete="current-password">
              <i class="fa-solid fa-eye-slash toggle-pwd" id="togglePassword"></i>
            </div>
            <div id="capsWarning" class="caps-lock-warning">
              <i class="fa-solid fa-triangle-exclamation"></i> <span>ক্যাপস লক চালু রয়েছে!</span>
            </div>
            <input type="hidden" id="token" value="<?php echo htmlspecialchars($reg_token, ENT_QUOTES, 'UTF-8'); ?>">
          </div>

          <div class="utility-row">
            <label class="remember-label">
              <input type="checkbox" id="rememberMe">
              <span>আমাকে মনে রাখুন</span>
            </label>
            <a href="<?php echo htmlspecialchars($tg_link); ?>" target="_blank" class="forgot-link">পাসওয়ার্ড ভুলে গেছেন?</a>
          </div>

          <div>
            <button type="submit" id="loginBtn" class="btn-login-submit">
              <i class="fa-solid fa-right-to-bracket"></i>
              <span>লগইন</span>
            </button>
          </div>
        </form>

        <div class="divider-wrapper">
          <span>অথবা</span>
        </div>

        <div>
          <a href="#" class="btn-google-login">
            <svg viewBox="0 0 24 24">
              <path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.82-2.4 3.68v3.05h3.88c2.27-2.09 3.66-5.17 3.66-9.17z"/>
              <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.05c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.1-6.72-4.93H1.25v3.15C3.26 21.36 7.34 24 12 24z"/>
              <path fill="#FBBC05" d="M5.28 14.27c-.25-.72-.38-1.49-.38-2.27s.13-1.55.38-2.27V6.58H1.25C.45 8.16 0 9.97 0 12s.45 3.84 1.25 5.42l4.03-3.15z"/>
              <path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.34 0 3.26 2.64 1.25 6.58l4.03 3.15c.95-2.83 3.6-4.98 6.72-4.98z"/>
            </svg>
            <span>Google দিয়ে লগইন করুন</span>
          </a>
        </div>

        <div class="register-footer">
          <span>এখনও অ্যাকাউন্ট নেই?</span>
          <a href="register_user.php">রেজিস্ট্রেশন করুন &rarr;</a>
        </div>
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
    const passwordInput = document.getElementById('password');
    const emailInput = document.getElementById('email');
    const rememberMeCheckbox = document.getElementById('rememberMe');
    const capsWarning = document.getElementById('capsWarning');

    if (localStorage.getItem('shebakit_saved_username')) {
      emailInput.value = localStorage.getItem('shebakit_saved_username');
      rememberMeCheckbox.checked = true;
    }

    togglePassword.addEventListener('click', function () {
      const isPwd = passwordInput.getAttribute('type') === 'password';
      passwordInput.setAttribute('type', isPwd ? 'text' : 'password');
      this.classList.toggle('fa-eye', isPwd);
      this.classList.toggle('fa-eye-slash', !isPwd);
    });

    passwordInput.addEventListener('keyup', function (e) {
      if (e.getModifierState && e.getModifierState('CapsLock')) {
        capsWarning.style.display = 'flex';
      } else {
        capsWarning.style.display = 'none';
      }
    });

    document.getElementById('loginForm').addEventListener('submit', function(e) {
      e.preventDefault();

      var email = emailInput.value.trim();
      var password = passwordInput.value.trim();
      var token = document.getElementById('token').value;
      var loginBtn = document.getElementById('loginBtn');

      if (!email || !password) {
        Swal.fire({
          icon: 'warning',
          title: 'তথ্য দিন',
          text: 'অনুগ্রহ করে আপনার ইউজারনেম এবং পাসওয়ার্ড প্রদান করুন।',
          confirmButtonColor: '#118c44',
          customClass: {
            popup: 'custom-swal-popup',
            title: 'custom-swal-title',
            htmlContainer: 'custom-swal-text'
          }
        });
        return;
      }

      if (rememberMeCheckbox.checked) {
        localStorage.setItem('shebakit_saved_username', email);
      } else {
        localStorage.removeItem('shebakit_saved_username');
      }

      loginBtn.disabled = true;
      loginBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> <span>প্রসেস হচ্ছে...</span>';

      var xhr = new XMLHttpRequest();
      xhr.open('POST', 'pages/login.php', true);
      xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

      xhr.onreadystatechange = function () {
        if (xhr.readyState == 4) {
          loginBtn.disabled = false;
          loginBtn.innerHTML = '<i class="fa-solid fa-right-to-bracket"></i> <span>লগইন</span>';

          if (xhr.status == 200) {
            try {
              var response = JSON.parse(xhr.responseText);

              if (response.status === 'success') {
                loginBtn.innerHTML = '<i class="fa-solid fa-circle-check"></i> <span>লগইন সফল!</span>';
                Swal.fire({
                  icon: 'success',
                  title: 'লগইন সফল হয়েছে!',
                  text: 'আপনাকে ড্যাশবোর্ডে নেওয়া হচ্ছে...',
                  showConfirmButton: false,
                  timer: 1500,
                  timerProgressBar: true,
                  customClass: {
                    popup: 'custom-swal-popup',
                    title: 'custom-swal-title',
                    htmlContainer: 'custom-swal-text'
                  }
                }).then(() => {
                  window.location.replace('dashboard.php');
                });
              } else {
                Swal.fire({
                  icon: 'error',
                  title: 'লগইন ব্যর্থ!',
                  text: response.message || 'ইউজারনেম বা পাসওয়ার্ড ভুল হয়েছে।',
                  confirmButtonColor: '#d92525',
                  customClass: {
                    popup: 'custom-swal-popup',
                    title: 'custom-swal-title',
                    htmlContainer: 'custom-swal-text'
                  }
                });
              }
            } catch (err) {
              Swal.fire({
                icon: 'error',
                title: 'সার্ভার রেসপন্স ত্রুটি',
                text: 'সার্ভার থেকে সঠিক রেসপন্স পাওয়া যায়নি।',
                confirmButtonColor: '#d92525',
                customClass: {
                  popup: 'custom-swal-popup',
                  title: 'custom-swal-title',
                  htmlContainer: 'custom-swal-text'
                }
              });
            }
          } else {
            Swal.fire({
              icon: 'error',
              title: 'সার্ভার ত্রুটি',
              text: 'সার্ভারের সাথে সংযোগ স্থাপন করা যায়নি।',
              confirmButtonColor: '#d92525',
              customClass: {
                popup: 'custom-swal-popup',
                title: 'custom-swal-title',
                htmlContainer: 'custom-swal-text'
              }
            });
          }
        }
      };

      xhr.send('email=' + encodeURIComponent(email) + '&password=' + encodeURIComponent(password) + '&token=' + encodeURIComponent(token));
    });
  </script>
<?php include __DIR__ . '/includes/site_footer.php'; ?>
</body>
</html>