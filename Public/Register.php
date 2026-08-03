<?php 
session_start();

$error = $_GET['error'] ?? '';
$errorField = $_SESSION['error_field'] ?? '';
$errorMessage = $_SESSION['error'] ?? '';
$old_fullname = $_SESSION['old_fullname'] ?? '';
$old_email = $_SESSION['old_email'] ?? '';

unset($_SESSION['error'], $_SESSION['error_field'], $_SESSION['old_fullname'], $_SESSION['old_email']);

$google_auth_url = "../auth/google_redirect.php"; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Page</title>
    <link href="../output.css" rel="stylesheet"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body class="min-h-screen bg-gray-100 flex items-center justify-center p-6 antialiased">

<fieldset class="flex items-center justify-center w-full max-w-6xl h-[85vh] min-h-[600px] bg-gray-900 bg-cover bg-center rounded-[40px] shadow-2xl relative overflow-hidden"
    style="background-image: url('../assets/img/Background_Login.png');">

    <div class="absolute top-8 left-8 flex items-center gap-2 z-20">
        <div class="w-3.5 h-3.5 bg-white rounded-full"></div>
        <span class="font-bold text-lg tracking-tight text-white">Job Portal</span>
    </div>

    <form action="../auth/Register.php" method="POST"
          class="w-full max-w-md p-10 bg-white rounded-[32px] shadow-2xl flex flex-col justify-center relative z-10">

        <h1 class="font-bold text-3xl text-center text-gray-900 tracking-tight">Create an account</h1>

        <!-- Full Name -->
        <input
            type="text"
            name="fullname"
            placeholder="Username / Full Name"
            value="<?php echo htmlspecialchars($old_fullname); ?>"
            class="bg-gray-50 p-4 rounded-xl w-full mb-4 border border-gray-200 text-gray-900 placeholder-gray-400 outline-none focus:bg-white focus:border-black transition-all"
            required
        >

        <!-- Email -->
        <input
            type="email"
            name="email"
            placeholder="Email Address"
            value="<?php echo htmlspecialchars($old_email); ?>"
            class="bg-gray-50 p-4 rounded-xl w-full border text-gray-900 placeholder-gray-400 outline-none focus:bg-white focus:border-black transition-all <?php echo ($errorField === 'email') ? 'border-red-500' : 'border-gray-200'; ?>"
            required
        >
        <?php if ($error === 'email_exists'): ?>
            <p class="text-red-500 text-xs mt-1.5 mb-4 ml-1"><?php echo $errorMessage; ?></p>
        <?php else: ?>
            <div class="mb-4"></div>
        <?php endif; ?>

        <!-- Role Select -->
        <div class="bg-gray-50 p-4 rounded-xl w-full mb-4 border border-gray-200 text-gray-900 outline-none focus-within:bg-white focus-within:border-black transition-all">
            <select name="role" id="role" required
                class="w-full bg-transparent text-gray-900 outline-none cursor-pointer appearance-none">
                <option value="" disabled selected>Select your role</option>
                <option value="candidate">Candidate</option>
                <option value="company">Company</option>
            </select>
        </div>

        <!-- Password -->
        <input
            type="password"
            name="password"
            placeholder="Password"
            class="bg-gray-50 p-4 rounded-xl w-full mb-4 border text-gray-900 placeholder-gray-400 outline-none focus:bg-white focus:border-black transition-all <?php echo ($errorField === 'password') ? 'border-red-500' : 'border-gray-200'; ?>"
            required
        >

        <!-- Confirm Password -->
        <input
            type="password"
            name="confirm_password"
            placeholder="Confirm Password"
            class="bg-gray-50 p-4 rounded-xl w-full border text-gray-900 placeholder-gray-400 outline-none focus:bg-white focus:border-black transition-all <?php echo ($errorField === 'confirm_password') ? 'border-red-500' : 'border-gray-200'; ?>"
            required
        >
        <?php if ($error === 'password_dontmatch'): ?>
            <p class="text-red-500 text-xs mt-1.5 ml-1"><?php echo $errorMessage; ?></p>
        <?php endif; ?>

        <!-- Submit -->
        <button type="submit"
            class="w-full bg-black text-white font-semibold p-4 rounded-xl mt-8 text-center hover:bg-gray-800 transition-all shadow-md active:scale-[0.99] cursor-pointer">
            Sign Up
        </button>

        <p class="text-center text-sm text-gray-500 mt-5">
            Already have an account? 
            <a href="login.php" class="font-bold text-gray-900 hover:underline">Log in</a>
        </p>



        <!-- Google -->
        <a href="<?php echo $google_auth_url; ?>" class="block w-full">
            <button type="button"
                class="w-full bg-gray-50 border border-gray-200 text-gray-700 font-medium p-4 rounded-xl text-center hover:bg-white hover:border-gray-300 transition-all flex items-center justify-center gap-2 active:scale-[0.99] cursor-pointer">
                <i class="fab fa-google text-red-500 text-lg"></i>
                <span>Continue with Google</span>
            </button>
        </a>

    </form>

</fieldset>

</body>
</html>