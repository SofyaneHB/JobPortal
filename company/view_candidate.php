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
    <title>Profil de <?= htmlspecialchars($candidate['full_name']) ?> - Job Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-950 text-slate-200 font-sans antialiased min-h-screen p-8">

    <div class="max-w-4xl mx-auto">
        <!-- Bouton Retour -->
        <div class="mb-6">
            <a href="applicants.php" class="inline-flex items-center text-sm font-medium text-indigo-400 hover:text-indigo-300 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Retour à la liste des candidatures
            </a>
        </div>

        <!-- En-tête du profil -->
        <div class="bg-slate-900/20 border border-slate-900 rounded-2xl overflow-hidden mb-8">
            <div class="p-8 border-b border-slate-900 md:flex md:items-center md:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-white mb-2"><?= htmlspecialchars($candidate['full_name']) ?></h1>
                    <div class="flex flex-col sm:flex-row sm:items-center text-slate-400 text-sm gap-4">
                        <span class="flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            <?= htmlspecialchars($candidate['email']) ?>
                        </span>
                        <?php if (!empty($candidate['phone'])): ?>
                        <span class="flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                            <?= htmlspecialchars($candidate['phone']) ?>
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Bouton Télécharger CV -->
                <?php if (!empty($cv_path)): ?>
                <div class="mt-4 md:mt-0">
                    <a href="download_cv.php?app_id=<?= (int)$candidate_id ?>" target="_blank" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        Voir / Télécharger le CV
                    </a>
                </div>
                <?php endif; ?>
            </div>

            <div class="p-8 grid grid-cols-1 md:grid-cols-3 gap-8">
                
                <!-- Colonne Principale -->
                <div class="md:col-span-2 space-y-8">
                    
                    <!-- Coordonnées -->
                    <section>
                        <h2 class="text-xl font-semibold text-white mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            Coordonnées
                        </h2>
                        <div class="space-y-3 text-sm text-slate-400">
                            <?php if (!empty($candidate['address'])): ?>
                            <div class="flex items-start gap-3">
                                <span class="text-slate-500 font-medium w-20 shrink-0">Adresse :</span>
                                <span><?= htmlspecialchars($candidate['address']) ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($candidate['country'])): ?>
                            <div class="flex items-start gap-3">
                                <span class="text-slate-500 font-medium w-20 shrink-0">Pays :</span>
                                <span><?= htmlspecialchars($candidate['country']) ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </section>

                </div>

                <!-- Colonne Latérale -->
                <div class="space-y-8">
                    <?php if (!empty($candidate['skills'])): ?>
                    <section class="bg-slate-950/50 rounded-xl p-6 border border-slate-900">
                        <h3 class="text-lg font-medium text-white mb-4">Compétences</h3>
                        <div class="flex flex-wrap gap-2">
                            <?php 
                            $skills = array_filter(array_map('trim', explode(',', $candidate['skills'])));
                            foreach ($skills as $skill): 
                            ?>
                                <span class="px-3 py-1 bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 rounded-full text-xs font-medium">
                                    <?= htmlspecialchars($skill) ?>
                               </span>
                            <?php endforeach; ?>
                        </div>
                    </section>
                    <?php else: ?>
                    <section class="bg-slate-950/50 rounded-xl p-6 border border-slate-900">
                        <h3 class="text-lg font-medium text-white mb-4">Compétences</h3>
                        <p class="text-slate-500 text-sm italic">Aucune compétence renseignée</p>
                    </section>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>

</body>
</html>