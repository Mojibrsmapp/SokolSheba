<?php
/** Shared branding. Missing table/keys keep existing installations working. */
function soko_brand_fields() {
    return [
        'site_name' => ['সাইটের নাম', 'সকল সেবা'],
        'site_tagline' => ['স্লোগান', 'সব কাজ, এক প্ল্যাটফর্মে'],
        'site_logo' => ['মূল লোগো', '/assets/logo.jpg'],
        'nav_logo' => ['নেভিগেশন লোগো (খালি থাকলে মূল লোগো)', ''],
        'site_favicon' => ['Favicon (খালি থাকলে মূল লোগো)', ''],
        'support_email' => ['সাপোর্ট ইমেইল', 'Shokolsheba@gmail.com'],
        'site_notice' => ['সাধারণ নোটিশ', 'সকল কাজ চালু আছে। সহায়তার জন্য সাপোর্টে যোগাযোগ করুন।'],
    ];
}
function soko_brand($key) {
    global $conn;
    static $settings = null;
    if ($settings === null) {
        $settings = [];
        try {
            $result = $conn->query('SELECT setting_key, setting_value FROM site_settings');
        } catch (Throwable $error) {
            $result = false;
        }
        if ($result) {
            while ($item = $result->fetch_assoc()) $settings[$item['setting_key']] = $item['setting_value'];
        }
    }
    $defaults = soko_brand_fields();
    $value = trim((string)($settings[$key] ?? ''));
    if ($value === '') $value = $defaults[$key][1] ?? '';
    if (in_array($key, ['nav_logo', 'site_favicon'], true) && $value === '') return soko_brand('site_logo');
    if (in_array($key, ['site_logo', 'nav_logo', 'site_favicon'], true)) return soko_safe_url($value, '/assets/logo.jpg');
    return $value;
}
function soko_safe_url($value, $fallback = '#') {
    $value = trim((string)$value);
    if (preg_match('~^https?://[^\s]+$~i', $value)) return $value;
    if (preg_match('~^/?(?:assets|uploads)/[a-zA-Z0-9_./% -]+$~', $value) && strpos($value, '..') === false) return '/' . ltrim($value, '/');
    return $fallback;
}
function soko_e($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function soko_whatsapp_url() {
    $number = preg_replace('/\D/', '', soko_brand('support_whatsapp_num') ?: '01849207118');
    if (strpos($number, '01') === 0) $number = '88' . $number;
    return 'https://wa.me/' . $number;
}

function soko_brand_name_html() {
    $parts = preg_split('/\s+/u', soko_brand('site_name'));
    if (count($parts) < 2) return soko_e(soko_brand('site_name'));
    $last = array_pop($parts);
    return soko_e(implode(' ', $parts)) . ' <span class="ss-brand-accent">' . soko_e($last) . '</span>';
}
