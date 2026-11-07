<?php 
include_once("header.php");
require_once __DIR__ . '/../includes/service_catalog.php';

$save_msg = "";
$save_error = "";

$categories_list = [];
$cat_q = $conn->query("SELECT DISTINCT category FROM custom_services WHERE category IS NOT NULL AND category != '' ORDER BY category ASC");
if ($cat_q && $cat_q->num_rows > 0) {
    while ($c = $cat_q->fetch_assoc()) {
        $categories_list[] = $c['category'];
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_service'])) {
    $service_name = trim($_POST['service_name'] ?? '');
    $category = trim($_POST['category'] ?? 'সাধারণ সার্ভিস');
    $price = floatval($_POST['price'] ?? 0);
    $service_type = trim($_POST['service_type'] ?? 'custom');
    $status = trim($_POST['status'] ?? 'active');
    $is_pinned = isset($_POST['is_pinned']) ? 1 : 0;
    $description = trim($_POST['description'] ?? '');
    $access_link = trim($_POST['access_link'] ?? '');

    $fields_array = soko_admin_form_fields($_POST);
    $custom_fields_json = json_encode($fields_array, JSON_UNESCAPED_UNICODE);

    $service_image_path = null;
    if (isset($_FILES['service_image']) && $_FILES['service_image']['error'] === 0) {
        $upDir = "uploads/services/";
        if (!is_dir($upDir)) {
            mkdir($upDir, 0755, true);
        }
        $ext = strtolower(pathinfo($_FILES["service_image"]["name"], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'svg'];
        if (in_array($ext, $allowed)) {
            $newFilename = "srv_" . time() . "_" . rand(100, 999) . "." . $ext;
            $targetPath = $upDir . $newFilename;
            if (move_uploaded_file($_FILES["service_image"]["tmp_name"], $targetPath)) {
                $service_image_path = $targetPath;
            }
        }
    }

    if (empty($service_name)) {
        $save_error = "সার্ভিসের নাম প্রদান করা আবশ্যক।";
    } else {
        $stmt = $conn->prepare("INSERT INTO custom_services (service_name, category, price, service_type, status, is_pinned, description, access_link, service_image, custom_fields) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdssissss", $service_name, $category, $price, $service_type, $status, $is_pinned, $description, $access_link, $service_image_path, $custom_fields_json);
        
        if ($stmt->execute()) {
            $save_msg = "সার্ভিসটি সফলভাবে ডাটাবেজে যুক্ত করা হয়েছে!";
        } else {
            $save_error = "ডাটাবেজে সেভ করতে সমস্যা হয়েছে।";
        }
        $stmt->close();
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
    .service-box {
        max-width: 820px;
        margin: 0 auto 30px;
        background: #ffffff;
        border-radius: 24px;
        border: 1px solid rgba(226, 232, 240, 0.9);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
        padding: 30px 24px;
    }
    .form-control-custom {
        width: 100%;
        border-radius: 14px;
        border: 1.5px solid #cbd5e1;
        padding: 11px 16px;
        font-size: 14px;
        outline: none;
        transition: all 0.2s ease;
    }
    .form-control-custom:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
    }
    .radio-card {
        border: 2px solid #e2e8f0;
        border-radius: 14px;
        padding: 12px 14px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: all 0.2s;
    }
    .dynamic-field-row {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 12px;
        margin-bottom: 10px;
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
        align-items: center;
    }
    .dynamic-field-row > input, .dynamic-field-row > select { min-width:0; }
    @media(max-width:600px) { .dynamic-field-row { grid-template-columns:minmax(0,1fr); } }
    .btn-gradient {
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
    .btn-gradient:hover {
        transform: translateY(-2px);
    }
</style>

<div class="container-fluid px-3 px-md-4 py-4">

    <div class="service-box">
        
        <div class="flex items-center justify-between border-b pb-4 mb-5">
            <div>
                <h3 class="text-xl font-extrabold text-slate-800 flex items-center gap-2">
                    <span class="text-indigo-600">🎯</span> সার্ভিস ম্যানেজমেন্ট
                </h3>
                <p class="text-xs text-slate-500 mt-1">➕ নতুন Service যোগ করুন</p>
            </div>
            <a href="services_list.php" class="text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 px-3.5 py-2 rounded-xl font-bold flex items-center gap-1.5 transition">
                <i class="fa-solid fa-list"></i> সকল সার্ভিস
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

        <form action="" method="POST" enctype="multipart/form-data">
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">নাম <span class="text-rose-500">*</span></label>
                    <input type="text" name="service_name" class="form-control-custom" placeholder="যেমন: NID কার্ড ডাউনলোড" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">ক্যাটাগরি <span class="text-rose-500">*</span></label>
                    <input type="text" name="category" list="categoryOptions" class="form-control-custom" placeholder="সিলেক্ট করুন বা নতুন লিখুন" required>
                    <datalist id="categoryOptions">
                        <?php foreach($categories_list as $cat_item): ?>
                            <option value="<?php echo htmlspecialchars($cat_item); ?>">
                        <?php endforeach; ?>
                        <option value="সাইন কপি">
                        <option value="কল লিস্ট সার্ভিস">
                        <option value="সার্ভারকপি সংক্রান্ত সার্ভিস">
                        <option value="( Location ) লোকেশন ট্র্যাকিং সার্ভিস">
                        <option value="বিকাশ / নগদ / রকেট/ সার্ভিস">
                        <option value="সাধারণ সার্ভিস">
                    </datalist>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">মূল্য (ডিফল্ট) <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.01" name="price" class="form-control-custom font-bold" placeholder="0.00" required>
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-xs font-bold text-slate-700 mb-1.5">ধরন</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <label class="radio-card">
                        <input type="radio" name="service_type" value="custom" checked class="accent-indigo-600">
                        <span class="text-xs">কাস্টম সার্ভিস</span>
                    </label>
                    <label class="radio-card">
                        <input type="radio" name="service_type" value="api" class="accent-indigo-600">
                        <span class="text-xs">API সার্ভিস (Auto Server Copy)</span>
                    </label>
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-xs font-bold text-slate-700 mb-1.5">স্ট্যাটাস</label>
                <div class="grid grid-cols-3 gap-3">
                    <label class="radio-card justify-center">
                        <input type="radio" name="status" value="active" checked class="accent-emerald-600">
                        <span class="text-xs font-bold text-emerald-700">চালু</span>
                    </label>
                    <label class="radio-card justify-center">
                        <input type="radio" name="status" value="inactive" class="accent-rose-600">
                        <span class="text-xs font-bold text-rose-700">বন্ধ</span>
                    </label>
                    <label class="radio-card justify-center">
                        <input type="radio" name="status" value="hidden" class="accent-slate-600">
                        <span class="text-xs font-bold text-slate-600">হাইড</span>
                    </label>
                </div>
            </div>

            <div class="mb-4 bg-slate-50 border border-slate-200 p-3.5 rounded-2xl flex items-center justify-between">
                <div>
                    <h6 class="text-xs font-bold text-slate-800">পিন করুন</h6>
                    <p class="text-[11px] text-slate-500">হোমপেজের শীর্ষে আলাদা গুরুত্ব দিয়ে প্রদর্শন করুন</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="is_pinned" value="1" class="sr-only peer">
                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                </label>
            </div>

            <div class="mb-4">
                <label class="block text-xs font-bold text-slate-700 mb-1.5">বর্ণনা</label>
                <textarea name="description" rows="3" class="form-control-custom" placeholder="সার্ভিস সম্পর্কে সংক্ষিপ্ত বিবরণ..."></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">অটো এক্সেস লিংক / টেক্সট (ঐচ্ছিক)</label>
                    <input type="text" name="access_link" class="form-control-custom" placeholder="https://... অথবা স্পেশাল ইনস্ট্রাকশন">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">সার্ভিস ছবি (ঐচ্ছিক)</label>
                    <input type="file" name="service_image" class="form-control-custom text-xs" accept="image/*">
                </div>
            </div>

            <div class="bg-indigo-50/50 border border-indigo-100 rounded-2xl p-4 mb-6">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h6 class="text-xs font-extrabold text-indigo-950 flex items-center gap-1.5">
                            <i class="fa-solid fa-file-pen text-indigo-600"></i> 📝 ফর্ম ফিল্ড (কাস্টম সার্ভিসের জন্য)
                        </h6>
                        <p class="text-[11px] text-slate-500">এখানের ফিল্ড ব্যবহারকারীর অর্ডার ফর্মে দেখাবে। খালি থাকলে সেবার ডিফল্ট ফিল্ড দেখাবে। NID আবেদনে জন্ম নিবন্ধন PDF আবশ্যক। আলাদা API/অটো পেজের নিজস্ব ফর্ম অপরিবর্তিত থাকবে; এই ফর্ম ব্যবহার করতে অটো এক্সেস লিংক ফাঁকা রাখুন।</p>
                    </div>
                    <button type="button" onclick="addFormField()" class="text-xs bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-3 py-1.5 rounded-xl shadow-sm transition">
                        + ফিল্ড যোগ করুন
                    </button>
                </div>

                <div id="dynamicFieldsContainer"></div>
            </div>

            <button type="submit" name="save_service" class="btn-gradient w-full">
                <i class="fa-solid fa-floppy-disk"></i> সার্ভিস সংরক্ষণ করুন
            </button>
        </form>
    </div>

</div>

<script>
let fieldCount = 0;

function addFormField() {
    fieldCount++;
    const row = document.createElement('div');
    row.className = 'dynamic-field-row';
    row.id = 'fieldRow_' + fieldCount;
    row.innerHTML = `
        <input type="text" name="field_title[${fieldCount}]" placeholder="ফিল্ডের শিরোনাম (যেমন: জন্মতারিখ / NID)" class="form-control-custom" required>
        <select name="field_type[${fieldCount}]" class="form-control-custom">
            <option value="text">টেক্সট ইনপুট (Text)</option>
            <option value="date">জন্মতারিখ (Date)</option>
            <option value="number">নাম্বার (Number)</option>
            <option value="tel">মোবাইল নম্বর (Tel)</option>
            <option value="file">ফাইল আপলোড (File)</option>
            <option value="textarea">বড় বিবরণ (Textarea)</option>
        </select>
        <input type="text" name="field_placeholder[${fieldCount}]" placeholder="ইনপুটের নির্দেশনা / Placeholder" class="form-control-custom">
        <input type="text" name="field_accept[${fieldCount}]" placeholder="ফাইল: .pdf অথবা image/*,.pdf" class="form-control-custom">
        <label class="flex items-center gap-1 text-xs text-slate-600 cursor-pointer select-none">
            <input type="checkbox" name="field_required[${fieldCount}]" value="1" checked class="accent-indigo-600">
            <span>বাধ্যতামূলক</span>
        </label>
        <button type="button" onclick="removeFormField('fieldRow_${fieldCount}')" class="text-rose-500 hover:text-rose-700 p-1">
            <i class="fa-solid fa-trash-can"></i>
        </button>
    `;
    document.getElementById('dynamicFieldsContainer').appendChild(row);
}

function removeFormField(id) {
    const el = document.getElementById(id);
    if (el) el.remove();
}
</script>

<?php include_once("footer.php"); ?>