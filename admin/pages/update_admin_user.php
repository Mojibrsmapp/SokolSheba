<?php
include('../includes/configuration.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST['reg_token'] as $id => $reg_token) {
        $whatsapp_group = $_POST['whatsapp_group'][$id];

        // Update query - removed the $token field
        $sql = "UPDATE admin_user SET reg_token = ?, whatsapp_group = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $reg_token, $whatsapp_group, $id); // Bind the parameters (reg_token and whatsapp_group)
        $stmt->execute();
    }
    $stmt->close();
    $conn->close();
    header("Location: ../createLink.php?update=success");
}