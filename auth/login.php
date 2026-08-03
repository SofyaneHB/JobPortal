<?php
session_start();

require "../config/db.php";
require "../includes/functions.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = clean_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $_SESSION['error'] = "Please fill in all fields.";
        $_SESSION['old_email'] = $email;
        redirect("../Public/login.php");
        exit;
    }

    try {
        $stmt = $pdo->prepare("
            SELECT u.*, c.id AS company_id
            FROM users u
            LEFT JOIN companies c ON c.user_id = u.id
            WHERE u.email = ?
            LIMIT 1
        ");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $_SESSION['error'] = "We couldn't find an account with that email.";
            $_SESSION['old_email'] = $email;
            redirect("../Public/login.php");
            exit;
        }

        if (empty($user['password']) || $user['password'] === null) {
            $_SESSION['error'] = "This account was created with Google. Please use 'Continue with Google'.";
            $_SESSION['old_email'] = $email;
            redirect("../Public/login.php");
            exit;
        }

        if (!password_verify($password, $user['password'])) {
            $_SESSION['error'] = "Incorrect password. Please try again.";
            $_SESSION['old_email'] = $email;
            redirect("../Public/login.php");
            exit;
        }

        $_SESSION['user_id']    = $user['id'];
        $_SESSION['user_name']  = $user['full_name'];
        $_SESSION['user_role']  = strtolower(trim($user['role']));
        $_SESSION['company_id'] = $user['company_id'] ?? null;

        if ($_SESSION['user_role'] === 'company' && !$_SESSION['company_id']) {
            $stmt = $pdo->prepare("INSERT INTO companies (company_name, user_id) VALUES (?, ?)");
            $stmt->execute([$user['full_name'] . " Company", $user['id']]);
            $_SESSION['company_id'] = $pdo->lastInsertId();
        }

        set_flash("success", "Welcome " . $user['full_name']);

        if ($_SESSION['user_role'] === 'admin') {
            header("Location: ../admin/dashboard.php");
            exit;
        }
        if ($_SESSION['user_role'] === 'company') {
            header("Location: ../company/dashboard.php");
            exit;
        }
        if ($_SESSION['user_role'] === 'candidate') {
            header("Location: ../candidate/dashboard.php");
            exit;
        }

        header("Location: ../index.php");
        exit;

    } catch (Exception $e) {
        $_SESSION['error'] = "Something went wrong. Please try again.";
        $_SESSION['old_email'] = $email;
        redirect("../Public/login.php");
        exit;
    }
}