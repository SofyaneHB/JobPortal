<?php
session_start();

if (empty($_SESSION['temp_user_id'])) {
    header("Location: login.php");
    exit;
}

$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Password</title>
    <link href="../output.css" rel="stylesheet">
</head>
<body class="min-h-screen bg-gray-100 flex items-center justify-center p-6 antialiased">

<fieldset class="flex items-center justify-center w-full max-w-6xl h-[85vh] min-h-[600px] bg-gray-900 bg-cover bg-center rounded-[40px] shadow-2xl relative overflow-hidden"
    style="background-image: url('../assets/img/Background_Login.png');">

    <div class="absolute top-8 left-8 flex items-center gap-2 z-20">
        <div class="w-3.5 h-3.5 bg-white rounded-full"></div>
        <span class="font-bold text-lg tracking-tight text-white">Job Portal</span>
    </div>

    <form action="../auth/set_password.php" method="POST"
          class="w-full max-w-md p-10 bg-white rounded-[32px] shadow-2xl flex flex-col justify-center relative z-10">

        <h1 class="font-bold text-3xl text-center text-gray-900 tracking-tight">Set your password</h1>
        <p class="text-gray-500 text-center my-5 text-sm">Create a password so you can log in with email next time.</p>

        <?php if ($error): ?>
            <p class="text-red-500 text-sm text-center mb-4"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <input type="hidden" name="user_id" value="<?php echo $_SESSION['temp_user_id']; ?>">

        <input type="password" name="password" placeholder="New Password" required
            class="bg-gray-50 p-4 rounded-xl w-full mb-4 border border-gray-200 text-gray-900 placeholder-gray-400 outline-none focus:bg-white focus:border-black transition-all">

        <input type="password" name="confirm_password" placeholder="Confirm Password" required
            class="bg-gray-50 p-4 rounded-xl w-full mb-6 border border-gray-200 text-gray-900 placeholder-gray-400 outline-none focus:bg-white focus:border-black transition-all">

        <button type="submit"
            class="w-full bg-black text-white font-semibold p-4 rounded-xl text-center hover:bg-gray-800 transition-all shadow-md active:scale-[0.99] cursor-pointer">
            Save Password
        </button>
    </form>

</fieldset>

</body>
</html>