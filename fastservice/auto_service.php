<?php 
session_start();
include_once("includes/configuration.php");

$srv = isset($_GET['type']) ? trim($_GET['type']) : 'general';
$service_titles = [
    'nid'      => 'AUTO NID সার্ভিস',
    'password' => 'AUTO USER PASSWORD সার্ভিস',
    'ministry' => 'AUTO MINISTRY সার্ভিস',
    'general'  => 'অটো স্পেশাল সার্ভিস'
];
$title = $service_titles[$srv] ?? 'অটো সার্ভিস';
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?> - FAST SERVICE</title>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Hind Siliguri', 'Plus Jakarta Sans', sans-serif;
            background: radial-gradient(circle at top, #FDF4FF 0%, #F8FAFC 100%);
            min-height: 100vh;
        }
    </style>
</head>
<body class="flex items-center justify-center p-4">

    <div class="max-w-md w-full bg-white rounded-3xl p-7 text-center border border-slate-200/90 shadow-xl relative overflow-hidden">
        <div class="absolute -top-12 -right-12 w-32 h-32 bg-purple-50 rounded-full blur-2xl"></div>
        <div class="absolute -bottom-12 -left-12 w-32 h-32 bg-pink-50 rounded-full blur-2xl"></div>

        <div class="w-20 h-20 mx-auto rounded-3xl bg-purple-50 border border-purple-100 flex items-center justify-center text-purple-600 text-3xl mb-5 shadow-sm">
            <i class="fa-solid fa-gears animate-spin" style="animation-duration: 8s;"></i>
        </div>

        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-purple-50 text-purple-700 border border-purple-200 mb-3">
            <span class="w-2 h-2 rounded-full bg-purple-500 animate-ping"></span> এপিআই মেইনটেন্যান্স
        </span>

        <h3 class="text-xl font-extrabold text-slate-800 mb-2">
            <?php echo htmlspecialchars($title); ?>
        </h3>

        <p class="text-xs text-slate-500 leading-relaxed mb-6">
            অটো বট সার্ভার ইন্টিগ্রেশন এবং ব্যাকএন্ড সার্ভিস রক্ষণাবেক্ষণ চলছে। দ্রুত ও নিরবচ্ছিন্ন ডেটা ডেলিভারির জন্য সার্ভিসটি সাময়িকভাবে হোল্ড করা হয়েছে। শীঘ্রই এটি লাইভ হবে।
        </p>

        <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-4 mb-6 text-left space-y-2">
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-700">
                <i class="fa-solid fa-robot text-purple-600 text-sm"></i> অটোমেটেড ইন্টিগ্রেশন চলমান
            </div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-700">
                <i class="fa-solid fa-shield-halved text-emerald-500 text-sm"></i> নিরাপদ ও সুরক্ষিত ডেটা ডেলিভারি
            </div>
        </div>

        <a href="dashboard.php" class="w-full inline-flex items-center justify-center gap-2 bg-purple-600 hover:bg-purple-700 text-white font-bold py-3.5 px-6 rounded-2xl shadow-lg shadow-purple-600/25 transition">
            <i class="fa-solid fa-arrow-left"></i> ড্যাশবোর্ডে ফিরে যান
        </a>
    </div>

</body>
</html>