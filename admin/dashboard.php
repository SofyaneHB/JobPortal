<?php
session_start();
require_once "../config/db.php";
require_once "../includes/functions.php";

// Vérification des permissions : seuls les utilisateurs avec le rôle 'admin' peuvent accéder au Dashboard
require_login(['admin']);

//  Récuperation des données de l'admin
$stmt = $pdo->prepare("SELECT full_name, email FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

// Récuperation des statistques globales
$stats = [
    'users' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'companies' => $pdo->query("SELECT COUNT(*) FROM companies")->fetchColumn(),
    'jobs' => $pdo->query("SELECT COUNT(*) FROM jobs")->fetchColumn()
];
?>

<!DOCTYPE html>
<html lang="en" class="h-full">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="shortcut icon" href="../assets/img/logo_jp_nogap.png">
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Lucide Icons — Bibliothèque d'icônes SVG légères -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>


</head>
<body class="flex h-full bg-slate-50/50 text-slate-900 antialiased">

<aside class="w-64 bg-slate-900 h-screen fixed inset-y-0 left-0 flex flex-col justify-between border-r border-slate-800">
    <div>
        <div class="h-16 flex items-center px-6 border-b border-slate-800/60">
            <div class="flex items-center gap-2.5">
                <div class="p-1.5 bg-indigo-600 rounded-lg text-white">
                     <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                </div>
                <span class="font-semibold text-lg tracking-tight text-white">Admin Panel</span>
            </div>
        </div>
        
                <!-- Menu de navigation -->

        <nav class="p-4 space-y-1">
            <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 bg-indigo-600 text-white font-medium rounded-xl shadow-sm transition-all duration-200">
                <i data-lucide="grid" class="w-4 h-4 opacity-90"></i>
                <span>Dashboard</span>
            </a>
            <a href="users.php" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800/50 font-medium rounded-xl transition-all duration-200 group">
                <i data-lucide="users" class="w-4 h-4 text-slate-500 group-hover:text-slate-300 transition-colors"></i>
                <span>Users</span>
            </a>
            <a href="companies.php" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800/50 font-medium rounded-xl transition-all duration-200 group">
                <i data-lucide="building-2" class="w-4 h-4 text-slate-500 group-hover:text-slate-300 transition-colors"></i>
                <span>Companies</span>
            </a>
            <a href="jobs.php" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800/50 font-medium rounded-xl transition-all duration-200 group">
                <i data-lucide="briefcase" class="w-4 h-4 text-slate-500 group-hover:text-slate-300 transition-colors"></i>
                <span>Jobs</span>
            </a>
        </nav>
    </div>

        <!-- Footer de la sidebar — Profil de l'admin connecté -->

    <div class="p-6 border-t border-slate-800/50 bg-slate-950/40 backdrop-blur-sm flex flex-col items-center justify-center transition-all duration-300 group hover:bg-slate-950/60">
        <div class="w-full text-center max-w-[200px]">
            <div class="text-sm font-semibold text-slate-100 tracking-wide leading-snug antialiased uppercase transition-colors duration-200 group-hover:text-white">
                <?= htmlspecialchars($admin['full_name'] ?? 'Admin') ?>
               </div>
            <div class="text-[11px] font-medium text-slate-400/80 tracking-wider font-mono mt-1 select-all truncate transition-colors duration-200 group-hover:text-slate-300">
                <?= htmlspecialchars($admin['email'] ?? '') ?>
            </div>
        </div>
    </div>
    </aside>

    <!-- Le contenu Principal -->
     
<div class="ml-64 flex-1 flex flex-col min-h-screen">
    <header class="h-16 border-b border-slate-200/60 bg-white/80 backdrop-blur-md sticky top-0 z-10 flex items-center justify-between px-8">
        <div class="flex items-center gap-2 text-sm text-slate-500">
            <span class="text-slate-800 font-medium">Admin Dashboard</span>
        </div>
    </header>

    <main class="flex-1 p-8 max-w-7xl w-full mx-auto">
        <div class="mb-8">
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Welcome back, Admin</h1>
            <p class="text-sm text-slate-500 mt-1">Here is a quick overview of your application's current statistics</p>
        </div>

        <!-- Les 3 Grilles (Total Users, Total Companies, Total Jobs) -->

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white p-6 rounded-2xl border border-slate-200/60 shadow-sm hover:shadow-md transition-shadow duration-200 flex items-center justify-between group">
                <div class="space-y-2">
                    <span class="text-sm font-medium text-slate-500 tracking-wide uppercase">Total Users</span>
                    <p class="text-3xl font-bold text-slate-900 tracking-tight">
                        <?= $stats['users'] ?>
                    </p>
                </div>
                <div class="p-3.5 bg-indigo-50 text-indigo-600 rounded-xl group-hover:scale-105 transition-transform duration-200">
                    <i data-lucide="users" class="w-6 h-6"></i>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl border border-slate-200/60 shadow-sm hover:shadow-md transition-shadow duration-200 flex items-center justify-between group">
                <div class="space-y-2">
                    <span class="text-sm font-medium text-slate-500 tracking-wide uppercase">Total Companies</span>
                    <p class="text-3xl font-bold text-slate-900 tracking-tight">
                        <?= $stats['companies'] ?>
                    </p>
                </div>
                <div class="p-3.5 bg-emerald-50 text-emerald-600 rounded-xl group-hover:scale-105 transition-transform duration-200">
                    <i data-lucide="building-2" class="w-6 h-6"></i>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl border border-slate-200/60 shadow-sm hover:shadow-md transition-shadow duration-200 flex items-center justify-between group">
                <div class="space-y-2">
                    <span class="text-sm font-medium text-slate-500 tracking-wide uppercase">Total Jobs</span>
                    <p class="text-3xl font-bold text-slate-900 tracking-tight">
                        <?= $stats['jobs'] ?>
                    </p>
                </div>
                <div class="p-3.5 bg-amber-50 text-amber-600 rounded-xl group-hover:scale-105 transition-transform duration-200">
                    <i data-lucide="briefcase" class="w-6 h-6"></i>
                </div>
            </div>
        </div>
    </main>
</div>

<script src="../assets/script.js"></script>
</body>
</html>