<?php
session_start();

require_once "../config/db.php";
require_once "../includes/functions.php";

require_login(['company']);

$user_id    = $_SESSION['user_id'];
$company_id = $_SESSION['company_id'];
$stmt = $pdo->prepare("SELECT company_name, logo, description FROM companies WHERE id = ? LIMIT 1");
$stmt->execute([$company_id]);
$company_data = $stmt->fetch(PDO::FETCH_ASSOC);
$display_name = $company_data['company_name'] ?? 'Company';

/* ── Sidebar info ── */
$stmt = $pdo->prepare("SELECT c.company_name, u.email FROM companies c JOIN users u ON c.user_id = u.id WHERE c.id = ? LIMIT 1");
$stmt->execute([$company_id]);
$company = $stmt->fetch(PDO::FETCH_ASSOC);

/* ── Handle DELETE ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_job_id'])) {
    $del_id = (int) $_POST['delete_job_id'];
    try {
        // Verify ownership before delete
        $chk = $pdo->prepare("SELECT id FROM jobs WHERE id = ? AND company_id = ?");
        $chk->execute([$del_id, $company_id]);
        if ($chk->rowCount() > 0) {
            $pdo->prepare("DELETE FROM applications WHERE job_id = ?")->execute([$del_id]);
            $pdo->prepare("DELETE FROM jobs WHERE id = ? AND company_id = ?")->execute([$del_id, $company_id]);
            set_flash("success", "Job supprimé avec succès.");
        }
    } catch (PDOException $e) {
        set_flash("error", "Erreur suppression : " . $e->getMessage());
    }
    redirect("my_jobs.php");
    exit;
}

/* ── Handle STATUS CHANGE (close/pause/activate) ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['job_id'], $_POST['new_status'])) {
    $job_id    = (int) $_POST['job_id'];
    $new_status = $_POST['new_status'];
    $allowed   = ['active', 'paused', 'closed'];

    if (in_array($new_status, $allowed)) {
        try {
            $stmt = $pdo->prepare("UPDATE jobs SET status = ? WHERE id = ? AND company_id = ?");
            $stmt->execute([$new_status, $job_id, $company_id]);
            set_flash("success", "Statut mis à jour.");
        } catch (PDOException $e) {
            set_flash("error", "Erreur : " . $e->getMessage());
        }
    }
    redirect("my_jobs.php");
    exit;
}

/* ── Fetch all jobs ── */
$stmt = $pdo->prepare("
    SELECT j.*, COUNT(a.id) AS applicant_count
    FROM jobs j
    LEFT JOIN applications a ON j.id = a.job_id
    WHERE j.company_id = ?
    GROUP BY j.id
    ORDER BY j.created_at DESC
");
$stmt->execute([$company_id]);
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Jobs</title>
    <link rel="icon" type="image/png" href="../assets/img/logo_jp.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="flex bg-slate-950 text-slate-100 min-h-screen">


<!-- SIDEBAR -->
<aside class="w-64 border-r border-slate-900 bg-slate-950/40 h-screen fixed flex flex-col justify-between z-40">
    <div>
        <div class="p-5 flex items-center gap-3 border-b border-slate-900">
            <div class="w-9 h-9 bg-gradient-to-tr from-indigo-600 to-cyan-500 text-white flex items-center justify-center font-bold text-sm rounded-xl">
                <?= strtoupper(substr($company['company_name'] ?? 'C', 0, 1)) ?>
            </div>
            <div>
                <div class="font-bold text-sm text-slate-200 tracking-tight"><?= htmlspecialchars($display_name) ?></div>
                <div class="text-[10px] text-indigo-400 font-semibold uppercase tracking-wider">Recuriter Dashboard</div>
            </div>
        </div>
        <nav class="p-4 space-y-1.5 text-xs font-medium">
            <a href="dashboard.php"  class="flex items-center px-3 py-2.5 text-slate-400 hover:text-slate-200 hover:bg-slate-900/40 rounded-xl transition">Dashboard</a>
            <a href="profile.php"    class="flex items-center px-3 py-2.5 text-slate-400 hover:text-slate-200 hover:bg-slate-900/40 rounded-xl transition">Company profile</a>
            <a href="add_job.php"    class="flex items-center px-3 py-2.5 text-slate-400 hover:text-slate-200 hover:bg-slate-900/40 rounded-xl transition">Publish a Job Offer</a>
            <a href="my_jobs.php"    class="flex items-center px-3 py-2.5 bg-indigo-600/10 border border-indigo-500/20 text-indigo-400 rounded-xl font-semibold">Manage Jobs</a>
            <a href="applicants.php" class="flex items-center px-3 py-2.5 text-slate-400 hover:text-slate-200 hover:bg-slate-900/40 rounded-xl transition">Applications Received</a>
        </nav>
    </div>
    <div class="p-4 border-t border-slate-900">
        <div class="text-xs font-bold text-slate-300 truncate"><?= htmlspecialchars($company['company_name'] ?? '') ?></div>
        <div class="text-[10px] text-slate-500 truncate mt-0.5"><?= htmlspecialchars($company['email'] ?? '') ?></div>
    </div>
</aside>

<!-- MAIN -->
<main class="ml-64 flex-1 p-8 md:p-10">

    <?php display_flash(); ?>

    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-2xl font-bold text-white">Manage Jobs</h1>
            <p class="text-slate-400 text-sm mt-1">Edit, close or delete your job offers</p>
        </div>
    </div>

    <div class="bg-slate-900/20 border border-slate-900 rounded-2xl overflow-hidden">
        <?php if (empty($jobs)): ?>
            <div class="p-12 text-center text-slate-500 text-sm">
                Aucune offre publiée pour l'instant.
            </div>
        <?php else: ?>
        <table class="w-full text-sm text-left">
            <thead class="border-b border-slate-900 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                <tr>
                    <th class="px-6 py-4">Poste</th>
                    <th class="px-6 py-4">Type</th>
                    <th class="px-6 py-4">Statut</th>
                    <th class="px-6 py-4">Candidats</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-900/60">
                <?php foreach ($jobs as $job):
                    $status = $job['status'] ?? 'active';
                    $statusStyles = [
                        'active' => 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20',
                        'paused' => 'bg-amber-500/10  text-amber-400  border border-amber-500/20',
                        'closed' => 'bg-rose-500/10   text-rose-400   border border-rose-500/20',
                    ];
                    $badgeClass = $statusStyles[$status] ?? $statusStyles['active'];
                ?>
                <tr class="hover:bg-slate-900/30 transition">
                    <td class="px-6 py-4">
                        <div class="font-bold text-slate-200"><?= htmlspecialchars($job['title']) ?></div>
                        <div class="text-xs text-slate-500 mt-0.5"> <?= htmlspecialchars($job['location']) ?></div>
                    </td>
                    <td class="px-6 py-4 text-slate-400 text-xs font-medium uppercase"><?= htmlspecialchars($job['type']) ?></td>

                    <!-- STATUS DROPDOWN -->
                    <td class="px-6 py-4">
                        <form method="POST" class="inline">
                            <input type="hidden" name="job_id" value="<?= $job['id'] ?>">
                            <select name="new_status" onchange="this.form.submit()"
                                class="text-xs font-bold rounded-lg px-3 py-1.5 border cursor-pointer focus:outline-none transition
                                <?= $badgeClass ?> bg-transparent">
                                <option value="active" <?= $status==='active'?'selected':'' ?>>Active</option>
                                <option value="paused" <?= $status==='paused'?'selected':'' ?>>Paused</option>
                                <option value="closed" <?= $status==='closed'?'selected':'' ?>>Closed</option>
                            </select>
                        </form>
                    </td>

                    <td class="px-6 py-4">
                        <span class="text-indigo-400 font-bold"><?= (int)$job['applicant_count'] ?></span>
                        <span class="text-slate-500 text-xs ml-1">candidate(s)</span>
                    </td>

                    <!-- ACTIONS -->
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-3">
                            <a href="edit_jobs.php?id=<?= $job['id'] ?>"
                               class="text-xs font-bold text-indigo-400 hover:text-indigo-300 transition">
                                Edit
                            </a>
                            <!-- DELETE with confirm -->
                            <form method="POST" onsubmit="return confirm('Supprimer cette offre et toutes ses candidatures ?')">
                                <input type="hidden" name="delete_job_id" value="<?= $job['id'] ?>">
                                <button type="submit" class="text-xs font-bold text-rose-400 hover:text-rose-300 transition">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</main>
</body>
</html>