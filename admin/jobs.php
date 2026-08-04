<?php

session_start();
require_once "../config/db.php";
require_once "../includes/functions.php";

require_login(['admin']);

// Traitement de Suppression d'un Job

if (isset($_GET['delete'])) {

    $stmt = $pdo->prepare("DELETE FROM jobs WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    set_flash("success", "Job deleted successfully");
    redirect("jobs.php");

}

// Récupération des jobs avec leurs entreprises

$jobs = $pdo->query("
    SELECT j.*, c.company_name 
    FROM jobs j 
    JOIN companies c ON j.company_id = c.id
    ORDER BY j.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Récupération des infos de l'admin

$stmt = $pdo->prepare("SELECT full_name, email FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en" class="h-full">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Jobs</title>
    <link rel="icon" type="image/png" href="../assets/img/logo_jp.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
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
            <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800/50 font-medium rounded-xl transition-all duration-200 group">
                <i data-lucide="grid" class="w-4 h-4 text-slate-500 group-hover:text-slate-300 transition-colors"></i>
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
            <a href="jobs.php" class="flex items-center gap-3 px-4 py-3 bg-indigo-600 text-white font-medium rounded-xl shadow-sm transition-all duration-200">
                <i data-lucide="briefcase" class="w-4 h-4 opacity-90"></i>
                <span>Jobs</span>
            </a>
        </nav>
    </div>
</aside>

<div class="ml-64 flex-1 flex flex-col min-h-screen">
    <main class="flex-1 p-8 max-w-7xl w-full mx-auto">
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Manage Jobs</h1>
                <p class="text-sm text-slate-500 mt-1">Review all the Jobs Offers</p>
            </div>

            <!-- Dynamic Counter Badge -->

            <div class="bg-slate-100 text-slate-700 px-3.5 py-1.5 rounded-lg text-xs font-semibold border border-slate-200 shadow-sm">
                Total Jobs: <?= count($jobs) ?>
            </div>
        </div>

        <!-- Tableau -->

        <div class="bg-white rounded-xl border border-slate-200/70 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200/70 text-slate-400 text-[11px] font-semibold uppercase tracking-wider">
                            <th class="px-8 py-4 w-5/12">Job Title</th>
                            <th class="px-8 py-4 w-3/12">Company</th>
                            <th class="px-8 py-4 w-2/12">Status</th>
                            <th class="px-8 py-4 text-right w-2/12">Management</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        <?php foreach($jobs as $job): ?>
                        <tr class="hover:bg-slate-50/50 transition-colors duration-150">


                            <!-- Job Title -->
                            <td class="px-8 py-5">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-amber-50 text-amber-600 rounded-lg flex items-center justify-center font-bold text-xs uppercase border border-amber-100/50">
                                        <?= substr(htmlspecialchars($job['title']), 0, 1) ?>
                                    </div>
                                    <span class="font-semibold text-slate-800 text-sm tracking-tight">
                                        <?= htmlspecialchars($job['title']) ?>
                                    </span>
                                </div>
                            </td>

                            <!-- Company Name -->
                            <td class="px-8 py-5">
                                <div class="flex items-center gap-2 text-slate-600 font-medium">
                                    <i data-lucide="building-2" class="w-3.5 h-3.5 text-slate-400"></i>
                                    <span><?= htmlspecialchars($job['company_name']) ?></span>
                                </div>
                            </td>

                            <!-- Status Badge -->
                            <td class="px-8 py-5">
                                <?php 
                                    $status = htmlspecialchars($job['status'] ?? '');
                                    if ($status === 'active') {
                                        echo '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-100">Active</span>';
                                    } elseif ($status === 'closed') {
                                        echo '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-slate-50 text-slate-600 border border-slate-200">Closed</span>';
                                    } else {
                                        echo '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-slate-50 text-slate-600 border border-slate-200">' . ucfirst($status) . '</span>';
                                    }
                                ?>
                            </td>

                            <!-- Sleek, Low-Profile Delete Button -->
                            <td class="px-8 py-5 text-right">
                                <a href="?delete=<?= $job['id'] ?>" 
                                   onclick="return confirm('Are you sure you want to delete this job?');"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-slate-500 hover:text-red-600 hover:bg-red-50 hover:border-red-200 rounded-lg border border-transparent transition-all duration-200">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    <span>Delete</span>
                                </a>
                            </td>


                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<!-- Render Icons Execution -->
<script src="../assets/script.js"></script>

</body>
</html>