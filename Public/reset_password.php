<?php
session_start();
require "../config/db.php";

$token = $_GET['token'] ?? '';
$error = $_SESSION['error'] ?? '';
$success = $_SESSION['success'] ?? '';
unset($_SESSION['error'], $_SESSION['success']);

$valid = false;
$email = '';

if ($token) {
    $stmt = $pdo->prepare("
        SELECT pr.*, u.email 
        FROM password_resets pr 
        JOIN users u ON u.id = pr.user_id 
        WHERE pr.token = ? AND pr.expires_at > NOW() 
        LIMIT 1
    ");
    $stmt->execute([$token]);
    $reset = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($reset) {
        $valid = true;
        $email = $reset['email'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link href="../output.css" rel="stylesheet">
</head>
<body class="min-h-screen bg-gray-100 flex items-center justify-center p-6 antialiased">

<fieldset class="flex items-center justify-center w-full max-w-6xl h-[85vh] min-h-[600px] bg-gray-900 bg-cover bg-center rounded-[40px] shadow-2xl relative overflow-hidden"
    style="background-image: url('../assets/img/Background_Login.png');">

    <div class="absolute top-8 left-8 flex items-center gap-2 z-20">
        <div class="w-3.5 h-3.5 bg-white rounded-full"></div>
        <span class="font-bold text-lg tracking-tight text-white">Job Portal</span>
    </div>

    <div class="w-full max-w-md p-10 bg-white rounded-[32px] shadow-2xl flex flex-col justify-center relative z-10">

        <?php if (!$valid): ?>
            <h1 class="font-bold text-3xl text-center text-gray-900 tracking-tight">Link expired</h1>
            <p class="text-gray-500 text-center my-5 text-sm">This reset link is invalid or has expired.</p>
            <a href="forgot_password.php" class="text-center text-sm font-bold text-gray-900 hover:underline">Request a new link</a>
        <?php else: ?>

            <h1 class="font-bold text-3xl text-center text-gray-900 tracking-tight">New password</h1>
            <p class="text-gray-500 text-center my-5 text-sm">For <?php echo htmlspecialchars($email); ?></p>

            <?php if ($error): ?>
                <p class="text-red-500 text-sm text-center mb-4"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
            <?php if ($success): ?>
                <p class="text-green-600 text-sm text-center mb-4"><?php echo htmlspecialchars($success); ?></p>
            <?php endif; ?>

<form action="../auth/reset_password.php" method="POST" autocomplete="off">
    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

    <!-- Add this hidden field so the browser knows which account -->
    <input type="text" name="username" value="<?php echo htmlspecialchars($email); ?>" 
           autocomplete="username" class="hidden" readonly>

    <input type="password" name="password" placeholder="New Password" required
        autocomplete="new-password"
        class="bg-gray-50 p-4 rounded-xl w-full mb-4 border border-gray-200 text-gray-900 placeholder-gray-400 outline-none focus:bg-white focus:border-black transition-all">

    <input type="password" name="confirm_password" placeholder="Confirm Password" required
        autocomplete="new-password"
        class="bg-gray-50 p-4 rounded-xl w-full mb-6 border border-gray-200 text-gray-900 placeholder-gray-400 outline-none focus:bg-white focus:border-black transition-all">

    <button type="submit"
        class="w-full bg-black text-white font-semibold p-4 rounded-xl text-center hover:bg-gray-800 transition-all shadow-md active:scale-[0.99] cursor-pointer">
        Update Password
    </button>
</form>
        <?php endif; ?>
    </div>

</fieldset>

</body>
</html>