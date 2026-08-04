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

</head>
<body class="flex h-full bg-slate-50/50 text-slate-900 antialiased">

<aside class="w-64 bg-slate-900 h-screen fixed inset-y-0 left-0 flex flex-col justify-between border-r border-slate-800">
    <div>
        <div class="h-16 flex items-center px-6 border-b border-slate-800/60">
            <div class="flex items-center gap-2.5">
                <div class="p-1.5 bg-indigo-600 rounded-lg text-white">
                    <svg xmlns="http://w3.org" viewBox="0 0 24 24" width="23" height="23" fill="currentColor">
                        <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                        <path d="M19.33 10.53l.94-1.63-1.63-.94-1.6.5c-.26-.18-.54-.33-.84-.46l-.3-1.8h-1.89l-.3 1.8c-.3.13-.58.28-.84.46l-1.6-.5-1.63.94.94 1.63c-.09.31-.15.63-.15.97s.06.66.15.97l-.94 1.63 1.63.94 1.6-.5c.26.18.54.33.84.46l.3 1.8h1.89l.3-1.8c.3-.13.58-.28.84-.46l1.6.5 1.63-.94-.94-1.63c.09-.31.15-.63.15-.97s-.06-.66-.15-.97zM12 15.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/>
                    </svg>

                </div>
                <span class="font-semibold text-lg tracking-tight text-white">Admin Dashboard</span>
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

    </aside>

    <!-- Le contenu Principal -->
     
<div class="ml-64 flex-1 flex flex-col min-h-screen mt-10">

    <main class="flex-1 p-8 max-w-7xl w-full mx-auto">
        <div class="mb-8">
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Welcome, Admin</h1>
            <p class="text-sm text-slate-500 mt-1"> A quick overview of your application's current statistics</p>
        </div>

        <!-- Les 3 Grilles (Total Users, Total Companies, Total Jobs) -->

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-16">
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