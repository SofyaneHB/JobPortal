<?php
session_start();
require_once "../config/db.php";

// Extraction des paramètres de recherche
$search = isset($_GET['search']) ? trim($_GET['search']) : (isset($_GET['keyword']) ? trim($_GET['keyword']) : '');
$location = isset($_GET['location']) ? trim($_GET['location']) : 'all';

$final_jobs = [];

if (isset($pdo)) {
    $conditions = ["j.status = 'active'"];
    $sql_params = [];

    if (!empty($search)) {
        $conditions[] = "(j.title LIKE :search OR j.description LIKE :search OR c.company_name LIKE :search)";
        $sql_params[':search'] = "%$search%";
    }

    if (strtolower($location) !== 'all' && !empty($location)) {
        $conditions[] = "LOWER(TRIM(j.location)) = :location";
        $sql_params[':location'] = strtolower($location);
    }

    $where_clause = implode(" AND ", $conditions);

    try {
        $query_string = "
            SELECT
                j.id, 
                j.title, 
                j.location, 
                j.type, 
                j.salary, 
                j.description, 
                j.created_at,
                COALESCE(c.company_name, 'Entreprise Partner') AS company, 
                c.logo
            FROM jobs j
            LEFT JOIN companies c ON j.company_id = c.id
            WHERE $where_clause
            ORDER BY j.id DESC
        ";
        $stmt = $pdo->prepare($query_string);
        $stmt->execute($sql_params);
        $db_jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $db_jobs = [];
    }

    $dynamic_colors = ['bg-indigo-600', 'bg-emerald-600', 'bg-sky-600', 'bg-pink-600', 'bg-violet-600', 'bg-teal-600', 'bg-cyan-600'];

    foreach ($db_jobs as $idx => $dj) {
        $clean_title = preg_replace('/[^A-Za-z0-9]/', '', $dj['title']);
        $dj['logo_txt'] = !empty($clean_title) ? strtoupper(substr($clean_title, 0, 2)) : 'JB';
        $dj['logo_bg'] = $dynamic_colors[$idx % count($dynamic_colors)];
        $dj['apps'] = 0; 

        if (!empty($dj['created_at'])) {
            $dj['posted'] = date('d M', strtotime($dj['created_at']));
        } else {
            $dj['posted'] = 'Récemment';
        }

        $contract = strtolower(trim($dj['type']));
        if ($contract === 'full-time' || $contract === 'cdi') $dj['type'] = 'CDI';
        elseif ($contract === 'internship' || $contract === 'stage') $dj['type'] = 'Stage';
        elseif ($contract === 'remote' || $contract === 'freelance') $dj['type'] = 'Freelance';
        elseif ($contract === 'part-time' || $contract === 'cdd') $dj['type'] = 'CDD';
        else $dj['type'] = strtoupper($dj['type']);

        $final_jobs[] = $dj;
    }
}

// Rendu HTML de la grille
if (empty($final_jobs)): ?>
    <div class="col-span-full py-16 px-4 border border-dashed border-slate-900 bg-slate-900/5 rounded-2xl text-center">
        <h3 class="text-base font-bold text-slate-300">Aucune offre disponible actuellement</h3>
        <p class="text-slate-500 text-xs mt-1">Modifiez vos critères de recherche ou vos filtres.</p>
    </div>
<?php else:
    foreach($final_jobs as $job): ?>
        <div class="bg-slate-900/10 border border-slate-900 hover:border-slate-800 rounded-2xl p-6 flex flex-col justify-between gap-4 hover:scale-[1.01] transition group backdrop-blur-sm">
            <div>
                <div class="flex justify-between items-start">
                    <div class="flex gap-4 items-center w-full">
                        <div class="w-11 h-11 shrink-0 relative flex items-center justify-center">
                            <?php 
                            $img_url = '';
                            if (!empty($job['logo'])) {
                                if (filter_var($job['logo'], FILTER_VALIDATE_URL)) {
                                    $img_url = $job['logo'];
                                } else {
                                    $img_url = "../uploads/" . $job['logo'];
                                }
                            }
                            ?>

                            <?php if (!empty($img_url)): ?>
                                <img src="<?= htmlspecialchars($img_url) ?>" 
                                     class="absolute inset-0 w-full h-full rounded-xl object-cover border border-slate-800 z-10"
                                     onerror="this.style.display='none';">
                            <?php endif; ?>

                            <div class="w-full h-full rounded-xl <?= htmlspecialchars($job['logo_bg'] ?? 'bg-indigo-600') ?> flex items-center justify-center font-bold text-sm text-white shadow-md uppercase tracking-wider">
                                <?= htmlspecialchars($job['logo_txt'] ?? 'JB') ?>
                            </div>
                        </div>

                        <div class="min-w-0 flex-1">
                            <h2 class="font-bold text-base tracking-tight text-slate-100 group-hover:text-indigo-400 transition truncate">
                                <?= htmlspecialchars($job['title']) ?>
                            </h2>
                            <p class="text-xs text-slate-400 mt-0.5 font-medium">
                                <?= htmlspecialchars($job['company']) ?> • <span class="text-indigo-400"><?= htmlspecialchars($job['location']) ?></span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2 text-[11px] font-medium mt-4">
                    <div class="bg-slate-950/60 border border-slate-900 p-2 rounded-xl flex items-center gap-1.5">
                        <span>💰</span> <span class="text-slate-300 truncate">Salaire: <b><?= htmlspecialchars(!empty($job['salary']) ? $job['salary'] : 'À négocier') ?></b></span>
                    </div>
                    <div class="bg-slate-950/60 border border-slate-900 p-2 rounded-xl flex items-center gap-1.5 text-indigo-400">
                        <span>💼</span> <span class="truncate">Contrat: <b><?= htmlspecialchars($job['type']) ?></b></span>
                    </div>
                    <div class="bg-slate-950/60 border border-slate-900 p-2 rounded-xl flex items-center gap-1.5">
                        <span>🔥</span> <span class="text-slate-300 truncate">Postulants: <b><?= (int)($job['apps'] ?? 0) ?></b></span>
                    </div>
                    <div class="bg-slate-950/60 border border-slate-900 p-2 rounded-xl flex items-center gap-1.5 text-slate-400">
                        <span>📅</span> <span class="truncate">Publié: <b><?= htmlspecialchars($job['posted'] ?? 'Récemment') ?></b></span>
                    </div>
                </div>

                <p class="text-xs text-slate-400 leading-relaxed mt-4 line-clamp-2">
                    <?= htmlspecialchars($job['description']) ?>
                </p>

                <div class="mt-4 pt-3 border-t border-slate-900/60 flex flex-wrap gap-1.5">
                    <span class="bg-slate-950 border border-slate-900 text-slate-400 px-2 py-0.5 rounded text-[10px] font-semibold">PHP</span>
                    <span class="bg-slate-950 border border-slate-900 text-slate-400 px-2 py-0.5 rounded text-[10px] font-semibold">MySQL</span>
                    <span class="bg-slate-950 border border-slate-900 text-slate-400 px-2 py-0.5 rounded text-[10px] font-semibold">Tailwind</span>
                </div>
            </div>

            <div class="flex justify-between items-center pt-3 border-t border-slate-900/40 mt-2 text-[11px]">
                <span class="text-slate-500 font-semibold flex items-center gap-1">
                    📍 <?= htmlspecialchars($job['location']) ?>
                </span>
                <a href="job_details.php?id=<?= $job['id'] ?>"
                   class="bg-slate-950 border border-slate-800 text-slate-300 hover:text-white hover:bg-slate-900 px-4 py-2 rounded-xl font-bold transition">
                    Voir détails →
                </a>
            </div>
        </div>
    <?php endforeach;
endif; ?>