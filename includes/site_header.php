<?php
require_once __DIR__ . '/site_brand.php';
require_once __DIR__ . '/site_icons.php';
if (defined('SOKO_HEADER_RENDERED')) return;
define('SOKO_HEADER_RENDERED', true);
$ss_logged_in = !empty($_SESSION['user_token']);
$ss_balance = $balance ?? null;
if ($ss_logged_in && $ss_balance === null) {
    $ss_stmt = $conn->prepare('SELECT balance FROM users WHERE token = ? LIMIT 1');
    if ($ss_stmt) {
        $ss_stmt->bind_param('s', $_SESSION['user_token']);
        $ss_stmt->execute();
        $ss_result = $ss_stmt->get_result()->fetch_assoc();
        $ss_balance = $ss_result['balance'] ?? 0;
        $ss_stmt->close();
    }
}
?>
<link rel="stylesheet" href="/assets/css/site_shell.css">
<header class="ss-header" data-site-shell>
    <div class="ss-header-inner">
        <a class="ss-brand" href="<?php echo $ss_logged_in ? '/dashboard.php' : '/index.php'; ?>">
            <img src="<?php echo soko_e(soko_brand('nav_logo')); ?>" alt="<?php echo soko_e(soko_brand('site_name')); ?>" onerror="this.onerror=null;this.src='/assets/logo.jpg'">
            <span class="ss-brand-copy"><strong><?php echo soko_brand_name_html(); ?></strong><small><?php echo soko_e(soko_brand('site_tagline')); ?></small></span>
        </a>
        <div class="ss-actions">
            <a class="ss-wallet" href="<?php echo $ss_logged_in ? '/recharge.php' : '/login.php'; ?>" aria-label="ওয়ালেট">
                <?php echo soko_icon('wallet'); ?><span><small>ওয়ালেট</small><b>৳ <span id="ss-balance"><?php echo soko_e($ss_balance ?? '0.00'); ?></span></b></span>
            </a>
            <button type="button" class="ss-square ss-notice" data-ss-open="ss-notice" aria-label="নোটিশ" aria-controls="ss-notice" aria-expanded="false"><?php echo soko_icon('bell'); ?><span class="ss-dot"></span></button>
            <button type="button" class="ss-square" data-ss-open="ss-menu" aria-label="মেনু" aria-controls="ss-menu" aria-expanded="false"><?php echo soko_icon('menu'); ?></button>
        </div>
    </div>
</header>
