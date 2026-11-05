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

$conn->close();
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>রেজিস্ট্রেশন - FAST SERVICE</title>
    <link rel="stylesheet" href="assets/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="shortcut icon" href="assets/images/logo-mini.svg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif; -webkit-font-smoothing: antialiased; }
        
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
          inset: 0; 
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
          position: fixed; 
          border-radius: 50%; 
          filter: blur(95px); 
          opacity: .55; 
          animation: orbFloat 10s ease-in-out infinite alternate; 
          z-index: -1; 
        }

        .orb-1 { top: 10%; left: 10%; width: 330px; height: 330px; background: linear-gradient(135deg, #8b5cf6, #ec4899); }
        .orb-2 { bottom: 10%; right: 10%; width: 370px; height: 370px; background: linear-gradient(135deg, #6366f1, #d946ef); animation-delay: -5s; }

        @keyframes orbFloat { 
          0% { transform: translate(0, 0) scale(1); } 
          100% { transform: translate(30px, -40px) scale(1.08); } 
        }

        .auth-container { width: 100%; max-width: 520px; padding: 20px; margin: auto; }
        
        .glass-card { 
          background: rgba(23, 15, 43, 0.75); 
          backdrop-filter: blur(25px) saturate(190%); 
          -webkit-backdrop-filter: blur(25px) saturate(190%); 
          border: 1px solid rgba(168, 85, 247, 0.35); 
          border-radius: 26px; 
          padding: 36px 30px; 
          box-shadow: 0 25px 65px rgba(0, 0, 0, 0.88), 
                      0 0 35px rgba(168, 85, 247, 0.2); 
          transition: all .4s cubic-bezier(.16, 1, .3, 1); 
        }

        .glass-card:hover { 
          border-color: rgba(236, 72, 153, 0.55); 
          box-shadow: 0 30px 75px rgba(0, 0, 0, 0.92), 
                      0 0 45px rgba(236, 72, 153, 0.28); 
        }

        .header-section { text-align: center; margin-bottom: 24px; }
        .title-text { 
          margin: 0 0 6px 0; 
          background: linear-gradient(135deg, #ffffff 0%, #cbd5e1 50%, #c084fc 100%); 
          -webkit-background-clip: text; 
          -webkit-text-fill-color: transparent; 
          font-size: 26px; 
          font-weight: 800; 
          letter-spacing: -.02em; 
        }
        .subtitle-text { margin: 0; color: #94a3b8; font-size: 13px; line-height: 1.5; }

        .info-guidance-box {
          background: rgba(139, 92, 246, 0.08);
          border: 1px solid rgba(168, 85, 247, 0.25);
          border-radius: 16px;
          padding: 14px 16px;
          margin-bottom: 22px;
          display: flex;
          flex-direction: column;
          gap: 6px;
        }

        .info-guidance-item {
          display: flex;
          align-items: center;
          gap: 10px;
          font-size: 12px;
          color: #cbd5e1;
        }

        .info-guidance-item i {
          font-size: 13px;
          color: #d946ef;
        }

        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0 16px; }
        .form-group-custom { margin-bottom: 18px; }
        .form-group-custom.full-width { grid-column: 1 / -1; }
        
        .form-label-custom { display: block; margin-bottom: 8px; color: #cbd5e1; font-size: 13px; font-weight: 600; }
        .input-wrapper { position: relative; display: flex; align-items: center; }
        
        .left-icon { position: absolute; left: 16px; z-index: 2; color: #a855f7; font-size: 16px; transition: color .3s ease; }
        
        .smooth-input { 
          width: 100%; 
          padding: 14px 44px 14px 48px; 
          border: 1px solid rgba(168, 85, 247, 0.28); 
          border-radius: 14px; 
          outline: none; 
          background: rgba(14, 9, 28, 0.88); 
          color: #f8fafc; 
          font-size: 14px; 
          font-weight: 500; 
          transition: all .3s cubic-bezier(.16, 1, .3, 1); 
        }
        .smooth-input::placeholder { color: #64748b; font-weight: 400; }
        .smooth-input:focus { border-color: #d946ef; background: rgba(14, 9, 28, 0.98); box-shadow: 0 0 22px rgba(217, 70, 239, .35); }
        .input-wrapper:focus-within .left-icon { color: #f43f5e; }
        
        .toggle-pwd { position: absolute; right: 16px; z-index: 2; color: #a855f7; cursor: pointer; font-size: 15px; transition: color .3s ease; }
        .toggle-pwd:hover { color: #fff; }
        
        .btn-smooth-submit { 
          width: 100%; 
          padding: 15px; 
          border: 0; 
          border-radius: 14px; 
          background: linear-gradient(135deg, #7c3aed 0%, #c026d3 50%, #e11d48 100%); 
          background-size: 200% 200%; 
          color: #fff; 
          font-size: 15px; 
          font-weight: 700; 
          cursor: pointer; 
          box-shadow: 0 10px 25px rgba(124, 58, 237, .45); 
          transition: all .4s cubic-bezier(.16, 1, .3, 1); 
          display: flex; 
          align-items: center; 
          justify-content: center; 
          gap: 10px; 
          animation: btnGlow 4s ease-in-out infinite alternate; 
        }
        @keyframes btnGlow { 0% { background-position: 0% 50%; box-shadow: 0 10px 25px rgba(124, 58, 237, .4); } 100% { background-position: 100% 50%; box-shadow: 0 10px 30px rgba(225, 29, 72, .55); } }
        .btn-smooth-submit:hover { transform: translateY(-2px); box-shadow: 0 15px 35px rgba(192, 38, 211, .65); }
        .btn-smooth-submit:disabled { opacity: .7; cursor: wait; transform: none; }
        
        .btn-smooth-login { 
          width: 100%; 
          margin-top: 14px; 
          padding: 13px; 
          border: 1px solid rgba(168, 85, 247, 0.28); 
          border-radius: 14px; 
          display: flex; 
          align-items: center; 
          justify-content: center; 
          gap: 8px; 
          background: rgba(255, 255, 255, .04); 
          color: #c084fc; 
          font-size: 14px; 
          font-weight: 600; 
          text-decoration: none; 
          transition: all .3s cubic-bezier(.16, 1, .3, 1); 
        }
        .btn-smooth-login:hover { border-color: rgba(236, 72, 153, .55); background: rgba(255, 255, 255, .08); color: #fff; text-decoration: none; transform: translateY(-2px); }
        
        .custom-swal-popup {
          background: #150d28 !important;
          border: 1px solid rgba(168, 85, 247, 0.4) !important;
          border-radius: 24px !important;
          box-shadow: 0 25px 50px rgba(0, 0, 0, 0.95), 0 0 35px rgba(168, 85, 247, 0.3) !important;
        }

        .custom-swal-title {
          color: #ffffff !important;
          font-family: 'Plus Jakarta Sans', sans-serif !important;
          font-weight: 700 !important;
        }

        .custom-swal-text {
          color: #cbd5e1 !important;
          font-family: 'Plus Jakarta Sans', sans-serif !important;
        }

        .swal2-timer-progress-bar {
          background: #ec4899 !important;
        }

        @media (max-width: 575px) { 
          body { align-items: flex-start; } 
          .auth-container { padding: 14px; } 
          .glass-card { padding: 26px 18px; } 
          .form-grid { grid-template-columns: 1fr; } 
          .form-group-custom.full-width { grid-column: auto; } 
        }
    </style>
</head>
<body>
    <div class="smooth-bg-mesh"></div>
    <div class="bg-orb orb-1"></div>
    <div class="bg-orb orb-2"></div>

    <div class="auth-container">
        <div class="glass-card">
            <div class="header-section">
                <h2 class="title-text">FAST SERVICE</h2>
                <p class="subtitle-text">নির্ভুল তথ্য দিয়ে আপনার নতুন অ্যাকাউন্ট তৈরি করুন</p>
            </div>

            <div class="info-guidance-box">
                <div class="info-guidance-item">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>সঠিক WhatsApp নম্বর দিন, অ্যাকাউন্ট পুনরুদ্ধারে প্রয়োজন হবে।</span>
                </div>
                <div class="info-guidance-item">
                    <i class="fa-solid fa-shield-halved"></i>
                    <span>সুরক্ষিত থাকতে কমপক্ষে ৬ ডিজিটের পাসওয়ার্ড ব্যবহার করুন।</span>
                </div>
                <div class="info-guidance-item">
                    <i class="fa-solid fa-bolt"></i>
                    <span>রেজিস্ট্রেশন সম্পন্ন হলে স্বয়ংক্রিয়ভাবে ড্যাশবোর্ডে প্রবেশ করবেন।</span>
                </div>
            </div>

            <form method="POST" action="" id="registerForm">
                <div class="form-grid">
                    <div class="form-group-custom">
                        <label for="name" class="form-label-custom"><i class="fa-solid fa-user me-1" style="color:#a855f7;"></i> আপনার নাম</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-signature left-icon"></i>
                            <input type="text" name="name" id="name" class="smooth-input" placeholder="সম্পূর্ণ নাম লিখুন" value="<?php echo htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required autocomplete="name">
                        </div>
                    </div>

                    <div class="form-group-custom">
                        <label for="number" class="form-label-custom"><i class="fa-brands fa-whatsapp me-1" style="color:#22c55e;"></i> WhatsApp নম্বর</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-phone left-icon"></i>
                            <input type="tel" name="number" id="number" class="smooth-input" placeholder="০১XXXXXXXXX" value="<?php echo htmlspecialchars($_POST['number'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required autocomplete="tel" inputmode="numeric">
                        </div>
                    </div>

                    <div class="form-group-custom full-width">
                        <label for="email" class="form-label-custom"><i class="fa-solid fa-envelope me-1" style="color:#ec4899;"></i> ইমেইল ঠিকানা</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-at left-icon"></i>
                            <input type="email" name="email" id="email" class="smooth-input" placeholder="name@example.com" value="<?php echo htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required autocomplete="email">
                        </div>
                    </div>

                    <div class="form-group-custom full-width">
                        <label for="password" class="form-label-custom"><i class="fa-solid fa-lock me-1" style="color:#f43f5e;"></i> পাসওয়ার্ড</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-key left-icon"></i>
                            <input type="password" name="password" id="password" class="smooth-input" placeholder="কমপক্ষে ৬ ডিজিটের পাসওয়ার্ড" required autocomplete="new-password">
                            <i class="fa-solid fa-eye toggle-pwd" id="togglePassword"></i>
                        </div>
                    </div>
                </div>

                <button type="submit" id="registerBtn" class="btn-smooth-submit">
                    <span>রেজিস্টার করুন</span>
                    <i class="fa-solid fa-user-plus"></i>
                </button>
                <a href="index.php" class="btn-smooth-login">
                    <i class="fa-solid fa-arrow-right-to-bracket"></i>
                    <span>ইতোমধ্যে অ্যাকাউন্ট আছে? লগইন করুন</span>
                </a>
            </form>
        </div>
    </div>

    <script src="assets/vendors/js/vendor.bundle.base.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const registerForm = document.getElementById('registerForm');
        const registerBtn = document.getElementById('registerBtn');

        togglePassword.addEventListener('click', function () {
            const isPassword = passwordInput.type === 'password';
            passwordInput.type = isPassword ? 'text' : 'password';
            this.classList.toggle('fa-eye', !isPassword);
            this.classList.toggle('fa-eye-slash', isPassword);
        });

        registerForm.addEventListener('submit', function () {
            registerBtn.disabled = true;
            registerBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i><span>প্রসেস হচ্ছে...</span>';
        });

        <?php if ($registrationSuccessful): ?>
        Swal.fire({
            icon: 'success',
            title: 'রেজিস্ট্রেশন সফল হয়েছে!',
            text: 'আপনাকে স্বয়ংক্রিয়ভাবে ড্যাশবোর্ডে নেওয়া হচ্ছে...',
            showConfirmButton: false,
            timer: 2200,
            timerProgressBar: true,
            allowOutsideClick: false,
            allowEscapeKey: false,
            customClass: {
                popup: 'custom-swal-popup',
                title: 'custom-swal-title',
                htmlContainer: 'custom-swal-text'
            }
        }).then(function () {
            window.location.replace('dashboard.php');
        });
        <?php elseif ($feedback !== ''): ?>
        Swal.fire({
            icon: 'error',
            title: 'রেজিস্ট্রেশন ব্যর্থ!',
            text: <?php echo json_encode($feedback, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>,
            confirmButtonColor: '#e11d48',
            customClass: {
                popup: 'custom-swal-popup',
                title: 'custom-swal-title',
                htmlContainer: 'custom-swal-text'
            }
        });
        <?php endif; ?>
    </script>
</body>
</html>