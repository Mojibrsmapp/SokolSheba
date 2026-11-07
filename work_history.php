<?php
include_once __DIR__ . '/header.php';
require_once __DIR__ . '/includes/customer_history.php';
$history_page = max(1, min(100000, (int)($_GET['history_page'] ?? 1)));
soko_render_order_history(soko_customer_orders($conn, $_SESSION['user_token'], [], $history_page), $history_page);

// Retain the legacy balance ledger separately; do not confuse it with results.
$ledger = [];
$ledger_error = false;
try {
    $ledger_stmt = $conn->prepare('SELECT h.* FROM history_work h INNER JOIN users u ON h.email = u.email WHERE u.token = ? ORDER BY h.id DESC LIMIT 50');
    if (!$ledger_stmt) throw new RuntimeException('Ledger unavailable');
    $ledger_stmt->bind_param('s', $_SESSION['user_token']);
    if (!$ledger_stmt->execute()) { $ledger_stmt->close(); throw new RuntimeException('Ledger unavailable'); }
    $ledger_result = $ledger_stmt->get_result();
    if ($ledger_result) while ($entry = $ledger_result->fetch_assoc()) $ledger[] = $entry;
    $ledger_stmt->close();
} catch (Throwable $error) {
    $ledger_error = true;
    error_log('Customer balance history could not be loaded.');
}
?>
<section class="ss-order-history">
    <h2>ব্যালেন্স লেনদেনের হিস্টোরি</h2>
    <p>সর্বশেষ ৫০টি লেনদেন। সেবার ফলাফল উপরের অর্ডার হিস্টোরিতে দেখুন।</p>
    <div class="ss-table-scroll" tabindex="0" role="region" aria-label="ব্যালেন্স লেনদেন">
        <table class="table"><thead><tr><th>কাজের ধরন</th><th>পূর্বের ব্যালেন্স</th><th>চার্জ</th><th>পরের ব্যালেন্স</th><th>সময়</th></tr></thead><tbody>
        <?php foreach ($ledger as $entry): ?>
            <tr><td><?php echo soko_h($entry['order_type']); ?></td><td>৳ <?php echo number_format((float)$entry['current_balance'], 2); ?></td><td>৳ <?php echo number_format((float)$entry['price'], 2); ?></td><td>৳ <?php echo number_format((float)$entry['balance_after_cut'], 2); ?></td><td><?php echo soko_h($entry['order_time']); ?></td></tr>
        <?php endforeach; ?>
        <?php if ($ledger_error): ?><tr><td colspan="5" role="status">লেনদেন লোড করা যায়নি। আবার চেষ্টা করুন।</td></tr>
        <?php elseif (!$ledger): ?><tr><td colspan="5">এখনও কোনো লেনদেন নেই।</td></tr><?php endif; ?>
        </tbody></table>
    </div>
</section>
<?php include_once __DIR__ . '/footer.php'; ?>
