<?php
session_start();
if (!isset($_SESSION['temp_google_email'])) {
    header("Location: Register.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete your Profile</title>
    <link href="../output.css" rel="stylesheet">
    <style>
        .dd { position: relative; }
        .dd-btn {
            display: flex; align-items: center; justify-content: space-between;
            padding: 1rem; background: #f9fafb; border: 1px solid #e5e7eb;
            border-radius: .75rem; color: #111827; cursor: pointer; transition: .2s;
        }
        .dd-btn:hover, .dd-btn.open { background: #fff; border-color: #000; box-shadow: 0 0 0 2px #f3f4f6; }
        .dd-btn svg { transition: .2s; }
        .dd-btn.open svg { transform: rotate(180deg); }
        .dd-list {
            position: absolute; top: calc(100% + .5rem); left: 0; right: 0;
            background: #fff; border: 1px solid #e5e7eb; border-radius: .75rem;
            box-shadow: 0 10px 25px rgba(0,0,0,.1); opacity: 0; visibility: hidden;
            transform: translateY(-8px); transition: .2s; z-index: 50; overflow: hidden; padding: .5rem 0;
        }
        .dd-list.show { opacity: 1; visibility: visible; transform: translateY(0); }
        .dd-item { padding: .75rem 1.25rem; cursor: pointer; transition: .15s; }
        .dd-item:hover { background: #f3f4f6; }
        .dd-item.sel { background: #111827; color: #fff; }
    </style>
</head>
<body class="min-h-screen bg-gray-100 flex items-center justify-center p-6 antialiased">

<fieldset class="flex items-center justify-center w-full max-w-6xl h-[85vh] min-h-[600px] bg-gray-900 bg-cover bg-center rounded-[40px] shadow-2xl relative overflow-hidden"
    style="background-image: url('../assets/img/Background_Login.png')">

    <div class="absolute top-8 left-8 flex items-center gap-2 z-20">
        <div class="w-3.5 h-3.5 bg-white rounded-full"></div>
        <span class="font-bold text-lg tracking-tight text-white">Job Portal</span>
    </div>

    <form action="../auth/google_register.php" method="POST"
          class="w-full max-w-md p-10 bg-white rounded-[32px] shadow-2xl flex flex-col justify-center relative z-10">

        <div class="bg-gray-100 p-3 rounded-2xl w-fit mx-auto mb-5 border border-gray-200 shadow-sm">
            <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
        </div>

        <h1 class="font-bold text-3xl text-center text-gray-900 tracking-tight">One last step</h1>
        <p class="text-gray-500 text-center my-5 text-sm sm:text-base leading-relaxed">
            Please configure your profile information.
        </p>

        <input type="text" name="username" required placeholder="Full Name / Username"
               class="bg-gray-50 p-4 rounded-xl w-full border border-gray-200 text-gray-900 placeholder-gray-400 outline-none transition-all duration-200 focus:bg-white focus:border-black focus:ring-2 focus:ring-gray-100 mb-4">

        <input type="hidden" name="role" id="r" value="candidate">

        <div class="dd" id="d">
            <div class="dd-btn" onclick="t()" tabindex="0">
                <span id="t">Candidate</span>
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>
            <div class="dd-list" id="l">
                <div class="dd-item sel" onclick="s(this,'candidate')">Candidate</div>
                <div class="dd-item" onclick="s(this,'company')">Company</div>
            </div>
        </div>

        <button type="submit"
            class="w-full bg-black text-white font-semibold p-4 rounded-xl mt-8 text-center hover:bg-gray-800 transition-all shadow-md active:scale-[0.99]">
            Create My Account
        </button>
    </form>
</fieldset>

<script>
const b = document.querySelector('.dd-btn'), l = document.getElementById('l');
function t() { l.classList.toggle('show'); b.classList.toggle('open'); }
function s(el, v) {
    document.querySelectorAll('.dd-item').forEach(i => i.classList.remove('sel'));
    el.classList.add('sel');
    document.getElementById('t').textContent = el.textContent;
    document.getElementById('r').value = v;
    t();
}
document.addEventListener('click', e => {
    if (!e.target.closest('#d')) { l.classList.remove('show'); b.classList.remove('open'); }
});
</script>

</body>
</html>