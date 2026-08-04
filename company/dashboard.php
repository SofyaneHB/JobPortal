<?php
session_start();

require_once "../config/db.php";
require_once "../includes/functions.php";

require_login(['company']);

/* IDs */
$company_id = $_SESSION['company_id'] ?? null;
$user_id = $_SESSION['user_id'] ?? null;

if (!$company_id || !$user_id) {
    set_flash("error", "Company session missing");
    redirect("../Public/login.php");
    exit;
}

/* FETCH COMPANY DATA (Name, Logo, Description) */
$stmt = $pdo->prepare("SELECT company_name, logo, description FROM companies WHERE id = ? LIMIT 1");
$stmt->execute([$company_id]);
$company_data = $stmt->fetch(PDO::FETCH_ASSOC);

$display_name = $company_data['company_name'] ?? 'Company';
$display_logo = $company_data['logo'] ?? '';
$display_desc = $company_data['description'] ?? '';

/* FETCH USER EMAIL */
$stmt = $pdo->prepare("SELECT email FROM users WHERE id = ? LIMIT 1");
$stmt->execute([$user_id]);
$user_email = $stmt->fetchColumn();

/* STATS */
$stmt = $pdo->prepare("SELECT COUNT(*) FROM jobs WHERE company_id = ?");
$stmt->execute([$company_id]);
$active_jobs = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM applications a JOIN jobs j ON a.job_id = j.id WHERE j.company_id = ?");
$stmt->execute([$company_id]);
$total_applicants = $stmt->fetchColumn();

/* RECENT JOBS */
$stmt = $pdo->prepare("SELECT j.*, COUNT(a.id) AS applicants FROM jobs j LEFT JOIN applications a ON j.id = a.job_id WHERE j.company_id = ? GROUP BY j.id ORDER BY j.created_at DESC LIMIT 5");
$stmt->execute([$company_id]);
$recent_jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* RECENT APPLICANTS */
$stmt = $pdo->prepare("SELECT u.full_name, j.title, a.status FROM applications a JOIN jobs j ON a.job_id = j.id JOIN users u ON a.candidate_id = u.id WHERE j.company_id = ? ORDER BY a.applied_at DESC LIMIT 5");
$stmt->execute([$company_id]);
$recent_applicants = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Company Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="flex bg-slate-950 text-slate-100 min-h-screen selection:bg-indigo-500 selection:text-white">

    <aside class="w-64 border-r border-slate-900 bg-slate-950/40 backdrop-blur-md h-screen fixed flex flex-col justify-between z-40">
        <div>
            <div class="p-5 flex items-center gap-3 border-b border-slate-900">
                <div class="w-9 h-9 bg-gradient-to-tr from-indigo-600 to-cyan-500 text-white flex items-center justify-center font-bold text-sm rounded-xl shadow-md shadow-indigo-600/20">
                    <?= strtoupper(substr($display_name, 0, 1)) ?>
                </div>
                <div>
                    <div class="font-bold text-sm text-slate-200 tracking-tight"><?= htmlspecialchars($display_name) ?></div>
                    <div class="text-[10px] text-indigo-400 font-semibold uppercase tracking-wider">Recuriter Dashboard</div>
                </div>
            </div>
            
            <nav class="p-4 space-y-1.5 text-xs font-medium">
                <a href="dashboard.php" class="flex items-center px-3 py-2.5 bg-indigo-600/10 border border-indigo-500/20 text-indigo-400 rounded-xl font-semibold">Dashboard</a>
                <a href="profile.php" class="flex items-center px-3 py-2.5 text-slate-400 hover:text-slate-200 hover:bg-slate-900/40 rounded-xl transition">Company Profile</a>
                <a href="add_job.php" class="flex items-center px-3 py-2.5 text-slate-400 hover:text-slate-200 hover:bg-slate-900/40 rounded-xl transition">Publish a Job Offer</a>
                <a href="my_jobs.php" class="flex items-center px-3 py-2.5 text-slate-400 hover:text-slate-200 hover:bg-slate-900/40 rounded-xl transition">Manage Jobs</a>
                <a href="applicants.php" class="flex items-center px-3 py-2.5 text-slate-400 hover:text-slate-200 hover:bg-slate-900/40 rounded-xl transition">Applocations Received</a>
            </nav>
        </div>
        
        <div class="p-4 border-t border-slate-900 bg-slate-950/60 backdrop-blur-md">
            <div class="text-xs font-bold text-slate-300 truncate"><?= htmlspecialchars($display_name) ?></div>
            <div class="text-[10px] text-slate-500 truncate mt-0.5"><?= htmlspecialchars($user_email) ?></div>
        </div>
    </aside>

    <main class="ml-64 flex-1 p-8 md:p-12 space-y-8 max-w-5xl">
        
        <div class="relative p-6 bg-slate-900/10 border border-slate-900 rounded-2xl overflow-hidden">
            <div class="absolute -right-16 -top-16 w-48 h-48 bg-indigo-500/5 rounded-full blur-3xl"></div>
            <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-white">Welcome, <?= htmlspecialchars($display_name) ?></h1>
            
            <?php if (!empty($display_logo)): ?>
                <div class="mt-2 flex items-center gap-2 text-xs text-slate-500">
                    <span>Comapny Link:</span>
                    <a href="<?= htmlspecialchars($display_logo) ?>" target="_blank" class="text-indigo-400 hover:underline truncate max-w-xs font-medium">
                        <?= htmlspecialchars($display_logo) ?>
                    </a>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($display_desc)): ?>
                <p class="text-slate-400 mt-3 text-xs md:text-sm leading-relaxed border-l-2 border-slate-800 pl-3 max-w-3xl italic">
                    "<?= htmlspecialchars($display_desc) ?>"
                </p>
            <?php endif; ?>
        </div>

        <?php display_flash(); ?>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div class="p-6 bg-slate-900/20 border border-slate-900 rounded-2xl relative overflow-hidden group hover:border-slate-800 transition">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Active Jobs</p>
                <p class="text-4xl font-bold mt-2 bg-gradient-to-r from-white to-slate-400 bg-clip-text text-transparent"><?= (int)$active_jobs ?></p>
            </div>
            
            <div class="p-6 bg-slate-900/20 border border-slate-900 rounded-2xl relative overflow-hidden group hover:border-slate-800 transition">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Applicants</p>
                <p class="text-4xl font-bold mt-2 bg-gradient-to-r from-white to-slate-400 bg-clip-text text-transparent"><?= (int)$total_applicants ?></p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <div class="p-6 bg-slate-900/20 border border-slate-900 rounded-2xl space-y-4">
                <div class="flex items-center justify-between border-b border-slate-900 pb-3">
                    <h2 class="font-bold text-sm uppercase tracking-wider text-slate-200 flex items-center gap-2">
                        Recent Jobs
                    </h2>
                </div>
                
                <div class="space-y-3">
                    <?php if (!$recent_jobs): ?>
                        <div class="text-center py-8 text-xs text-slate-500 border border-dashed border-slate-900 rounded-xl bg-slate-950/20">
                            No jobs yet
                        </div>
                    <?php else: ?>
                        <?php foreach ($recent_jobs as $job): ?>
                            <div class="p-3 bg-slate-950/60 border border-slate-900/60 rounded-xl flex items-center justify-between gap-3 text-xs">
                                <div class="min-w-0">
                                    <p class="font-semibold text-slate-200 truncate"><?= htmlspecialchars($job['title']) ?></p>
                                    <p class="text-[10px] text-slate-500 font-medium uppercase tracking-wide mt-0.5"><?= htmlspecialchars($job['type'] ?? 'N/A') ?></p>
                                </div>
                                <span class="shrink-0 text-[11px] font-medium text-indigo-400 bg-indigo-500/10 border border-indigo-500/10 px-2 py-0.5 rounded-md">
                                    <?= (int)$job['applicants'] ?> applicants
                                </span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="p-6 bg-slate-900/20 border border-slate-900 rounded-2xl space-y-4">
                <div class="flex items-center justify-between border-b border-slate-900 pb-3">
                    <h2 class="font-bold text-sm uppercase tracking-wider text-slate-200 flex items-center gap-2">
                        Recent Applicants
                    </h2>
                </div>
                
                <div class="space-y-3">
                    <?php if (!$recent_applicants): ?>
                        <div class="text-center py-8 text-xs text-slate-500 border border-dashed border-slate-900 rounded-xl bg-slate-950/20">
                            No applicants yet
                        </div>
                    <?php else: ?>
                        <?php foreach ($recent_applicants as $a): ?>
                            <div class="p-3 bg-slate-950/60 border border-slate-900/60 rounded-xl flex items-center justify-between gap-3 text-xs">
                                <div class="min-w-0">
                                    <p class="font-semibold text-slate-200 truncate"><?= htmlspecialchars($a['full_name']) ?></p>
                                    <p class="text-[10px] text-slate-400 truncate mt-0.5"><?= htmlspecialchars($a['title']) ?></p>
                                </div>
                                <?php 
                                    $status = strtolower($a['status']);
                                    $badgeStyle = 'bg-amber-500/10 text-amber-400 border border-amber-500/10'; // pending
                                    if($status === 'accepted') $badgeStyle = 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/10';
                                    if($status === 'rejected') $badgeStyle = 'bg-rose-500/10 text-rose-400 border border-rose-500/10';
                                ?>
                                <span class="shrink-0 text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded <?= $badgeStyle ?>">
                                    <?= htmlspecialchars(ucfirst($a['status'])) ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </main>
</body>
</html>