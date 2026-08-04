<?php
// company/add_job.php
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

// --- Handle Form Submission ---

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = clean_input($_POST['title'] ?? '');
    $location = clean_input($_POST['location'] ?? '');
    $raw_type = clean_input($_POST['type'] ?? 'full-time'); 
    $salary = clean_input($_POST['salary'] ?? '');
    $education = clean_input($_POST['education'] ?? '');
    $experience = clean_input($_POST['experience'] ?? '');
    $description = clean_input($_POST['description'] ?? '');
    $tech_stack = clean_input($_POST['tech_stack'] ?? '');
    $requirements = clean_input($_POST['requirements'] ?? '');
    $Tasks = clean_input($_POST['daily_tasks'] ?? '');


    $type_mapping = ['CDI'=>'full-time', 'Stage'=>'internship', 'Freelance'=>'remote', 'CDD'=>'part-time'];
    $type = isset($type_mapping[$raw_type]) ? $type_mapping[$raw_type] : $raw_type;

    if (empty($title) || empty($description) || empty($location)) {
        set_flash("error", "Veuillez remplir tous les champs obligatoires (*).");
    } else {
        try {
            // CORRECTION ICI : 10 placeholders pour 10 variables
$stmt = $pdo->prepare("
    INSERT INTO jobs (
        company_id,
        title,
        description,
        location,
        type,
        salary,
        education,
        experience,
        tech_stack,
        requirements,
        Tasks,
        created_at
    )
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
");

$stmt->execute([
    $company_id,
    $title,
    $description,
    $location,
    $type,
    $salary,
    $education,
    $experience,
    $tech_stack,
    $requirements,
    $Tasks
]);
            
            set_flash("success", "L'offre d'emploi a été publiée avec succès !");
            redirect("my_jobs.php");
            exit;
        } catch (PDOException $e) {
            set_flash("error", "Erreur technique : " . $e->getMessage());
        }
    }
}

// --- Fetch Company Info for Sidebar ---
$company = ['company_name' => 'Recruteur', 'email' => ''];
try {
    if (isset($pdo)) {
        $stmt = $pdo->prepare("SELECT c.company_name, u.email FROM companies c JOIN users u ON c.user_id = u.id WHERE c.id = ? LIMIT 1");
        $stmt->execute([$company_id]);
        $fetched = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($fetched) {
            $company = $fetched;
        }
    }
} catch (PDOException $e) {
    // Fail-safe
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Publish Job Offers</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="flex bg-slate-950 text-slate-100 min-h-screen selection:bg-indigo-500 selection:text-white">

    <aside class="w-64 border-r border-slate-900 bg-slate-950/40 backdrop-blur-md h-screen fixed flex flex-col justify-between z-40">
        <div>
            <div class="p-5 flex items-center gap-3 border-b border-slate-900">
                <div class="w-9 h-9 bg-gradient-to-tr from-indigo-600 to-cyan-500 text-white flex items-center justify-center font-bold text-sm rounded-xl shadow-md shadow-indigo-600/20">
                    <?= strtoupper(substr($company['company_name'], 0, 1)) ?>
                </div>
                <div>
                    <div class="font-bold text-sm text-slate-200 tracking-tight"><?= htmlspecialchars($display_name) ?></div>
                    <div class="text-[10px] text-indigo-400 font-semibold uppercase tracking-wider">Recuriter Dashboard</div>
                </div>
            </div>
            
            <nav class="p-4 space-y-1.5 text-xs font-medium">
                <a href="dashboard.php" class="flex items-center px-3 py-2.5 text-slate-400 hover:text-slate-200 hover:bg-slate-900/40 rounded-xl transition">Dashboard</a>
                <a href="profile.php" class="flex items-center px-3 py-2.5 text-slate-400 hover:text-slate-200 hover:bg-slate-900/40 rounded-xl transition">Company Dashboard</a>
                <a href="add_job.php" class="flex items-center px-3 py-2.5 bg-indigo-600/10 border border-indigo-500/20 text-indigo-400 rounded-xl font-semibold">Publish a Job Offer</a>
                <a href="my_jobs.php" class="flex items-center px-3 py-2.5 text-slate-400 hover:text-slate-200 hover:bg-slate-900/40 rounded-xl transition">Manage Jobs</a>
                <a href="applicants.php" class="flex items-center px-3 py-2.5 text-slate-400 hover:text-slate-200 hover:bg-slate-900/40 rounded-xl transition">Applications Received</a>
            </nav>
        </div>
        
        <div class="p-4 border-t border-slate-900 bg-slate-950/60 backdrop-blur-md">
            <div class="text-xs font-bold text-slate-300 truncate"><?= htmlspecialchars($company['company_name']) ?></div>
            <div class="text-[10px] text-slate-500 truncate mt-0.5"><?= htmlspecialchars($company['email']) ?></div>
        </div>
    </aside>

    <main class="ml-64 flex-1 p-8 md:p-12 max-w-4xl">
        
        <div class="mb-8">
            <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-white">Publish a Job Offer</h1>
            <p class="text-slate-400 text-xs md:text-sm mt-1">Fill in required technical criteria</p>
        </div>

        <?php display_flash(); ?>

        <div class="bg-slate-900/20 border border-slate-900 p-6 md:p-8 rounded-2xl backdrop-blur-sm">
            <form method="POST" action="" class="space-y-6">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Job Title</label>
                        <input type="text" name="title" placeholder="ex: Développeur Full-Stack PHP" required 
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-slate-200 placeholder-slate-600 focus:outline-none focus:border-indigo-500 transition">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Location / City</label>
                        <input type="text" name="location" placeholder="ex: Taroudant, Agadir, Remote..." required 
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-slate-200 placeholder-slate-600 focus:outline-none focus:border-indigo-500 transition">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Contract Type</label>
                        <div class="relative">
                            <select name="type" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-slate-300 focus:outline-none focus:border-indigo-500 transition appearance-none cursor-pointer">
                                <option value="full-time">CDI (Full-Time)</option>
                                <option value="internship">Stage / PFE (Internship)</option>
                                <option value="remote">Remote (Freelance / Projet)</option>
                                <option value="part-time">CDD (Part-Time)</option>
                            </select>
                            <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none text-slate-500 text-xs">▼</div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Salary</label>
                        <input type="text" name="salary" placeholder="ex: 6 000 - 9 000 DH, À négocier..." 
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-slate-200 placeholder-slate-600 focus:outline-none focus:border-indigo-500 transition">
                    </div>
                </div>

                <!-- NOUVEAU : FORMULAIRE DYNAMIQUE ÉDUCATION & EXPÉRIENCE -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Degree</label>
                        <input type="text" name="education" placeholder="ex: Bac+3 / Bac+5 Standard, Master..." 
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-slate-200 placeholder-slate-600 focus:outline-none focus:border-indigo-500 transition">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Required Experience Level</label>
                        <input type="text" name="experience" placeholder="ex: Junior / Senior, 2 ans d'expérience..." 
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-slate-200 placeholder-slate-600 focus:outline-none focus:border-indigo-500 transition">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Technical Stack (ex: PHP, MySQL, React)</label>
                        <input type="text" name="tech_stack" placeholder="ex: PHP, Tailwind, Linux..." class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-slate-200 focus:border-indigo-500 transition">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Key Skills (séparées par des virgules)</label>
                        <input type="text" name="requirements" placeholder="ex: Git, Clean Code, Agile..." class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-slate-200 focus:border-indigo-500 transition">
                    </div>
                </div>


                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Daily Tasks (séparées par une virgule)</label>
                    <textarea name="daily_tasks" placeholder="ex: Analyse des besoins, Développement modulaire, Revues de code..." 
                            class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-slate-200 focus:border-indigo-500 transition resize-none"></textarea>
                </div>



                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Detailed Job Description</label>
                    <textarea name="description" rows="6" placeholder="Job responsibilities, required technologies (PHP, PDO, Tailwind CSS, Linux/Fedora), and ideal candidate profile..." 
                              class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-slate-200 placeholder-slate-600 focus:outline-none focus:border-indigo-500 transition leading-relaxed resize-none"></textarea>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" class="w-full md:w-auto text-xs font-bold uppercase tracking-wider bg-indigo-600 hover:bg-indigo-500 text-white px-8 py-3.5 rounded-xl transition shadow-lg shadow-indigo-600/10 active:scale-[0.98]">
                        Publish the Job Offer
                    </button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>