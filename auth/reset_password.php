<?php
session_start();
require "../config/db.php";
require "../includes/functions.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    redirect("../Public/login.php");
    exit;
}

$token = $_POST['token'] ?? '';
$password = $_POST['password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';

if (!$token || !$password || !$confirm) {
    set_flash("error", "All fields are required");
    redirect("../Public/reset_password.php?token=" . urlencode($token));
    exit;
}

if ($password !== $confirm) {
    set_flash("error", "Passwords do not match");
    redirect("../Public/reset_password.php?token=" . urlencode($token));
    exit;
}

if (strlen($password) < 6) {
    set_flash("error", "Password must be at least 6 characters");
    redirect("../Public/reset_password.php?token=" . urlencode($token));
    exit;
}

$stmt = $pdo->prepare("
    SELECT pr.user_id 
    FROM password_resets pr 
    WHERE pr.token = ? AND pr.expires_at > NOW() 
    LIMIT 1
");
$stmt->execute([$token]);
$reset = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reset) {
    set_flash("error", "Invalid or expired token");
    redirect("../Public/forgot_password.php");
    exit;
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$upd = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
$upd->execute([$hash, $reset['user_id']]);

$del = $pdo->prepare("DELETE FROM password_resets WHERE user_id = ?");
$del->execute([$reset['user_id']]);

set_flash("success", "Password updated! You can now log in.");
redirect("../Public/login.php");
exit;