<?php
require_once __DIR__ . '/site_brand.php';
require_once __DIR__ . '/site_icons.php';
if (defined('SOKO_FOOTER_RENDERED')) return;
define('SOKO_FOOTER_RENDERED', true);
$ss_page = basename($_SERVER['SCRIPT_NAME'] ?? '');
include __DIR__ . '/page_history.php';
// Public/auth pages retain support, menu and notices, but no fixed bottom bar.
$ss_show_navigation = !in_array($ss_page, ['login.php', 'register_user.php', 'index.php'], true);
$ss_active = in_array($ss_page, ['dashboard.php', 'index.php']) ? 'home' : (in_array($ss_page, ['profile.php', 'login.php', 'register_user.php']) ? 'user' : (strpos($ss_page, 'recharge') !== false || strpos($ss_page, 'payment_') === 0 ? 'wallet' : 'grid'));
$ss_links = [
    ['home', '/dashboard.php', 'হোম'], ['grid', '/services.php', 'সেবা'],
    ['wallet', '/recharge.php', 'ওয়ালেট'], ['user', '/profile.php', 'প্রোফাইল'],
];
?>
<?php if ($ss_show_navigation): ?>
<nav class="ss-footer" aria-label="প্রধান নেভিগেশন" data-site-shell>
    <div class="ss-footer-inner">
        <?php foreach ($ss_links as $ss_i => $ss_link): ?>
        <?php if ($ss_i === 2): ?><button type="button" class="ss-center" data-ss-open="ss-auto" aria-label="অটো সার্ভিস মেনু" aria-controls="ss-auto" aria-expanded="false"><?php echo soko_icon('bolt'); ?></button><?php endif; ?>
        <a href="<?php echo $ss_link[1]; ?>"<?php echo $ss_active === $ss_link[0] ? ' class="ss-active" aria-current="page"' : ''; ?>><?php echo soko_icon($ss_link[0]); ?><span><?php echo $ss_link[2]; ?></span></a>
        <?php endforeach; ?>
    </div>
</nav>
<?php endif; ?>
<div class="ss-support" id="ss-support" data-site-shell>
    <div class="ss-support-panel" id="ss-support-panel" hidden>
        <strong>সাপোর্ট ও সহায়তা</strong>
        <a href="<?php echo soko_e(soko_whatsapp_url()); ?>" target="_blank" rel="noopener noreferrer">WhatsApp সাপোর্ট</a>
        <?php if (soko_brand('whatsapp_group')): ?><a href="<?php echo soko_e(soko_safe_url(soko_brand('whatsapp_group'))); ?>" target="_blank" rel="noopener noreferrer">WhatsApp গ্রুপ</a><?php endif; ?>
        <a href="<?php echo soko_e(soko_safe_url(soko_brand('telegram_link') ?: 'https://t.me/Sheba_kit')); ?>" target="_blank" rel="noopener noreferrer">Telegram চ্যানেল</a>
        <?php if (filter_var(soko_brand('support_email'), FILTER_VALIDATE_EMAIL)): ?><a href="mailto:<?php echo soko_e(soko_brand('support_email')); ?>"><?php echo soko_e(soko_brand('support_email')); ?></a><?php endif; ?>
        <a href="/support.php">সকল সহায়তা দেখুন →</a>
    </div>
    <button type="button" class="ss-support-toggle" id="ss-support-toggle" aria-label="সাপোর্ট পেতে ক্লিক করুন; টেনে সরাতে পারবেন" aria-controls="ss-support-panel" aria-expanded="false"><?php echo soko_icon('support'); ?></button>
</div>
<div class="ss-overlay" id="ss-menu" hidden data-site-shell>
    <section class="ss-drawer" role="dialog" aria-modal="true" aria-labelledby="ss-menu-title" tabindex="-1">
        <div class="ss-drawer-brand">
            <img src="<?php echo soko_e(soko_brand('nav_logo')); ?>" alt="">
            <div><strong id="ss-menu-title"><?php echo soko_e(soko_brand('site_name')); ?></strong><small><?php echo soko_e(soko_brand('site_tagline')); ?></small></div>
            <button type="button" class="ss-square" data-ss-close aria-label="মেনু বন্ধ করুন"><?php echo soko_icon('close'); ?></button>
        </div>
        <?php $ss_menu_groups = [
            'শুরু করুন' => [['/dashboard.php','হোম ড্যাশবোর্ড','home'],['/services.php','সকল সেবা','grid'],['/auto_service.php','অটো সার্ভিস','bolt']],
            'আপনার সেবা' => [['/serverCopy.php','অটো সার্ভার কপি','file'],['/sign_order.php?type=10_12_17','সাইন কপি অর্ডার','file'],['/auto_tin.php','অটো টিন সার্টিফিকেট','file'],['/services.php?cat=জন্ম নিবন্ধন সেবা','জন্ম নিবন্ধন সেবা','file']],
            'আরও সেবা' => [['/numbertobio.php','সিম বায়োমেট্রিক','grid'],['/location_service.php','লোকেশন সেবা','grid'],['/mfs_service.php','বিকাশ-নগদ-রকেট','wallet']],
            'আমার অ্যাকাউন্ট' => [['/recharge.php','ওয়ালেট / রিচার্জ','wallet'],['/work_history.php','অর্ডার হিস্টোরি ও রেজাল্ট','history'],['/profile.php','আমার প্রোফাইল','user'],['/support.php','সাপোর্ট ও সহায়তা','support'],['/logout.php','লগআউট','logout']],
        ]; ?>
        <div class="ss-drawer-links">
        <?php foreach ($ss_menu_groups as $ss_group_title => $ss_group_links): ?>
            <div class="ss-menu-group"><p><?php echo soko_e($ss_group_title); ?></p>
            <?php foreach ($ss_group_links as $ss_item):
                $ss_menu_active = basename(parse_url($ss_item[0], PHP_URL_PATH)) === $ss_page && strpos($ss_item[0], '?') === false;
            ?>
                <a href="<?php echo soko_e($ss_item[0]); ?>"<?php echo $ss_menu_active ? ' class="ss-menu-active" aria-current="page"' : ''; ?>>
                    <span class="ss-menu-icon"><?php echo soko_icon($ss_item[2]); ?></span><span><?php echo soko_e($ss_item[1]); ?></span><span class="ss-menu-arrow" aria-hidden="true">›</span>
                </a>
            <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
        </div>
    </section>
</div>
<?php if ($ss_show_navigation): ?>
<div class="ss-overlay ss-modal" id="ss-auto" hidden data-site-shell>
    <section class="ss-modal-card" role="dialog" aria-modal="true" aria-labelledby="ss-auto-title" tabindex="-1">
        <div class="ss-dialog-title"><strong id="ss-auto-title">২৪/৭ অটো সার্ভিসসমূহ</strong><button type="button" class="ss-square" data-ss-close aria-label="বন্ধ করুন"><?php echo soko_icon('close'); ?></button></div>
        <?php foreach (['voter'=>'ভোটার তথ্য অনুসন্ধান','userid'=>'User Id to Server Copy','death'=>'Death Cer. Make','sign2server'=>'Sign 2 Server Copy','haf'=>'Haf Server Copy','epassport'=>'E-Passport Make','driving'=>'Driving License','clone_sonod'=>'Clone Sonod','sign2nid'=>'Sign 2 Nid','cv'=>'CV Make'] as $ss_type=>$ss_label): ?>
        <a href="/auto_service.php?type=<?php echo $ss_type; ?>"><?php echo $ss_label; ?> →</a>
        <?php endforeach; ?>
    </section>
</div>
<?php endif; ?>
<div class="ss-overlay ss-modal" id="ss-notice" hidden data-site-shell>
    <section class="ss-modal-card" role="dialog" aria-modal="true" aria-labelledby="ss-notice-title" tabindex="-1">
        <div class="ss-dialog-title"><strong id="ss-notice-title">জরুরী নোটিশ</strong><button type="button" class="ss-square" data-ss-close aria-label="বন্ধ করুন"><?php echo soko_icon('close'); ?></button></div>
        <p class="ss-notice-text"><?php echo nl2br(soko_e(soko_brand('site_notice'))); ?></p>
    </section>
</div>
<script src="/assets/js/site_shell.js" defer></script>
