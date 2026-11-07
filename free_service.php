<?php 
session_start();
include_once("includes/configuration.php");

$srv = isset($_GET['type']) ? trim($_GET['type']) : 'general';
$service_titles = [
    'biometric'   => 'ফ্রি বায়োমেট্রিক চেক',
    'server_copy' => 'ফ্রি সার্ভার কপি',
    'make'        => 'ফ্রি মেক সার্ভিস',
    'general'     => 'ফ্রি স্পেশাল সার্ভিস'
];
$title = $service_titles[$srv] ?? 'ফ্রি সার্ভিস';
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?> - Sokol Kaj</title>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Hind Siliguri', 'Plus Jakarta Sans', sans-serif;
            background: radial-gradient(circle at top, #EFF6FF 0%, #F8FAFC 100%);
            min-height: 100vh;
        }
    </style>
</head>
<body class="">
<?php include __DIR__ . '/includes/site_header.php'; ?>

    <div class="max-w-md w-full mx-auto my-6 bg-white rounded-3xl p-7 text-center border border-slate-200/90 shadow-xl relative overflow-hidden">
        <div class="absolute -top-12 -right-12 w-32 h-32 bg-indigo-50 rounded-full blur-2xl"></div>
        <div class="absolute -bottom-12 -left-12 w-32 h-32 bg-emerald-50 rounded-full blur-2xl"></div>

        <div class="w-20 h-20 mx-auto rounded-3xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 text-3xl mb-5 shadow-sm">
            <i class="fa-solid fa-gift animate-bounce"></i>
        </div>

        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 mb-3">
            <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span> আপডেট চলমান
        </span>

        <h3 class="text-xl font-extrabold text-slate-800 mb-2">
            <?php echo htmlspecialchars($title); ?>
        </h3>

        <p class="text-xs text-slate-500 leading-relaxed mb-6">
            আমাদের এই ফ্রি সার্ভিসটির সিস্টেম আপগ্রেড ও টেস্টিং কার্যক্রম চলমান রয়েছে। খুব শীঘ্রই সম্পূর্ণ বিনামূল্যে এই সুবিধাটি সকল সম্মানিত ইউজারদের জন্য উন্মুক্ত করা হবে। অনুগ্রহ করে সাথেই থাকুন!
        </p>

        <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-4 mb-6 text-left space-y-2">
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-700">
                <i class="fa-solid fa-circle-check text-emerald-500 text-sm"></i> ১০০% ফ্রি এক্সেস শীঘ্রই চালু হবে
            </div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-700">
                <i class="fa-solid fa-bolt text-amber-500 text-sm"></i> দ্রুততম ডেটা সার্ভার কনফিগারেশন চলছে
            </div>
        </div>

        <a href="dashboard.php" class="w-full inline-flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3.5 px-6 rounded-2xl shadow-lg shadow-indigo-600/25 transition">
            <i class="fa-solid fa-arrow-left"></i> ড্যাশবোর্ডে ফিরে যান
        </a>
    </div>

<?php include __DIR__ . '/includes/site_footer.php'; ?>
</body>
</html>