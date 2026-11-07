<?php
// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
session_start();

// Admin session already thakle direct dashboard/bio page-e redirect
if (isset($_SESSION['admin_token']) && !empty($_SESSION['admin_token'])) {
    header("Location: dashboard.php");
    exit();
}

// Token validation
$text_token = '';
if (isset($_GET['admin_token']) && !empty($_GET['admin_token'])) {
    $text_token = trim($_GET['admin_token']);
} elseif (isset($_COOKIE['admin_session_token_unique']) && !empty($_COOKIE['admin_session_token_unique'])) {
    $text_token = trim($_COOKIE['admin_session_token_unique']);
} else {
    include_once("404.php");
    exit();
}

// Database configuration
include_once("includes/configuration.php");

// Prepared statement to securely verify admin token
$stmt =$conn->prepare("SELECT id, name, email, token, balance FROM admin_user WHERE token = ? AND status = 1 LIMIT 1");
$stmt->bind_param("s", $text_token);$stmt->execute();
$result =$stmt->get_result();

if ($result->num_rows > 0) {
    $admin_data =$result->fetch_assoc();
    $id =$admin_data["id"];
    $email =$admin_data["email"];
    $name =$admin_data["name"];
    $token =$admin_data["token"];
} else {
    $stmt->close();
    include_once("404.php");
    exit();
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Admin Portal - Sokol Kaj</title>
    
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Hind+Siliguri:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <!-- Core Vendor CSS -->
    <link rel="stylesheet" href="assets/vendors/feather/feather.css">
    <link rel="stylesheet" href="assets/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="shortcut icon" href="<?php echo soko_e(soko_brand('site_favicon')); ?>" />

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }

        body {
            font-family: 'Plus Jakarta Sans', 'Hind Siliguri', sans-serif;
            background: radial-gradient(circle at top left, #1e1b4b, #0f172a 60%, #020617);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #F8FAFC;
        }

        .auth-card {
            background: rgba(15, 23, 42, 0.82);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6);
            width: 100%;
            max-width: 440px;
            padding: 36px 30px;
            position: relative;
            overflow: hidden;
        }

        .auth-card::before {
            content: '';
            position: absolute;
            top: -60px;
            right: -60px;
            width: 160px;
            height: 160px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.25) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        .form-input-field {
            background: rgba(30, 41, 59, 0.7) !important;
            border: 1.5px solid rgba(255, 255, 255, 0.12) !important;
            color: #FFFFFF !important;
            border-radius: 14px !important;
            padding: 13px 16px 13px 44px !important;
            font-size: 14px !important;
            transition: all 0.25s ease;
            width: 100%;
        }

        .form-input-field:focus {
            background: rgba(30, 41, 59, 0.95) !important;
            border-color: #6366F1 !important;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.2) !important;
            outline: none;
        }

        .form-input-field::placeholder {
            color: #64748B !important;
            font-size: 13px;
        }

        .input-icon-wrap {
            position: relative;
            margin-bottom: 18px;
        }

        .input-icon-wrap .icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #818CF8;
            font-size: 15px;
            pointer-events: none;
        }

        .login-btn {
            background: linear-gradient(135deg, #4F46E5 0%, #6366F1 50%, #7C3AED 100%);
            color: #FFFFFF;
            font-weight: 700;
            font-size: 15px;
            padding: 13px;
            border-radius: 14px;
            width: 100%;
            border: none;
            cursor: pointer;
            box-shadow: 0 8px 24px rgba(79, 70, 229, 0.35);
            transition: all 0.25s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(79, 70, 229, 0.45);
        }

        .login-btn:active {
            transform: scale(0.98);
        }
    </style>
</head>

<body>
    <div class="p-3 w-full flex items-center justify-center">
        <div class="auth-card">
            
            <!-- Branding Header -->
            <div class="text-center mb-6">
                <div class="w-16 h-16 mx-auto mb-3 rounded-2xl bg-white/10 border border-white/20 p-2 shadow-inner flex items-center justify-center">
                    <img src="<?php echo soko_e(soko_brand('site_logo')); ?>" alt="Sokol Kaj Logo" class="w-full h-full object-cover rounded-xl">
                </div>
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold text-amber-300 bg-amber-400/15 border border-amber-400/25 mb-2">
                    <i class="fa-solid fa-shield-halved"></i> SECURE ADMIN PORTAL
                </div>
                <h2 class="text-xl font-extrabold text-white tracking-wide">প্রবেশ প্যানেল</h2>
                <p class="text-xs text-slate-400 mt-1">স্বাগতম, <?php echo htmlspecialchars($name); ?>! অনুগ্রহ করে লগইন করুন।</p>
            </div>

            <!-- Login Form -->
            <form id="adminLoginForm">
                <div class="input-icon-wrap">
                    <i class="fa-solid fa-envelope icon"></i>
                    <input type="email" class="form-input-field" id="email" placeholder="এডমিন ইমেইল এড্রেস" required autocomplete="username">
                </div>

                <div class="input-icon-wrap">
                    <i class="fa-solid fa-lock icon"></i>
                    <input type="password" class="form-input-field" id="password" placeholder="গোপন পাসওয়ার্ড" required autocomplete="current-password">
                </div>

                <input type="hidden" id="token" value="<?php echo htmlspecialchars($token); ?>">

                <div class="mt-5">
                    <button type="submit" id="loginBtn" class="login-btn">
                        <span>সাইন ইন করুন</span>
                        <i class="fa-solid fa-arrow-right-to-bracket text-sm"></i>
                    </button>
                </div>
            </form>

            <div class="mt-6 text-center text-slate-500 text-[11px]">
                <p>&copy; 2026 Sokol Kaj. All Rights Reserved.</p>
            </div>

        </div>
    </div>

    <!-- Scripts -->
    <script src="assets/vendors/js/vendor.bundle.base.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
    document.getElementById('adminLoginForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const btn = document.getElementById('loginBtn');
        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value.trim();
        const token = document.getElementById('token').value.trim();

        if (!email || !password) {
            Swal.fire({
                icon: 'warning',
                title: 'তথ্য দিন',
                text: 'অনুগ্রহ করে ইমেইল এবং পাসওয়ার্ড উভয়ই পূরণ করুন।',
                background: '#0F172A',
                color: '#F8FAFC',
                confirmButtonColor: '#4F46E5'
            });
            return;
        }

        // Loading state
        btn.disabled = true;
        btn.innerHTML = `<i class="fa-solid fa-circle-notch fa-spin text-sm"></i> <span>যাচাই হচ্ছে...</span>`;

        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'pages/login.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                btn.disabled = false;
                btn.innerHTML = `<span>সাইন ইন করুন</span> <i class="fa-solid fa-arrow-right-to-bracket text-sm"></i>`;

                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);

                        if (response.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'লগইন সফল!',
                                text: response.message || 'ড্যাশবোর্ডে প্রবেশ করা হচ্ছে...',
                                showConfirmButton: false,
                                timer: 1400,
                                background: '#0F172A',
                                color: '#F8FAFC'
                            }).then(() => {
                                window.location.href = 'dashboard.php';
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'প্রবেশ ব্যর্থ!',
                                text: response.message || 'ভুল ইমেইল অথবা পাসওয়ার্ড দেওয়া হয়েছে।',
                                background: '#0F172A',
                                color: '#F8FAFC',
                                confirmButtonColor: '#4F46E5'
                            });
                        }
                    } catch (err) {
                        Swal.fire({
                            icon: 'error',
                            title: 'সার্ভার ত্রুটি',
                            text: 'সার্ভার থেকে সঠিক রেসপন্স পাওয়া যায়নি।',
                            background: '#0F172A',
                            color: '#F8FAFC',
                            confirmButtonColor: '#4F46E5'
                        });
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'কানেকশন সমস্যা',
                        text: 'নেটওয়ার্ক বা সার্ভারে সমস্যা দেখা দিয়েছে।',
                        background: '#0F172A',
                        color: '#F8FAFC',
                        confirmButtonColor: '#4F46E5'
                    });
                }
            }
        };

        xhr.send('email=' + encodeURIComponent(email) + '&password=' + encodeURIComponent(password) + '&token=' + encodeURIComponent(token));
    });
    </script>
</body>
</html>