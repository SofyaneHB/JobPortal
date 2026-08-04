<?php
session_start();

require_once '../config/db.php'; 
require_once "../includes/functions.php";

require_login(['company']);

$candidate_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$candidate_id) {
    set_flash("error", "Identifiant de candidat invalide.");
    redirect("applicants.php");
    exit;
}

$company_id = $_SESSION['company_id'] ?? null;
$stmt = $pdo->prepare("SELECT company_name, logo, description FROM companies WHERE id = ? LIMIT 1");
$stmt->execute([$company_id]);
$company_data = $stmt->fetch(PDO::FETCH_ASSOC);
$display_name = $company_data['company_name'] ?? 'Company';


try {
    // Récupère les infos du candidat depuis la table users uniquement
    $stmt = $pdo->prepare("
        SELECT id, full_name, email, phone, address, country, skills 
        FROM users 
        WHERE id = ? AND role = 'candidate'
        LIMIT 1
    ");
    $stmt->execute([$candidate_id]);
    $candidate = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    set_flash("error", "Erreur de base de données.");
    redirect("applicants.php");
    exit;
}

if (!$candidate) {
    set_flash("error", "Candidat introuvable.");
    redirect("applicants.php");
    exit;
}

// Récupère le CV le plus récent de ce candidat pour l'entreprise connectée
$cv_path = null;
if ($company_id) {
    $stmt = $pdo->prepare("
        SELECT a.cv_path 
        FROM applications a
        JOIN jobs j ON a.job_id = j.id
        WHERE a.candidate_id = ? AND j.company_id = ?
        ORDER BY a.applied_at DESC
        LIMIT 1
    ");
    $stmt->execute([$candidate_id, $company_id]);
    $cv_path = $stmt->fetchColumn();
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Candidate Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

</head>
<body class="bg-slate-950 text-slate-200 min-h-screen">

    <!-- Top Bar -->
    <div class="border-b border-slate-900 bg-slate-950 sticky top-0 z-50">
        <div class="max-w-3xl mx-auto px-6 py-4 flex items-center justify-between">
            <a href="applicants.php" class="inline-flex items-center gap-2 text-sm text-slate-400 hover:text-indigo-400 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
                Back to applications
            </a>
            <?php if (!empty($cv_path)): ?>
            <a href="download_cv.php?app_id=<?= (int)$candidate_id ?>" target="_blank"
               class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium rounded-xl transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Download CV
            </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="max-w-3xl mx-auto px-6 py-12 mt-8">

        <!-- Header Card -->
        <div class="bg-slate-900/20 border border-slate-900 rounded-2xl p-8 mb-6 text-center">
            <h1 class="text-2xl font-bold text-white"><?= htmlspecialchars($candidate['full_name']) ?></h1>
            <div class="flex flex-wrap items-center justify-center gap-4 mt-3 text-sm text-slate-400">
                <span class="flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <?= htmlspecialchars($candidate['email']) ?>
                </span>
                <?php if (!empty($candidate['phone'])): ?>
                <span class="flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                    <?= htmlspecialchars($candidate['phone']) ?>
                </span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Contact Info: 2 up / 2 down, 50/50, left aligned, 1 line -->
        <div class="bg-slate-900/20 border border-slate-900 rounded-2xl p-6 mb-6">
            <h2 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-5">Contact Information</h2>
            <div class="grid grid-cols-2 gap-4">
                <div class="p-4 rounded-xl bg-slate-950/40 border border-slate-900">
                    <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Email</div>
                    <div class="text-sm text-slate-300 font-medium"><?= htmlspecialchars($candidate['email']) ?></div>
                </div>
                <div class="p-4 rounded-xl bg-slate-950/40 border border-slate-900">
                    <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Phone</div>
                    <div class="text-sm text-slate-300 font-medium">
                        <?= !empty($candidate['phone']) ? htmlspecialchars($candidate['phone']) : '<span class="text-slate-600">—</span>' ?>
                    </div>
                </div>
                <div class="p-4 rounded-xl bg-slate-950/40 border border-slate-900">
                    <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Address</div>
                    <div class="text-sm text-slate-300 font-medium"><?= !empty($candidate['address']) ? htmlspecialchars($candidate['address']) : '<span class="text-slate-600">—</span>' ?></div>
                </div>
                <div class="p-4 rounded-xl bg-slate-950/40 border border-slate-900">
                    <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Country</div>
                    <div class="text-sm text-slate-300 font-medium">
                        <?= !empty($candidate['country']) ? htmlspecialchars($candidate['country']) : '<span class="text-slate-600">—</span>' ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Skills: left aligned, not centered -->
        <div class="bg-slate-900/20 border border-slate-900 rounded-2xl p-6">
            <h2 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-5">Skills</h2>
            <?php if (!empty($candidate['skills'])): 
                $skills = array_filter(array_map('trim', explode(',', $candidate['skills'])));
                if (!empty($skills)):
            ?>
            <div class="flex flex-wrap gap-2">
                <?php foreach ($skills as $skill): ?>
                    <span class="px-3.5 py-1.5 bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 rounded-lg text-xs font-semibold">
                        <?= htmlspecialchars($skill) ?>
                    </span>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <p class="text-sm text-slate-500 italic">No skills listed</p>
            <?php endif; else: ?>
            <p class="text-sm text-slate-500 italic">No skills listed</p>
            <?php endif; ?>
        </div>

    </div>

</body>
</html>