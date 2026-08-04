<?php
session_start();

require_once "../config/db.php";
require_once "../includes/functions.php";

require_login(['company']);

$user_id    = $_SESSION['user_id'] ?? null;
$company_id = $_SESSION['company_id'] ?? null;
$stmt = $pdo->prepare("SELECT company_name, logo, description FROM companies WHERE id = ? LIMIT 1");
$stmt->execute([$company_id]);
$company_data = $stmt->fetch(PDO::FETCH_ASSOC);
$display_name = $company_data['company_name'] ?? 'Company';

if (!$company_id || !$user_id) {
    set_flash("error", "Session manquante.");
    redirect("../Public/login.php");
    exit;
}

/* ── Sidebar info ── */
try {
    $stmt = $pdo->prepare("SELECT c.company_name, u.email FROM companies c JOIN users u ON c.user_id = u.id WHERE c.id = ? LIMIT 1");
    $stmt->execute([$company_id]);
    $company = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['company_name' => 'Company', 'email' => ''];
} catch (PDOException $e) {
    $company = ['company_name' => 'Company', 'email' => ''];
}

/* ── Fetch Company Notifications ── */
try {
    $stmt = $pdo->prepare("
        SELECT id, message, is_read, created_at, type, link
        FROM notifications 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT 10
    ");
    $stmt->execute([$user_id]);
    $company_notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $unread_notif_count = count(array_filter($company_notifications, fn($n) => !$n['is_read']));
} catch (PDOException $e) {
    $company_notifications = [];
    $unread_notif_count = 0;
}

/* ── Mark notifications as read ── */
if (isset($_GET['read_notif'])) {
    $notif_id = (int) $_GET['read_notif'];
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
    $stmt->execute([$notif_id, $user_id]);
    redirect("applicants.php");
    exit;
}

/* ── Handle STATUS UPDATE ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['app_id'], $_POST['status'])) {
    $app_id  = (int) $_POST['app_id'];
    $status  = $_POST['status'];
    $allowed = ['pending', 'accepted', 'rejected'];

    if (in_array($status, $allowed)) {
        try {
            $stmtInfo = $pdo->prepare("
                SELECT a.candidate_id, j.title, j.id as job_id
                FROM applications a 
                INNER JOIN jobs j ON a.job_id = j.id 
                WHERE a.id = ? AND j.company_id = ?
            ");
            $stmtInfo->execute([$app_id, $company_id]);
            $appInfo = $stmtInfo->fetch(PDO::FETCH_ASSOC);

            if ($appInfo) {
                $stmt = $pdo->prepare("
                    UPDATE applications a
                    INNER JOIN jobs j ON a.job_id = j.id
                    SET a.status = ?
                    WHERE a.id = ? AND j.company_id = ?
                ");
                $stmt->execute([$status, $app_id, $company_id]);

                $jobTitle = $appInfo['title'];
                if ($status === 'accepted') {
                    $message = "Félicitations ! Votre candidature pour « {$jobTitle} » a été acceptée.";
                    
                    $stmt2 = $pdo->prepare("
                        UPDATE jobs j
                        INNER JOIN applications a ON a.job_id = j.id
                        SET j.status = 'closed'
                        WHERE a.id = ? AND j.company_id = ?
                    ");
                    $stmt2->execute([$app_id, $company_id]);
                    
                    set_flash("success", "Candidature acceptée — le poste a été fermé automatiquement.");
                } elseif ($status === 'rejected') {
                    $message = "Malheureusement, votre candidature pour « {$jobTitle} » n'a pas été retenue.";
                    set_flash("success", "Statut mis à jour.");
                } else {
                    $message = "Le statut de votre candidature pour « {$jobTitle} » a été mis à jour.";
                    set_flash("success", "Statut mis à jour.");
                }

                $stmtNotif = $pdo->prepare("
                    INSERT INTO notifications (user_id, message, type, link, is_read, created_at)
                    VALUES (?, ?, 'status_change', ?, 0, NOW())
                ");
                $link = "../candidate/my_applications.php";
                $stmtNotif->execute([$appInfo['candidate_id'], $message, $link]);
            }

        } catch (PDOException $e) {
            set_flash("error", "Erreur : " . $e->getMessage());
        }
    }

    redirect("applicants.php");
    exit;
}

/* ── Fetch applicants WITH CV PATH ── */
try {
    $stmt = $pdo->prepare("
        SELECT
            a.id          AS app_id,
            a.status,
            a.applied_at,
            a.cv_path,
            u.full_name,
            u.email,
            u.id          AS candidate_id,
            j.title       AS job_title,
            j.id          AS job_id,
            j.status      AS job_status
        FROM applications a
        INNER JOIN jobs  j ON a.job_id       = j.id
        INNER JOIN users u ON a.candidate_id = u.id
        WHERE j.company_id = ?
        ORDER BY a.applied_at DESC
    ");
    $stmt->execute([$company_id]);
    $applicants = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $applicants = [];
    error_log("applicants fetch error: " . $e->getMessage());
}

$byJob = [];
foreach ($applicants as $app) {
    $byJob[$app['job_title']][] = $app;
}

/* ── Handle CV Path Verification ── */
function getCvPath($app) {
    // Priorité 1: cv_path enregistré dans la table applications
    if (!empty($app['cv_path'])) {
        $path = $app['cv_path'];
        if (file_exists($path)) return $path;
        if (file_exists("../" . ltrim($path, '/'))) return "../" . ltrim($path, '/');
        if (file_exists(__DIR__ . '/' . $path)) return __DIR__ . '/' . $path;
        if (file_exists(__DIR__ . '/../' . $path)) return __DIR__ . '/../' . $path;
    }
    
    // Priorité 2: Recherche par ID du candidat dans uploads/cv/
    $candidateId = $app['candidate_id'] ?? null;
    $possibleDirs = ["../uploads/cv/", "uploads/cv/"];

    foreach ($possibleDirs as $uploadDir) {
        if (!is_dir($uploadDir)) continue;

        if ($candidateId) {
            $patterns = [
                $uploadDir . "*_" . $candidateId . ".*",
                $uploadDir . "*_" . $candidateId . "_*.*",
                $uploadDir . $candidateId . ".*"
            ];
            foreach ($patterns as $pattern) {
                $files = glob($pattern);
                if (!empty($files)) return $files[0];
            }
        }

        // Priorité 3: Recherche par nom
        if (!empty($app['full_name'])) {
            $safeName = preg_replace('/[^a-zA-Z0-9]/', '_', strtolower($app['full_name']));
            $files = glob($uploadDir . "*" . $safeName . "*.*");
            if (!empty($files)) return $files[0];
        }
    }
    
    return null;
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Applications Received</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .notif-dropdown { display: none; }
        .notif-dropdown.show { display: block; }
        .notif-unread { background: rgba(99, 102, 241, 0.08); }
    </style>
</head>

<body class="flex bg-slate-950 text-slate-100 min-h-screen">

<aside class="w-64 border-r border-slate-900 bg-slate-950/40 h-screen fixed flex flex-col justify-between z-40">
    <div>
        <div class="p-5 flex items-center gap-3 border-b border-slate-900">
            <div class="w-9 h-9 bg-gradient-to-tr from-indigo-600 to-cyan-500 text-white flex items-center justify-center font-bold text-sm rounded-xl">
                <?= strtoupper(substr($company['company_name'], 0, 1)) ?>
            </div>
            <div>
                <div class="font-bold text-sm text-slate-200"><?= htmlspecialchars($display_name) ?></div>
                <div class="text-[10px] text-indigo-400 font-semibold uppercase tracking-wider">Recuriter Dashboard</div>
            </div>
        </div>
        <nav class="p-4 space-y-1.5 text-xs font-medium">
            <a href="dashboard.php"  class="flex items-center px-3 py-2.5 text-slate-400 hover:text-slate-200 hover:bg-slate-900/40 rounded-xl transition">Dashboard</a>
            <a href="profile.php"    class="flex items-center px-3 py-2.5 text-slate-400 hover:text-slate-200 hover:bg-slate-900/40 rounded-xl transition">Company Dashboard</a>
            <a href="add_job.php"    class="flex items-center px-3 py-2.5 text-slate-400 hover:text-slate-200 hover:bg-slate-900/40 rounded-xl transition">Publish a Job Offer</a>
            <a href="my_jobs.php"    class="flex items-center px-3 py-2.5 text-slate-400 hover:text-slate-200 hover:bg-slate-900/40 rounded-xl transition">Manage Jobs</a>
            <a href="applicants.php" class="flex items-center px-3 py-2.5 bg-indigo-600/10 border border-indigo-500/20 text-indigo-400 rounded-xl font-semibold">Applications Received</a>
        </nav>
    </div>
    <div class="p-4 border-t border-slate-900">
        <div class="text-xs font-bold text-slate-300 truncate"><?= htmlspecialchars($company['company_name']) ?></div>
        <div class="text-[10px] text-slate-500 truncate mt-0.5"><?= htmlspecialchars($company['email']) ?></div>
    </div>
</aside>

<main class="ml-64 flex-1">

    <div class="p-8 md:p-10">

        <?php display_flash(); ?>

        <div class="mb-8">
            <h1 class="text-2xl font-bold text-white">Applications Received</h1>
            <p class="text-slate-400 text-sm mt-1">Manage applications, accept or reject candidate profiles </p>
        </div>

        <?php if (empty($applicants)): ?>
            <div class="bg-slate-900/20 border border-slate-900 rounded-2xl p-12 text-center text-slate-500 text-sm">
                No candidates.
            </div>

        <?php else: ?>

            <?php foreach ($byJob as $jobTitle => $apps):
                $jobStatus = $apps[0]['job_status'] ?? 'active';
                $isClosed  = $jobStatus === 'closed';
            ?>
            <div class="mb-8">
                <div class="flex items-center gap-3 mb-3">
                    <h2 class="font-bold text-slate-200 text-sm uppercase tracking-wider"><?= htmlspecialchars($jobTitle) ?></h2>
                    <?php if ($isClosed): ?>
                        <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded bg-rose-500/10 text-rose-400 border border-rose-500/20">Fermé</span>
                    <?php else: ?>
                        <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Actif</span>
                    <?php endif; ?>
                    <span class="text-xs text-slate-500"><?= count($apps) ?> Candidates(s)</span>
                </div>

                <div class="bg-slate-900/20 border border-slate-900 rounded-2xl overflow-hidden">
                    <table class="w-full text-sm text-left">
                        <thead class="border-b border-slate-900 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                            <tr>
                                <th class="px-6 py-3">Candidat</th>
                                <th class="px-6 py-3">Date</th>
                                <th class="px-6 py-3">CV</th>
                                <th class="px-6 py-3">Statut</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-900/60">
                            <?php foreach ($apps as $app):
                                $s = $app['status'];
                                $badge = [
                                    'pending'  => 'bg-amber-500/10  text-amber-400  border-amber-500/20',
                                    'accepted' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                                    'rejected' => 'bg-rose-500/10   text-rose-400   border-rose-500/20',
                                ][$s] ?? 'bg-slate-500/10 text-slate-400 border-slate-500/20';
                                
                                $cvPath = getCvPath($app);
                                $hasCv = $cvPath !== null;
                            ?>
                            <tr class="hover:bg-slate-900/30 transition">
                                <td class="px-6 py-4">
    <!-- Remplacement du div par une balise <a> avec un lien vers la page du profil -->
    <a href="view_candidate.php?id=<?= $app['candidate_id'] ?>" title="Voir le profil" class="font-bold text-slate-200 hover:text-indigo-400 transition block">
        <?= htmlspecialchars($app['full_name']) ?>
    </a>
    <div class="text-xs text-slate-500"><?= htmlspecialchars($app['email']) ?></div>
</td>
                                <td class="px-6 py-4 text-slate-400 text-xs">
                                    <?= date('d M Y', strtotime($app['applied_at'])) ?>
                                </td>
                                
                                <td class="px-6 py-4">
                                    <?php if ($hasCv): ?>
                                        <a href="download_cv.php?app_id=<?= (int)$app['app_id'] ?>" 
                                           target="_blank"
                                           class="inline-flex items-center gap-2 px-3 py-1.5 bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 rounded-lg text-xs font-semibold hover:bg-indigo-500/20 transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                            View / Download CV
                                        </a>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-2 px-3 py-1.5 bg-slate-800/50 text-slate-500 border border-slate-800 rounded-lg text-xs">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            Aucun CV
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <td class="px-6 py-4">
                                    <?php if ($isClosed && $s !== 'accepted'): ?>
                                        <span class="text-[10px] font-bold uppercase px-3 py-1 rounded border <?= $badge ?>">
                                            <?= ucfirst($s) ?>
                                        </span>
                                    <?php else: ?>
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="app_id" value="<?= $app['app_id'] ?>">
                                            <select name="status" onchange="this.form.submit()"
                                                class="text-xs font-bold rounded-lg px-3 py-1.5 border cursor-pointer focus:outline-none bg-transparent transition <?= $badge ?>">
                                                <option value="pending"  <?= $s==='pending' ?'selected':'' ?>>Pending</option>
                                                <option value="accepted" <?= $s==='accepted'?'selected':'' ?>>Accepted</option>
                                                <option value="rejected" <?= $s==='rejected'?'selected':'' ?>>Rejected</option>
                                            </select>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endforeach; ?>

        <?php endif; ?>
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