<?php 
session_start();

$errorMessage = $_SESSION['error'] ?? '';
$old_email = $_SESSION['old_email'] ?? '';
$hasError = !empty($errorMessage);

unset($_SESSION['error'], $_SESSION['old_email']);

$google_auth_url = "../auth/google_redirect.php"; 
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login page</title>
    <link href="../output.css" rel="stylesheet"> 
</head>

<body class="min-h-screen bg-gray-100 flex items-center justify-center p-6 antialiased">

<!-- Main Wrapper -->
<fieldset class="flex items-center justify-center w-full max-w-6xl h-[85vh] min-h-[600px] bg-gray-900 bg-cover bg-center rounded-[40px] shadow-2xl relative overflow-hidden"
    style="background-image: url('../assets/img/Background_Login.png');">

    <!-- Logo -->
    <div class="absolute top-8 left-8 flex items-center gap-2 z-20">
        <div class="w-3.5 h-3.5 bg-white rounded-full"></div>
        <span class="font-bold text-lg tracking-tight text-white">Job Portal</span>
    </div>

    <!-- LOGIN FORM CARD -->
    <form action="../auth/login.php" method="POST"
          class="w-full max-w-md p-10 bg-white rounded-[32px] shadow-2xl flex flex-col justify-center relative z-10">



        <h1 class="font-bold text-3xl text-center text-gray-900 tracking-tight">Sign in </h1>

        <p class="text-gray-500 text-center my-5 text-sm sm:text-base leading-relaxed">
            Enter your email and password to continue
        </p>

        <!-- ========== MESSAGE D'ERREUR PROFESSIONNEL ========== -->
        <?php if ($hasError): ?>
            <div class="flex items-start gap-3 bg-red-50 border-l-4 border-red-500 rounded-r-lg p-4 mb-5 animate-[fadeIn_0.3s_ease-out]">
                <!-- Icône warning -->
                <div class="flex-shrink-0 w-5 h-5 rounded-full bg-red-500 flex items-center justify-center mt-0.5">
                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01"/>
                    </svg>
                </div>
                <!-- Texte -->
                <p class="text-sm font-medium text-red-800 leading-relaxed">
                    <?= htmlspecialchars($errorMessage) ?>
                </p>
            </div>
        <?php endif; ?>

        <!-- EMAIL -->
        <div class="mb-4">
            <input
                type="email"
                name="email"
                placeholder="Enter Email"
                value="<?= htmlspecialchars($old_email) ?>"
                class="bg-gray-50 p-4 rounded-xl w-full border text-gray-900 placeholder-gray-400 outline-none transition-all duration-200 
                    <?= $hasError ? 'border-red-300 bg-red-50/30 focus:bg-white focus:border-red-400 focus:ring-2 focus:ring-red-100' : 'border-gray-200 focus:bg-white focus:border-black focus:ring-2 focus:ring-gray-100' ?>"
                required
            >
        </div>

        <!-- PASSWORD -->
        <div>
            <input
                type="password"
                name="password"
                placeholder="Enter Password"
                class="bg-gray-50 p-4 rounded-xl w-full border border-gray-200 text-gray-900 placeholder-gray-400 outline-none focus:bg-white focus:border-black transition-all duration-200 focus:ring-2 focus:ring-gray-100"
                required
            >
        </div>

        <!-- Forgot Password -->
        <div class="flex justify-end mt-3">
            <a href="forgot_password.php" class="text-sm font-medium text-gray-500 hover:text-black hover:underline transition-colors">
                Forgot password?
            </a>
        </div>

        <!-- SUBMIT -->
        <button type="submit"
            class="w-full bg-black text-white font-semibold p-4 rounded-xl mt-8 text-center hover:bg-gray-800 cursor-pointer transition-all shadow-md active:scale-[0.99]">
            Get Started
        </button>

    </form>

</fieldset>

</body>
</html>