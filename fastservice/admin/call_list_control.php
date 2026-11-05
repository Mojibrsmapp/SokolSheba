<?php
include_once("header.php");

$msg = "";
$err = "";

$conn->query("CREATE TABLE IF NOT EXISTS `call_history_packages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_category` varchar(50) NOT NULL,
  `duration_key` varchar(50) NOT NULL,
  `duration_title` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 1150.00,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `cat_dur` (`service_category`, `duration_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_call_packages'])) {
    $ids =$_POST['pkg_id'] ?? [];
    $prices =$_POST['pkg_price'] ?? [];
    $statuses =$_POST['pkg_status'] ?? [];

    for ($i = 0; $i < count($ids); $i++) {$pid = intval($ids[$i]);
        $pprice = floatval($prices[$i]);$pstatus = trim($statuses[$i]);

        $up =$conn->prepare("UPDATE call_history_packages SET price = ?, status = ? WHERE id = ?");
        $up->bind_param("dsi", $pprice, $pstatus,$pid);
        $up->execute();$up->close();
    }
    $msg = "কল ও মেসেজ হিস্ট্রি সার্ভিসের সকল রেট সফলভাবে আপডেট করা হয়েছে!";
}

$pkgs = [];
$res =$conn->query("SELECT * FROM call_history_packages ORDER BY service_category ASC, id ASC");
if ($res &&$res->num_rows > 0) {
    while ($r =$res->fetch_assoc()) {
        $pkgs[] =$r;
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
    .panel-card {
        max-width: 900px;
        margin: 0 auto 30px;
        background: #ffffff;
        border-radius: 22px;
        border: 1px solid rgba(226, 232, 240, 0.9);
        box-shadow: 0 6px 22px rgba(0, 0, 0, 0.03);
        padding: 26px 22px;
    }
    .custom-input {
        width: 100%;
        border-radius: 12px;
        border: 1.5px solid #cbd5e1;
        padding: 9px 14px;
        font-size: 13.5px;
        font-weight: 700;
        outline: none;
        transition: all 0.2s ease;
    }
    .custom-input:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
    }
    .btn-gradient {
        background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
        color: #ffffff;
        font-weight: 700;
        padding: 13px 22px;
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
    .btn-gradient:hover {
        transform: translateY(-2px);
    }
</style>

<div class="container-fluid px-3 px-md-4 py-4">

    <div class="panel-card">
        
        <div class="flex items-center justify-between border-b pb-4 mb-5">
            <div>
                <h3 class="text-xl font-extrabold text-slate-800 flex items-center gap-2">
                    <span class="text-rose-600"><i class="fa-solid fa-phone-volume"></i></span> কল ও মেসেজ লিস্ট রেট কন্ট্রোল
                </h3>
                <p class="text-xs text-slate-500 mt-1">কল লিস্ট, মেসেজ লিস্ট ও কম্বো প্যাকেজের মূল্য নির্ধারণ করুন</p>
            </div>
            <a href="dashboard.php" class="text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 px-3.5 py-2 rounded-xl font-bold flex items-center gap-1.5 transition">
                <i class="fa-solid fa-arrow-left"></i> ড্যাশবোর্ড
            </a>
        </div>

        <?php if(!empty($msg)): ?>
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-2xl mb-4 text-xs font-bold flex items-center gap-2">
                <i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i> <?php echo $msg; ?>
            </div>
        <?php elseif(!empty($err)): ?>
            <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-2xl mb-4 text-xs font-bold flex items-center gap-2">
                <i class="fa-solid fa-circle-exclamation text-rose-600 text-sm"></i> <?php echo $err; ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="space-y-3 mb-6">
                <?php 
                $cat_bangla = ['call' => 'কল লিস্ট', 'sms' => 'মেসেজ লিস্ট', 'both' => 'কল + মেসেজ'];
                foreach ($pkgs as$p): 
                ?>
                    <div class="bg-slate-50 border border-slate-200/90 rounded-2xl p-3.5 flex flex-col md:flex-row md:items-center justify-between gap-3">
                        <input type="hidden" name="pkg_id[]" value="<?php echo $p['id']; ?>">
                        
                        <div class="flex-1">
                            <span class="text-xs font-black text-slate-800 block">
                                <?php echo ($cat_bangla[$p['service_category']] ?? $p['service_category']); ?> — (<?php echo htmlspecialchars($p['duration_title']); ?>)
                            </span>
                            <span class="text-[11px] text-slate-400">ক্যাটাগরি: <?php echo htmlspecialchars($p['service_category']); ?></span>
                        </div>

                        <div class="w-full md:w-44">
                            <label class="block text-[11px] font-bold text-slate-500 mb-1">মূল্য (টাকা)</label>
                            <input type="number" step="1" name="pkg_price[]" value="<?php echo floatval($p['price']); ?>" class="custom-input bg-white text-rose-600" required>
                        </div>

                        <div class="w-full md:w-36">
                            <label class="block text-[11px] font-bold text-slate-500 mb-1">স্ট্যাটাস</label>
                            <select name="pkg_status[]" class="custom-input bg-white font-bold <?php echo ($p['status'] === 'active') ? 'text-emerald-600' : 'text-rose-600'; ?>">
                                <option value="active" <?php echo ($p['status'] === 'active') ? 'selected' : ''; ?>>চালু (Active)</option>
                                <option value="inactive" <?php echo ($p['status'] === 'inactive') ? 'selected' : ''; ?>>বন্ধ (Off)</option>
                            </select>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <button type="submit" name="save_call_packages" class="btn-gradient w-full">
                <i class="fa-solid fa-floppy-disk"></i> পরিবর্তন সংরক্ষণ করুন
            </button>
        </form>

    </div>

</div>

<?php include_once("footer.php"); ?>