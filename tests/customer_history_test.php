<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once dirname(__DIR__) . '/includes/customer_history.php';
function expect_history($value, $message) { if (!$value) throw new RuntimeException($message); }
foreach (['নতুন জন্ম নিবন্ধনের জন্য আবেদন', 'নতুন জন্ম নিবন্ধন', 'New birth registration', 'Birth registration application'] as $name) {
    expect_history(soko_is_new_birth_application($name), 'Detect new birth: ' . $name);
    $fields = soko_resolve_fields(['service_name' => $name, 'custom_fields' => '[{"title":"জন্ম নিবন্ধন নম্বর","required":1}]']);
    expect_history(!in_array('brn', array_column($fields, 'key')), 'No issued BRN required for first application');
    expect_history(in_array('dob', array_column($fields, 'key')), 'Keep date of birth');
}
foreach (['জন্ম নিবন্ধন সংশোধন', 'জন্ম নিবন্ধন দিয়ে NID', 'Birth verification', 'NID আবেদন'] as $name) expect_history(!soko_is_new_birth_application($name), 'Do not alter existing-registration services');
$existing = soko_resolve_fields(['service_name' => 'জন্ম নিবন্ধন']);
expect_history(in_array('brn', array_column($existing, 'key')), 'Existing registration still needs its number');

class HistoryTestResult {
    private $rows;
    function __construct($rows) { $this->rows = $rows; }
    function fetch_assoc() { return array_shift($this->rows); }
}
class HistoryTestStatement {
    public $bindings;
    private $rows;
    function __construct($rows) { $this->rows = $rows; }
    function bind_param($types, &...$params) { $this->bindings = [$types, $params]; }
    function execute() { return true; }
    function get_result() { return new HistoryTestResult($this->rows); }
    function close() {}
}
class HistoryTestConnection {
    public $sql, $statement, $rows = [];
    function prepare($sql) { $this->sql = $sql; return $this->statement = new HistoryTestStatement($this->rows); }
}
$conn = new HistoryTestConnection();
for ($i = 1; $i <= 21; $i++) $conn->rows[] = ['id' => $i];
$data = soko_customer_orders($conn, 'owner-token', ['service' => "নতুন জন্ম নিবন্ধন ' test"], 2);
expect_history(count($data['rows']) === 20 && $data['more'], 'Paginate without dropping next-page flag');
expect_history(strpos($conn->sql, 'u.token = ?') !== false && strpos($conn->sql, 'o.order_type = ?') !== false, 'Scope owner and exact service');
expect_history(strpos($conn->sql, "' test") === false, 'Filter never interpolated into SQL');
expect_history(strpos($conn->sql, 'OFFSET 20') !== false, 'Page offset');
expect_history($conn->statement->bindings === ['ss', ['owner-token', "নতুন জন্ম নিবন্ধন ' test"]], 'Bound owner and service parameters');
soko_customer_orders($conn, 'owner-token', ['category' => 'জন্ম নিবন্ধন সেবা']);
expect_history(strpos($conn->sql, 'category = ?') !== false, 'Category filter is parameterized');
expect_history(soko_customer_orders($conn, '')['rows'] === [], 'Guests cannot query history');

$order = ['id' => 17, 'order_type' => 'নতুন জন্ম নিবন্ধন', 'status' => 'success', 'price' => 20, 'created_at' => '2026-11-08', 'result_text' => "নিবন্ধন নম্বর: 12345678901234567\n<script>alert(1)</script>", 'result_file' => 'uploads/results/result.pdf'];
ob_start(); soko_render_order_result($order); $html = ob_get_clean();
expect_history(strpos($html, '12345678901234567') !== false && strpos($html, 'order_result.php?order=17') !== false, 'Show text AND file');
expect_history(strpos($html, '<script>') === false && strpos($html, '&lt;script&gt;') !== false, 'Escape result text');
expect_history(!soko_order_result_has_file(['result_file' => 'uploads/orders/customer-input.pdf']), 'Never mislabel input attachment as output');
$order['result_file'] = ''; $order['result_text'] = ''; $order['status'] = 'pending';
ob_start(); soko_render_order_result($order); $html = ob_get_clean();
expect_history(strpos($html, 'আগে থেকে জন্ম নিবন্ধন নম্বর লাগে না') !== false, 'Explain waiting for issued birth number');

$root = sys_get_temp_dir() . '/soko-result-test-' . bin2hex(random_bytes(4));
mkdir($root . '/uploads/results', 0755, true); mkdir($root . '/admin/uploads/results', 0755, true);
file_put_contents($root . '/uploads/results/root.pdf', 'fixture'); file_put_contents($root . '/admin/uploads/results/admin.pdf', 'fixture');
expect_history(soko_order_result_path('uploads/results/root.pdf', $root) === $root . '/uploads/results/root.pdf', 'Root result directory');
expect_history(soko_order_result_path('uploads/results/admin.pdf', $root) === $root . '/admin/uploads/results/admin.pdf', 'Legacy admin directory');
foreach (['../config.php','uploads/results/../../config.php','https://example.com/file.pdf','uploads/results/file.php','uploads/orders/input.pdf'] as $bad) expect_history(soko_order_result_path($bad, $root) === null, 'Reject unsafe path: ' . $bad);
file_put_contents($root . '/uploads/results/admin.pdf', 'different owner');
expect_history(soko_order_result_path('uploads/results/admin.pdf', $root) === null, 'Fail closed on ambiguous legacy filenames');
expect_history(soko_order_result_path('admin/uploads/results/admin.pdf', $root) === $root . '/admin/uploads/results/admin.pdf', 'Explicit admin path is unambiguous');
unlink($root . '/uploads/results/admin.pdf');
file_put_contents($root . '/outside.pdf', 'secret');
if (symlink($root . '/outside.pdf', $root . '/uploads/results/link.pdf')) {
    expect_history(soko_order_result_path('uploads/results/link.pdf', $root) === null, 'Reject symlink escape from result directories');
    unlink($root . '/uploads/results/link.pdf');
}
unlink($root . '/outside.pdf');
unlink($root . '/uploads/results/root.pdf'); unlink($root . '/admin/uploads/results/admin.pdf');
rmdir($root . '/uploads/results'); rmdir($root . '/uploads'); rmdir($root . '/admin/uploads/results'); rmdir($root . '/admin/uploads'); rmdir($root . '/admin'); rmdir($root);
echo "PASS: new-birth fields, owner-scoped history, pagination, escaped text+file results and safe result paths\n";
