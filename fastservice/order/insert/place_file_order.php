<?php
session_start();
include_once("../../includes/configuration.php");
include_once("../classes/OrderHandler.php");

$email = base64_decode($_SESSION["user_id"]);
$type = $_POST['operator'];
$orderType = "file";
$nid = $_POST['phone'];
$name = "";
$birthDate =  "";
$aboutOrder =  "";

// Create an instance of OrderHandler
$orderHandler = new OrderHandler($conn);
$response = $orderHandler->placeOrder($email, $type, $orderType, $nid, $name, $birthDate, $aboutOrder);
echo $response;
$conn->close();
