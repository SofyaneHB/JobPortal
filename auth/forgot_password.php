<?php
session_start();
require "../config/db.php";
require "../includes/functions.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    redirect("../Public/forgot_password.php");
    exit;
}

$email = clean_input($_POST['email'] ?? '');

if (!$email) {
    set_flash("error", "Please enter your email");
    redirect("../Public/forgot_password.php");
    exit;
}

$stmt = $pdo->prepare("SELECT id, full_name, email FROM users WHERE email = ? LIMIT 1");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    set_flash("success", "If this email exists, you will receive a reset link shortly.");
    redirect("../Public/forgot_password.php");
    exit;
}

$token = bin2hex(random_bytes(32));
$expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

$del = $pdo->prepare("DELETE FROM password_resets WHERE user_id = ?");
$del->execute([$user['id']]);

$ins = $pdo->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)");
$ins->execute([$user['id'], $token, $expires]);

$reset_link = "http://localhost/Projet_Stage/Public/reset_password.php?token=" . $token;

$smtp_host      = getenv('SMTP_HOST') ?: 'smtp.gmail.com';
$smtp_user      = getenv('SMTP_USER') ?: '';
$smtp_pass      = getenv('SMTP_PASS') ?: '';
$smtp_port      = getenv('SMTP_PORT') ?: 587;
$smtp_from      = getenv('SMTP_FROM') ?: 'sofianhabbouch625@gmail.com';
$smtp_from_name = getenv('SMTP_FROM_NAME') ?: 'Job Portal';

if (empty($smtp_user) || empty($smtp_pass)) {
    error_log("SMTP not configured. Reset link for {$user['email']}: {$reset_link}");
    set_flash("success", "DEV MODE - Reset link: " . $reset_link);
    redirect("../Public/forgot_password.php");
    exit;
}

require '../vendor/autoload.php';

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = $smtp_host;
    $mail->SMTPAuth   = true;
    $mail->Username   = $smtp_user;
    $mail->Password   = $smtp_pass;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = (int)$smtp_port;
    $mail->CharSet    = 'UTF-8';

    // Mask sender: display "Job Portal", reply goes to noreply
    $mail->setFrom($smtp_from, $smtp_from_name);
    $mail->addReplyTo('noreply@jobportal.com', 'Job Portal');
    
    $mail->addAddress($user['email'], $user['full_name']);

    $mail->isHTML(true);
    $mail->Subject = 'Password Reset - Job Portal';

    $mail->Body = '
        <div style="max-width:480px;margin:0 auto;font-family:Segoe UI,Roboto,Helvetica,Arial,sans-serif;color:#1a1a1a;">
            <div style="padding:40px 32px;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;">
                <div style="text-align:center;margin-bottom:28px;">
                    <h2 style="margin:12px 0 0;font-size:20px;font-weight:700;letter-spacing:-0.5px;">Job Portal</h2>
                </div>
                <h1 style="font-size:22px;font-weight:700;margin:0 0 8px;">Reset your password</h1>
                <p style="color:#6b7280;font-size:15px;line-height:1.6;margin:0 0 28px;">
                    Hi ' . htmlspecialchars($user['full_name']) . ',<br><br>
                    We received a request to reset your password. Click the button below to choose a new one.
                </p>
                <div style="text-align:center;margin:32px 0;">
                    <a href="' . $reset_link . '" 
                       style="display:inline-block;padding:14px 32px;background:#000;color:#fff;text-decoration:none;border-radius:10px;font-size:15px;font-weight:600;">
                        Reset Password
                    </a>
                </div>
                <p style="color:#9ca3af;font-size:13px;line-height:1.6;margin:0;">
                    This link expires in <strong>1 hour</strong>. If you did not request this, you can safely ignore this email.
                </p>
            </div>
            <p style="text-align:center;color:#9ca3af;font-size:12px;margin-top:20px;">
                &copy; Job Portal. All rights reserved.
            </p>
        </div>
    ';

    $mail->AltBody = "Hi " . $user['full_name'] . ",\n\nWe received a request to reset your password. Visit this link:\n" . $reset_link . "\n\nThis link expires in 1 hour. Ignore if you didn't request this.";

    $mail->send();

    set_flash("success", "If this email exists, you will receive a reset link shortly.");
    redirect("../Public/forgot_password.php");
    exit;

} catch (Exception $e) {
    error_log("PHPMailer error for {$user['email']}: " . $e->getMessage());
    set_flash("error", "Failed to send email. Please try again later.");
    redirect("../Public/forgot_password.php");
    exit;
}