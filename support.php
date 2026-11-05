<?php 
session_start();
include_once("includes/configuration.php");

$settings_data = [];
$q_set = $conn->query("SELECT setting_key, setting_value FROM site_settings");
if ($q_set) {
    while ($rs = $q_set->fetch_assoc()) {
        $settings_data[$rs['setting_key']] = $rs['setting_value'];
    }
}
$support_num = !empty($settings_data['support_whatsapp_num']) ? $settings_data['support_whatsapp_num'] : '01964706489';
$whatsapp_link = !empty($settings_data['whatsapp_group']) ? $settings_data['whatsapp_group'] : 'https://wa.me/88' . preg_replace('/[^0-9]/', '', $support_num);
$telegram_link = !empty($settings_data['telegram_link']) ? $settings_data['telegram_link'] : 'https://t.me/Sheba_kit';
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>কাস্টমার সার্ভিস ও হেল্প - সকল সেবা</title>
    <link href="https://fonts.googleapis.com/css2?family=Nikosh&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Nikosh', sans-serif; background: #F4F7F6; color: #1E293B; }
        .support-card { background: #fff; border-radius: 16px; border: 1px solid #E2E8F0; padding: 16px; transition: 0.2s; }
        .support-card:hover { border-color: #2E7D32; box-shadow: 0 6px 16px rgba(46,125,50,0.08); }
    </style>
</head>
<body class="pb-16">

    <header class="bg-white sticky top-0 z-50 border-b border-slate-200 px-4 py-3 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="dashboard.php" class="text-slate-700 text-lg"><i class="fa-solid fa-arrow-left"></i></a>
            <h1 class="text-base font-extrabold text-slate-800">কাস্টমার সার্ভিস</h1>
        </div>
    </header>

    <main class="max-w-xl mx-auto px-4 py-6 space-y-6">
        <div class="text-center space-y-2">
            <div class="w-16 h-16 bg-green-100 text-green-700 rounded-full flex items-center justify-center text-3xl mx-auto shadow-inner">
                <i class="fa-solid fa-headset"></i>
            </div>
            <h2 class="text-lg font-extrabold text-slate-900">কিভাবে আপনাকে সাহায্য করতে পারি?</h2>
            <p class="text-xs text-slate-500">যেকোনো সমস্যা বা সহায়তার জন্য নিচের মাধ্যমগুলোতে যোগাযোগ করুন।</p>
        </div>

        <div class="space-y-3">
            <a href="<?php echo htmlspecialchars($whatsapp_link); ?>" target="_blank" class="support-card flex items-center justify-between">
                <div class="flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl"><i class="fa-brands fa-whatsapp"></i></div>
                    <div>
                        <h4 class="text-sm font-extrabold text-slate-800">WhatsApp সাপোর্ট</h4>
                        <p class="text-[11px] text-slate-500"><?php echo htmlspecialchars($support_num); ?></p>
                    </div>
                </div>
                <i class="fa-solid fa-chevron-right text-xs text-slate-400"></i>
            </a>

            <a href="<?php echo htmlspecialchars($telegram_link); ?>" target="_blank" class="support-card flex items-center justify-between">
                <div class="flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-500 flex items-center justify-center text-xl"><i class="fa-brands fa-telegram"></i></div>
                    <div>
                        <h4 class="text-sm font-extrabold text-slate-800">Telegram চ্যানেল ও গ্রুপ</h4>
                        <p class="text-[11px] text-slate-500">আপডেট ও নোটিশ পেতে যুক্ত হোন</p>
                    </div>
                </div>
                <i class="fa-solid fa-chevron-right text-xs text-slate-400"></i>
            </a>

            <div onclick="Swal.fire('লাইভ চ্যাট', 'আমাদের লাইভ চ্যাট সাপোর্ট টিম বর্তমানে WhatsApp এ সক্রিয় রয়েছে।', 'info')" class="support-card flex items-center justify-between cursor-pointer">
                <div class="flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-500 flex items-center justify-center text-xl"><i class="fa-solid fa-comments"></i></div>
                    <div>
                        <h4 class="text-sm font-extrabold text-slate-800">চ্যাট উইথ শিবা / লাইভ চ্যাট</h4>
                        <p class="text-[11px] text-slate-500">সাহায্য নিন দ্রুত ও সহজে</p>
                    </div>
                </div>
                <i class="fa-solid fa-chevron-right text-xs text-slate-400"></i>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>