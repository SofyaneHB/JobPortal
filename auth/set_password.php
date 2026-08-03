<?php
session_start();
require "../config/db.php";
require "../includes/functions.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    redirect("../Public/login.php");
    exit;
}

$user_id = $_POST['user_id'] ?? '';
$password = $_POST['password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';

if (!$user_id || !$password || !$confirm) {
    $_SESSION['error'] = "All fields are required";
    redirect("../Public/set_password.php");
    exit;
}

if ($password !== $confirm) {
    $_SESSION['error'] = "Passwords do not match";
    redirect("../Public/set_password.php");
    exit;
}

if (strlen($password) < 6) {
    $_SESSION['error'] = "Password must be at least 6 characters";
    redirect("../Public/set_password.php");
    exit;
}

try {
    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->execute([$hash, $user_id]);

    // Fetch full user data for session
    $stmt = $pdo->prepare("
        SELECT u.*, c.id AS company_id
        FROM users u
        LEFT JOIN companies c ON c.user_id = u.id
        WHERE u.id = ?
        LIMIT 1
    ");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    unset($_SESSION['temp_user_id'], $_SESSION['temp_user_email']);

    $_SESSION['user_id']    = $user['id'];
    $_SESSION['user_name']  = $user['full_name'];
    $_SESSION['user_role']  = strtolower(trim($user['role']));
    $_SESSION['company_id'] = $user['company_id'] ?? null;

    if ($_SESSION['user_role'] === 'company' && !$_SESSION['company_id']) {
        $stmt = $pdo->prepare("INSERT INTO companies (company_name, user_id) VALUES (?, ?)");
        $stmt->execute([$user['full_name'] . " Company", $user['id']]);
        $_SESSION['company_id'] = $pdo->lastInsertId();
    }

    set_flash("success", "Password set! Welcome " . $user['full_name']);

    if ($_SESSION['user_role'] === 'admin') {
        header("Location: ../admin/dashboard.php");
    } elseif ($_SESSION['user_role'] === 'company') {
        header("Location: ../company/dashboard.php");
    } else {
        header("Location: ../candidate/dashboard.php");
    }
    exit;

} catch (Exception $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
    redirect("../Public/set_password.php");
    exit;
}