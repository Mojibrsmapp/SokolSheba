<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once dirname(__DIR__) . '/includes/service_catalog.php';
$post=json_decode($argv[1] ?? '{}',true);
if(!is_array($post)) throw new RuntimeException('Invalid fixture input');
$stored=soko_admin_form_fields($post);
echo json_encode(soko_resolve_fields(['service_name'=>$post['service_name'] ?? 'কাস্টম সেবা','custom_fields'=>json_encode($stored,JSON_UNESCAPED_UNICODE)]),JSON_UNESCAPED_UNICODE);
