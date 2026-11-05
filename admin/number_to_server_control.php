<?php
include_once("header.php");

$msg = "";
$err = "";

$conn->query("CREATE TABLE IF NOT EXISTS `number_to_server_services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `operator_key` varchar(50) NOT NULL UNIQUE,
  `operator_name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 90.00,
  `logo_url` text NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_number_server_settings'])) {
    $ids =$_POST['op_id'] ?? [];
    $names =$_POST['op_name'] ?? [];
    $prices =$_POST['op_price'] ?? [];
    $logos =$_POST['op_logo'] ?? [];
    $statuses =$_POST['op_status'] ?? [];

    for ($i = 0; $i < count($ids); $i++) {$oid = intval($ids[$i]);
        $oname = trim($names[$i]);$oprice = floatval($prices[$i]);
        $ologo = trim($logos[$i]);$ostatus = trim($statuses[$i]);

        $up =$conn->prepare("UPDATE number_to_server_services SET operator_name = ?, price = ?, logo_url = ?, status = ? WHERE id = ?");
        $up->bind_param("sdssi", $oname, $oprice,$ologo, $ostatus,$oid);
        $up->execute();$up->close();
    }
    $msg = "নাম্বার টু সার্ভার কপির অপারেটর সেটিংস ও রেট সফলভাবে আপডেট করা হয়েছে!";
}

$ops_list = [];
$res =$conn->query("SELECT * FROM number_to_server_services ORDER BY id ASC");
if ($res &&$res->num_rows > 0) {
    while ($r =$res->fetch_assoc()) {
        $ops_list[] =$r;
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
        max-width: 950px;
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
        font-weight: 600;
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
                    <span class="text-rose-600"><i class="fa-solid fa-sim-card"></i></span> নাম্বার টু সার্ভার কপি কন্ট্রোল
                </h3>
                <p class="text-xs text-slate-500 mt-1">গ্রামীণফোন, বাংলালিংক, রবি, এয়ারটেল ও টেলিটক এর মূল্য ও সক্রিয়তা পরিবর্তন করুন</p>
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
            <div class="space-y-4 mb-6">
                <?php foreach ($ops_list as$op): ?>
                    <div class="bg-slate-50 border border-slate-200/90 rounded-2xl p-4 flex flex-col md:flex-row md:items-center justify-between gap-3">
                        <input type="hidden" name="op_id[]" value="<?php echo $op['id']; ?>">
                        
                        <div class="w-12 h-12 flex-shrink-0 flex items-center justify-center bg-white border border-slate-200 rounded-xl p-1">
                            <img src="<?php echo htmlspecialchars($op['logo_url']); ?>" alt="Logo" class="max-w-full max-h-full object-contain">
                        </div>

                        <div class="flex-1">
                            <label class="block text-[11px] font-bold text-slate-500 mb-1">অপারেটর নাম</label>
                            <input type="text" name="op_name[]" value="<?php echo htmlspecialchars($op['operator_name']); ?>" class="custom-input bg-white" required>
                        </div>

                        <div class="w-full md:w-32">
                            <label class="block text-[11px] font-bold text-slate-500 mb-1">মূল্য (টাকা)</label>
                            <input type="number" step="1" name="op_price[]" value="<?php echo floatval($op['price']); ?>" class="custom-input bg-white font-black text-rose-600" required>
                        </div>

                        <div class="flex-1">
                            <label class="block text-[11px] font-bold text-slate-500 mb-1">লোগো URL</label>
                            <input type="text" name="op_logo[]" value="<?php echo htmlspecialchars($op['logo_url']); ?>" class="custom-input bg-white text-xs" required>
                        </div>

                        <div class="w-full md:w-32">
                            <label class="block text-[11px] font-bold text-slate-500 mb-1">স্ট্যাটাস</label>
                            <select name="op_status[]" class="custom-input bg-white font-bold <?php echo ($op['status'] === 'active') ? 'text-emerald-600' : 'text-rose-600'; ?>">
                                <option value="active" <?php echo ($op['status'] === 'active') ? 'selected' : ''; ?>>চালু (Active)</option>
                                <option value="inactive" <?php echo ($op['status'] === 'inactive') ? 'selected' : ''; ?>>বন্ধ (Off)</option>
                            </select>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <button type="submit" name="save_number_server_settings" class="btn-gradient w-full">
                <i class="fa-solid fa-floppy-disk"></i> সকল পরিবর্তন সংরক্ষণ করুন
            </button>
        </form>

    </div>

</div>

<?php include_once("footer.php"); ?>