<?php
/** Legacy catalog URL: use the same admin-driven form and validation everywhere. */
session_start();
require_once __DIR__ . '/includes/configuration.php';
require_once __DIR__ . '/includes/service_catalog.php';
if (!soko_current_user($conn)) { header('Location: index.php'); exit; }
$id = (int)($_GET['id'] ?? $_POST['service_id'] ?? 0);
$service = soko_get_service($conn, $id, '');
if (!$service) { header('Location: services.php'); exit; }
header('Location: service_order.php?id=' . (int)$service['id']);
exit;
