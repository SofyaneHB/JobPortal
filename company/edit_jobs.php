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


$job_id = $_GET['id'] ?? null;

if (!$job_id) {
    redirect("my_jobs.php");
    exit;
}

// --- Verify Job Belongs to Company ---
$stmt = $pdo->prepare("SELECT * FROM jobs WHERE id = ? AND company_id = ? LIMIT 1");
$stmt->execute([$job_id, $company_id]);
$job = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$job) {
    set_flash("error", "Job not found or access denied.");
    redirect("my_jobs.php");
    exit;
}

// --- Handle Update ---
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = clean_input($_POST['title']);
    $location = clean_input($_POST['location']);
    $type = clean_input($_POST['type']);
    $salary = clean_input($_POST['salary']);
    $status = clean_input($_POST['status']);
    $description = clean_input($_POST['description']);

    $stmt = $pdo->prepare("
        UPDATE jobs 
        SET title = ?, location = ?, type = ?, salary = ?, status = ?, description = ? 
        WHERE id = ? AND company_id = ?
    ");
    $stmt->execute([$title, $location, $type, $salary, $status, $description, $job_id, $company_id]);
    
    set_flash("success", "Job updated successfully!");
    redirect("my_jobs.php");
    exit;
}

$stmt = $pdo->prepare("SELECT full_name, email FROM users WHERE id = ? LIMIT 1");
$stmt->execute([$user_id]);
$currentUser = $stmt->fetch(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Job</title>
    <link rel="icon" type="image/png" href="../assets/img/logo_jp.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        /* Custom dark select styling */
        select option { background: #0f172a; color: #e2e8f0; }
    </style>
</head>
<body class="flex bg-slate-950 text-slate-100 min-h-screen">

<!-- SIDEBAR -->
<aside class="w-64 border-r border-slate-900 bg-slate-950/40 h-screen fixed flex flex-col justify-between z-40">
    <div>
        <div class="p-5 flex items-center gap-3 border-b border-slate-900">
            <div class="w-9 h-9 bg-gradient-to-tr from-indigo-600 to-cyan-500 text-white flex items-center justify-center font-bold text-sm rounded-xl">
                <?= strtoupper(substr($currentUser['full_name'], 0, 1)) ?>
            </div>
            <div>
                <div class="font-bold text-sm text-slate-200"><?= htmlspecialchars($display_name)?></div>
                <div class="text-[10px] text-indigo-400 font-semibold uppercase tracking-wider">Recruiter Dashboard</div>
            </div>
        </div>
        <nav class="p-4 space-y-1.5 text-xs font-medium">
            <a href="dashboard.php"  class="flex items-center px-3 py-2.5 text-slate-400 hover:text-slate-200 hover:bg-slate-900/40 rounded-xl transition">Dashboard</a>
            <a href="profile.php"    class="flex items-center px-3 py-2.5 text-slate-400 hover:text-slate-200 hover:bg-slate-900/40 rounded-xl transition">Company Profile</a>
            <a href="add_job.php"    class="flex items-center px-3 py-2.5 text-slate-400 hover:text-slate-200 hover:bg-slate-900/40 rounded-xl transition">Publish a Job Offer</a>
            <a href="my_jobs.php"    class="flex items-center px-3 py-2.5 bg-indigo-600/10 border border-indigo-500/20 text-indigo-400 rounded-xl font-semibold">Manage Jobs</a>
            <a href="applicants.php" class="flex items-center px-3 py-2.5 text-slate-400 hover:text-slate-200 hover:bg-slate-900/40 rounded-xl transition">Applications Received</a>
        </nav>
    </div>
    <div class="p-4 border-t border-slate-900">
        <div class="text-xs font-bold text-slate-300 truncate"><?= htmlspecialchars($currentUser['full_name']) ?></div>
        <div class="text-[10px] text-slate-500 truncate mt-0.5"><?= htmlspecialchars($currentUser['email']) ?></div>
    </div>
</aside>

<!-- MAIN -->
<main class="ml-64 flex-1 p-8 md:p-10">

    <?php display_flash(); ?>

    <!-- Header -->
    <div class="flex items-center gap-3 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-white">Edit Job: <?= htmlspecialchars($job['title']) ?></h1>
            <p class="text-slate-400 text-sm mt-1">Modify your job listing details.</p>
        </div>
    </div>

    <!-- Form Card -->
    <div class="max-w-3xl bg-slate-900/20 border border-slate-900 rounded-2xl p-8">
        <form method="POST" action="">

            <!-- Row 1: Title + Status -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Job Title</label>
                    <input type="text" name="title" value="<?= htmlspecialchars($job['title']) ?>" required
                        class="w-full bg-slate-950/50 border border-slate-800 rounded-xl px-4 py-3 text-sm text-slate-200 placeholder-slate-600 outline-none focus:border-indigo-500/50 focus:ring-2 focus:ring-indigo-500/10 transition-all">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Status</label>
                    <div class="relative">
                        <select name="status" 
                            class="w-full bg-slate-950/50 border border-slate-800 rounded-xl px-4 py-3 text-sm text-slate-200 outline-none focus:border-indigo-500/50 focus:ring-2 focus:ring-indigo-500/10 transition-all appearance-none cursor-pointer">
                            <option value="active" <?= $job['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="paused" <?= $job['status'] === 'paused' ? 'selected' : '' ?>>Paused</option>
                            <option value="closed" <?= $job['status'] === 'closed' ? 'selected' : '' ?>>Closed</option>
                        </select>
                        <svg class="w-4 h-4 text-slate-500 absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Row 2: Location + Type + Salary -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-5">
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Location</label>
                    <input type="text" name="location" value="<?= htmlspecialchars($job['location']) ?>" required
                        class="w-full bg-slate-950/50 border border-slate-800 rounded-xl px-4 py-3 text-sm text-slate-200 placeholder-slate-600 outline-none focus:border-indigo-500/50 focus:ring-2 focus:ring-indigo-500/10 transition-all">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Employment Type</label>
                    <div class="relative">
                        <select name="type" 
                            class="w-full bg-slate-950/50 border border-slate-800 rounded-xl px-4 py-3 text-sm text-slate-200 outline-none focus:border-indigo-500/50 focus:ring-2 focus:ring-indigo-500/10 transition-all appearance-none cursor-pointer">
                            <option value="full-time" <?= $job['type'] === 'full-time' ? 'selected' : '' ?>>Full-Time</option>
                            <option value="part-time" <?= $job['type'] === 'part-time' ? 'selected' : '' ?>>Part-Time</option>
                            <option value="remote" <?= $job['type'] === 'remote' ? 'selected' : '' ?>>Remote</option>
                            <option value="internship" <?= $job['type'] === 'internship' ? 'selected' : '' ?>>Internship</option>
                        </select>
                        <svg class="w-4 h-4 text-slate-500 absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Salary Range</label>
                    <input type="text" name="salary" value="<?= htmlspecialchars($job['salary'] ?? '') ?>" 
                        class="w-full bg-slate-950/50 border border-slate-800 rounded-xl px-4 py-3 text-sm text-slate-200 placeholder-slate-600 outline-none focus:border-indigo-500/50 focus:ring-2 focus:ring-indigo-500/10 transition-all">
                </div>
            </div>

            <!-- Row 3: Description -->
            <div class="mb-8">
                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Job Description</label>
                <textarea name="description" rows="6" required
                    class="w-full bg-slate-950/50 border border-slate-800 rounded-xl px-4 py-3 text-sm text-slate-200 placeholder-slate-600 outline-none focus:border-indigo-500/50 focus:ring-2 focus:ring-indigo-500/10 transition-all resize-none"><?= htmlspecialchars($job['description']) ?></textarea>
            </div>

            <!-- Submit -->
            <div class="flex justify-end">
                <button type="submit" 
                    class="inline-flex items-center gap-2  bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold px-6 py-3 rounded-xl transition-all shadow-lg shadow-indigo-600/20 active:scale-[0.98] ">
                    Save Updates
                </button>
            </div>

        </form>
    </div>
</main>

</body>
</html>