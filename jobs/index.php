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
<title>Jobs Board</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-slate-950 text-slate-100 min-h-screen pb-20">

<nav class="border-b border-slate-900 bg-slate-950/80 backdrop-blur-md">
    <div class="max-w-6xl mx-auto px-4 h-16 flex justify-between items-center">
        <h1 class="font-bold text-transparent bg-gradient-to-r from-indigo-400 to-cyan-400 bg-clip-text">
            Sofyane Jobs
        </h1>
        <div class="text-xs flex gap-4">
            <a href="../candidate/dashboard.php">Dashboard</a>
            <a href="../candidate/applications.php">Applications</a>
        </div>
    </div>
</nav>

<main class="max-w-6xl mx-auto px-4 mt-10">
    <div class="mb-8 flex flex-col md:flex-row gap-3">
        <input id="search"
            class="flex-1 bg-slate-900 border border-slate-800 rounded-xl px-4 py-3"
            placeholder="Rechercher un poste...">
        <select id="city"
            class="bg-slate-900 border border-slate-800 rounded-xl px-4 py-3">
            <option value="all">Toutes les villes</option>
            <?php foreach($villes as $v): ?>
            <option value="<?= htmlspecialchars($v) ?>">
                <?= htmlspecialchars(ucfirst($v)) ?>
            </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="grid md:grid-cols-2 gap-4">
    <?php if (empty($jobs)): ?>
        <div class="col-span-full py-16 text-center border border-dashed border-slate-800 rounded-2xl">
            <h3 class="text-base font-bold text-slate-300">Aucune offre disponible</h3>
            <p class="text-slate-500 text-xs mt-1">Revenez plus tard ou modifiez vos filtres.</p>
        </div>
    <?php else: ?>
        <?php foreach($jobs as $job): ?>
        <?php 
            $card_title_clean = preg_replace('/[^A-Za-z0-9]/', '', $job['title']);
            $display_logo_txt = !empty($card_title_clean) ? strtoupper(substr($card_title_clean, 0, 2)) : 'JB';
        ?>
        <div class="job bg-slate-900/30 border border-slate-800 rounded-2xl p-5 flex flex-col gap-4 hover:border-indigo-500 hover:scale-[1.01] transition"
            data-title="<?= strtolower($job['title']) ?>"
            data-company="<?= strtolower($job['company']) ?>"
            data-location="<?= strtolower($job['location']) ?>"
            data-type="<?= $job['type'] ?>">
            <div class="flex justify-between">
                <div class="flex gap-3">
                    <div class="w-11 h-11 rounded-xl bg-indigo-600 flex items-center justify-center font-bold">
                        <?= htmlspecialchars($display_logo_txt) ?>
                    </div>
                    <div>
                        <h2 class="font-bold text-sm"><?= htmlspecialchars($job['title']) ?></h2>
                        <p class="text-xs text-slate-400">
                            <?= htmlspecialchars($job['company']) ?> • <?= htmlspecialchars($job['location']) ?>
                        </p>
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-2 text-xs">
                <div class="bg-slate-800/40 p-2 rounded-lg">
                    Salaire : <b><?= htmlspecialchars($job['salary'] ?? 'À négocier') ?></b>
                </div>
                <div class="bg-slate-800/40 p-2 rounded-lg text-indigo-400">
                    Type : <b><?= htmlspecialchars($job['type']) ?></b>
                </div>
                <div class="bg-slate-800/40 p-2 rounded-lg">
                    Candidats : <b><?= (int)($job['apps'] ?? 0) ?></b>
                </div>
                <div class="bg-slate-800/40 p-2 rounded-lg text-slate-400">
                    Publié : <b><?= htmlspecialchars($job['posted'] ?? 'Récemment') ?></b>
                </div>
            </div>
            <p class="text-xs text-slate-400 line-clamp-2">
                <?= htmlspecialchars($job['description'] ?? 'Rejoignez notre équipe et construisez des produits exceptionnels.') ?>
            </p>
            <div class="flex justify-between items-center pt-3 border-t border-slate-800">
                <span class="text-xs text-slate-500">
                    📍 <?= htmlspecialchars($job['location']) ?>
                </span>
                <a href="job_details.php?id=<?= (int)$job['id'] ?>"
                    class="bg-indigo-600 px-4 py-2 rounded-xl text-xs font-semibold hover:bg-indigo-500">
                    Voir détails
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
    </div>
</main>

<script>
const search = document.getElementById("search");
const city = document.getElementById("city");
const cards = document.querySelectorAll(".job");

function filter() {
    const q = search.value.toLowerCase();
    const c = city.value.toLowerCase();
    cards.forEach(card => {
        const match = card.dataset.title.includes(q) || card.dataset.company.includes(q);
        const matchCity = c === "all" || card.dataset.location === c;
        card.style.display = (match && matchCity) ? "flex" : "none";
    });
}

search.addEventListener("input", filter);
city.addEventListener("change", filter);
</script>

</body>
</html>