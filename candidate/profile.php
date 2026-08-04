<?php
session_start();

require_once "../config/db.php";
require_once "../includes/functions.php";

require_login(['candidate']);

$user_id = $_SESSION['user_id'];

// Fetch User Data
$stmt = $pdo->prepare("SELECT full_name, email, phone, address, country, skills FROM users WHERE id = ? LIMIT 1");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['full_name' => '', 'email' => '', 'phone' => '', 'address' => '', 'country' => '', 'skills' => ''];

// Fetch Notifications Count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
$stmt->execute([$user_id]);
$unread_count = (int) $stmt->fetchColumn();
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
    <link rel="icon" type="image/png" href="../assets/img/logo_jp.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="flex bg-slate-950 text-slate-100 min-h-screen">

    <!-- SIDEBAR -->
    <aside class="w-64 border-r border-slate-900 bg-slate-950/40 h-screen fixed flex flex-col justify-between z-40">
        <div>
            <div class="p-5 flex items-center gap-3 border-b border-slate-900">
                <div class="w-9 h-9 bg-gradient-to-tr from-emerald-500 to-cyan-500 text-white flex items-center justify-center font-bold text-sm rounded-xl">
                    <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
                </div>
                <div>
                    <div class="font-bold text-sm text-slate-200">Sofyane_HB</div>
                    <div class="text-[10px] text-emerald-400 font-semibold uppercase tracking-wider">Candidate Space</div>
                </div>
            </div>
            <nav class="p-4 space-y-1.5 text-xs font-medium">
                <a href="dashboard.php" class="flex items-center px-3 py-2.5 text-slate-400 hover:text-slate-200 hover:bg-slate-900/40 rounded-xl transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    Dashboard
                </a>
                <a href="../jobs/index.php" class="flex items-center px-3 py-2.5 text-slate-400 hover:text-slate-200 hover:bg-slate-900/40 rounded-xl transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    Job Offers
                </a>
                <a href="applications.php" class="flex items-center px-3 py-2.5 text-slate-400 hover:text-slate-200 hover:bg-slate-900/40 rounded-xl transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    My Applications
                </a>
                <a href="profile.php" class="flex items-center px-3 py-2.5 bg-emerald-600/10 border border-emerald-500/20 text-emerald-400 rounded-xl font-semibold">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    My Profile
                </a>
            </nav>
        </div>
        <div class="p-4 border-t border-slate-900">
            <div class="text-xs font-bold text-slate-300 truncate"><?= htmlspecialchars($user['full_name']) ?></div>
            <div class="text-[10px] text-slate-500 truncate mt-0.5"><?= htmlspecialchars($user['email']) ?></div>
        </div>
    </aside>

    <!-- MAIN -->
    <main class="ml-64 flex-1">
        <nav class="border-b border-slate-900 bg-slate-950/40 backdrop-blur-md sticky top-0 z-30">
            <div class="flex items-center justify-between px-8 py-3">
                <div class="text-xs text-slate-500 font-medium">My Profile</div>
                <div class="flex items-center gap-4">
                    <a href="dashboard.php" class="relative p-2 text-slate-400 hover:text-slate-200 transition rounded-lg hover:bg-slate-900/40">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"></path>
                            <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"></path>
                        </svg>
                        <?php if ($unread_count > 0): ?>
                        <span class="absolute -top-0.5 -right-0.5 w-5 h-5 bg-rose-500 text-white text-[10px] font-bold flex items-center justify-center rounded-full animate-pulse">
                            <?= $unread_count > 9 ? '9+' : $unread_count ?>
                        </span>
                        <?php endif; ?>
                    </a>
                    <div class="flex items-center gap-2 pl-4 border-l border-slate-900">
                        <div class="w-8 h-8 bg-gradient-to-tr from-emerald-500 to-cyan-500 text-white flex items-center justify-center font-bold text-xs rounded-lg">
                            <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <div class="p-8 md:p-12 max-w-3xl">
            <div class="mb-8">
                <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-white">My Profile</h1>
            </div>
            
            <?php display_flash(); ?>

            <div class="bg-slate-900/20 border border-slate-900 rounded-2xl p-6 md:p-8">
                <form method="POST" action="update_profile.php" class="space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Full Name</label>
                            <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-slate-200 focus:border-emerald-500 transition">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Email</label>
                            <input type="email" value="<?= htmlspecialchars($user['email']) ?>" disabled class="w-full bg-slate-950/50 border border-slate-800 rounded-xl px-4 py-3 text-sm text-slate-500 cursor-not-allowed">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Phone Number</label>
                            <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-slate-200 focus:border-emerald-500 transition">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Country</label>
                            <input type="text" name="country" value="<?= htmlspecialchars($user['country'] ?? '') ?>" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-slate-200 focus:border-emerald-500 transition">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Address</label>
                        <textarea name="address" rows="2" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-slate-200 focus:border-emerald-500 transition resize-none"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Skills</label>
                        <textarea name="skills" rows="3" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-slate-200 focus:border-emerald-500 transition resize-none"><?= htmlspecialchars($user['skills'] ?? '') ?></textarea>
                    </div>
                    <div class="flex justify-end pt-2">
                        <button type="submit" class="bg-emerald-600 hover:bg-emerald-500 text-white px-8 py-3 rounded-xl text-xs font-bold uppercase tracking-wider transition shadow-lg shadow-emerald-600/10">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>