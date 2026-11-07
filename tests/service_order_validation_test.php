<?php
/** Run the real controller's POST path with no real account or database writes. */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
function soko_current_user($conn) { return ['id'=>17,'balance'=>1000,'email'=>'fixture@example.invalid']; }
function soko_get_service($conn, $id, $name) { return $GLOBALS['test_service']; }
function soko_payment_channels($conn) { return []; }
require_once dirname(__DIR__) . '/includes/service_catalog.php';
class NoWriteConnection {
    public $transactions=0;
    function begin_transaction() { $this->transactions++; throw new RuntimeException('Unexpected write'); }
    function rollback() {}
}
$conn=new NoWriteConnection();
$_GET=['id'=>1]; $_SESSION=['user_token'=>'fixture'];
$_SERVER['REQUEST_METHOD']='POST';
foreach(['missing_text','missing_pdf','fake_pdf','admin_required'] as $case) {
    $test_service=['id'=>1,'service_name'=>'নতুন NID জন্য আবেদন','category'=>'আবেদন','description'=>'test','price'=>20,'status'=>'active','custom_fields'=>'[]'];
    if($case==='admin_required') { $test_service['service_name']='কাস্টম সেবা';$test_service['custom_fields']='[{"title":"কাস্টম আবশ্যক","type":"text","required":1}]'; }
    $fields=soko_resolve_fields($test_service);
    $_POST=['place_order'=>1,'payment_method'=>'wallet'];$_FILES=[];
    foreach($fields as $field) if($field['type']!=='file') $_POST['field_'.$field['key']]='test';
    if($case==='missing_text' || $case==='admin_required') $_POST=[]+['place_order'=>1,'payment_method'=>'wallet'];
    if($case==='fake_pdf') {
        $tmp=tempnam(sys_get_temp_dir(),'bad-pdf-');file_put_contents($tmp,'fake');
        $_FILES['file_birth_pdf']=['name'=>'birth.pdf','tmp_name'=>$tmp,'size'=>4,'error'=>0];
    }
    $source=file_get_contents(dirname(__DIR__).'/service_order.php');
    $source=substr($source,5,strpos($source,'?>')-5);
    $source=str_replace('session_start();','',$source);
    $source=str_replace('include_once("includes/configuration.php");','',$source);
    eval($source);
    if(empty($errors) || $conn->transactions!==0) throw new RuntimeException('Invalid POST reached billing: '.$case);
    if(isset($tmp)) { unlink($tmp);unset($tmp); }
    echo "PASS: controller rejects $case before transaction/balance deduction\n";
}
