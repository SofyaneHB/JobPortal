<?php
session_start();
$error = $_SESSION['error'] ?? '';
$success = $_SESSION['success'] ?? '';
unset($_SESSION['error'], $_SESSION['success']);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="icon" type="image/png" href="../assets/img/logo_jp.png">
    <link href="../output.css" rel="stylesheet">
</head>

<body class="min-h-screen bg-gray-100 flex items-center justify-center p-6 antialiased">

<fieldset class="flex items-center justify-center w-full max-w-6xl h-[85vh] min-h-[600px] bg-gray-900 bg-cover bg-center rounded-[40px] shadow-2xl relative overflow-hidden"
    style="background-image: url('../assets/img/Background_Login.png');">

    <div class="absolute top-8 left-8 flex items-center gap-2 z-20">
        <div class="w-3.5 h-3.5 bg-white rounded-full"></div>
        <span class="font-bold text-lg tracking-tight text-white">Job Portal</span>
    </div>

    <form action="../auth/forgot_password.php" method="POST"
          class="w-full max-w-md p-10 bg-white rounded-[32px] shadow-2xl flex flex-col justify-center relative z-10">

        <h1 class="font-bold text-3xl text-center text-gray-900 tracking-tight">Reset password</h1>
        <p class="text-gray-500 text-center my-5 text-sm">Enter your email and we'll send you a link</p>

        <?php if ($error): ?>
            <p class="text-red-500 text-sm text-center mb-4"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <?php if ($success): ?>
            <p class="text-green-600 text-sm text-center mb-4"><?php echo htmlspecialchars($success); ?></p>
        <?php endif; ?>

        <input type="email" name="email" placeholder="Enter Email" required
            class="bg-gray-50 p-4 rounded-xl w-full mb-6 border border-gray-200 text-gray-900 placeholder-gray-400 outline-none focus:bg-white focus:border-black transition-all">

        <button type="submit"
            class="w-full bg-black text-white font-semibold p-4 rounded-xl text-center hover:bg-gray-800 transition-all shadow-md active:scale-[0.99] cursor-pointer">
            Send Reset Link
        </button>

        <p class="text-center text-sm text-gray-500 mt-5">
            Remember your password? 
            <a href="login.php" class="font-bold text-gray-900 hover:underline">Log in</a>
        </p>
    </form>

</fieldset>

</body>
</html>