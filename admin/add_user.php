<?php 
include_once("header.php");

$save_msg = "";
$save_error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_user'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $initial_balance = floatval($_POST['balance'] ?? 0);
    $address = trim($_POST['address'] ?? '');

    if (empty($name) || empty($email) || empty($mobile) || empty($password)) {
        $save_error = "অনুগ্রহ করে সকল আবশ্যকীয় তথ্য পূরণ করুন।";
    } else {
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR mobile = ? LIMIT 1");
        $check_stmt->bind_param("ss", $email, $mobile);
        $check_stmt->execute();
        $check_res = $check_stmt->get_result();

        if ($check_res->num_rows > 0) {
            $save_error = "এই ইমেইল অথবা মোবাইল নম্বর দিয়ে ইতিমধ্যে একাউন্ট রয়েছে!";
        } else {
            $token = bin2hex(random_bytes(16));
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $default_image = "assets/images/faces/face8.jpg";

            $insert_stmt = $conn->prepare("INSERT INTO users (name, email, mobile, password, balance, token, address, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $insert_stmt->bind_param("ssssdsss", $name, $email, $mobile, $hashed_password, $initial_balance, $token, $address, $default_image);

            if ($insert_stmt->execute()) {
                $save_msg = "ব্যবহারকারী সফলভাবে যুক্ত করা হয়েছে!";
            } else {
                $save_error = "ডাটাবেজে যুক্ত করতে সমস্যা হয়েছে। অনুগ্রহ করে পুনরায় চেষ্টা করুন।";
            }
            $insert_stmt->close();
        }
        $check_stmt->close();
    }
}
?>

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<script src="https://cdn.tailwindcss.com"></script>

<style>
    body {
        background: #f1f5f9 !important;
        font-family: 'Plus Jakarta Sans', 'Hind Siliguri', sans-serif !important;
        color: #1e293b;
    }
    .user-form-card {
        max-width: 760px;
        margin: 0 auto 30px;
        background: #ffffff;
        border-radius: 24px;
        border: 1px solid rgba(226, 232, 240, 0.9);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
        padding: 30px 24px;
    }
    .form-input-box {
        width: 100%;
        border-radius: 14px;
        border: 1.5px solid #cbd5e1;
        padding: 11px 16px;
        font-size: 14px;
        outline: none;
        transition: all 0.2s ease;
        background: #ffffff;
    }
    .form-input-box:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
    }
    .btn-create-user {
        background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
        color: #ffffff;
        font-weight: 700;
        padding: 13px 20px;
        border-radius: 14px;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        box-shadow: 0 4px 15px rgba(79, 70, 229, 0.25);
        cursor: pointer;
        transition: transform 0.2s;
    }
    .btn-create-user:hover {
        transform: translateY(-2px);
    }
</style>

<div class="container-fluid px-3 px-md-4 py-4">

    <div class="user-form-card">
        
        <div class="flex items-center justify-between border-b pb-4 mb-5">
            <div>
                <h3 class="text-xl font-extrabold text-slate-800 flex items-center gap-2">
                    <span class="text-indigo-600"><i class="fa-solid fa-user-plus"></i></span> নতুন ব্যবহারকারী যোগ করুন
                </h3>
                <p class="text-xs text-slate-500 mt-1">ব্যবহারকারীর প্রোফাইল তথ্য এবং প্রাথমিক ব্যালেন্স নির্ধারণ করুন</p>
            </div>
            <a href="users.php" class="text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 px-3.5 py-2 rounded-xl font-bold flex items-center gap-1.5 transition">
                <i class="fa-solid fa-users"></i> গ্রাহক তালিকা
            </a>
        </div>

        <?php if(!empty($save_msg)): ?>
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-2xl mb-4 text-xs font-bold flex items-center gap-2">
                <i class="fa-solid fa-circle-check text-emerald-600"></i> <?php echo $save_msg; ?>
            </div>
        <?php elseif(!empty($save_error)): ?>
            <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-2xl mb-4 text-xs font-bold flex items-center gap-2">
                <i class="fa-solid fa-circle-exclamation text-rose-600"></i> <?php echo $save_error; ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">পূর্ণ নাম <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" class="form-input-box" placeholder="যেমন: মো. রহিম উদ্দিন" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">ইমেইল এড্রেস <span class="text-rose-500">*</span></label>
                    <input type="email" name="email" class="form-input-box" placeholder="user@gmail.com" required>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">মোবাইল নম্বর <span class="text-rose-500">*</span></label>
                    <input type="text" name="mobile" class="form-input-box" placeholder="018XXXXXXXX" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">লগইন পাসওয়ার্ড <span class="text-rose-500">*</span></label>
                    <input type="password" name="password" class="form-input-box" placeholder="নূন্যতম ৬ ডিজিট" required>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">প্রাথমিক ব্যালেন্স (ঐচ্ছিক)</label>
                    <input type="number" step="0.01" name="balance" class="form-input-box font-bold" placeholder="0.00" value="0.00">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">ঠিকানা (ঐচ্ছিক)</label>
                    <input type="text" name="address" class="form-input-box" placeholder="গ্রাহকের সম্পূর্ণ ঠিকানা">
                </div>
            </div>

            <div class="mt-6">
                <button type="submit" name="save_user" class="btn-create-user w-full">
                    <i class="fa-solid fa-floppy-disk"></i> ব্যবহারকারী সংরক্ষণ করুন
                </button>
            </div>
        </form>
    </div>

</div>

<?php include_once("footer.php"); ?>