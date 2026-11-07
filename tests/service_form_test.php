<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once dirname(__DIR__) . '/includes/service_catalog.php';
function expect_form($test, $message) { if (!$test) throw new RuntimeException($message); }
function fields_for($name, $custom = []) { return soko_resolve_fields(['service_name'=>$name,'custom_fields'=>json_encode($custom, JSON_UNESCAPED_UNICODE)]); }
foreach (['নতুন জন্ম নিবন্ধনের জন্য আবেদন','New birth registration application'] as $name) {
    $fields = fields_for($name, [['title'=>'জন্ম নিবন্ধন নম্বর','type'=>'text','required'=>1]]);
    $keys = array_column($fields,'key');
    foreach (['name','father_name','mother_name','village','thana','district','division'] as $key) expect_form(in_array($key,$keys,true), 'Birth field: '.$key);
    expect_form(!in_array('brn',$keys,true), 'Cannot require already-issued BRN');
}
foreach (['নতুন NID জন্য আবেদন','নতুন এনআইডি আবেদন','New NID application'] as $name) {
    $fields = fields_for($name, [['title'=>'NID নম্বর','required'=>1]]);
    $pdf = array_values(array_filter($fields, function($f){return $f['key']==='birth_pdf';}));
    expect_form(count($pdf)===1 && $pdf[0]['required'] && $pdf[0]['type']==='file', 'Required birth PDF');
    expect_form(!in_array('nid',array_column($fields,'key'),true), 'New NID does not need issued NID');
}
foreach (['নতুন পাসপোর্ট আবেদন','পাসপোর্টের জন্য আবেদন','New passport application'] as $name) {
    $fields = fields_for($name, [['title'=>'পাসপোর্ট নম্বর','required'=>1]]);
    expect_form(in_array('nid',array_column($fields,'key'),true), 'Passport application uses NID');
    expect_form(!in_array('passport',array_column($fields,'key'),true), 'New passport cannot need issued number');
}
foreach (['পাসপোর্ট নবায়নের আবেদন','Passport renewal application','NID সংশোধন আবেদন','Passport verification'] as $name) expect_form(soko_application_kind($name)==='', 'Do not replace renewal/verification: '.$name);
foreach (['বিকাশ ইনফো','নগদ ইনফো','রকেট ইনফো','উপায় ইনফো','Upay info'] as $name) {
    expect_form(!in_array('trx',array_column(fields_for($name),'key'),true), 'Info is not payment TrxID');
    expect_form(!in_array('trx',array_column(fields_for($name,[['title'=>'ট্রানজেকশন আইডি','required'=>1],['title'=>'মোবাইল নম্বর','required'=>1]]),'key'),true), 'Remove legacy info TrxID');
}
// Sparse admin row indices: unchecked/deleted rows must not shift required flags.
$posted = ['field_title'=>[2=>'বিশেষ নির্দেশনা',7=>'ফাইল',10=>'যোগাযোগ'],
    'field_type'=>[2=>'textarea',7=>'file',10=>'tel'], 'field_required'=>[7=>'1'],
    'field_placeholder'=>[2=>'এখানে লিখুন'], 'field_accept'=>[7=>'.pdf'], 'field_key'=>[2=>'custom_note']];
$saved = soko_admin_form_fields($posted);
expect_form(array_column($saved,'required')===[0,1,0], 'Required boxes retain correct rows');
$fields = fields_for('উপায় ইনফো', $saved);
expect_form(count($fields)===3 && $fields[0]['title']==='বিশেষ নির্দেশনা' && $fields[0]['type']==='textarea', 'Admin configuration overrides built-in defaults');
expect_form($fields[0]['placeholder']==='এখানে লিখুন' && $fields[1]['accept']==='.pdf', 'Admin placeholder and accept persist');
expect_form($fields[0]['key']==='custom_note', 'Stable key survives edits');
$v=soko_validate_order_fields($fields, ['field_custom_note'=>'ঐচ্ছিক'], []);
expect_form(count($v['errors'])===1 && strpos($v['errors'][0],'ফাইল')!==false, 'Required file enforced server-side, optional text allowed');
$fields=fields_for('নতুন NID জন্য আবেদন');
$post=[];
foreach($fields as $field) if($field['type']!=='file') $post['field_'.$field['key']]='test';
$v=soko_validate_order_fields($fields,$post,[]);
expect_form(count($v['errors'])===1 && strpos($v['errors'][0],'PDF')!==false, 'Missing NID birth PDF rejected');
$tmp=tempnam(sys_get_temp_dir(),'soko-pdf-');
file_put_contents($tmp,'not a PDF');
$file=['name'=>'birth.pdf','tmp_name'=>$tmp,'size'=>9,'error'=>UPLOAD_ERR_OK];
expect_form(soko_order_upload_error($file,'.pdf')!=='', 'Renamed image/text rejected');
file_put_contents($tmp,'%PDF-1.4 test fixture');$file['size']=20;
expect_form(soko_order_upload_error($file,'.pdf,application/pdf')==='', 'PDF header accepted');
expect_form(soko_validate_order_fields($fields,$post,['file_birth_pdf'=>$file])['errors']===[], 'Complete required data validates');
$file['name']='birth.jpg'; expect_form(soko_order_upload_error($file,'.pdf')!=='','Non-PDF extension rejected');
$file['name']='birth.pdf';$file['size']=6*1024*1024;expect_form(soko_order_upload_error($file,'.pdf')!=='','Oversized PDF rejected');
unlink($tmp);
echo "PASS: application fields, admin precedence/sparse required flags, optional fields and PDF validation\n";
