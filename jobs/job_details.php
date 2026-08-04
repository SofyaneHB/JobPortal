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
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($job['title']); ?> — Job Details</title>
    <link rel="icon" type="image/png" href="../assets/img/logo_jp.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass {
            background: rgba(15, 23, 42, 0.5);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255,255,255,0.04);
        }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen antialiased selection:bg-indigo-500 selection:text-white">

    <!-- Navbar -->
    <nav class="fixed top-0 left-0 right-0 z-50 glass border-b border-slate-800/50">
        <div class="max-w-6xl mx-auto px-6 h-16 flex items-center justify-between">
            <a href="index.php" class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-cyan-500 flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0"/>
                    </svg>
                </div>
                <span class="font-bold text-lg tracking-tight">Jobs <span class="text-indigo-400">Offers</span></span>
            </a>
        </div>
    </nav>

    <!-- Hero Glow -->
    <div class="pt-24 relative overflow-hidden">
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[700px] h-[300px] bg-indigo-600/8 rounded-full blur-[100px] pointer-events-none"></div>
    </div>

    <main class="max-w-6xl mx-auto px-6 pb-24 relative">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

            <!-- Left Column -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Job Header Card -->
                <div class="glass rounded-2xl p-6 md:p-8">
                    <div class="flex flex-col md:flex-row md:items-start justify-between gap-5">
                        <div class="flex items-start gap-4">
                            <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-indigo-600 to-violet-600 flex items-center justify-center font-bold text-lg text-white shadow-lg shadow-indigo-900/20 shrink-0">
                                <?php echo strtoupper(substr($job['company'], 0, 2)); ?>
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 mb-1.5 flex-wrap">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 text-[10px] font-bold uppercase tracking-wider">
                                        <?php echo htmlspecialchars($display_type); ?>
                                    </span>
                                    <?php if ((int)$job['applicants'] > 0): ?>
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-slate-800/60 text-slate-400 text-[10px] font-semibold">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
                                        </svg>
                                        <?php echo (int)$job['applicants']; ?> applicants
                                    </span>
                                    <?php endif; ?>
                                </div>
                                <h1 class="text-2xl md:text-3xl font-extrabold text-white tracking-tight leading-tight">
                                    <?php echo htmlspecialchars($job['title']); ?>
                                </h1>
                                <p class="text-sm text-slate-400 mt-1.5 flex items-center gap-1.5 flex-wrap">
                                    <span class="text-indigo-400 font-semibold"><?php echo htmlspecialchars($job['company']); ?></span>
                                    <span class="text-slate-600">&bull;</span>
                                    <span class="flex items-center gap-1">
                                        <?php echo htmlspecialchars($job['location']); ?>
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div class="flex flex-row md:flex-col gap-3 md:gap-2 text-xs text-slate-400 md:text-right shrink-0">
                            <div class="flex items-center md:justify-end gap-1.5 px-3 py-2 rounded-xl bg-slate-950/40 border border-slate-800/60">
                               
                                <span class="text-slate-200 font-semibold"><?php echo htmlspecialchars($job['salary'] ?: 'Negotiable'); ?> Dhs</span>
                            </div>
                            <div class="flex items-center md:justify-end gap-1.5 px-3 py-2 rounded-xl bg-slate-950/40 border border-slate-800/60">
                                
                                <?php echo htmlspecialchars($job['created_at']); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div class="glass rounded-2xl p-6 md:p-8 space-y-5">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center">
                            <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <h2 class="text-sm font-bold text-white uppercase tracking-wider">Job Description</h2>
                    </div>
                    <div class="text-sm text-slate-300 leading-relaxed whitespace-pre-line">
                        <?php echo htmlspecialchars($job['description']); ?>
                    </div>
                </div>

                <!-- Tasks -->
                <div class="glass rounded-2xl p-6 md:p-8 space-y-5">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-cyan-500/10 border border-cyan-500/20 flex items-center justify-center">
                            <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                            </svg>
                        </div>
                        <h2 class="text-sm font-bold text-white uppercase tracking-wider">Daily Tasks</h2>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <?php foreach($job['tasks'] as $task): ?>
                        <div class="flex items-start gap-3 p-3.5 rounded-xl bg-slate-950/40 border border-slate-800/60">
                            <div class="w-5 h-5 rounded-md bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center shrink-0 mt-0.5">
                                <svg class="w-3 h-3 text-indigo-400" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                                </svg>
                            </div>
                            <span class="text-sm text-slate-300"><?php echo htmlspecialchars(trim($task)); ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Requirements -->
                <div class="glass rounded-2xl p-6 md:p-8 space-y-5">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-violet-500/10 border border-violet-500/20 flex items-center justify-center">
                            <svg class="w-4 h-4 text-violet-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.125 2.25h-4.5c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125v-9M10.125 2.25h.375a9 9 0 019 9v.375M10.125 2.25A3.375 3.375 0 0113.875 5.625v1.5c0 .621.504 1.125 1.125 1.125h1.5a3.375 3.375 0 013.375 3.375M9 15l2.25 2.25L15 12"/>
                            </svg>
                        </div>
                        <h2 class="text-sm font-bold text-white uppercase tracking-wider">Requirements</h2>
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
                        <div class="p-4 rounded-xl bg-slate-950/40 border border-slate-800/60">
                            <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Education</div>
                            <div class="text-sm text-slate-200 font-medium"><?php echo htmlspecialchars($job['education']); ?></div>
                        </div>
                        <div class="p-4 rounded-xl bg-slate-950/40 border border-slate-800/60">
                            <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Experience</div>
                            <div class="text-sm text-slate-200 font-medium"><?php echo htmlspecialchars($job['experience']); ?></div>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <?php foreach ($job['requirements'] as $req): ?>
                        <div class="flex items-start gap-3 text-sm text-slate-300">
                            <div class="w-5 h-5 rounded-full bg-cyan-500/10 border border-cyan-500/20 flex items-center justify-center shrink-0 mt-0.5">
                                <svg class="w-3 h-3 text-cyan-400" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                                </svg>
                            </div>
                            <span><?php echo htmlspecialchars(trim($req)); ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Recommended Jobs -->
                <?php if (!empty($recommended_jobs)): ?>
                <div class="pt-4">
                    <div class="flex items-center justify-between mb-5">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-amber-500/10 border border-amber-500/20 flex items-center justify-center">
                                <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 11.25v8.25a1.5 1.5 0 01-1.5 1.5H4.5a1.5 1.5 0 01-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 109.375 7.5H12m0-2.625V7.5m0-2.625A2.625 2.625 0 1114.625 7.5H12m0 0V21m-8.625-9.75h18c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125h-18c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>
                                </svg>
                            </div>
                            <h2 class="text-sm font-bold text-white uppercase tracking-wider">Recommended for you</h2>
                        </div>
                        <span class="text-[10px] text-slate-500 bg-slate-900 border border-slate-800 px-2.5 py-1 rounded-lg font-semibold">
                            <?php echo count($recommended_jobs); ?> jobs
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <?php foreach ($recommended_jobs as $match): 
                            $rj = $match['job'];
                            $r_score = (int)$match['score'];
                            $r_colors = scoreColor($r_score);
                        ?>
                        <a href="job_details.php?id=<?php echo (int)$rj['id']; ?>" class="glass glass-hover rounded-2xl p-5 block group">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-10 h-10 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-center text-indigo-400 font-bold text-xs uppercase shrink-0">
                                        <?php echo strtoupper(substr($rj['company_name'] ?? 'JO', 0, 2)); ?>
                                    </div>
                                    <div class="min-w-0">
                                        <h3 class="text-sm font-bold text-slate-100 group-hover:text-indigo-300 transition truncate">
                                            <?php echo htmlspecialchars($rj['title']); ?>
                                        </h3>
                                        <p class="text-[11px] text-slate-500 truncate"><?php echo htmlspecialchars($rj['company_name'] ?? 'Entreprise'); ?></p>
                                    </div>
                                </div>
                                <span class="text-[10px] font-extrabold px-2 py-1 rounded-lg <?php echo $r_colors['bg'] . ' ' . $r_colors['text']; ?> shrink-0">
                                    <?php echo $r_score; ?>%
                                </span>
                            </div>
                            <div class="progress-track mb-3">
                                <div class="progress-fill bg-gradient-to-r <?php echo $r_colors['bar']; ?>" style="width: <?php echo $r_score; ?>%"></div>
                            </div>
                            <div class="flex items-center justify-between text-[11px] text-slate-400">
                                <span class="flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    </svg>
                                    <?php echo htmlspecialchars($rj['location'] ?? 'Remote'); ?>
                                </span>
                                <span class="text-slate-200 font-semibold"><?php echo htmlspecialchars($rj['salary'] ?? 'Negotiable'); ?></span>
                            </div>
                            <?php if (!empty($match['matched_list'])): ?>
                            <div class="flex flex-wrap gap-1.5 mt-3 pt-3 border-t border-slate-800/40">
                                <?php foreach (array_slice($match['matched_list'], 0, 3) as $sk): ?>
                                    <span class="text-[10px] bg-emerald-500/8 text-emerald-400 px-2 py-0.5 rounded-md border border-emerald-500/15 font-medium"><?php echo htmlspecialchars($sk); ?></span>
                                <?php endforeach; ?>
                                <?php if (count($match['matched_list']) > 3): ?>
                                    <span class="text-[10px] text-slate-600 font-medium">+<?php echo count($match['matched_list']) - 3; ?></span>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

            </div>

            <!-- Right Column: Sticky Sidebar -->
            <div class="lg:col-span-1 lg:sticky lg:top-24 space-y-5">

                <!-- Apply Card -->
                <div class="glass rounded-2xl p-6 space-y-5">
                    <div class="flex items-center gap-3">
                     
                        <h3 class="text-sm font-bold text-white uppercase tracking-wider">Apply Now</h3>
                    </div>
                    
                    <form method="POST" enctype="multipart/form-data" class="space-y-4" id="applicationForm">
                        <input type="hidden" name="job_id" value="<?php echo (int)$job['id']; ?>">
                        
                        <div class="relative group cursor-pointer">
                            <input type="file" name="cv_file" required accept=".pdf" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" id="cvInput">
                            <div class="border border-dashed border-slate-700 hover:border-indigo-500/40 bg-slate-950/40 rounded-xl p-5 text-center transition-all group-hover:bg-slate-950/60">
                                <div class="text-xs font-semibold text-slate-400 transition" id="fileNameDisplay">Upload your CV (PDF)</div>
                                <div class="text-[10px] text-slate-600 mt-1">Click or drag & drop</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2.5 ">
                            <button type="submit" name="action" value="analyze" class="bg-slate-800 hover:bg-slate-700 text-slate-200 font-semibold text-[11px] uppercase tracking-wider rounded-xl py-3 transition active:scale-[0.97]">
                                Analyze
                            </button>
                            <button type="submit" name="action" value="apply" class="bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-[11px] uppercase tracking-wider rounded-xl py-3 transition shadow-lg shadow-indigo-600/20 active:scale-[0.97]">
                                Apply
                            </button>
                        </div>
                    </form>
                </div>

                <!-- AI Results -->
                <?php if ($ai_result): ?>
                    <?php
                    $score = (int)$ai_result['score'];
                    $colors = scoreColor($score);
                    $rec = $ai_result['recommendations'] ?? null;
                    ?>

                    <!-- Score Card -->
                    <div class="glass rounded-2xl p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <div>
                                    <div class="text-xs font-bold text-white uppercase tracking-wider">AI Match</div>
                                </div>
                            </div>
                            <span class="text-2xl font-extrabold <?php echo $colors['text']; ?>"><?php echo $score; ?>%</span>
                        </div>
                        <div class="progress-track mb-4">
                            <div class="progress-fill bg-gradient-to-r <?php echo $colors['bar']; ?>" style="width: <?php echo $score; ?>%"></div>
                        </div>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            <?php echo htmlspecialchars($ai_result['feedback']); ?>
                        </p>
                    </div>

                    <?php if (!empty($rec)): ?>
                        <!-- Recommendations -->
                        <div class="glass rounded-2xl p-6 space-y-4">
                            <div class="flex items-center gap-3">
                                <h3 class="text-xs font-bold text-white uppercase tracking-wider">Recommendations</h3>
                            </div>

                            <div class="text-xs">
                                <?php if (!empty($rec['general_advice'])): ?>
                                    <?php foreach ($rec['general_advice'] as $advice): ?>
                                        <div class="alert-box alert-<?php echo $advice['type']; ?>">
                                            <?php echo htmlspecialchars($advice['message']); ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($rec['priority_actions'])): ?>
                                <div class="bg-indigo-500/[0.04] border border-indigo-500/10 rounded-xl p-4">
                                    <div class="text-[10px] font-bold text-indigo-400 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                                        
                                        Priority Actions
                                    </div>
                                    <ul class="space-y-2">
                                        <?php foreach ($rec['priority_actions'] as $action): ?>
                                            <li class="text-xs text-slate-300 flex items-start gap-2">
                                                <span class="text-indigo-400 mt-0.5 text-[10px]">&#9656;</span>
                                                <?php echo htmlspecialchars($action); ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Skills Analysis -->
                        <?php if (!empty($rec['matched_skills']) || !empty($rec['missing_skills'])): ?>
                        <div class="glass rounded-2xl p-6 space-y-5">
                            <div class="flex items-center gap-3">
                                <h3 class="text-xs font-bold text-white uppercase tracking-wider">Skills Analysis</h3>
                            </div>

                            <?php if (!empty($rec['matched_skills'])): ?>
                                <div>
                                    <div class="flex items-center justify-between mb-2.5">
                                        <span class="text-[11px] font-semibold text-emerald-400 flex items-center gap-1.5">
                                            Matched
                                        </span>
                                        <span class="text-[10px] bg-emerald-500/10 text-emerald-400 px-2 py-0.5 rounded-md font-bold border border-emerald-500/15"><?php echo count($rec['matched_skills']); ?></span>
                                    </div>
                                    <div class="flex flex-wrap gap-1.5">
                                        <?php foreach ($rec['matched_skills'] as $skill): ?>
                                            <span class="tag-match text-[11px] font-semibold px-2.5 py-1 rounded-md"><?php echo htmlspecialchars($skill); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($rec['missing_skills'])): ?>
                                <div class="pt-4 border-t border-slate-800/50">
                                    <div class="flex items-center justify-between mb-2.5">
                                        <span class="text-[11px] font-semibold text-amber-400 flex items-center gap-1.5">
                                            To Develop
                                        </span>
                                    </div>
                                    <div class="flex flex-wrap gap-1.5">
                                        <?php foreach (array_slice($rec['missing_skills'], 0, 8) as $skill): ?>
                                            <span class="tag-miss text-[11px] font-semibold px-2.5 py-1 rounded-md"><?php echo htmlspecialchars($skill); ?></span>
                                        <?php endforeach; ?>
                                        <?php if (count($rec['missing_skills']) > 8): ?>
                                            <span class="tag-more text-[11px] font-semibold px-2.5 py-1 rounded-md">+<?php echo count($rec['missing_skills']) - 8; ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($rec['estimated_learning_time'])): ?>
                                <div class="flex items-center gap-2 pt-3 border-t border-slate-800/30 text-[11px] text-slate-400">
                                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Estimated learning: <span class="text-slate-200 font-semibold"><?php echo htmlspecialchars($rec['estimated_learning_time']); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <!-- Specific Tips -->
                        <?php if (!empty($rec['specific_tips'])): ?>
                        <div class="glass rounded-2xl p-6 space-y-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-pink-500/10 border border-pink-500/20 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-pink-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z"/>
                                    </svg>
                                </div>
                                <h3 class="text-xs font-bold text-white uppercase tracking-wider">Tips for this role</h3>
                            </div>
                            <ul class="space-y-3">
                                <?php foreach ($rec['specific_tips'] as $tip): ?>
                                    <li class="text-xs text-slate-300 leading-relaxed flex items-start gap-2.5">
                                        <span class="w-1 h-1 rounded-full bg-pink-400 mt-1.5 shrink-0"></span>
                                        <?php echo htmlspecialchars($tip); ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endif; ?>

                        <!-- Competitiveness -->
                        <?php if (!empty($rec['competitiveness'])): ?>
                        <div class="glass rounded-2xl p-5">
                            <div class="flex items-center gap-3 mb-3">
                                
                                <h3 class="text-xs font-bold text-white uppercase tracking-wider">Positioning</h3>
                            </div>
                            <div class="text-center py-2">
                                <div class="text-sm font-bold <?php echo $rec['competitiveness']['level'] === 'high' ? 'text-emerald-400' : ($rec['competitiveness']['level'] === 'medium' ? 'text-amber-400' : 'text-rose-400'); ?>">
                                    <?php echo htmlspecialchars($rec['competitiveness']['advice']); ?>
                                </div>
                                <div class="text-[10px] text-slate-500 mt-1">Match ratio: <?php echo $rec['competitiveness']['ratio']; ?></div>
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
                if(this.files.length > 0) {
                    fileNameDisplay.textContent = this.files[0].name;
                    fileNameDisplay.classList.add('text-indigo-400');
                }
            });
        }
    </script>
</body>
</html>
