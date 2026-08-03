<?php

session_start();
require_once "../config/db.php";
require_once "../includes/functions.php";

require_login(['admin']);

// Traitement de Suppression d'un User

if (isset($_GET['delete'])) {

    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    set_flash("success", "User deleted successfully");
    redirect("users.php");

}

$users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT full_name, email FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="flex h-full bg-slate-50/50 text-slate-900 antialiased">

<!-- Sidebar Navigation -->
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
        
        <nav class="p-4 space-y-1">
            <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800/50 font-medium rounded-xl transition-all duration-200 group">
                <i data-lucide="grid" class="w-4 h-4 text-slate-500 group-hover:text-slate-300 transition-colors"></i>
                <span>Dashboard</span>
            </a>
            <a href="users.php" class="flex items-center gap-3 px-4 py-3 bg-indigo-600 text-white font-medium rounded-xl shadow-sm transition-all duration-200">
                <i data-lucide="users" class="w-4 h-4 opacity-90"></i>
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

    <!-- Sidebar Footer Profile Details -->
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

<!-- Content Section Area -->
<div class="ml-64 flex-1 flex flex-col min-h-screen">
    <!-- Main Top Sticky Navbar -->
    <header class="h-16 border-b border-slate-200/60 bg-white/80 backdrop-blur-md sticky top-0 z-10 flex items-center justify-between px-8">
        <div class="flex items-center gap-2 text-sm text-slate-500">
            <span class="text-slate-800 font-medium">Users</span>
        </div>
    </header>

    <!-- App Data Elements -->
    <main class="flex-1 p-8 max-w-7xl w-full mx-auto">
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Manage Users</h1>
                <p class="text-sm text-slate-500 mt-1">Review permissions, monitor active registration metrics, or discard records.</p>
            </div>
            <!-- Dynamic Counter Badge -->
            <div class="bg-slate-100 text-slate-700 px-3.5 py-1.5 rounded-lg text-xs font-semibold border border-slate-200 shadow-sm">
                Total Users: <?= count($users) ?>
            </div>
        </div>
        
        <!-- Premium UI Table Layout -->
        <div class="bg-white rounded-xl border border-slate-200/70 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200/70 text-slate-400 text-[11px] font-semibold uppercase tracking-wider">
                            <th class="px-8 py-4 w-1/3">Username</th>
                            <th class="px-8 py-4 w-1/3">Email Address</th>
                            <th class="px-8 py-4">Role Status</th>
                            <th class="px-8 py-4 text-right">Management</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        <?php foreach($users as $user): ?>
                        <tr class="hover:bg-slate-50/50 transition-colors duration-150">
                            <!-- Name & Visual Profile Details -->
                            <td class="px-8 py-5">
                                <div class="font-medium text-slate-900 text-sm tracking-tight">
                                    <?= htmlspecialchars($user['full_name']) ?>
                                </div>
                            </td>
                            <!-- Email Detail (Using a clean look) -->
                            <td class="px-8 py-5">
                                <span class="text-slate-500 font-normal select-all">
                                    <?= htmlspecialchars($user['email']) ?>
                                </span>
                            </td>
                            <!-- Modern Rounded Pill Badge -->
                            <td class="px-8 py-5">
                                <?php 
                                    $role = htmlspecialchars($user['role'] ?? '');
                                    if ($role === 'admin') {
                                        echo '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-100">Admin</span>';
                                    } elseif ($role === 'company') {
                                        echo '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-100">Company</span>';
                                    } elseif ($role === 'candidate') {
                                        echo '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-sky-50 text-sky-700 border border-sky-100">Candidate</span>';
                                    } else {
                                        echo '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-slate-50 text-slate-600 border border-slate-200">None</span>';
                                    }
                                ?>
                            </td>
                            <!-- Sleek, Low-Profile Delete Button -->
                            <td class="px-8 py-5 text-right">
                                <a href="?delete=<?= $user['id'] ?>" 
                                   onclick="return confirm('Are you sure you want to delete this user?');"
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
<script>
    lucide.createIcons();
</script>
</body>
</html>