<?php
// Render actual templates offline with mock data; never expose this fixture over HTTP.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$root=dirname(__DIR__);
require_once $root . '/includes/site_brand.php';
require_once $root . '/includes/customer_history.php';
require_once $root . '/includes/payment_flow.php';
class PreviewRows { private $rows; function __construct($rows){$this->rows=$rows;} function fetch_assoc(){return array_shift($this->rows);} }
class PreviewStatement { function bind_param($types,&...$params){} function execute(){return true;} function get_result(){global $fixture_orders;return new PreviewRows($fixture_orders);} function close(){} }
class PreviewConnection { function query($sql){return new PreviewRows([]);} function prepare($sql){return new PreviewStatement();} }
$conn=new PreviewConnection();
$_SESSION=['user_token'=>'fixture-owner']; $_GET=[];
$file=$argv[1] ?? '';
if (!in_array($file, ['service_order.php', 'recharge.php', 'services.php', 'auto_tin.php', 'numbertobio.php', 'mfs_service.php', 'location_service.php', 'imei_service.php', 'call_sms_list.php', 'order_file.php', 'order_text.php', 'order_service.php', 'auto_service.php', 'admin/add_service.php', 'admin/edit_service.php'], true)) throw new RuntimeException('Unsupported fixture page');
$_SERVER['SCRIPT_NAME']='/'.$file;
$fixture_orders=[
 ['id'=>17,'order_type'=>'নতুন জন্ম নিবন্ধনের জন্য আবেদন','status'=>'success','price'=>20,'created_at'=>'2026-11-08 12:00:00','result_text'=>'জন্ম নিবন্ধন নম্বর: 12345678901234567','result_file'=>'uploads/results/demo.pdf'],
 ['id'=>16,'order_type'=>'নতুন জন্ম নিবন্ধনের জন্য আবেদন','status'=>'pending','price'=>20,'created_at'=>'2026-11-08 11:00:00','result_text'=>'','result_file'=>''],
 ['id'=>15,'order_type'=>str_repeat('সেবা ',10),'status'=>'cancel','price'=>20,'created_at'=>'2026-11-08 10:00:00','result_text'=>str_repeat('1234567890',80),'result_file'=>''],
];
$balance=70; $id=17; $is_active=true; $errors=[]; $error_msg='';
$category='জন্ম নিবন্ধন সেবা'; $service_title=$argv[2] ?? 'নতুন জন্ম নিবন্ধনের জন্য আবেদন'; $service_desc='সঠিক তথ্য দিয়ে নতুন জন্ম নিবন্ধনের আবেদন করুন।';
$service=['id'=>1,'service_name'=>$service_title,'category'=>$category,'description'=>$service_desc,'price'=>20,'status'=>'active','custom_fields'=>$argv[3] ?? '[]'];
$fields=soko_resolve_fields($service); $old_values=[]; $service_icon='fa-file-lines'; $price=$total_payable=20; $service_charge=0; $wallet_balance=70; $default_method='wallet';
$order_submitted=false; $gateway_numbers=['bkash'=>'01712345678','nagad'=>'01712345678','rocket'=>'01712345678','upay'=>'01712345678']; $first_available_method='bkash';
$history_records=[['id'=>1,'method'=>'bkash','trx_id'=>'ABC12345','amount'=>100,'status'=>'completed','created_at'=>'2026-11-08 10:00:00']];
$service_name='ব্যালেন্স রিচার্জ'; $service_category='টপ-আপ সেবা';
$is_cat_page=true; $active_cat=$hero_cat=$category; $search_q=''; $hero_h1=$service_title; $hero_sub=$service_desc; $hero_meta=['bg'=>'#107C41','icon'=>'fa-file-lines','soft'=>'#E7F4EC']; $default_color=$hero_meta;
$hero_copy=[]; $category_colors=[$category=>$hero_meta]; $all_cats=[$category]; $total_services=1; $services_by_cat=[$category=>[$service+['service_type'=>'text','access_link'=>'']]];
$whatsapp_link='https://wa.me/8801712345678';
$recent_orders=$fixture_orders;
$order_err=$order_msg=$order_error=$order_success=$alert_msg=$alert_type='';
$custom_fields=$fields;
$option=$service+['name'=>$service_title,'logo_url'=>'/assets/logo.jpg','operator_name'=>'অপারেটর','input_placeholder'=>'মোবাইল নম্বর দিন','placeholder'=>'মোবাইল নম্বর দিন','service_title'=>$service_title];
$operators=$text_services=$mfs_items=$location_items=$imei_items=[$option];
$selected_id=1; $selected_price=20; $selected_placeholder='মোবাইল নম্বর দিন';
$initial_group='call'; $initial_dur='3m'; $services_map=['call_3m'=>$option];
$services=[$service]; $categories=[$category]; $selected_service_name=$service_title;
$allServices=[['id'=>1,'name'=>$service_title,'category'=>$category,'description'=>$service_desc,'status'=>'active','restricted'=>false,'link'=>'service_order.php?id=1','type'=>'text','price'=>20,'fields'=>$fields]];
$servicesByCategory=[$category=>$allServices]; $serviceCount=1;
$escape=function($value){return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');};
$template = in_array($file, ['order_file.php','order_service.php'], true) ? 'service_order.php' : $file;
$source=file_get_contents($root.'/'.$template);
if (strpos($file, 'admin/') === 0) {
    $categories_list=[$category]; $save_msg=$save_error='';
    $srv=$service+['service_type'=>'custom','is_pinned'=>0,'access_link'=>'','service_image'=>''];
    $existing_fields=$fields;
    $source=substr($source,strpos($source,'<link href='));
    $source=str_replace('<?php include_once("footer.php"); ?>','',$source);
    $source='<!DOCTYPE html><html lang="bn"><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><body>'.$source.'</body></html>';
} else $source=substr($source,strpos($source,'<!DOCTYPE html>'));
$source=str_replace('__DIR__',var_export($root,true),$source);
if($file==='recharge.php')ob_start();
eval('?>'.$source);
