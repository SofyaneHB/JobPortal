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
    <link rel="icon" type="image/png" href="../assets/img/logo_jp.png">
    <link href="../output.css" rel="stylesheet"> 
    <link rel="stylesheet" href="../assets/style.css">
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
            class="bg-gray-50 p-4 rounded-xl w-full mb-4 border border-gray-200 text-gray-900 placeholder-gray-400 outline-none focus:bg-white focus:border-black transition-all mb-3"
            required
        >

        <!-- Email -->
        <input
            type="email"
            name="email"
            placeholder="Enter Email"
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
        <input type="hidden" name="role" id="r" value="" required>
            <div class="dd mb-4" id="d">
                <div class="dd-btn" onclick="t()" tabindex="0">
                    <span id="t" class="text-gray-400">Select your role</span>
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                </div>
            <div class="dd-list" id="l">
                <div class="dd-item" onclick="s(this,'candidate')">Candidate</div>
                <div class="dd-item" onclick="s(this,'company')">Company</div>
            </div>
        </div>

        <!-- Password -->
        <input
            type="password"
            name="password"
            placeholder="Enter Password"
            class="bg-gray-50 p-4 rounded-xl w-full mb-4 border text-gray-900 placeholder-gray-400 outline-none focus:bg-white focus:border-black transition-all <?php echo ($errorField === 'password') ? 'border-red-500' : 'border-gray-200'; ?>"
            required
        >

        <!-- Confirm Password -->
        <input
            type="password"
            name="confirm_password"
            placeholder="Confirm your Password"
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

        <!-- Already have an account? Log in or continue with Google -->
        <div class="flex items-center justify-center gap-3 mt-6 text-sm">
            <p class="text-gray-500">
                Already have an account? 
                <a href="login.php" class="font-bold text-gray-900 hover:underline">Log in</a> 
                or continue with
            </p>
            <a href="<?php echo $google_auth_url; ?>" 
               class="w-9 h-9 flex items-center justify-center rounded-full border border-gray-200 bg-white hover:bg-gray-50 transition-all active:scale-95 shadow-sm">
                <i class="fab fa-google text-red-500 text-base"></i>
            </a>
        </div>

    </form>

</fieldset>

</body>

<script>
function t() {
    document.getElementById('l').classList.toggle('show');
    document.querySelector('.dd-btn').classList.toggle('open');
}
function s(el, v) {
    document.getElementById('r').value = v;
    document.getElementById('t').textContent = el.textContent;
    document.getElementById('t').classList.remove('text-gray-400');
    document.querySelectorAll('.dd-item').forEach(i => i.classList.remove('sel'));
    el.classList.add('sel');
    t();
}
document.addEventListener('click', function(e) {
    if (!document.getElementById('d').contains(e.target)) {
        document.getElementById('l').classList.remove('show');
        document.querySelector('.dd-btn').classList.remove('open');
    }
});
</script>

</html>




