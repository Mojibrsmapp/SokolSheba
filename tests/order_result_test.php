<?php
/** Exercise the actual download handler with mock DB rows and temporary files. */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once dirname(__DIR__) . '/includes/customer_history.php';
$case = $argv[1] ?? 'owner';
if (!in_array($case, ['owner', 'other', 'guest', 'missing', 'input'], true)) throw new RuntimeException('Unknown case');
session_start();
$_SESSION = $case === 'guest' ? [] : ['user_token' => $case === 'other' ? 'other-token' : 'owner-token'];
$_GET = ['order' => $case === 'missing' ? 999 : 17];
$test_root = sys_get_temp_dir() . '/soko-download-' . bin2hex(random_bytes(4));
mkdir($test_root . '/uploads/results', 0755, true);
file_put_contents($test_root . '/uploads/results/issued.pdf', 'RESULT-OWNER-FILE');
class DownloadTestRows {
    private $row;
    function __construct($row) { $this->row = $row; }
    function fetch_assoc() { return $this->row; }
}
class DownloadTestStatement {
    private $bindings;
    function bind_param($types, &...$params) {
        if ($types !== 'is') throw new RuntimeException('Order and owner must be bound');
        $this->bindings = $params;
    }
    function execute() { return true; }
    function get_result() {
        global $case;
        $row = $this->bindings === [17, 'owner-token'] ? ['id' => 17, 'result_file' => $case === 'input' ? 'uploads/orders/customer.pdf' : 'uploads/results/issued.pdf'] : null;
        return new DownloadTestRows($row);
    }
    function close() {}
}
class DownloadTestConnection {
    function prepare($sql) {
        if (strpos($sql, 'WHERE o.id = ? AND u.token = ?') === false || strpos($sql, 'u.id = o.user_id') === false) throw new RuntimeException('Download ownership restriction missing');
        return new DownloadTestStatement();
    }
}
$conn = new DownloadTestConnection();
ob_start();
register_shutdown_function(function () use ($case, $test_root) {
    $content = ob_get_clean();
    unlink($test_root . '/uploads/results/issued.pdf');
    rmdir($test_root . '/uploads/results'); rmdir($test_root . '/uploads'); rmdir($test_root);
    $status = http_response_code() ?: 200;
    $expected = $case === 'owner' ? 200 : ($case === 'guest' ? 302 : 404);
    if ($status !== $expected || ($case === 'owner' ? $content !== 'RESULT-OWNER-FILE' : strpos($content, 'RESULT-OWNER-FILE') !== false)) {
        throw new RuntimeException("Download case $case failed: HTTP $status, body $content");
    }
    echo "PASS: download $case (HTTP $status)\n";
});
$source = file_get_contents(dirname(__DIR__) . '/order_result.php');
// Replace only infrastructure: keep the handler's authorization and response logic.
$source = str_replace('session_start();', '', $source);
$source = str_replace("require_once __DIR__ . '/includes/configuration.php';", '', $source);
$source = str_replace("require_once __DIR__ . '/includes/customer_history.php';", '', $source);
$source = str_replace("soko_order_result_path(\$order['result_file'])", "soko_order_result_path(\$order['result_file'], \$test_root)", $source);
eval('?>' . $source);
