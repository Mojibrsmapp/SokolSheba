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

$query = "SELECT whatsapp_group FROM admin_user WHERE id = 1";
$result = mysqli_query($conn, $query);
$reg_token = "";

if ($result) {
    $row = mysqli_fetch_assoc($result);
    $reg_token = $row['whatsapp_group'];
}
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login - FAST SERVICE</title>

  <link rel="stylesheet" href="assets/vendors/css/vendor.bundle.base.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="shortcut icon" href="assets/images/logo-mini.svg" />

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

  <style>
    * {
      box-sizing: border-box;
      font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
      -webkit-font-smoothing: antialiased;
      -moz-osx-font-smoothing: grayscale;
      text-rendering: optimizeLegibility;
    }

    body {
      margin: 0;
      padding: 0;
      min-height: 100vh;
      overflow-x: hidden;
      background-color: #0b071a;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
    }

    .smooth-bg-mesh {
      position: fixed;
      top: 0;
      left: 0;
      width: 100vw;
      height: 100vh;
      background: radial-gradient(circle at 15% 20%, rgba(139, 92, 246, 0.28) 0%, transparent 45%),
                  radial-gradient(circle at 85% 80%, rgba(244, 63, 94, 0.24) 0%, transparent 45%),
                  radial-gradient(circle at 50% 50%, rgba(79, 70, 229, 0.18) 0%, transparent 55%),
                  linear-gradient(135deg, #0b071a 0%, #160d2e 50%, #070412 100%);
      z-index: -2;
      animation: meshPulse 12s ease-in-out infinite alternate;
    }

    @keyframes meshPulse {
      0% { transform: scale(1); filter: hue-rotate(0deg); }
      100% { transform: scale(1.05); filter: hue-rotate(20deg); }
    }

    .bg-orb {
      position: absolute;
      border-radius: 50%;
      filter: blur(95px);
      opacity: 0.55;
      animation: orbFloat 10s ease-in-out infinite alternate;
      z-index: -1;
    }

    .orb-1 {
      top: 10%;
      left: 10%;
      width: 330px;
      height: 330px;
      background: linear-gradient(135deg, #8b5cf6, #ec4899);
    }

    .orb-2 {
      bottom: 10%;
      right: 10%;
      width: 370px;
      height: 370px;
      background: linear-gradient(135deg, #6366f1, #d946ef);
      animation-delay: -5s;
    }

    @keyframes orbFloat {
      0% { transform: translate(0, 0) scale(1); }
      100% { transform: translate(30px, -40px) scale(1.08); }
    }

    .auth-container {
      width: 100%;
      max-width: 420px;
      padding: 20px;
      margin: auto;
      animation: cardEntrance 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }

    @keyframes cardEntrance {
      0% { opacity: 0; transform: translateY(30px) scale(0.95); }
      100% { opacity: 1; transform: translateY(0) scale(1); }
    }

    .glass-card {
      background: rgba(23, 15, 43, 0.75);
      backdrop-filter: blur(25px) saturate(190%);
      -webkit-backdrop-filter: blur(25px) saturate(190%);
      border-radius: 26px;
      padding: 42px 34px;
      border: 1px solid rgba(168, 85, 247, 0.35);
      box-shadow: 0 25px 65px rgba(0, 0, 0, 0.88),
                  0 0 35px rgba(168, 85, 247, 0.2);
      transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .glass-card:hover {
      border-color: rgba(236, 72, 153, 0.55);
      box-shadow: 0 30px 75px rgba(0, 0, 0, 0.92),
                  0 0 45px rgba(236, 72, 153, 0.28);
      transform: translateY(-3px);
    }

    .auth-header {
      text-align: center;
      margin-bottom: 30px;
    }

    .auth-header h2 {
      margin: 0;
      font-size: 26px;
      font-weight: 800;
      background: linear-gradient(135deg, #ffffff 0%, #cbd5e1 50%, #c084fc 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      letter-spacing: -0.02em;
    }

    .auth-header p {
      margin: 8px 0 0 0;
      color: #94a3b8;
      font-size: 14px;
      font-weight: 400;
    }

    .form-group-custom {
      margin-bottom: 22px;
    }

    .input-wrapper {
      position: relative;
      display: flex;
      align-items: center;
    }

    .input-wrapper i.left-icon {
      position: absolute;
      left: 18px;
      color: #a855f7;
      font-size: 16px;
      z-index: 2;
      transition: color 0.3s ease;
    }

    .smooth-input {
      width: 100%;
      padding: 16px 16px 16px 52px;
      border-radius: 14px;
      border: 1px solid rgba(168, 85, 247, 0.28);
      background: rgba(14, 9, 28, 0.88);
      color: #f8fafc;
      font-size: 15px;
      font-weight: 500;
      outline: none;
      transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .smooth-input::placeholder {
      color: #64748b;
      font-weight: 400;
    }

    .smooth-input:focus {
      border-color: #d946ef;
      background: rgba(14, 9, 28, 0.98);
      box-shadow: 0 0 22px rgba(217, 70, 239, 0.35);
    }

    .smooth-input:focus ~ i.left-icon {
      color: #f43f5e;
    }

    .toggle-pwd {
      position: absolute;
      right: 18px;
      color: #a855f7;
      cursor: pointer;
      font-size: 16px;
      z-index: 2;
      transition: color 0.3s ease;
    }

    .toggle-pwd:hover {
      color: #ffffff;
    }

    .btn-smooth-submit {
      width: 100%;
      padding: 16px;
      border-radius: 14px;
      border: none;
      background: linear-gradient(135deg, #7c3aed 0%, #c026d3 50%, #e11d48 100%);
      background-size: 200% 200%;
      color: #ffffff;
      font-size: 16px;
      font-weight: 700;
      letter-spacing: 0.02em;
      cursor: pointer;
      box-shadow: 0 10px 25px rgba(124, 58, 237, 0.45);
      transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      margin-top: 10px;
      animation: btnGlow 4s ease-in-out infinite alternate;
    }

    @keyframes btnGlow {
      0% { background-position: 0% 50%; box-shadow: 0 10px 25px rgba(124, 58, 237, 0.4); }
      100% { background-position: 100% 50%; box-shadow: 0 10px 30px rgba(225, 29, 72, 0.55); }
    }

    .btn-smooth-submit:hover {
      transform: translateY(-3px);
      box-shadow: 0 15px 35px rgba(192, 38, 211, 0.65);
    }

    .btn-smooth-submit:active {
      transform: translateY(-1px);
    }

    .register-footer {
      text-align: center;
      margin-top: 24px;
      padding-top: 20px;
      border-top: 1px solid rgba(255, 255, 255, 0.08);
      font-size: 14px;
      color: #94a3b8;
    }

    .register-footer a {
      color: #ec4899;
      text-decoration: none;
      font-weight: 700;
      margin-left: 6px;
      transition: all 0.3s ease;
      display: inline-flex;
      align-items: center;
      gap: 5px;
    }

    .register-footer a:hover {
      color: #f43f5e;
      text-shadow: 0 0 10px rgba(236, 72, 153, 0.6);
      transform: translateX(2px);
    }

    .custom-swal-popup {
      background: rgba(18, 12, 34, 0.96) !important;
      backdrop-filter: blur(22px);
      border: 1px solid rgba(168, 85, 247, 0.45) !important;
      border-radius: 20px !important;
      box-shadow: 0 25px 50px rgba(0, 0, 0, 0.9), 0 0 35px rgba(168, 85, 247, 0.3) !important;
    }

    .custom-swal-title {
      color: #f8fafc !important;
      font-family: 'Plus Jakarta Sans', sans-serif !important;
      font-weight: 700 !important;
    }

    .custom-swal-text {
      color: #94a3b8 !important;
      font-family: 'Plus Jakarta Sans', sans-serif !important;
    }
  </style>
</head>
<body>

  <div class="smooth-bg-mesh"></div>
  <div class="bg-orb orb-1"></div>
  <div class="bg-orb orb-2"></div>

  <div class="auth-container">
    <div class="glass-card">
      
      <div class="auth-header">
        <h2>স্বাগতম</h2>
        <p>আপনার অ্যাকাউন্টে লগইন করুন</p>
      </div>

      <form id="loginForm">
        <div class="form-group-custom">
          <div class="input-wrapper">
            <i class="fa-solid fa-user left-icon"></i>
            <input type="text" class="smooth-input" id="email" placeholder="ইউজারনেম" required autocomplete="username">
          </div>
        </div>

        <div class="form-group-custom">
          <div class="input-wrapper">
            <i class="fa-solid fa-lock left-icon"></i>
            <input type="password" class="smooth-input" id="password" placeholder="পাসওয়ার্ড" required autocomplete="current-password">
            <i class="fa-solid fa-eye toggle-pwd" id="togglePassword"></i>
          </div>
          <input type="hidden" id="token" value="<?php echo htmlspecialchars($reg_token, ENT_QUOTES, 'UTF-8'); ?>">
        </div>

        <div>
          <button type="submit" id="loginBtn" class="btn-smooth-submit">
            <i class="fa-solid fa-arrow-right-to-bracket"></i>
            <span>সাইন ইন করুন</span>
          </button>
        </div>
      </form>

      <div class="register-footer">
        <span>অ্যাকাউন্ট নেই?</span>
        <a href="register_user.php">
          <span>রেজিস্টার করুন</span>
          <i class="fa-solid fa-arrow-right"></i>
        </a>
      </div>

    </div>
  </div>

  <script src="assets/vendors/js/vendor.bundle.base.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <script>
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');

    togglePassword.addEventListener('click', function () {
      const isPwd = passwordInput.getAttribute('type') === 'password';
      passwordInput.setAttribute('type', isPwd ? 'text' : 'password');
      this.classList.toggle('fa-eye', !isPwd);
      this.classList.toggle('fa-eye-slash', isPwd);
    });

    document.getElementById('loginForm').addEventListener('submit', function(e) {
      e.preventDefault();

      var email = document.getElementById('email').value.trim();
      var password = document.getElementById('password').value.trim();
      var token = document.getElementById('token').value;
      var loginBtn = document.getElementById('loginBtn');

      if (!email || !password) {
        Swal.fire({
          icon: 'warning',
          title: 'তথ্য দিন',
          text: 'অনুগ্রহ করে আপনার ইউজারনেম এবং পাসওয়ার্ড প্রদান করুন।',
          confirmButtonColor: '#7c3aed',
          customClass: {
            popup: 'custom-swal-popup',
            title: 'custom-swal-title',
            htmlContainer: 'custom-swal-text'
          }
        });
        return;
      }

      loginBtn.disabled = true;
      loginBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> <span>প্রসেস হচ্ছে...</span>';

      var xhr = new XMLHttpRequest();
      xhr.open('POST', 'pages/login.php', true);
      xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

      xhr.onreadystatechange = function () {
        if (xhr.readyState == 4) {
          loginBtn.disabled = false;
          loginBtn.innerHTML = '<i class="fa-solid fa-arrow-right-to-bracket"></i> <span>সাইন ইন করুন</span>';

          if (xhr.status == 200) {
            try {
              var response = JSON.parse(xhr.responseText);
              
              if (response.status === 'success') {
                loginBtn.innerHTML = '<i class="fa-solid fa-circle-check"></i> <span>লগইন সফল</span>';
                Swal.fire({
                  icon: 'success',
                  title: 'লগইন সফল হয়েছে!',
                  text: 'আপনাকে ড্যাশবোর্ডে নেওয়া হচ্ছে...',
                  showConfirmButton: false,
                  timer: 2000,
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
                  text: response.message,
                  confirmButtonColor: '#e11d48',
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
                text: 'সার্ভার থেকে রেসপন্স প্রসেস করা যায়নি।',
                confirmButtonColor: '#e11d48',
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
              text: 'সার্ভারের সাথে সংযোগ স্থাপন করা সম্ভব হয়নি।',
              confirmButtonColor: '#e11d48',
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
</body>
</html>