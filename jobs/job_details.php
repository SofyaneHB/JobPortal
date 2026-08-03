<?php
session_start();

require_once "../config/db.php";
require_once "../includes/functions.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { header("Location: index.php"); exit; }

$job = null;
$ai_result = null;
$recommended_jobs = [];

try {
    if (isset($pdo)) {
        $stmt = $pdo->prepare("
            SELECT j.*, 
                   COALESCE(c.company_name, 'Entreprise Partner') as company_name,
                   c.logo,
                   (SELECT COUNT(id) FROM applications WHERE job_id = j.id) as applicants_count
            FROM jobs j
            LEFT JOIN companies c ON j.company_id = c.id
            WHERE j.id = ? LIMIT 1
        ");
        $stmt->execute([$id]);
        $db_job = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($db_job) {
            $job = [
                'id' => $db_job['id'],
                'title' => $db_job['title'],
                'company' => $db_job['company_name'],
                'location' => $db_job['location'],
                'type' => $db_job['type'],
                'salary' => $db_job['salary'],
                'description' => $db_job['description'],
                'logo' => $db_job['logo'],
                'applicants' => $db_job['applicants_count'],
                'education' => !empty($db_job['education']) ? $db_job['education'] : 'Bac+3 / Bac+5',
                'experience' => !empty($db_job['experience']) ? $db_job['experience'] : 'Junior / Senior',
                'created_at' => isset($db_job['created_at']) ? date('d M Y', strtotime($db_job['created_at'])) : 'Récemment',
                'requirements' => !empty($db_job['requirements']) ? explode(',', $db_job['requirements']) : ['Maîtrise de la stack technique', 'Autonomie et rigueur'],
                'tasks' => !empty($db_job['Tasks']) ? explode(',', $db_job['Tasks']) : ["Conception technique", "Développement de fonctionnalités", "Revues de code"],
                'skills' => !empty($db_job['skills']) ? $db_job['skills'] : '',
            ];
        }
    }
} catch (PDOException $e) {
    error_log("Erreur chargement offre: " . $e->getMessage());
}

if (!$job) {
    set_flash('error', 'Offre introuvable.');
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'analyze' || $action === 'apply') {
        $target_dir = "../uploads/cv/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

        $target_file = null;
        if (isset($_FILES['cv_file']) && $_FILES['cv_file']['error'] === UPLOAD_ERR_OK) {
            $file_ext = strtolower(pathinfo($_FILES['cv_file']['name'], PATHINFO_EXTENSION));
            $candidate_prefix = isset($_SESSION['user_id']) ? $_SESSION['user_id'] . "_" : "";
            $clean_name = time() . "_" . $candidate_prefix . preg_replace("/[^a-zA-Z0-9.]/", "_", pathinfo($_FILES['cv_file']['name'], PATHINFO_FILENAME)) . "." . $file_ext;
            $target_file = $target_dir . $clean_name;

            if (move_uploaded_file($_FILES['cv_file']['tmp_name'], $target_file)) {
                chmod($target_file, 0664);
            } else {
                set_flash('error', "Échec du stockage du CV.");
                header("Location: job_details.php?id={$id}");
                exit;
            }
        } else {
            set_flash('error', "Veuillez sélectionner un fichier PDF valide.");
            header("Location: job_details.php?id={$id}");
            exit;
        }

        if ($action === 'analyze') {
            $absolute_path = realpath($target_file) ?: dirname(__DIR__) . '/uploads/cv/' . basename($target_file);
            if (file_exists($absolute_path)) {
                $ai_files = [
                    '../ai/cv_parser.php', '../ai/skill_extractor.php',
                    '../ai/job_matcher.php', '../ai/recommendation_engine.php', '../ai/ai_service.php'
                ];
                foreach ($ai_files as $f) { if (file_exists($f)) require_once $f; }

                if (class_exists('AIService')) {
                    $aiService = new AIService($pdo);
                    $aiResponse = $aiService->analyzeCVForJob($absolute_path, $_FILES['cv_file']['type'], $job);
                    if ($aiResponse['success']) {
                        $ai_result = [
                            'score' => $aiResponse['score'],
                            'feedback' => $aiResponse['feedback'],
                            'skills' => $aiResponse['extracted_skills'] ?? [],
                            'recommendations' => $aiResponse['recommendations'] ?? null
                        ];
                    } else {
                        $ai_result = ['score' => 0, 'feedback' => $aiResponse['error'] ?? "Erreur", 'recommendations' => null];
                    }
                } else {
                    if (file_exists("../ai/job_matcher.php")) require_once "../ai/job_matcher.php";
                    if (class_exists('CVParser')) {
                        $text = CVParser::extractText($absolute_path, $_FILES['cv_file']['type']);
                        if (!empty(trim($text)) && class_exists('JobMatcher')) {
                            $matcher = new JobMatcher($pdo);
                            $res = $matcher->matchSpecificJobWithAI($text, $job);
                            $ai_result = ['score' => $res['score'], 'feedback' => $res['feedback'], 'recommendations' => null];
                        } else {
                            $ai_result = ['score' => 0, 'feedback' => "Impossible de lire le PDF.", 'recommendations' => null];
                        }
                    }
                }

                if (!empty($ai_result['skills'])) {
                    $cv_skills = array_map('strtolower', array_map('trim', $ai_result['skills']));
                    $cv_skills = array_values(array_unique(array_filter($cv_skills)));

                    if (!empty($cv_skills)) {
                        $stmt = $pdo->prepare("
                            SELECT j.id, j.title, j.location, j.type, j.salary, j.skills, j.description,
                                   COALESCE(c.company_name, 'Entreprise') as company_name
                            FROM jobs j
                            LEFT JOIN companies c ON j.company_id = c.id
                            WHERE j.status = 'active' AND j.id != ?
                        ");
                        $stmt->execute([$id]);
                        $all_jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        $scored_jobs = [];
                        foreach ($all_jobs as $j) {
                            if (empty($j['skills'])) continue;

                            $job_skills_raw = array_map('trim', explode(',', $j['skills']));
                            $job_skills = array_map('strtolower', $job_skills_raw);
                            $job_skills = array_values(array_unique(array_filter($job_skills)));

                            if (empty($job_skills)) continue;

                            $matched = array_values(array_intersect($cv_skills, $job_skills));
                            $matched_count = count($matched);
                            $total_job_skills = count($job_skills);

                            $score = $total_job_skills > 0 ? round(($matched_count / $total_job_skills) * 100) : 0;

                            $scored_jobs[] = [
                                'job' => $j,
                                'score' => (int)min(100, max(0, $score)),
                                'matched_skills' => $matched_count,
                                'matched_list' => $matched
                            ];
                        }

                        usort($scored_jobs, function($a, $b) {
                            return $b['score'] <=> $a['score'];
                        });

                        $recommended_jobs = array_slice($scored_jobs, 0, 6);
                    }
                }
            }
        }

        if ($action === 'apply') {
            if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'candidate') {
                set_flash('error', 'Connectez-vous en tant que candidat.');
                redirect("../Public/login.php"); exit;
            }
            try {
                $check = $pdo->prepare("SELECT id FROM applications WHERE job_id = ? AND candidate_id = ?");
                $check->execute([$job['id'], $_SESSION['user_id']]);
                if ($check->rowCount() > 0) {
                    set_flash('error', 'Déjà postulé.'); redirect("../candidate/applications.php"); exit;
                }
                
                // ENREGISTREMENT DE CV_PATH DANS LA BASE DE DONNÉES
                $insert = $pdo->prepare("INSERT INTO applications (job_id, candidate_id, cv_path, status, applied_at) VALUES (?, ?, ?, 'pending', NOW())");
                $insert->execute([$job['id'], $_SESSION['user_id'], $target_file]);

                // NOTIFICATION RECRUTEUR
                $stmtJob = $pdo->prepare("
                    SELECT j.title, c.user_id AS company_user_id 
                    FROM jobs j 
                    JOIN companies c ON j.company_id = c.id 
                    WHERE j.id = ? LIMIT 1
                ");
                $stmtJob->execute([$job['id']]);
                $jobInfo = $stmtJob->fetch(PDO::FETCH_ASSOC);

                if ($jobInfo) {
                    $stmtName = $pdo->prepare("SELECT full_name FROM users WHERE id = ? LIMIT 1");
                    $stmtName->execute([$_SESSION['user_id']]);
                    $candidateName = $stmtName->fetchColumn() ?? 'Un candidat';

                    $notifMsg = "Nouvelle candidature reçue de « " . $candidateName . " » pour le poste « " . htmlspecialchars($jobInfo['title']) . " ».";

                    $stmtNotif = $pdo->prepare("
                        INSERT INTO notifications (user_id, message, type, link, is_read, created_at)
                        VALUES (?, ?, 'new_application', 'applicants.php', 0, NOW())
                    ");
                    $stmtNotif->execute([$jobInfo['company_user_id'], $notifMsg]);
                }

                set_flash('success', "Candidature envoyée !"); redirect("../candidate/applications.php"); exit;
            } catch (PDOException $e) {
                error_log("Erreur candidature: " . $e->getMessage());
                set_flash('error', 'Erreur technique : ' . $e->getMessage()); redirect("job_details.php?id={$id}"); exit;
            }
        }
    }
}

$display_type = $job['type'];
if (in_array(strtolower($job['type']), ['full-time', 'cdi'])) $display_type = 'CDI';
if (in_array(strtolower($job['type']), ['internship', 'stage'])) $display_type = 'Stage';
if (in_array(strtolower($job['type']), ['freelance'])) $display_type = 'Freelance';

function scoreColor($s) {
    if ($s >= 75) return ['bg' => 'bg-emerald-500/10', 'text' => 'text-emerald-400', 'border' => 'border-emerald-500/20', 'bar' => 'from-emerald-400 to-emerald-600'];
    if ($s >= 50) return ['bg' => 'bg-indigo-500/10', 'text' => 'text-indigo-400', 'border' => 'border-indigo-500/20', 'bar' => 'from-indigo-400 to-violet-500'];
    if ($s >= 30) return ['bg' => 'bg-amber-500/10', 'text' => 'text-amber-400', 'border' => 'border-amber-500/20', 'bar' => 'from-amber-400 to-orange-500'];
    return ['bg' => 'bg-rose-500/10', 'text' => 'text-rose-400', 'border' => 'border-rose-500/20', 'bar' => 'from-rose-400 to-red-600'];
}
?>

<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($job['title']); ?> | Détails</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass {
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255,255,255,0.05);
        }
        .glass-strong {
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.06);
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(16px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .anim-up { animation: slideUp 0.5s ease forwards; opacity: 0; }
        .anim-d1 { animation-delay: 0.08s; }
        .anim-d2 { animation-delay: 0.16s; }
        .anim-d3 { animation-delay: 0.24s; }
        .anim-d4 { animation-delay: 0.32s; }

        .progress-track {
            height: 6px;
            background: rgba(255,255,255,0.04);
            border-radius: 999px;
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            border-radius: 999px;
            transition: width 1.2s cubic-bezier(0.22, 1, 0.36, 1);
            position: relative;
        }
        .progress-fill::after {
            content: ''; position: absolute; inset: 0;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            animation: shimmer 2.5s infinite;
        }
        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        .rec-card { transition: all 0.2s ease; }
        .rec-card:hover { border-color: rgba(99,102,241,0.25); transform: translateY(-1px); }

        .tag-match {
            background: rgba(34,197,94,0.1); color: #4ade80; border: 1px solid rgba(34,197,94,0.18);
            font-size: 11px; font-weight: 600; padding: 3px 10px; border-radius: 6px;
        }
        .tag-miss {
            background: rgba(234,179,8,0.1); color: #facc15; border: 1px solid rgba(234,179,8,0.18);
            font-size: 11px; font-weight: 600; padding: 3px 10px; border-radius: 6px;
        }
        .tag-more {
            background: rgba(255,255,255,0.04); color: #64748b; border: 1px solid rgba(255,255,255,0.06);
            font-size: 11px; font-weight: 600; padding: 3px 10px; border-radius: 6px;
        }

        .alert-box {
            padding: 12px 14px; border-radius: 10px; font-size: 12px; line-height: 1.55;
            margin-bottom: 8px; border-left: 3px solid transparent;
        }
        .alert-success { background: rgba(34,197,94,0.06); border-left-color: #22c55e; color: #86efac; }
        .alert-info    { background: rgba(59,130,246,0.06); border-left-color: #3b82f6; color: #93c5fd; }
        .alert-warning { background: rgba(234,179,8,0.06); border-left-color: #eab308; color: #fde047; }
        .alert-danger  { background: rgba(239,68,68,0.06); border-left-color: #ef4444; color: #fca5a5; }
        .alert-tip     { background: rgba(139,92,246,0.06); border-left-color: #8b5cf6; color: #c4b5fd; }
        .alert-action  { background: rgba(236,72,153,0.06); border-left-color: #ec4899; color: #f9a8d4; }

        .job-rec-card {
            background: rgba(15, 23, 42, 0.5);
            border: 1px solid rgba(255,255,255,0.04);
            border-radius: 14px;
            padding: 16px;
            transition: all 0.25s ease;
        }
        .job-rec-card:hover {
            background: rgba(15, 23, 42, 0.7);
            border-color: rgba(99, 102, 241, 0.25);
            transform: translateY(-3px);
            box-shadow: 0 8px 30px rgba(0,0,0,0.3);
        }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen pb-24 selection:bg-indigo-500 selection:text-white">

    <nav class="border-b border-slate-900 bg-slate-950/80 backdrop-blur-md sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-4 h-16 flex items-center justify-between">
            <a href="index.php" class="font-extrabold text-transparent bg-gradient-to-r from-indigo-400 to-cyan-400 bg-clip-text text-lg tracking-tight">
                Sofyane_HB Jobs
            </a>
            <a href="index.php" class="text-xs font-semibold text-slate-400 hover:text-slate-200 transition bg-slate-900 border border-slate-800/80 px-4 py-2 rounded-xl">
                &larr; Retour aux offres
            </a>
        </div>
    </nav>

    <main class="max-w-6xl mx-auto px-4 mt-10">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">

            <div class="lg:col-span-2 space-y-8">
                <div class="p-6 glass rounded-2xl relative overflow-hidden">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 bg-indigo-600/10 border border-indigo-500/20 text-indigo-400 rounded-xl flex items-center justify-center font-bold text-lg uppercase shrink-0">
                                <?php echo strtoupper(substr($job['company'], 0, 2)); ?>
                            </div>
                            <div>
                                <span class="inline-flex items-center text-[10px] font-bold uppercase tracking-widest px-2 py-0.5 rounded-md bg-indigo-500/10 text-indigo-400 border border-indigo-500/10">
                                    <?php echo htmlspecialchars($display_type); ?>
                                </span>
                                <h1 class="text-xl md:text-2xl font-extrabold mt-1 text-white tracking-tight"><?php echo htmlspecialchars($job['title']); ?></h1>
                                <p class="text-xs text-slate-400 mt-1 font-medium">
                                    <span class="text-indigo-400 font-semibold"><?php echo htmlspecialchars($job['company']); ?></span> &bull; &#128205; <?php echo htmlspecialchars($job['location']); ?>
                                </p>
                            </div>
                        </div>
                        <div class="text-left md:text-right text-xs text-slate-400 space-y-1 border-t md:border-t-0 pt-3 md:pt-0 border-slate-800">
                            <div>&#128176; <span class="text-slate-200 font-bold"><?php echo htmlspecialchars($job['salary']); ?></span></div>
                            <div>&#128197; <span class="text-slate-300"><?php echo htmlspecialchars($job['created_at']); ?></span></div>
                            <div>&#128293; Candidats : <span class="text-indigo-400 font-bold"><?php echo (int)$job['applicants']; ?></span></div>
                        </div>
                    </div>
                </div>

                <div class="space-y-3">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 flex items-center gap-2">
                        <span class="w-1 h-3 bg-indigo-500 rounded-full"></span>
                        Description du poste & missions
                    </h2>
                    <div class="text-slate-300 text-sm leading-relaxed glass p-6 rounded-2xl whitespace-pre-line">
                        <p class="mb-4"><?php echo htmlspecialchars($job['description']); ?></p>
                        <div class="mt-4 font-semibold flex items-center gap-1.5 text-xs uppercase tracking-wider text-slate-400">
                            <span>&#127919;</span> Taches quotidiennes :
                        </div>
                        <ul class="list-disc pl-5 mt-2 space-y-2 text-xs text-slate-400">
                            <?php foreach($job['tasks'] as $task): ?>
                                <li><?php echo htmlspecialchars($task); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>

                <div class="space-y-3">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 flex items-center gap-2">
                        <span class="w-1 h-3 bg-cyan-400 rounded-full"></span>
                        Profil recherche & competences requises
                    </h2>
                    <div class="glass rounded-2xl p-6 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pb-3 border-b border-slate-800/60 text-xs">
                            <div>&#127891; <span class="text-slate-400">Education :</span> <span class="text-slate-200 font-semibold"><?php echo htmlspecialchars($job['education']); ?></span></div>
                            <div>&#9889; <span class="text-slate-400">Experience :</span> <span class="text-slate-200 font-semibold"><?php echo htmlspecialchars($job['experience']); ?></span></div>
                        </div>
                        <div class="space-y-2.5">
                            <?php foreach ($job['requirements'] as $req): ?>
                                <div class="flex items-start gap-3 text-xs text-slate-400">
                                    <svg class="w-4 h-4 text-cyan-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"></path>
                                    </svg>
                                    <span><?php echo htmlspecialchars(trim($req)); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <?php if (!empty($recommended_jobs)): ?>
                <div class="space-y-5 pt-6 border-t border-slate-800/40">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">&#128188;</span>
                            <h2 class="text-sm font-extrabold text-white tracking-tight uppercase">
                                Offres recommandees selon votre CV
                            </h2>
                        </div>
                        <span class="text-[10px] text-slate-500 bg-slate-900 border border-slate-800 px-2 py-1 rounded-lg">
                            <?php echo count($recommended_jobs); ?> offres
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php foreach ($recommended_jobs as $match): 
                            $rj = $match['job'];
                            $r_score = (int)$match['score'];
                            $r_colors = scoreColor($r_score);
                        ?>
                        <a href="job_details.php?id=<?php echo (int)$rj['id']; ?>" class="job-rec-card group block">
                            <div class="flex items-start justify-between mb-2">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-lg bg-slate-800 border border-slate-700 flex items-center justify-center text-indigo-400 font-bold text-[10px] uppercase">
                                        <?php echo strtoupper(substr($rj['company_name'] ?? 'JO', 0, 2)); ?>
                                    </div>
                                    <div class="min-w-0">
                                        <h3 class="text-xs font-bold text-slate-100 group-hover:text-indigo-300 transition truncate">
                                            <?php echo htmlspecialchars($rj['title']); ?>
                                        </h3>
                                        <p class="text-[10px] text-slate-500 truncate"><?php echo htmlspecialchars($rj['company_name'] ?? 'Entreprise'); ?></p>
                                    </div>
                                </div>
                                <span class="text-[10px] font-extrabold px-2 py-1 rounded-md <?php echo $r_colors['bg'] . ' ' . $r_colors['text']; ?> shrink-0">
                                    <?php echo $r_score; ?>%
                                </span>
                            </div>

                            <div class="progress-track mb-3">
                                <div class="progress-fill bg-gradient-to-r <?php echo $r_colors['bar']; ?>" style="width: <?php echo $r_score; ?>%"></div>
                            </div>

                            <div class="flex items-center justify-between text-[10px] text-slate-400 mb-2">
                                <span>&#128205; <?php echo htmlspecialchars($rj['location'] ?? 'Remote'); ?></span>
                                <span class="text-indigo-400 font-semibold"><?php echo htmlspecialchars($rj['salary'] ?? 'A negocier'); ?></span>
                            </div>

                            <?php if (!empty($match['matched_list'])): ?>
                            <div class="flex flex-wrap gap-1 items-center">
                                <span class="text-[10px] text-slate-500 mr-1"><?php echo count($match['matched_list']); ?> match</span>
                                <?php foreach (array_slice($match['matched_list'], 0, 3) as $sk): ?>
                                    <span class="text-[9px] bg-emerald-500/10 text-emerald-400 px-1.5 py-0.5 rounded border border-emerald-500/15"><?php echo htmlspecialchars($sk); ?></span>
                                <?php endforeach; ?>
                                <?php if (count($match['matched_list']) > 3): ?>
                                    <span class="text-[9px] text-slate-600">+<?php echo count($match['matched_list']) - 3; ?></span>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

            </div>

            <div class="lg:col-span-1 lg:sticky lg:top-24 space-y-5">
                <div class="p-6 glass-strong rounded-2xl space-y-5">
                    <h3 class="font-extrabold text-sm text-slate-200 uppercase tracking-wider">Candidature express IA</h3>
                    <form method="POST" enctype="multipart/form-data" class="space-y-4" id="applicationForm">
                        <input type="hidden" name="job_id" value="<?php echo (int)$job['id']; ?>">
                        <div>
                            <div class="relative group cursor-pointer border border-dashed border-slate-700 hover:border-indigo-500/50 bg-slate-950 rounded-xl p-4 text-center transition">
                                <input type="file" name="cv_file" required accept=".pdf" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" id="cvInput">
                                <div class="space-y-1">
                                    <div class="text-xl" id="uploadIcon">&#128196;</div>
                                    <div class="text-xs font-semibold text-slate-400 group-hover:text-slate-200 transition" id="fileNameDisplay">Choisir un fichier PDF</div>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <button type="submit" name="action" value="analyze" class="bg-slate-800 hover:bg-slate-700 font-bold uppercase tracking-wider text-[10px] rounded-xl py-3.5 text-slate-200 transition">
                                Analyser CV
                            </button>
                            <button type="submit" name="action" value="apply" class="bg-indigo-600 hover:bg-indigo-500 font-bold uppercase tracking-wider text-[10px] rounded-xl py-3.5 text-white shadow-lg shadow-indigo-600/20 transition">
                                Postuler
                            </button>
                        </div>
                    </form>
                </div>

                <?php if ($ai_result): ?>
                    <?php
                    $score = (int)$ai_result['score'];
                    $colors = scoreColor($score);
                    $rec = $ai_result['recommendations'] ?? null;
                    ?>

                    <div class="p-5 rec-card glass-strong rounded-2xl anim-up anim-d1">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg <?php echo $colors['bg']; ?> border <?php echo $colors['border']; ?> flex items-center justify-center text-sm">
                                    <?php echo $score >= 75 ? '&#127775;' : ($score >= 50 ? '&#9989;' : ($score >= 30 ? '&#9888;&#65039;' : '&#128218;')); ?>
                                </div>
                                <div>
                                    <div class="text-xs font-bold text-slate-200 uppercase tracking-wider">Match IA</div>
                                    <div class="text-[10px] text-slate-500">Base sur votre CV</div>
                                </div>
                            </div>
                            <span class="text-lg font-extrabold <?php echo $colors['text']; ?>"><?php echo $score; ?>%</span>
                        </div>
                        <div class="progress-track mb-4">
                            <div class="progress-fill bg-gradient-to-r <?php echo $colors['bar']; ?>" style="width: <?php echo $score; ?>%"></div>
                        </div>
                        <p class="text-[11px] text-slate-400 leading-relaxed">
                            <?php echo htmlspecialchars($ai_result['feedback']); ?>
                        </p>
                    </div>

                    <?php if (!empty($rec)): ?>
                        <div class="p-5 rec-card glass-strong rounded-2xl anim-up anim-d2">
                            <div class="flex items-center gap-2 mb-3">
                                <span class="text-sm">&#127919;</span>
                                <span class="text-xs font-bold text-slate-300 uppercase tracking-wider">Recommandations</span>
                            </div>

                            <?php if (!empty($rec['general_advice'])): ?>
                                <?php foreach ($rec['general_advice'] as $advice): ?>
                                    <div class="alert-box alert-<?php echo $advice['type']; ?>">
                                        <?php echo htmlspecialchars($advice['message']); ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>

                            <?php if (!empty($rec['priority_actions'])): ?>
                                <div class="mt-3 bg-indigo-500/[0.04] border border-indigo-500/10 rounded-lg p-3">
                                    <div class="text-[10px] font-bold text-indigo-400 uppercase tracking-wider mb-2">&#9889; Actions prioritaires</div>
                                    <ul class="space-y-1.5">
                                        <?php foreach ($rec['priority_actions'] as $action): ?>
                                            <li class="text-[11px] text-slate-300 flex items-start gap-2">
                                                <span class="text-indigo-400 mt-0.5 text-[10px]">&#9656;</span>
                                                <?php echo htmlspecialchars($action); ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($rec['matched_skills']) || !empty($rec['missing_skills'])): ?>
                        <div class="p-5 rec-card glass-strong rounded-2xl anim-up anim-d3">
                            <div class="flex items-center gap-2 mb-4">
                                <span class="text-sm">&#128269;</span>
                                <span class="text-xs font-bold text-slate-300 uppercase tracking-wider">Analyse des competences</span>
                            </div>

                            <?php if (!empty($rec['matched_skills'])): ?>
                                <div class="mb-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-[11px] font-semibold text-emerald-400">&#9989; Competences alignees</span>
                                        <span class="text-[10px] bg-emerald-500/10 text-emerald-400 px-1.5 py-0.5 rounded font-bold"><?php echo count($rec['matched_skills']); ?></span>
                                    </div>
                                    <div class="flex flex-wrap gap-1.5">
                                        <?php foreach ($rec['matched_skills'] as $skill): ?>
                                            <span class="tag-match"><?php echo htmlspecialchars($skill); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($rec['missing_skills'])): ?>
                                <div class="pt-3 border-t border-slate-800/50">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-[11px] font-semibold text-amber-400">&#128218; A developper</span>
                                        <span class="text-[10px] bg-amber-500/10 text-amber-400 px-1.5 py-0.5 rounded font-bold"><?php echo count($rec['missing_skills']); ?></span>
                                    </div>
                                    <div class="flex flex-wrap gap-1.5">
                                        <?php foreach (array_slice($rec['missing_skills'], 0, 8) as $skill): ?>
                                            <span class="tag-miss"><?php echo htmlspecialchars($skill); ?></span>
                                        <?php endforeach; ?>
                                        <?php if (count($rec['missing_skills']) > 8): ?>
                                            <span class="tag-more">+<?php echo count($rec['missing_skills']) - 8; ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($rec['estimated_learning_time'])): ?>
                                <div class="flex items-center gap-2 mt-4 pt-3 border-t border-slate-800/30 text-[11px] text-slate-400">
                                    <span>&#9201;</span>
                                    <span>Estimation : <span class="text-slate-200 font-semibold"><?php echo htmlspecialchars($rec['estimated_learning_time']); ?></span></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($rec['specific_tips'])): ?>
                        <div class="p-5 rec-card glass-strong rounded-2xl anim-up anim-d3 border-amber-500/10">
                            <div class="flex items-center gap-2 mb-3">
                                <span class="text-sm">&#128161;</span>
                                <span class="text-xs font-bold text-slate-300 uppercase tracking-wider">Conseils pour ce poste</span>
                            </div>
                            <ul class="space-y-2.5">
                                <?php foreach ($rec['specific_tips'] as $tip): ?>
                                    <li class="text-[11px] text-slate-300 leading-relaxed flex items-start gap-2">
                                        <span class="text-amber-400 mt-0.5 shrink-0">&bull;</span>
                                        <?php echo htmlspecialchars($tip); ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($rec['competitiveness'])): ?>
                        <div class="p-4 rec-card glass-strong rounded-2xl anim-up anim-d4">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="text-sm">&#128202;</span>
                                <span class="text-xs font-bold text-slate-300 uppercase tracking-wider">Votre positionnement</span>
                            </div>
                            <div class="text-center py-2">
                                <div class="text-sm font-bold
                                    <?php echo $rec['competitiveness']['level'] === 'high' ? 'text-emerald-400' : ($rec['competitiveness']['level'] === 'medium' ? 'text-amber-400' : 'text-rose-400'); ?>">
                                    <?php echo htmlspecialchars($rec['competitiveness']['advice']); ?>
                                </div>
                                <div class="text-[10px] text-slate-500 mt-1">Ratio match / lacunes : <?php echo $rec['competitiveness']['ratio']; ?></div>
                            </div>
                        </div>
                        <?php endif; ?>

                    <?php endif; ?>
                <?php endif; ?>

            </div>
        </div>
    </main>

    <script>
        const cvInput = document.getElementById('cvInput');
        const fileNameDisplay = document.getElementById('fileNameDisplay');
        if(cvInput) {
            cvInput.addEventListener('change', function() {
                if(this.files.length > 0) fileNameDisplay.textContent = this.files[0].name;
            });
        }
    </script>
</body>
</html>