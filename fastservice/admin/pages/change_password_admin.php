<?php
session_start();
include_once("../includes/configuration.php");

// এডমিন লগইন যাচাই
if (!isset($_SESSION['admin_token'])) {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token_user = $_SESSION['admin_token'];
    $currentPassword = trim($_POST['currentPassword'] ?? '');
    $newPassword = trim($_POST['newPassword'] ?? '');
    $confirmPassword = trim($_POST['confirmPassword'] ?? '');

    // ফাঁকা ফিল্ড যাচাই
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        header("Location: ../profile.php?msg=error&text=" . urlencode("সবগুলো তথ্য সঠিকভাবে পূরণ করুন!"));
        exit();
    }

    // নতুন পাসওয়ার্ড নিশ্চিতকরণ যাচাই
    if ($newPassword !== $confirmPassword) {
        header("Location: ../profile.php?msg=error&text=" . urlencode("নতুন পাসওয়ার্ড এবং কনফার্ম পাসওয়ার্ড মিলেনি!"));
        exit();
    }

    // এডমিন অ্যাকাউন্ট অনুসন্ধান
    $stmt = $conn->prepare("SELECT id, password FROM admin_user WHERE token = ? LIMIT 1");
    if (!$stmt) {
        header("Location: ../profile.php?msg=error&text=" . urlencode("ডাটাবেজ সংযোগে ত্রুটি!"));
        exit();
    }

    $stmt->bind_param("s", $token_user);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        $admin_id = $admin['id'];
        $db_password = $admin['password'];

        // MD5 হ্যাশ যাচাই
        $hashed_current = md5($currentPassword);

        // বর্তমান পাসওয়ার্ড সঠিক আছে কিনা পরীক্ষা
        if ($hashed_current !== $db_password && $currentPassword !== $db_password) {
            header("Location: ../profile.php?msg=error&text=" . urlencode("বর্তমান পাসওয়ার্ড সঠিক নয়!"));
            exit();
        }

        // নতুন পাসওয়ার্ড MD5 হ্যাশ করে আপডেট করা
        $hashed_new = md5($newPassword);
        $update_stmt = $conn->prepare("UPDATE admin_user SET password = ? WHERE id = ?");
        $update_stmt->bind_param("si", $hashed_new, $admin_id);

        if ($update_stmt->execute()) {
            $update_stmt->close();
            $stmt->close();
            header("Location: ../profile.php?msg=success&text=" . urlencode("পাসওয়ার্ড সফলভাবে পরিবর্তন করা হয়েছে!"));
            exit();
        } else {
            $update_stmt->close();
            $stmt->close();
            header("Location: ../profile.php?msg=error&text=" . urlencode("পাসওয়ার্ড আপডেট করতে সমস্যা হয়েছে!"));
            exit();
        }

    } else {
        $stmt->close();
        header("Location: ../profile.php?msg=error&text=" . urlencode("এডমিন অ্যাকাউন্ট খুঁজে পাওয়া যায়নি!"));
        exit();
    }
} else {
    header("Location: ../profile.php");
    exit();
}