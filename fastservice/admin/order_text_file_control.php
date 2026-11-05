<?php
include_once("header.php");

$msg = "";
$err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['save_text_services'])) {
        $ids = $_POST['t_id'] ?? [];
        $prices = $_POST['t_price'] ?? [];
        $statuses = $_POST['t_status'] ?? [];

        for ($i = 0; $i < count($ids); $i++) {
            $sid = intval($ids[$i]);
            $sprc = floatval($prices[$i]);
            $sst = trim($statuses[$i]);

            $up = $conn->prepare("UPDATE order_text_services SET price = ?, status = ? WHERE id = ?");
            $up->bind_param("dsi", $sprc, $sst, $sid);
            $up->execute();
            $up->close();
        }
        $msg = "অর্ডার টেক্সট সার্ভিসের মূল্য ও সক্রিয়তা সফলভাবে আপডেট করা হয়েছে!";
    } elseif (isset($_POST['save_file_services'])) {
        $ids = $_POST['f_id'] ?? [];
        $prices = $_POST['f_price'] ?? [];
        $statuses = $_POST['f_status'] ?? [];

        for ($i = 0; $i < count($ids); $i++) {
            $sid = intval($ids[$i]);
            $sprc = floatval($prices[$i]);
            $sst = trim($statuses[$i]);

            $up = $conn->prepare("UPDATE order_file_services SET price = ?, status = ? WHERE id = ?");
            $up->bind_param("dsi", $sprc, $sst, $sid);
            $up->execute();
            $up->close();
        }
        $msg = "অর্ডার ফাইল সার্ভিসের মূল্য ও সক্রিয়তা সফলভাবে আপডেট করা হয়েছে!";
    }
}

$text_list = [];
$res_t = $conn->query("SELECT * FROM order_text_services ORDER BY id ASC");
if ($res_t && $res_t->num_rows > 0) {
    while ($r = $res_t->fetch_assoc()) {
        $text_list[] = $r;
    }
}

$file_list = [];
$res_f = $conn->query("SELECT * FROM order_file_services ORDER BY id ASC");
if ($res_f && $res_f->num_rows > 0) {
    while ($r = $res_f->fetch_assoc()) {
        $file_list[] = $r;
    }
}
?>

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<script src="https://cdn.tailwindcss.com"></script>

<div class="container-fluid px-3 px-md-4 py-4">

    <div class="max-w-5xl mx-auto">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-xl font-extrabold text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-sliders text-indigo-600"></i> টেক্সট ও ফাইল সার্ভিস কন্ট্রোল
                </h3>
                <p class="text-xs text-slate-500 mt-1">টেক্সট এবং ফাইল সার্ভিসের প্রতিটি আইটেমের রেট ও চালু/বন্ধ স্ট্যাটাস নিয়ন্ত্রণ করুন</p>
            </div>
            <a href="dashboard.php" class="text-xs font-bold bg-white border px-3.5 py-2 rounded-xl text-slate-700 hover:bg-slate-50 shadow-sm">
                <i class="fa-solid fa-arrow-left"></i> ড্যাশবোর্ড
            </a>
        </div>

        <?php if (!empty($msg)): ?>
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-2xl mb-4 text-xs font-bold">
                <i class="fa-solid fa-circle-check text-emerald-600"></i> <?php echo $msg; ?>
            </div>
        <?php endif; ?>

        <div class="flex gap-2 mb-4 border-b pb-2">
            <button onclick="toggleTab('textTab')" id="btnTextTab" class="px-4 py-2 rounded-xl font-bold text-xs bg-indigo-600 text-white shadow">
                অর্ডার টেক্সট সার্ভিস (<?php echo count($text_list); ?>)
            </button>
            <button onclick="toggleTab('fileTab')" id="btnFileTab" class="px-4 py-2 rounded-xl font-bold text-xs bg-white text-slate-700 border hover:bg-slate-50">
                অর্ডার ফাইল সার্ভিস (<?php echo count($file_list); ?>)
            </button>
        </div>

        <!-- টেক্সট ফর্ম -->
        <div id="textTab" class="bg-white rounded-3xl p-6 border shadow-sm mb-6">
            <form action="" method="POST">
                <div class="space-y-3 mb-6 max-h-[550px] overflow-y-auto pr-2">
                    <?php foreach ($text_list as $t): ?>
                    <div class="bg-slate-50 p-3 rounded-2xl border flex items-center justify-between gap-3">
                        <input type="hidden" name="t_id[]" value="<?php echo $t['id']; ?>">
                        <div class="flex-1">
                            <span class="text-xs font-bold text-slate-800 block"><?php echo htmlspecialchars($t['service_title']); ?></span>
                            <span class="text-[11px] text-slate-400"><?php echo htmlspecialchars($t['placeholder']); ?></span>
                        </div>
                        <div class="w-32">
                            <label class="text-[10px] font-bold text-slate-500 block">মূল্য (৳)</label>
                            <input type="number" step="1" name="t_price[]" value="<?php echo floatval($t['price']); ?>" class="w-full bg-white border rounded-xl px-3 py-1.5 text-xs font-bold" required>
                        </div>
                        <div class="w-32">
                            <label class="text-[10px] font-bold text-slate-500 block">স্ট্যাটাস</label>
                            <select name="t_status[]" class="w-full bg-white border rounded-xl px-3 py-1.5 text-xs font-bold <?php echo ($t['status'] === 'active') ? 'text-emerald-600' : 'text-rose-600'; ?>">
                                <option value="active" <?php echo ($t['status'] === 'active') ? 'selected' : ''; ?>>চালু</option>
                                <option value="inactive" <?php echo ($t['status'] === 'inactive') ? 'selected' : ''; ?>>বন্ধ</option>
                            </select>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <button type="submit" name="save_text_services" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-2xl shadow transition">
                    টেক্সট সার্ভিসের রেট সংরক্ষণ করুন
                </button>
            </form>
        </div>

        <div id="fileTab" class="bg-white rounded-3xl p-6 border shadow-sm mb-6 hidden">
            <form action="" method="POST">
                <div class="space-y-3 mb-6 max-h-[550px] overflow-y-auto pr-2">
                    <?php foreach ($file_list as $f): ?>
                    <div class="bg-slate-50 p-3 rounded-2xl border flex items-center justify-between gap-3">
                        <input type="hidden" name="f_id[]" value="<?php echo $f['id']; ?>">
                        <div class="flex-1">
                            <span class="text-xs font-bold text-slate-800 block"><?php echo htmlspecialchars($f['service_title']); ?></span>
                            <span class="text-[11px] text-slate-400"><?php echo htmlspecialchars($f['placeholder']); ?></span>
                        </div>
                        <div class="w-32">
                            <label class="text-[10px] font-bold text-slate-500 block">মূল্য (৳)</label>
                            <input type="number" step="1" name="f_price[]" value="<?php echo floatval($f['price']); ?>" class="w-full bg-white border rounded-xl px-3 py-1.5 text-xs font-bold text-emerald-600" required>
                        </div>
                        <div class="w-32">
                            <label class="text-[10px] font-bold text-slate-500 block">স্ট্যাটাস</label>
                            <select name="f_status[]" class="w-full bg-white border rounded-xl px-3 py-1.5 text-xs font-bold <?php echo ($f['status'] === 'active') ? 'text-emerald-600' : 'text-rose-600'; ?>">
                                <option value="active" <?php echo ($f['status'] === 'active') ? 'selected' : ''; ?>>চালু</option>
                                <option value="inactive" <?php echo ($f['status'] === 'inactive') ? 'selected' : ''; ?>>বন্ধ</option>
                            </select>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <button type="submit" name="save_file_services" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 rounded-2xl shadow transition">
                    ফাইল সার্ভিসের রেট সংরক্ষণ করুন
                </button>
            </form>
        </div>

    </div>

</div>

<script>
    function toggleTab(tabId) {
        if (tabId === 'textTab') {
            $('#textTab').removeClass('hidden');
            $('#fileTab').addClass('hidden');
            $('#btnTextTab').removeClass('bg-white text-slate-700 border').addClass('bg-indigo-600 text-white shadow');
            $('#btnFileTab').removeClass('bg-emerald-600 text-white shadow').addClass('bg-white text-slate-700 border');
        } else {
            $('#fileTab').removeClass('hidden');
            $('#textTab').addClass('hidden');
            $('#btnFileTab').removeClass('bg-white text-slate-700 border').addClass('bg-emerald-600 text-white shadow');
            $('#btnTextTab').removeClass('bg-indigo-600 text-white shadow').addClass('bg-white text-slate-700 border');
        }
    }
</script>

<?php include_once("footer.php"); ?>