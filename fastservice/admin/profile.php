<?php 
include("header.php");

function getUserIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($ips[0]);
    } else {
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }
}

$user_ip = getUserIP();
?>

<div class="max-w-5xl mx-auto space-y-6">

    <!-- Page Title Header -->
    <div class="flex items-center justify-between pb-4 border-b border-slate-200">
        <div>
            <h2 class="text-xl font-extrabold text-slate-800">এডমিন প্রোফাইল</h2>
            <p class="text-xs text-slate-500">আপনার একাউন্ট তথ্য ও নিরাপত্তা সেটিংস পরিচালনা করুন</p>
        </div>
        <a href="dashboard.php" class="text-xs font-bold text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 px-3.5 py-2 rounded-xl transition shadow-sm">
            <i class="fa-solid fa-arrow-left me-1"></i> ড্যাশবোর্ড
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-12 gap-6">

        <!-- Left Column: Profile Card -->
        <div class="md:col-span-5 space-y-6">
            <div class="bg-white rounded-3xl p-6 border border-slate-200/90 shadow-sm text-center relative overflow-hidden">
                <div class="absolute top-0 left-0 right-0 h-20 bg-gradient-to-r from-indigo-500 to-purple-600"></div>
                
                <div class="relative pt-6">
                    <div class="w-24 h-24 mx-auto rounded-2xl bg-white p-1 shadow-md border border-slate-100">
                        <img src="https://fastservice.totthohub.com/assets/logo.jpg" alt="Profile Picture" class="w-full h-full object-cover rounded-xl" onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($admin_name); ?>&background=4f46e5&color=fff';">
                    </div>

                    <h3 class="text-lg font-extrabold text-slate-800 mt-3"><?php echo htmlspecialchars($admin_name ?? 'Admin'); ?></h3>
                    <p class="text-xs text-slate-400 font-medium"><?php echo htmlspecialchars($admin_email ?? ''); ?></p>
                    
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-100 mt-2">
                        <i class="fa-solid fa-shield-check text-xs"></i> Super Administrator
                    </div>

                    <div class="mt-6 pt-5 border-t border-slate-100 grid grid-cols-2 gap-3 text-left">
                        <div class="bg-slate-50 p-3 rounded-2xl border border-slate-100">
                            <span class="text-[10px] text-slate-400 font-bold block uppercase">এডমিন ব্যালেন্স</span>
                            <span class="text-sm font-black text-indigo-600">৳ <?php echo number_format($admin_balance ?? 0, 2); ?></span>
                        </div>
                        <div class="bg-slate-50 p-3 rounded-2xl border border-slate-100">
                            <span class="text-[10px] text-slate-400 font-bold block uppercase">একাউন্ট আইডি</span>
                            <span class="text-sm font-black text-slate-700">#<?php echo htmlspecialchars($admin_id ?? '1'); ?></span>
                        </div>
                    </div>

                    <button type="button" onclick="openPasswordModal()" class="w-full mt-5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-xl text-xs transition shadow-md shadow-indigo-600/20 flex items-center justify-center gap-2">
                        <i class="fa-solid fa-key"></i> পাসওয়ার্ড পরিবর্তন করুন
                    </button>
                </div>
            </div>

            <!-- Security Notice Box -->
            <div class="bg-amber-50 border border-amber-200/80 rounded-2xl p-4 text-left">
                <div class="flex items-start gap-2.5">
                    <i class="fa-solid fa-triangle-exclamation text-amber-600 mt-0.5 text-sm"></i>
                    <div>
                        <h5 class="text-xs font-bold text-amber-900">নিরাপত্তা সতর্কতা</h5>
                        <p class="text-[11px] text-amber-700 mt-0.5 leading-relaxed">
                            এডমিন প্যানেলে সবসময় শক্তিশালী পাসওয়ার্ড ব্যবহার করুন এবং কাজ শেষে লগআউট নিশ্চিত করুন।
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Information & Details -->
        <div class="md:col-span-7 space-y-6">
            
            <div class="bg-white rounded-3xl p-6 border border-slate-200/90 shadow-sm space-y-5">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h4 class="font-extrabold text-sm text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-circle-info text-indigo-600"></i> আপনার বিবরণ ও সেশন তথ্য
                    </h4>
                    <span class="text-[10px] bg-emerald-50 text-emerald-700 border border-emerald-200 px-2.5 py-0.5 rounded-full font-bold">একটিভ সেশন</span>
                </div>

                <div class="space-y-3">
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-xs font-semibold text-slate-500">নাম (Name)</span>
                        <span class="text-xs font-bold text-slate-800"><?php echo htmlspecialchars($admin_name ?? ''); ?></span>
                    </div>

                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-xs font-semibold text-slate-500">ইমেইল (Email)</span>
                        <span class="text-xs font-bold text-slate-800"><?php echo htmlspecialchars($admin_email ?? ''); ?></span>
                    </div>

                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-xs font-semibold text-slate-500">আইপি এড্রেস (IP Address)</span>
                        <span class="text-xs font-mono font-bold text-indigo-600"><?php echo htmlspecialchars($user_ip); ?></span>
                    </div>

                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-xs font-semibold text-slate-500">রোল / পারমিশন</span>
                        <span class="text-xs font-bold text-emerald-600">ফুল এক্সেস (Super Admin)</span>
                    </div>

                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-xs font-semibold text-slate-500">বর্তমান তারিখ ও সময়</span>
                        <span class="text-xs font-bold text-slate-700"><?php echo date('d M Y, h:i A'); ?></span>
                    </div>
                </div>
            </div>

            <!-- Quick Action Links -->
            <div class="bg-white rounded-3xl p-5 border border-slate-200/90 shadow-sm flex items-center justify-between">
                <div>
                    <h5 class="text-xs font-bold text-slate-800">লগআউট করতে চান?</h5>
                    <p class="text-[11px] text-slate-400">বর্তমান এডমিন সেশন শেষ করতে সাইন আউট করুন</p>
                </div>
                <a href="logout.php" class="bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i> সাইন আউট
                </a>
            </div>

        </div>

    </div>

</div>

<!-- Change Password Modal -->
<div id="passwordModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 relative">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
            <h4 class="font-extrabold text-sm text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-key text-indigo-600"></i> পাসওয়ার্ড পরিবর্তন
            </h4>
            <button type="button" onclick="closePasswordModal()" class="text-slate-400 hover:text-slate-600 text-lg">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="pages/change_password_admin.php" method="POST" id="adminPasswordForm" class="space-y-4">
            <div>
                <label for="currentPassword" class="block text-xs font-bold text-slate-600 mb-1">বর্তমান পাসওয়ার্ড</label>
                <input type="password" class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-semibold bg-slate-50 outline-none focus:border-indigo-500 focus:bg-white transition" id="currentPassword" name="currentPassword" placeholder="বর্তমান পাসওয়ার্ড দিন" required>
            </div>

            <div>
                <label for="newPassword" class="block text-xs font-bold text-slate-600 mb-1">নতুন পাসওয়ার্ড</label>
                <input type="password" class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-semibold bg-slate-50 outline-none focus:border-indigo-500 focus:bg-white transition" id="newPassword" name="newPassword" placeholder="নতুন পাসওয়ার্ড দিন" required>
            </div>

            <div>
                <label for="confirmPassword" class="block text-xs font-bold text-slate-600 mb-1">নতুন পাসওয়ার্ড নিশ্চিত করুন</label>
                <input type="password" class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-semibold bg-slate-50 outline-none focus:border-indigo-500 focus:bg-white transition" id="confirmPassword" name="confirmPassword" placeholder="নতুন পাসওয়ার্ড পুনরায় দিন" required>
            </div>

            <div class="pt-2 flex gap-2">
                <button type="button" onclick="closePasswordModal()" class="w-1/3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs py-2.5 rounded-xl transition">
                    বাতিল
                </button>
                <button type="submit" class="w-2/3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs py-2.5 rounded-xl shadow-md shadow-indigo-600/20 transition">
                    পাসওয়ার্ড আপডেট করুন
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openPasswordModal() {
        document.getElementById('passwordModal').classList.remove('hidden');
    }

    function closePasswordModal() {
        document.getElementById('passwordModal').classList.add('hidden');
    }

    document.getElementById('adminPasswordForm')?.addEventListener('submit', function(e) {
        const newPass = document.getElementById('newPassword').value;
        const confPass = document.getElementById('confirmPassword').value;

        if (newPass !== confPass) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'পাসওয়ার্ড মিলেনি!',
                text: 'নতুন পাসওয়ার্ড এবং কনফার্ম পাসওয়ার্ড একই হতে হবে।',
                confirmButtonColor: '#4f46e5'
            });
        }
    });

    // URL theke message niye SweetAlert dekhano
    const urlParams = new URLSearchParams(window.location.search);
    const msg = urlParams.get('msg');
    const text = urlParams.get('text');

    if (msg && text) {
        Swal.fire({
            icon: msg === 'success' ? 'success' : 'error',
            title: msg === 'success' ? 'সফল!' : 'ব্যর্থ!',
            text: text,
            confirmButtonColor: '#4f46e5'
        }).then(() => {
            const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
            window.history.replaceState({ path: cleanUrl }, '', cleanUrl);
        });
    }
</script>

<?php include("footer.php"); ?>