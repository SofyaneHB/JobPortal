<?php
session_start();
require "../config/db.php"; 
require "../includes/functions.php"; 

$code = $_GET['code'] ?? null;

if (!$code) {
    set_flash("error", "Google authentication canceled.");
    header("Location: ../Public/Register.php");
    exit;
}

$client_id = getenv('GOOGLE_CLIENT_ID');
$client_secret = getenv('GOOGLE_CLIENT_SECRET');
$redirect_uri = "http://localhost/Projet_Stage/auth/google_callback.php";

// Exchange code for access token
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://oauth2.googleapis.com/token");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'code' => $code,
    'client_id' => $client_id,
    'client_secret' => $client_secret,
    'redirect_uri' => $redirect_uri,
    'grant_type' => 'authorization_code'
]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = json_decode(curl_exec($ch), true);
curl_close($ch);

$access_token = $response['access_token'] ?? null;

if (!$access_token) {
    set_flash("error", "Failed to obtain access token.");
    header("Location: ../Public/Register.php");
    exit;
}

// 2. Get user info from Google
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://www.googleapis.com/oauth2/v3/userinfo");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $access_token
]);
$google_user = json_decode(curl_exec($ch), true);
curl_close($ch);

$email = $google_user['email'] ?? null;
$google_name = $google_user['name'] ?? 'User'; 

if (!$email) {
    set_flash("error", "Failed to fetch user email from Google.");
    header("Location: ../Public/Register.php");
    exit;
}

// 3. Check if user already exists
$stmt = $pdo->prepare("
    SELECT u.*, c.id AS company_id 
    FROM users u 
    LEFT JOIN companies c ON c.user_id = u.id 
    WHERE u.email = ? 
    LIMIT 1
");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    // ========== FIX: Force password set if NULL ==========
    if (empty($user['password']) || $user['password'] === null) {
        $_SESSION['temp_user_id'] = $user['id'];
        $_SESSION['temp_user_email'] = $user['email'];
        header("Location: ../Public/set_password.php");
        exit;
    }

    $_SESSION['user_id']    = $user['id'];
    $_SESSION['user_name']  = $user['full_name'];
    $_SESSION['user_role']  = strtolower(trim($user['role']));
    $_SESSION['company_id'] = $user['company_id'] ?? null;

    set_flash("success", "Welcome back " . $user['full_name']);

    if ($_SESSION['user_role'] === 'admin') {
        header("Location: ../admin/dashboard.php");
    } elseif ($_SESSION['user_role'] === 'company') {
        header("Location: ../company/dashboard.php");
    } else {
        header("Location: ../candidate/dashboard.php");
    }
    exit;
}

// 4. New user — store temp data and redirect to complete signup
$_SESSION['temp_google_email'] = $email;
$_SESSION['temp_google_name'] = $google_name;
header("Location: ../Public/complete_google_signup.php");
exit;