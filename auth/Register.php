<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require "../config/db.php";
require "../includes/functions.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $fullname = clean_input($_POST["fullname"] ?? '');
    $email = clean_input($_POST["email"] ?? '');
    $password = $_POST["password"] ?? '';
    $confirm_password = $_POST["confirm_password"] ?? '';
    $role = clean_input($_POST["role"] ?? 'candidate');

    if (!in_array($role, ['candidate', 'company'], true)) {
        $role = 'candidate';
    }

    $_SESSION['old_fullname'] = $fullname;
    $_SESSION['old_email'] = $email;

    if (empty($fullname) || empty($email) || empty($password)) {
        $_SESSION['error'] = "All fields are required";
        header("Location: ../Public/Register.php?error=empty");
        exit;
    }

    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match";
        $_SESSION['error_field'] = "confirm_password";
        header("Location: ../Public/Register.php?error=password_dontmatch");
        exit;
    }

    // Remplacement de la fonction custom par une requête native claire si nécessaire
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $_SESSION['error'] = "Email already exists";
        $_SESSION['error_field'] = "email";
        header("Location: ../Public/Register.php?error=email_exists");
        exit;
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO users (full_name, email, password, role)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([
            $fullname,
            $email,
            password_hash($password, PASSWORD_DEFAULT),
            $role
        ]);

        unset($_SESSION['old_fullname'], $_SESSION['old_email']);
        header("Location: ../Public/login.php?signup=success");
        exit;

    } catch (PDOException $e) {
        $_SESSION['error'] = "Database Error: " . $e->getMessage();
        header("Location: ../Public/Register.php?error=db");
        exit;
    }
}