<?php
session_start();

require_once "../config/db.php";
require_once "../includes/functions.php";

require_login(['candidate']);

$user_id = $_SESSION['user_id'];

//Fetch Candidate Info

$stmt = $pdo->prepare("SELECT full_name, email, phone, address, country, skills FROM users WHERE id = ? LIMIT 1");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['full_name' => 'Candidate', 'email' => '', 'phone' => '', 'address' => '', 'country' => '', 'skills' => ''];

// Fetch Notifications 
$stmt = $pdo->prepare("
    SELECT id, message, is_read, created_at 
    FROM notifications 
    WHERE user_id = ? 
    ORDER BY created_at DESC
");
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

$unread_count = count(array_filter($notifications, fn($n) => !$n['is_read']));

// Mark all as read 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_all_read'])) {
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$user_id]);
    redirect("dashboard.php");
    exit;
}

// Mark single as read

if (isset($_GET['read_id'])) {
    $read_id = (int) $_GET['read_id'];
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
    $stmt->execute([$read_id, $user_id]);
    redirect("dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord Candidat | Sofyane_HB</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .notif-dropdown { display: none; position: absolute; right: 0; top: 100%; margin-top: 0.5rem; }
        .notif-dropdown.show { display: block; }
        .notif-unread { background: rgba(99, 102, 241, 0.08); border-left: 3px solid #6366f1; }
    </style>
</head>
<body class="flex bg-slate-950 text-slate-100 min-h-screen">

    <aside class="w-64 border-r border-slate-900 bg-slate-950/40 h-screen fixed flex flex-col justify-between z-40">
        <div>
            <div class="p-5 flex items-center gap-3 border-b border-slate-900">
                <div class="w-9 h-9 bg-gradient-to-tr from-emerald-500 to-cyan-500 text-white flex items-center justify-center font-bold text-sm rounded-xl">
                    <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
                </div>
                <div>
                    <div class="font-bold text-sm text-slate-200">Sofyane_HB_Portal</div>
                    <div class="text-[10px] text-emerald-400 font-semibold uppercase tracking-wider">Espace Candidat</div>
                </div>
            </div>
            <nav class="p-4 space-y-1.5 text-xs font-medium">
                <a href="dashboard.php" class="flex items-center px-3 py-2.5 bg-emerald-600/10 border border-emerald-500/20 text-emerald-400 rounded-xl font-semibold">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    Dashboard
                </a>
                <a href="../jobs/index.php" class="flex items-center px-3 py-2.5 text-slate-400 hover:text-slate-200 hover:bg-slate-900/40 rounded-xl transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    Offres d'emploi
                </a>
                <a href="applications.php" class="flex items-center px-3 py-2.5 text-slate-400 hover:text-slate-200 hover:bg-slate-900/40 rounded-xl transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Mes Candidatures
                </a>
                <a href="profile.php" class="flex items-center px-3 py-2.5 text-slate-400 hover:text-slate-200 hover:bg-slate-900/40 rounded-xl transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    Mon Profil
                </a>
                
               <a href="../auth/logout.php" class="flex items-center px-3 py-2.5 text-rose-400 hover:text-rose-300 hover:bg-rose-950/20 border border-transparent hover:border-rose-900/40 rounded-xl transition mt-4">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Se déconnecter
                </a>
            </nav>
        </div>
        
        <div class="p-4 border-t border-slate-900 flex items-center justify-between gap-2 bg-slate-950/20">
            <div class="min-w-0 flex-1">
                <div class="text-xs font-bold text-slate-300 truncate"><?= htmlspecialchars($user['full_name']) ?></div>
                <div class="text-[10px] text-slate-500 truncate mt-0.5"><?= htmlspecialchars($user['email']) ?></div>
            </div>
            <a href="../auth/logout.php" title="Déconnexion" class="p-1.5 rounded-lg bg-slate-900 border border-slate-800 text-slate-400 hover:text-rose-400 hover:border-rose-900/50 transition shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
            </a>
        </div>
    </aside>

    <main class="ml-64 flex-1">

        

        <nav class="border-b border-slate-900 bg-slate-950/40 backdrop-blur-md sticky top-0 z-30">
            <div class="flex items-center justify-between px-8 py-3">
                <div class="text-xs text-slate-500 font-medium">
                    <?= date('l, d F Y') ?>
                </div>

                <div class="flex items-center gap-4">
                    <div class="relative">
                        <button onclick="toggleNotifDropdown()" class="relative p-2 text-slate-400 hover:text-slate-200 transition rounded-lg hover:bg-slate-900/40">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"></path>
                                <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"></path>
                            </svg>
                            <?php if ($unread_count > 0): ?>
                            <span class="absolute -top-0.5 -right-0.5 w-5 h-5 bg-rose-500 text-white text-[10px] font-bold flex items-center justify-center rounded-full animate-pulse">
                                <?= $unread_count > 9 ? '9+' : $unread_count ?>
                            </span>
                            <?php endif; ?>
                        </button>

                        <div id="notifDropdown" class="notif-dropdown w-80 bg-slate-900 border border-slate-800 rounded-xl shadow-2xl shadow-black/50 overflow-hidden z-50">
                            <div class="px-4 py-3 border-b border-slate-800 flex items-center justify-between">
                                <span class="text-xs font-bold text-slate-200">Notifications</span>
                                <?php if ($unread_count > 0): ?>
                                <form method="POST" class="inline">
                                    <input type="hidden" name="mark_all_read" value="1">
                                    <button type="submit" class="text-[10px] text-indigo-400 hover:text-indigo-300">Tout marquer comme lu</button>
                                </form>
                                <?php endif; ?>
                            </div>
                            <div class="max-h-80 overflow-y-auto">
                                <?php if (empty($notifications)): ?>
                                    <div class="p-6 text-center text-slate-500 text-xs">
                                        <svg class="w-8 h-8 mx-auto mb-2 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                        Aucune notification
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($notifications as $notif): 
                                        $isUnread = !$notif['is_read'];
                                        $bgClass = $isUnread ? 'notif-unread' : '';
                                    ?>
                                    <a href="?read_id=<?= $notif['id'] ?>" class="block px-4 py-3 hover:bg-slate-800/50 transition <?= $bgClass ?> border-b border-slate-800/50 last:border-0">
                                        <div class="flex items-start gap-3">
                                            <div class="mt-1">
                                                <?php if ($isUnread): ?>
                                                    <div class="w-2 h-2 bg-indigo-500 rounded-full"></div>
                                                <?php else: ?>
                                                    <div class="w-2 h-2 bg-slate-600 rounded-full"></div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-xs text-slate-200 leading-relaxed <?= $isUnread ? 'font-semibold' : 'font-normal' ?>">
                                                    <?= htmlspecialchars($notif['message']) ?>
                                                </p>
                                                <p class="text-[10px] text-slate-500 mt-1">
                                                    <?= date('d M Y à H:i', strtotime($notif['created_at'])) ?>
                                                </p>
                                            </div>
                                        </div>
                                    </a>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 pl-4 border-l border-slate-900">
                        <div class="w-8 h-8 bg-gradient-to-tr from-emerald-500 to-cyan-500 text-white flex items-center justify-center font-bold text-xs rounded-lg">
                            <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
                        </div>
                        <div class="hidden md:block">
                            <div class="text-xs font-medium text-slate-200"><?= htmlspecialchars($user['full_name']) ?></div>
                            <div class="text-[10px] text-slate-500"><?= htmlspecialchars($user['email']) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <div class="p-8 md:p-12 max-w-5xl">

            <div class="mb-8">
                <div class="text-[11px] font-bold uppercase tracking-wider text-emerald-400 mb-1">Bienvenue</div>
                <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-white">Bonjour, <?= htmlspecialchars($user['full_name']) ?></h1>
                <p class="text-slate-400 text-xs md:text-sm mt-1">Voici vos notifications et mises à jour récentes.</p>
            </div>

            <?php display_flash(); ?>

            <div class="bg-slate-900/20 border border-slate-900 rounded-2xl p-6 md:p-8 mb-8 relative overflow-hidden">
                <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-emerald-500 via-cyan-500 to-emerald-500 opacity-60"></div>

                <div class="flex items-center gap-4 mb-6">
                    <div class="w-14 h-14 bg-gradient-to-tr from-emerald-500 to-cyan-500 text-white flex items-center justify-center font-bold text-xl rounded-2xl shadow-lg shadow-emerald-500/10">
                        <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-white"><?= htmlspecialchars($user['full_name']) ?></h2>
                        <p class="text-xs text-slate-500"><?= htmlspecialchars($user['email']) ?></p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                    <div class="flex items-center gap-3 bg-slate-950/50 border border-slate-800/50 rounded-xl p-3.5">
                        <div class="w-8 h-8 bg-emerald-500/10 rounded-lg flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        </div>
                        <div class="min-w-0">
                            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Téléphone</div>
                            <div class="text-sm font-medium text-slate-200 truncate"><?= !empty($user['phone']) ? htmlspecialchars($user['phone']) : '<span class="text-slate-600 italic text-xs">Non renseigné</span>' ?></div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 bg-slate-950/50 border border-slate-800/50 rounded-xl p-3.5">
                        <div class="w-8 h-8 bg-cyan-500/10 rounded-lg flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div class="min-w-0">
                            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Pays</div>
                            <div class="text-sm font-medium text-slate-200 truncate"><?= !empty($user['country']) ? htmlspecialchars($user['country']) : '<span class="text-slate-600 italic text-xs">Non renseigné</span>' ?></div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 bg-slate-950/50 border border-slate-800/50 rounded-xl p-3.5 md:col-span-2 lg:col-span-1">
                        <div class="w-8 h-8 bg-indigo-500/10 rounded-lg flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <div class="min-w-0">
                            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Adresse</div>
                            <div class="text-sm font-medium text-slate-200 truncate"><?= !empty($user['address']) ? htmlspecialchars($user['address']) : '<span class="text-slate-600 italic text-xs">Non renseigné</span>' ?></div>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 bg-slate-950/50 border border-slate-800/50 rounded-xl p-3.5 md:col-span-2 lg:col-span-3">
                        <div class="w-8 h-8 bg-amber-500/10 rounded-lg flex items-center justify-center shrink-0 mt-0.5">
                            <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Compétences</div>
                            <div class="text-sm font-medium text-slate-200 leading-relaxed">
                                <?php if (!empty($user['skills'])): ?>
                                    <?php 
                                    $skills_array = array_filter(array_map('trim', explode(',', $user['skills'])));
                                    foreach ($skills_array as $skill): 
                                    ?>
                                    <span class="inline-block bg-slate-800/50 border border-slate-700/50 text-slate-300 text-xs px-2.5 py-1 rounded-lg mr-2 mb-1"><?= htmlspecialchars(trim($skill)) ?></span>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <span class="text-slate-600 italic text-xs">Aucune compétence renseignée</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mb-6 flex items-center justify-between">
                <h2 class="text-sm font-bold uppercase tracking-wider text-slate-200 flex items-center gap-2">
                    <span class="w-1 h-3 bg-indigo-500 rounded-full"></span>
                    Notifications
                    <?php if ($unread_count > 0): ?>
                    <span class="text-[10px] bg-rose-500 text-white px-1.5 py-0.5 rounded-full font-bold"><?= $unread_count ?></span>
                    <?php endif; ?>
                </h2>
                <?php if ($unread_count > 0): ?>
                <form method="POST" class="inline">
                    <input type="hidden" name="mark_all_read" value="1">
                    <button type="submit" class="text-[11px] font-bold text-indigo-400 hover:text-indigo-300 transition">
                        Tout marquer comme lu
                    </button>
                </form>
                <?php endif; ?>
            </div>

            <div class="bg-slate-900/20 border border-slate-900 rounded-2xl overflow-hidden">
                <?php if (empty($notifications)): ?>
                    <div class="p-12 text-center text-slate-500 text-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="mx-auto mb-4 text-slate-700">
                            <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"></path>
                            <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"></path>
                        </svg>
                        <p>Aucune notification pour l'instant.</p>
                        <p class="text-xs mt-2 text-slate-600">Les notifications apparaîtront ici quand une entreprise répondra à votre candidature.</p>
                    </div>
                <?php else: ?>
                    <div class="divide-y divide-slate-900/60">
                        <?php foreach ($notifications as $notif): 
                            $isUnread = !$notif['is_read'];
                            $rowClass = $isUnread ? 'notif-unread' : 'hover:bg-slate-900/30';
                        ?>
                        <div class="p-4 md:px-6 md:py-4 transition <?= $rowClass ?> flex items-start gap-4">
                            <div class="mt-1">
                                <?php if ($isUnread): ?>
                                    <div class="w-2.5 h-2.5 bg-indigo-500 rounded-full animate-pulse"></div>
                                <?php else: ?>
                                    <div class="w-2.5 h-2.5 bg-slate-700 rounded-full"></div>
                                <?php endif; ?>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-slate-200 leading-relaxed <?= $isUnread ? 'font-semibold' : 'font-normal' ?>">
                                    <?= htmlspecialchars($notif['message']) ?>
                                </p>
                                <p class="text-[11px] text-slate-500 mt-1">
                                    <?= date('d M Y à H:i', strtotime($notif['created_at'])) ?>
                                </p>
                            </div>
                            <?php if ($isUnread): ?>
                                <a href="?read_id=<?= $notif['id'] ?>" 
                                   class="shrink-0 text-[11px] font-bold text-indigo-400 hover:text-indigo-300 transition px-3 py-1.5 rounded-lg border border-indigo-500/20 bg-indigo-500/5 hover:bg-indigo-500/10">
                                    Marquer comme lu
                                </a>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </main>

    <script>
    function toggleNotifDropdown() {
        const dropdown = document.getElementById('notifDropdown');
        dropdown.classList.toggle('show');
    }

    document.addEventListener('click', function(e) {
        const bell = e.target.closest('button[onclick="toggleNotifDropdown()"]');
        const dropdown = document.getElementById('notifDropdown');
        if (!bell && dropdown.classList.contains('show')) {
            dropdown.classList.remove('show');
        }
    });
    </script>
</body>
</html>