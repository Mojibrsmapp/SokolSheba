<?php
// Included near the end of each shared customer layout, not on public/auth pages.
if (empty($_SESSION['user_token'])) return;
$ss_history_pages = ['service_order.php', 'order_service.php', 'order_file.php', 'order_text.php', 'services.php', 'auto_service.php', 'auto_tin.php', 'numbertobio.php', 'mfs_service.php', 'location_service.php', 'imei_service.php', 'call_sms_list.php'];
if (!in_array($ss_page, $ss_history_pages, true)) return;
require_once __DIR__ . '/customer_history.php';
if (in_array($ss_page, ['auto_tin.php', 'numbertobio.php', 'mfs_service.php', 'location_service.php', 'imei_service.php', 'call_sms_list.php', 'order_text.php'], true) && isset($recent_orders)) {
    // These existing service handlers already load an owner-scoped filtered list.
    soko_render_order_history(['rows' => $recent_orders, 'more' => false]);
    return;
}
$ss_filter = [];
if (in_array($ss_page, ['service_order.php', 'order_service.php'], true)) $ss_filter['service'] = $service['service_name'];
elseif ($ss_page === 'services.php' && !empty($is_cat_page)) $ss_filter['category'] = $active_cat;
$ss_history_page = max(1, min(100000, (int)($_GET['history_page'] ?? 1)));
soko_render_order_history(soko_customer_orders($conn, $_SESSION['user_token'], $ss_filter, $ss_history_page), $ss_history_page);
