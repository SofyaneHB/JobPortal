<?php
session_start();
require "../config/db.php";
require "../includes/functions.php";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_SESSION['temp_google_email'])) {
    
    $username = clean_input($_POST['username'] ?? '');
    $role = clean_input($_POST['role'] ?? 'candidate');
    $email = $_SESSION['temp_google_email'];

    if (empty($username) || empty($role)) {
        set_flash("error", "All fields are required");
        header("Location: ../Public/complete_google_signup.php");
        exit;
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO users (full_name, email, role)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$username, $email, $role]);
        
        $user_id = $pdo->lastInsertId();

        // Auto-create company if needed
        if (strtolower(trim($role)) === 'company') {
            $stmt = $pdo->prepare("
                INSERT INTO companies (company_name, user_id)
                VALUES (?, ?)
            ");
            $stmt->execute([
                $username . " Company",
                $user_id
            ]);
        }

        unset($_SESSION['temp_google_email']);
        unset($_SESSION['temp_google_name']);
        
        // ========== FIX: Redirect to set password instead of dashboard ==========
        $_SESSION['temp_user_id'] = $user_id;
        $_SESSION['temp_user_email'] = $email;
        header("Location: ../Public/set_password.php");
        exit;

    } catch (PDOException $e) {
        set_flash("error", "Database Error: " . $e->getMessage());
        header("Location: ../Public/complete_google_signup.php");
        exit;
    }
} else {
    header("Location: ../Public/Register.php");
    exit;
}