<?php
session_start();

require_once "../config/db.php";
require_once "../includes/functions.php";

// Pagination
$limit = 10;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $limit;

// Récupération des offres depuis la base de données
$jobs = [];

try {
    if (isset($pdo)) {
        $stmt = $pdo->prepare("
            SELECT
                j.id,
                j.title,
                j.location,
                j.type,
                j.salary,
                j.description,
                j.created_at as posted,
                COALESCE(c.company_name,'Entreprise') AS company,
                c.logo,
                COUNT(a.id) AS apps
            FROM jobs j
            LEFT JOIN companies c ON j.company_id = c.id
            LEFT JOIN applications a ON j.id = a.job_id
            GROUP BY j.id
            ORDER BY j.created_at DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    $jobs = [];
}

// Extraction des villes uniques pour le filtre
$villes = array_unique(array_map('strtolower', array_column($jobs, 'location')));
?>


<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Jobs Offers</title>
<link rel="icon" type="image/png" href="../assets/img/logo_jp.png">
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    body { 
        font-family: 'Plus Jakarta Sans', sans-serif; 
    }

    .glass {
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255,255,255,0.04);
    }

</style>
</head>

<body class="bg-slate-950 text-slate-100 min-h-screen antialiased">

<!-- NAVBAR -->
<nav class="fixed top-0 left-0 right-0 z-50 glass border-b border-slate-800/50">
    <div class="max-w-6xl mx-auto px-6 h-16 flex justify-between items-center">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-cyan-500 flex items-center justify-center">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0"/>
                </svg>
            </div>
            <span class="font-bold text-lg tracking-tight">Jobs <span class="text-indigo-400">Offers</span></span>
        </div>
        <div class="flex items-center gap-1 text-xs font-medium">
            <a href="../candidate/dashboard.php" class="px-4 py-2 text-slate-400 hover:text-white hover:bg-slate-800/50 rounded-xl transition">Dashboard</a>
            <a href="../candidate/applications.php" class="px-4 py-2 text-slate-400 hover:text-white hover:bg-slate-800/50 rounded-xl transition">My Applications</a>
            <a href="../Public/Register.php" class="ml-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl transition shadow-lg shadow-indigo-600/20">Sign Up</a>
            <a href="../Public/login.php" class="ml-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl transition shadow-lg shadow-indigo-600/20">Sign In</a>
        </div>
    </div>
</nav>

<!-- HERO -->
<div class="pt-32 pb-12 px-6 relative overflow-hidden">

<!-- SEARCH BAR -->
<div class="max-w-2xl mx-auto px-6 mb-12 -mt-2 relative z-10">
    <div class="glass rounded-2xl p-2 flex flex-col sm:flex-row gap-2 shadow-2xl shadow-black/20">
        <div class="flex-1 relative">
            <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input id="search"
                class="w-full bg-transparent pl-11 pr-4 py-3 text-sm text-white placeholder-slate-500 outline-none"
                placeholder="Search by job title, company, or keywords...">
        </div>
        <div class="w-px bg-slate-800 hidden sm:block"></div>
        <div class="relative min-w-[160px]">
            <select id="city"
                class="w-full bg-transparent pl-10 pr-8 py-3 text-sm text-slate-300 outline-none appearance-none cursor-pointer">
                <option value="all">All Cities</option>
                <?php foreach($villes as $v): ?>
                <option value="<?= htmlspecialchars($v) ?>">
                    <?= htmlspecialchars(ucfirst($v)) ?>
                </option>
                <?php endforeach; ?>
            </select>
            <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
            </svg>
        </div>
    </div>
</div>

<!-- MAIN CONTENT -->
<main class="max-w-6xl mx-auto px-6 pb-20">

    <?php if (empty($jobs)): ?>
        <div class="flex flex-col items-center justify-center py-24 text-center">
            <div class="w-16 h-16 rounded-2xl bg-slate-900 border border-slate-800 flex items-center justify-center mb-4">
                <svg class="w-7 h-7 text-slate-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0"/>
                </svg>
            </div>
            <h3 class="text-lg font-bold text-slate-300 mb-1">No jobs available</h3>
            <p class="text-slate-500 text-sm">Check back later or adjust your search filters.</p>
        </div>

    <?php else: ?>
        <div class="grid md:grid-cols-2 gap-4">
        <?php foreach($jobs as $job): ?>
        <?php 
            $card_title_clean = preg_replace('/[^A-Za-z0-9]/', '', $job['title']);
            $display_logo_txt = !empty($card_title_clean) ? strtoupper(substr($card_title_clean, 0, 2)) : 'JB';
            
            // Type badge colors
            $typeColors = [
                'full-time'  => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                'part-time'  => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
                'remote'     => 'bg-cyan-500/10 text-cyan-400 border-cyan-500/20',
                'internship' => 'bg-purple-500/10 text-purple-400 border-purple-500/20',
            ];
            $typeClass = $typeColors[strtolower($job['type'])] ?? 'bg-slate-700/30 text-slate-400 border-slate-700';
        ?>
        <div class="job job-card shine bg-slate-900/40 border border-slate-800/60 rounded-2xl p-5 flex flex-col gap-4 cursor-default"
            data-title="<?= strtolower($job['title']) ?>"
            data-company="<?= strtolower($job['company']) ?>"
            data-location="<?= strtolower($job['location']) ?>"
            data-type="<?= $job['type'] ?>">
            
            <!-- Top Row -->
            <div class="flex items-start justify-between gap-4">
                <div class="flex gap-4">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-600 to-violet-600 flex items-center justify-center font-bold text-sm text-white shadow-lg shadow-indigo-900/20 shrink-0">
                        <?= htmlspecialchars($display_logo_txt) ?>
                    </div>
                    <div class="min-w-0">
                        <h2 class="font-bold text-[15px] text-white leading-tight mb-0.5 truncate"><?= htmlspecialchars($job['title']) ?></h2>
                        <p class="text-xs text-slate-400 truncate">
                            <?= htmlspecialchars($job['company']) ?>
                        </p>
                    </div>
                </div>
                <span class="shrink-0 inline-flex items-center px-2.5 py-1 rounded-lg border text-[11px] font-semibold uppercase tracking-wide <?= $typeClass ?>">
                    <?= htmlspecialchars($job['type']) ?>
                </span>
            </div>

            <!-- Description -->
            <p class="text-[13px] text-slate-400 leading-relaxed line-clamp-2">
                <?= htmlspecialchars($job['description'] ?? 'Join our team and build exceptional products with talented people.') ?>
            </p>

            <!-- Meta Row -->
            <div class="flex items-center gap-3 text-xs text-slate-500 flex-wrap">
                <span class="inline-flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <?= htmlspecialchars($job['location']) ?>
                </span>
                <span class="w-1 h-1 rounded-full bg-slate-700"></span>
                <span class="inline-flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <?= !empty($job['posted']) ? date('M d, Y', strtotime($job['posted'])) : 'Recently' ?>
                </span>
                <span class="w-1 h-1 rounded-full bg-slate-700"></span>
                <span class="inline-flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
                    </svg>
                    <?= (int)($job['apps'] ?? 0) ?> applicants
                </span>
            </div>

            <!-- Bottom Row: Salary + CTA -->
            <div class="flex items-center justify-between pt-4 border-t border-slate-800/60">
                <div class="text-sm font-semibold text-white">
                    <?= !empty($job['salary']) ? htmlspecialchars($job['salary']) : 'Salary negotiable' ?>
                </div>
                <a href="job_details.php?id=<?= (int)$job['id'] ?>"
                    class="group inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold rounded-xl transition-all shadow-lg shadow-indigo-600/20 active:scale-[0.97]">
                    View Details
                </a>
            </div>
        </div>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<script>
const search = document.getElementById("search");
const city = document.getElementById("city");
const cards = document.querySelectorAll(".job");

function filter() {
    const q = search.value.toLowerCase();
    const c = city.value.toLowerCase();
    let visible = 0;
    
    cards.forEach(card => {
        const match = card.dataset.title.includes(q) || card.dataset.company.includes(q);
        const matchCity = c === "all" || card.dataset.location === c;
        const show = match && matchCity;
        card.style.display = show ? "flex" : "none";
        if (show) visible++;
    });
}

search.addEventListener("input", filter);
city.addEventListener("change", filter);
</script>

</body>
</html>
