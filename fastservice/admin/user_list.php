<?php
session_start();
include_once("header.php");

if (!isset($_SESSION["admin_token"])) { 
    header("location: index.php");
    exit;
} 

$users_query = $conn->query("SELECT * FROM users ORDER BY id DESC");
$total_users_count = 0;
$active_users_count = 0;
$total_user_balance = 0.00;

$user_list = [];
if ($users_query && $users_query->num_rows > 0) {
    while ($r = $users_query->fetch_assoc()) {
        $user_list[] = $r;
        $total_users_count++;
        if (($r['status'] ?? 0) == 1) {
            $active_users_count++;
        }
        $total_user_balance += floatval($r['balance'] ?? 0);
    }
}
?>

<div class="space-y-6">

    <!-- Marquee Notice -->
    <?php
        if (file_exists('includes/marquee.php')) {
            include_once 'includes/marquee.php';
            if (class_exists('Marquee')) {
                $marquee = new Marquee($conn, 4);
                $marquee->display();
            }
        }
    ?>

    <!-- Header Section -->
    <div class="bg-white p-5 md:p-6 rounded-3xl border border-slate-200/90 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">User Management Console</span>
            </div>
            <h3 class="text-xl font-black text-slate-800">সকল নিবন্ধিত ইউজার তালিকা</h3>
            <p class="text-xs text-slate-500 font-medium">গ্রাহক একাউন্টের ব্যালেন্স, স্ট্যাটাস, পাসওয়ার্ড ও অ্যাক্সেস নিয়ন্ত্রণ করুন</p>
        </div>

        <div class="flex items-center gap-2.5 w-full md:w-auto">
            <a href="createLink.php" class="w-full md:w-auto bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs px-4 py-2.5 rounded-xl transition shadow-md shadow-indigo-600/20 flex items-center justify-center gap-2">
                <i class="fa-solid fa-user-plus text-xs"></i> একাউন্ট তৈরির লিংক
            </a>
            <button onclick="location.reload()" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs px-3.5 py-2.5 rounded-xl transition" title="রিলোড">
                <i class="fa-solid fa-rotate-right"></i>
            </button>
        </div>
    </div>

    <!-- Stats Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-4 md:p-5 rounded-2xl border border-slate-200/90 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider block">মোট গ্রাহক</span>
                <h4 class="text-2xl font-black text-slate-800 mt-1"><?php echo $total_users_count; ?> জন</h4>
                <span class="text-[11px] text-slate-500 font-medium">নিবন্ধিত মোট একাউন্ট</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl font-bold">
                <i class="fa-solid fa-users"></i>
            </div>
        </div>

        <div class="bg-white p-4 md:p-5 rounded-2xl border border-slate-200/90 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider block">সক্রিয় গ্রাহক</span>
                <h4 class="text-2xl font-black text-emerald-600 mt-1"><?php echo $active_users_count; ?> জন</h4>
                <span class="text-[11px] text-slate-500 font-medium">অ্যাক্টিভ স্ট্যাটাসধারী</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl font-bold">
                <i class="fa-solid fa-user-check"></i>
            </div>
        </div>

        <div class="bg-white p-4 md:p-5 rounded-2xl border border-slate-200/90 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider block">গ্রাহকদের মোট ব্যালেন্স</span>
                <h4 class="text-2xl font-black text-purple-600 mt-1">৳ <?php echo number_format($total_user_balance, 2); ?></h4>
                <span class="text-[11px] text-slate-500 font-medium">সিস্টেম কারেন্ট ওয়ালেট</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl font-bold">
                <i class="fa-solid fa-wallet"></i>
            </div>
        </div>
    </div>

    <!-- User Table Card -->
    <div class="bg-white p-5 md:p-6 rounded-3xl border border-slate-200/90 shadow-sm">
        <div class="overflow-x-auto">
            <table id="userTable" class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-[10.5px]">
                    <tr>
                        <th class="p-3.5 text-center w-12">#</th>
                        <th class="p-3.5">ইউজার পরিচিতি</th>
                        <th class="p-3.5">লগইন লিংক ও টোকেন</th>
                        <th class="p-3.5 text-center">ব্যালেন্স ও রিচার্জ</th>
                        <th class="p-3.5 text-center">স্ট্যাটাস</th>
                        <th class="p-3.5 text-center">পাসওয়ার্ড</th>
                        <th class="p-3.5 text-center">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                    <?php if (!empty($user_list)): ?>
                        <?php foreach ($user_list as $index => $u): 
                            $uid      = $u["id"];
                            $email    = $u["email"] ?? '';
                            $name     = $u["name"] ?? 'N/A';
                            $mobile   = $u["mobile"] ?? 'N/A';
                            $token    = $u["token"] ?? '';
                            $balance  = number_format(floatval($u["balance"] ?? 0), 2);
                            $status   = intval($u["status"] ?? 0);
                            $login_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . "/index.php?text_token=" . $token;
                        ?>
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="p-3.5 text-center font-bold text-slate-400">
                                <?php echo $index + 1; ?>
                            </td>

                            <td class="p-3.5">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-sm shrink-0 border border-indigo-100">
                                        <?php echo strtoupper(mb_substr($name, 0, 1, 'UTF-8')); ?>
                                    </div>
                                    <div class="max-w-[200px] truncate">
                                        <span class="font-extrabold text-slate-800 block text-xs truncate"><?php echo htmlspecialchars($name); ?></span>
                                        <span class="text-[11px] text-slate-500 block truncate"><?php echo htmlspecialchars($email); ?></span>
                                        <span class="text-[10px] text-slate-400 font-mono"><i class="fa-solid fa-phone text-[9px] me-1"></i><?php echo htmlspecialchars($mobile); ?></span>
                                    </div>
                                </div>
                            </td>

                            <td class="p-3.5">
                                <div class="flex items-center gap-1.5">
                                    <button type="button" onclick="copyLoginLink('<?php echo htmlspecialchars($login_url, ENT_QUOTES); ?>')" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-2.5 py-1.5 rounded-lg text-[11px] font-bold transition flex items-center gap-1" title="কপি লগইন লিংক">
                                        <i class="fa-solid fa-copy text-xs"></i> কপি
                                    </button>
                                    
                                    <form action="pages/udpate_token.php" method="post" class="inline m-0">
                                        <input type="hidden" name="id" value="<?php echo $uid; ?>">
                                        <input type="hidden" name="old_token" value="<?php echo htmlspecialchars($token); ?>">
                                        <input type="hidden" name="hidden_link" value="<?php echo htmlspecialchars($login_url); ?>">
                                        <button type="submit" name="token_submit" onclick="return confirm('<?php echo htmlspecialchars($email); ?> ইউজারের জন্য নতুন টোকেন তৈরি করতে চান?');" class="bg-indigo-50 hover:bg-indigo-100 text-indigo-600 px-2.5 py-1.5 rounded-lg text-[11px] font-bold transition" title="টোকেন রিসেট">
                                            <i class="fa-solid fa-arrows-rotate text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>

                            <td class="p-3.5 text-center">
                                <div class="flex flex-col items-center gap-1">
                                    <span class="text-xs font-black text-emerald-600 bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 rounded-lg">
                                        ৳ <?php echo $balance; ?>
                                    </span>
                                    <form action="pages/balance_add.php" method="post" class="flex items-center gap-1 mt-1">
                                        <input type="number" step="any" name="balance" class="w-20 border border-slate-200 rounded-lg px-2 py-1 text-xs text-center font-bold bg-slate-50 outline-none focus:border-indigo-500" placeholder="টাকা" required>
                                        <input type="hidden" name="id" value="<?php echo $uid; ?>">
                                        <button type="submit" name="submit" onclick="return confirm('<?php echo htmlspecialchars($email); ?> এর ব্যালেন্স আপডেট করতে চান?');" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[10px] px-2 py-1 rounded-lg transition" title="যোগ করুন">
                                            <i class="fa-solid fa-plus"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>

                            <td class="p-3.5 text-center">
                                <?php if ($status == 1): ?>
                                    <a href="pages/active_deactive.php?token=<?php echo urlencode($token); ?>&status=<?php echo $status; ?>" onclick="return confirm('ইউজারটিকে নিষ্ক্রিয় (Deactive) করতে চান?');" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10.5px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 transition no-underline">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> সক্রিয়
                                    </a>
                                <?php else: ?>
                                    <a href="pages/active_deactive.php?token=<?php echo urlencode($token); ?>&status=<?php echo $status; ?>" onclick="return confirm('ইউজারটিকে সক্রিয় (Active) করতে চান?');" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10.5px] font-bold bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100 transition no-underline">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> বন্ধ
                                    </a>
                                <?php endif; ?>
                            </td>

                            <td class="p-3.5 text-center">
                                <button type="button" onclick="changeUserPassword('<?php echo htmlspecialchars($token); ?>', '<?php echo htmlspecialchars($email); ?>')" class="bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 px-2.5 py-1 rounded-lg text-[11px] font-bold transition flex items-center justify-center gap-1 mx-auto">
                                    <i class="fa-solid fa-key text-[10px]"></i> পরিবর্তন
                                </button>
                            </td>

                            <td class="p-3.5 text-center">
                                <a href="pages/delete_user.php?token=<?php echo urlencode($token); ?>" onclick="return confirm('সতর্কতা: <?php echo htmlspecialchars($email); ?> অ্যাকাউন্টটি ডিলিট করতে চান?');" class="bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 w-8 h-8 rounded-lg flex items-center justify-center transition mx-auto" title="ইউজার মুছুন">
                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php include_once("footer.php"); ?>

<!-- DataTables Scripts & Styling -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

<style>
    .dataTables_wrapper .dataTables_length select {
        padding: 5px 25px 5px 10px;
        border-radius: 10px;
        border: 1px solid #E2E8F0;
        background-color: #F8FAFC;
        font-size: 11.5px;
        font-weight: 700;
        outline: none;
    }
    .dataTables_wrapper .dataTables_filter input {
        padding: 6px 14px;
        border-radius: 12px;
        border: 1px solid #CBD5E1;
        background-color: #F8FAFC;
        font-size: 11.5px;
        outline: none;
        transition: all 0.2s;
    }
    .dataTables_wrapper .dataTables_filter input:focus {
        border-color: #4F46E5;
        background-color: #FFFFFF;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
    }
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate {
        font-size: 11.5px;
        color: #64748B !important;
        margin-top: 14px;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        border-radius: 8px !important;
        padding: 4px 10px !important;
        font-size: 11px !important;
        font-weight: 700 !important;
        border: 1px solid #E2E8F0 !important;
        background: #FFFFFF !important;
        color: #334155 !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: #4F46E5 !important;
        color: #FFFFFF !important;
        border-color: #4F46E5 !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #EEF2FF !important;
        color: #4F46E5 !important;
        border-color: #C7D2FE !important;
    }
</style>

<script>
    $(document).ready(function () {
        $('#userTable').DataTable({
            "ordering": false,
            "pageLength": 10,
            "language": {
                "search": "খুঁজুন:",
                "lengthMenu": "প্রতি পেজে _MENU_ টি রেকর্ড",
                "info": "সর্বমোট _TOTAL_ টির মধ্যে _START_ থেকে _END_ দেখানো হচ্ছে",
                "infoEmpty": "কোনো রেকর্ড পাওয়া যায়নি",
                "zeroRecords": "খুঁজে পাওয়া যায়নি",
                "paginate": {
                    "first": "প্রথম",
                    "last": "শেষ",
                    "next": "পরবর্তী",
                    "previous": "পূর্ববর্তী"
                }
            }
        });
    });

    function copyLoginLink(url) {
        if (!url) return;
        navigator.clipboard.writeText(url).then(function() {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'লগইন লিংক কপি হয়েছে!',
                showConfirmButton: false,
                timer: 2000
            });
        }).catch(function(err) {
            Swal.fire('Error', 'কপি করা সম্ভব হয়নি', 'error');
        });
    }

    function changeUserPassword(token, email) {
        Swal.fire({
            title: 'পাসওয়ার্ড পরিবর্তন',
            input: 'text',
            inputLabel: email + ' এর জন্য নতুন পাসওয়ার্ড লিখুন',
            inputPlaceholder: 'নতুন পাসওয়ার্ড দিন',
            showCancelButton: true,
            confirmButtonText: 'পরিবর্তন করুন',
            cancelButtonText: 'বাতিল',
            confirmButtonColor: '#4f46e5',
            inputValidator: (value) => {
                if (!value || !value.trim()) {
                    return 'পাসওয়ার্ড ফিল্ড খালি রাখা যাবে না!';
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'pages/change_password_admin.php',
                    type: 'POST',
                    data: {
                        token: token,
                        new_password: result.value.trim()
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response && response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'সফল!',
                                text: 'পাসওয়ার্ড সফলভাবে পরিবর্তন করা হয়েছে।',
                                confirmButtonColor: '#4f46e5'
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'ব্যর্থ!',
                                text: (response && response.message) ? response.message : 'পাসওয়ার্ড পরিবর্তন ব্যর্থ হয়েছে।',
                                confirmButtonColor: '#e11d48'
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'ত্রুটি!',
                            text: 'সার্ভারের সাথে সংযোগ স্থাপন করা যায়নি।',
                            confirmButtonColor: '#e11d48'
                        });
                    }
                });
            }
        });
    }
</script>