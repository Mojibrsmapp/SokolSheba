<?php
/** Old file/custom service links must not bypass the canonical field validator. */
session_start();
require_once __DIR__ . '/includes/configuration.php';
require_once __DIR__ . '/includes/service_catalog.php';
if (!soko_current_user($conn)) { header('Location: index.php'); exit; }
$id = (int)($_POST['service_id'] ?? $_GET['service_id'] ?? $_GET['id'] ?? 0);
$name = $_GET['service'] ?? $_GET['service_name'] ?? '';
if (!is_string($name)) $name = '';
if ($id <= 0 && trim($name) === '') { header('Location: services.php'); exit; }
$service = soko_get_service($conn, $id, trim($name));
if (!$service) { header('Location: services.php'); exit; }
// No wallet deduction or upload happens on this compatibility route.
header('Location: service_order.php?id=' . (int)$service['id']);
exit;
