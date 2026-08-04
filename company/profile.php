<?php
session_start();

require_once "../config/db.php";
require_once "../includes/functions.php";

require_login(['company']);

$user_id = $_SESSION['user_id'];
$company_id = $_SESSION['company_id'];

$stmt = $pdo->prepare("SELECT company_name, logo, description FROM companies WHERE id = ? LIMIT 1");
$stmt->execute([$company_id]);
$company_data = $stmt->fetch(PDO::FETCH_ASSOC);
$display_name = $company_data['company_name'] ?? 'Company';

// --- Handle Form Submission ---
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $company_name = clean_input($_POST['company_name'] ?? '');
    $description = clean_input($_POST['description'] ?? '');
    $logo = clean_input($_POST['logo'] ?? '');

    if (empty($company_name)) {
        set_flash("error", "Company name is required");
    } else {
        $stmt = $pdo->prepare("
            UPDATE companies 
            SET company_name = ?, description = ?, logo = ? 
            WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([$company_name, $description, $logo, $company_id, $user_id]);
        
        set_flash("success", "Profile updated successfully!");
        redirect("profile.php");
        exit;
    }
}

// --- FETCH FRESH DATA ---
$stmt = $pdo->prepare("
    SELECT c.*, u.email 
    FROM companies c
    JOIN users u ON c.user_id = u.id
    WHERE c.id = ?
");
$stmt->execute([$company_id]);
$company = $stmt->fetch(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="flex bg-slate-950 text-slate-100 min-h-screen selection:bg-indigo-500 selection:text-white">

    <!-- Sidebar Recruteur (Consistent with add_job.php) -->
    <aside class="w-64 border-r border-slate-900 bg-slate-950/40 backdrop-blur-md h-screen fixed flex flex-col justify-between z-40">
        <div>
            <div class="p-5 flex items-center gap-3 border-b border-slate-900">
                <div class="w-9 h-9 bg-gradient-to-tr from-indigo-600 to-cyan-500 text-white flex items-center justify-center font-bold text-sm rounded-xl shadow-md shadow-indigo-600/20">
                    <?= strtoupper(substr($company['company_name'] ?? 'C', 0, 1)) ?>
                </div>
                <div>
                    <div class="font-bold text-sm text-slate-200 tracking-tight"><?= htmlspecialchars($display_name) ?></div>
                    <div class="text-[10px] text-indigo-400 font-semibold uppercase tracking-wider">Recuriter Dashboard</div>
                </div>
            </div>
            
            <nav class="p-4 space-y-1.5 text-xs font-medium">
                <a href="dashboard.php" class="flex items-center px-3 py-2.5 text-slate-400 hover:text-slate-200 hover:bg-slate-900/40 rounded-xl transition">Dashboard</a>
                <a href="profile.php" class="flex items-center px-3 py-2.5 bg-indigo-600/10 border border-indigo-500/20 text-indigo-400 rounded-xl font-semibold">Company Profile</a>
                <a href="add_job.php" class="flex items-center px-3 py-2.5 text-slate-400 hover:text-slate-200 hover:bg-slate-900/40 rounded-xl transition">Publish a Job Offer</a>
                <a href="my_jobs.php" class="flex items-center px-3 py-2.5 text-slate-400 hover:text-slate-200 hover:bg-slate-900/40 rounded-xl transition">Manage Jobs</a>
                <a href="applicants.php" class="flex items-center px-3 py-2.5 text-slate-400 hover:text-slate-200 hover:bg-slate-900/40 rounded-xl transition">Applications Received</a>
            </nav>
        </div>
        
        <!-- Pied de la Sidebar -->
        <div class="p-4 border-t border-slate-900 bg-slate-950/60 backdrop-blur-md">
            <div class="text-xs font-bold text-slate-300 truncate"><?= htmlspecialchars($company['company_name'] ?? 'Company') ?></div>
            <div class="text-[10px] text-slate-500 truncate mt-0.5"><?= htmlspecialchars($company['email'] ?? '') ?></div>
        </div>
    </aside>

    <!-- Zone Principale -->
    <main class="ml-64 flex-1 p-8 md:p-12 max-w-4xl">
        
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-white">Company Profile</h1>
            <p class="text-slate-400 text-xs md:text-sm mt-1">Update company information</p>
        </div>

        <?php display_flash(); ?>

        <!-- Formulaire Premium (Match exact avec add_job.php) -->
        <div class="bg-slate-900/20 border border-slate-900 p-6 md:p-8 rounded-2xl backdrop-blur-sm">
            <form method="POST" action="" class="space-y-6">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Company Name</label>
                        <input type="text" name="company_name" value="<?= htmlspecialchars($company['company_name'] ?? '') ?>" required 
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-slate-200 placeholder-slate-600 focus:outline-none focus:border-indigo-500 transition">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Company Website</label>
                        <input type="text" name="logo" value="<?= htmlspecialchars($company['logo'] ?? '') ?>" placeholder="https://..." 
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-slate-200 placeholder-slate-600 focus:outline-none focus:border-indigo-500 transition">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Company Overview</label>
                    <textarea name="description" rows="5" placeholder="Présentez votre entreprise, votre culture technique (Agile, DevOps) et vos secteurs d'activité..." 
                              class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-slate-200 placeholder-slate-600 focus:outline-none focus:border-indigo-500 transition leading-relaxed resize-none"><?= htmlspecialchars($company['description'] ?? '') ?></textarea>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" class="w-full md:w-auto text-xs font-bold uppercase tracking-wider bg-indigo-600 hover:bg-indigo-500 text-white px-8 py-3.5 rounded-xl transition shadow-lg shadow-indigo-600/10 active:scale-[0.98]">
                        Save Updates
                    </button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>