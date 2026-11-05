<?php
include_once("header.php");

$msg = "";
$err = "";

$conn->query("CREATE TABLE IF NOT EXISTS `location_services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_key` varchar(50) NOT NULL UNIQUE,
  `service_name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 300.00,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `icon_bg` varchar(30) NOT NULL DEFAULT 'bg-pink-100',
  `icon_color` varchar(30) NOT NULL DEFAULT 'text-pink-600',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_location_settings'])) {
    $service_ids = $_POST['service_id'] ?? [];
    $service_names = $_POST['service_name'] ?? [];
    $prices = $_POST['price'] ?? [];
    $statuses = $_POST['status'] ?? [];

    $success_count = 0;
    for ($i = 0; $i < count($service_ids); $i++) {
        $sid = intval($service_ids[$i]);
        $sname = trim($service_names[$i]);
        $sprice = floatval($prices[$i]);
        $sstatus = trim($statuses[$i]);

        $up_stmt = $conn->prepare("UPDATE location_services SET service_name = ?, price = ?, status = ? WHERE id = ?");
        $up_stmt->bind_param("sdsi", $sname, $sprice, $sstatus, $sid);
        if ($up_stmt->execute()) {
            $success_count++;
        }
        $up_stmt->close();
    }
    $msg = "লোকেশন ট্র্যাকিং সার্ভিসের মূল্য ও সেটিংস সফলভাবে আপডেট করা হয়েছে!";
}

$loc_services = [];
$res = $conn->query("SELECT * FROM location_services ORDER BY id ASC");
if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        $loc_services[] = $row;
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
                    <span class="text-indigo-600"><i class="fa-solid fa-satellite-dish"></i></span> লোকেশন ট্র্যাকিং সার্ভিস কন্ট্রোল
                </h3>
                <p class="text-xs text-slate-500 mt-1">ব্যবহারকারীর পেজে প্রদর্শিত প্যাকেজগুলোর নাম, মূল্য ও সক্রিয়তা পরিবর্তন করুন</p>
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
                <?php foreach ($loc_services as $srv): ?>
                    <div class="bg-slate-50 border border-slate-200/90 rounded-2xl p-3.5 flex flex-col md:flex-row md:items-center justify-between gap-3">
                        <input type="hidden" name="service_id[]" value="<?php echo $srv['id']; ?>">
                        
                        <div class="flex-1">
                            <label class="block text-[11px] font-bold text-slate-500 mb-1">প্যাকেজের নাম</label>
                            <input type="text" name="service_name[]" value="<?php echo htmlspecialchars($srv['service_name']); ?>" class="custom-input bg-white" required>
                        </div>

                        <div class="w-full md:w-36">
                            <label class="block text-[11px] font-bold text-slate-500 mb-1">মূল্য (টাকা)</label>
                            <input type="number" step="1" name="price[]" value="<?php echo floatval($srv['price']); ?>" class="custom-input bg-white font-black text-slate-800" required>
                        </div>

                        <div class="w-full md:w-36">
                            <label class="block text-[11px] font-bold text-slate-500 mb-1">স্ট্যাটাস</label>
                            <select name="status[]" class="custom-input bg-white font-bold <?php echo ($srv['status'] === 'active') ? 'text-emerald-600' : 'text-rose-600'; ?>">
                                <option value="active" <?php echo ($srv['status'] === 'active') ? 'selected' : ''; ?>>চালু (Active)</option>
                                <option value="inactive" <?php echo ($srv['status'] === 'inactive') ? 'selected' : ''; ?>>বন্ধ (Off)</option>
                            </select>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <button type="submit" name="save_location_settings" class="btn-gradient w-full">
                <i class="fa-solid fa-floppy-disk"></i> সকল পরিবর্তন সংরক্ষণ করুন
            </button>
        </form>

    </div>

</div>

<?php include_once("footer.php"); ?>