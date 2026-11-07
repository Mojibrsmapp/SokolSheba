<?php
/** Run with PHP CLI; deliberately never serve fixtures via the website. */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once dirname(__DIR__) . '/includes/site_brand.php';
function check($condition, $message) { if (!$condition) throw new RuntimeException($message); }
class ShellTestResult {
    private $rows;
    public function __construct($rows) { $this->rows = $rows; }
    public function fetch_assoc() { return array_shift($this->rows); }
}
class ShellTestConnection {
    public $queries = 0;
    private $mode;
    public function __construct($mode) { $this->mode = $mode; }
    public function query($sql) {
        $this->queries++;
        if ($this->mode === 'missing') throw new RuntimeException('Missing settings table');
        $values = $this->mode === 'custom' ? [
            'site_name' => 'আমার ডিজিটাল সেবা', 'site_logo' => '/uploads/branding/custom.png',
            'nav_logo' => 'https://example.com/nav.png', 'site_favicon' => 'https://example.com/favicon.png',
            'support_email' => 'help@example.com', 'support_whatsapp_num' => '+880 1712-345678',
            'telegram_link' => 'javascript:alert(1)', 'site_notice' => '<script>alert("unsafe")</script>',
        ] : [];
        return new ShellTestResult(array_map(function ($key, $value) { return ['setting_key' => $key, 'setting_value' => $value]; }, array_keys($values), $values));
    }
}
$mode = $argv[1] ?? 'defaults';
$conn = new ShellTestConnection($mode);
$_SESSION = ['user_token' => 'fixture-token'];
$_SERVER['SCRIPT_NAME'] = '/profile.php';
$balance = '100.00';
check(soko_safe_url('javascript:alert(1)') === '#', 'Reject javascript URLs');
check(soko_safe_url('//evil.example/logo.png') === '#', 'Reject protocol-relative URLs');
check(soko_safe_url('/uploads/../config.php') === '#', 'Reject path traversal');
check(soko_safe_url('/uploads/branding/logo.png') === '/uploads/branding/logo.png', 'Allow local logo');
check(soko_safe_url('https://example.com/logo.png') === 'https://example.com/logo.png', 'Allow HTTPS logo');
if ($mode === 'custom') {
    check(soko_brand('site_name') === 'আমার ডিজিটাল সেবা', 'Custom name');
    check(soko_brand('nav_logo') === 'https://example.com/nav.png', 'Separate nav logo');
    check(soko_brand('site_favicon') === 'https://example.com/favicon.png', 'Separate favicon');
    check(soko_whatsapp_url() === 'https://wa.me/8801712345678', 'Normalize international number');
} else {
    check(soko_brand('site_name') === 'সকল সেবা', 'Default name / missing table');
    check(soko_brand('nav_logo') === soko_brand('site_logo'), 'Nav fallback to site logo');
    check(soko_brand('site_favicon') === soko_brand('site_logo'), 'Favicon fallback to site logo');
    check(soko_whatsapp_url() === 'https://wa.me/8801849207118', 'Default local number');
}
ob_start();
include dirname(__DIR__) . '/includes/site_header.php';
include dirname(__DIR__) . '/includes/site_header.php'; // Duplicate include is safe.
include dirname(__DIR__) . '/includes/site_footer.php';
include dirname(__DIR__) . '/includes/site_footer.php';
$html = ob_get_clean();
check(substr_count($html, '<header class="ss-header"') === 1, 'Exactly one shared header');
check(substr_count($html, '<nav class="ss-footer"') === 1, 'Exactly one shared footer');
check(substr_count($html, 'id="ss-support-toggle"') === 1, 'Exactly one support widget');
check(strpos($html, 'class="ss-active" aria-current="page"') !== false, 'Current page highlighted');
check(strpos($html, 'javascript:') === false, 'Unsafe support URL never rendered');
check(strpos($html, '<script>alert') === false, 'Notices escaped');
check($conn->queries === 1, 'One branding query per request');
if ($mode === 'custom') check(strpos($html, 'mailto:help@example.com') !== false, 'Custom support email');
if (getenv('SOKO_RENDER_FIXTURE')) {
    echo '<!doctype html><html lang="bn"><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><body style="margin:0;background:#f4f7f6">', $html, '<main style="padding:24px;min-height:1200px"><h1>প্রোফাইল</h1><p>সকল সেবা</p></main></body></html>';
} else {
    echo "PASS: site shell ($mode)\n";
}
