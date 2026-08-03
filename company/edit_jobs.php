<?php
session_start();

require_once "../config/db.php";
require_once "../includes/functions.php";

require_login(['company']);

$user_id = $_SESSION['user_id'];
$company_id = $_SESSION['company_id'];
$job_id = $_GET['id'] ?? null;

if (!$job_id) {
    redirect("my_jobs.php");
    exit;
}

// --- Verify Job Belongs to Company ---
$stmt = $pdo->prepare("SELECT * FROM jobs WHERE id = ? AND company_id = ? LIMIT 1");
$stmt->execute([$job_id, $company_id]);
$job = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$job) {
    set_flash("error", "Job not found or access denied.");
    redirect("my_jobs.php");
    exit;
}

// --- Handle Update ---
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = clean_input($_POST['title']);
    $location = clean_input($_POST['location']);
    $type = clean_input($_POST['type']);
    $salary = clean_input($_POST['salary']);
    $status = clean_input($_POST['status']);
    $description = clean_input($_POST['description']);

    $stmt = $pdo->prepare("
        UPDATE jobs 
        SET title = ?, location = ?, type = ?, salary = ?, status = ?, description = ? 
        WHERE id = ? AND company_id = ?
    ");
    $stmt->execute([$title, $location, $type, $salary, $status, $description, $job_id, $company_id]);
    
    set_flash("success", "Job updated successfully!");
    redirect("my_jobs.php");
    exit;
}

$stmt = $pdo->prepare("SELECT full_name, email FROM users WHERE id = ? LIMIT 1");
$stmt->execute([$user_id]);
$currentUser = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Job</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex bg-gray-50">

<aside class="w-64 bg-white border-r h-screen fixed">
    <div class="p-4 flex items-center gap-3 border-b">
        <div class="w-10 h-10 bg-indigo-600 text-white flex items-center justify-center rounded-lg">J</div>
        <a href="profile.php" class="block p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">Profile</a>
        <a href="add_job.php" class="block p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">Add Job</a>
        <a href="my_jobs.php" class="block p-2 bg-indigo-600 text-white rounded-lg">My Jobs</a>
        <a href="applicants.php" class="block p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">Applicants</a>
    </div>
    <div class="absolute bottom-0 w-full p-4 border-t">
        <div class="text-sm font-bold"><?= htmlspecialchars($currentUser['full_name']) ?></div>
        <div class="text-xs text-gray-500"><?= htmlspecialchars($currentUser['email']) ?></div>
    </div>
</aside>

<main class="ml-64 flex-1 p-8">
    <div class="flex items-center gap-3 mb-6">
        <a href="my_jobs.php" class="text-gray-400 hover:text-indigo-600 font-bold text-xl">&larr;</a>
        <div>
            <h1 class="text-2xl font-bold mb-1">Edit Job: <?= htmlspecialchars($job['title']) ?></h1>
            <p class="text-gray-500">Modify your job listing details.</p>
        </div>
    </div>

    <div class="max-w-3xl bg-white p-6 rounded-xl border">
        <form method="POST" action="">
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Job Title</label>
                    <input type="text" name="title" value="<?= htmlspecialchars($job['title']) ?>" required class="w-full border-gray-300 border rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-600 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full border-gray-300 border rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-600 outline-none bg-white">
                        <option value="active" <?= $job['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="paused" <?= $job['status'] === 'paused' ? 'selected' : '' ?>>Paused</option>
                        <option value="closed" <?= $job['status'] === 'closed' ? 'selected' : '' ?>>Closed</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                    <input type="text" name="location" value="<?= htmlspecialchars($job['location']) ?>" required class="w-full border-gray-300 border rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-600 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Employment Type</label>
                    <select name="type" class="w-full border-gray-300 border rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-600 outline-none bg-white">
                        <option value="full-time" <?= $job['type'] === 'full-time' ? 'selected' : '' ?>>Full-Time</option>
                        <option value="part-time" <?= $job['type'] === 'part-time' ? 'selected' : '' ?>>Part-Time</option>
                        <option value="remote" <?= $job['type'] === 'remote' ? 'selected' : '' ?>>Remote</option>
                        <option value="internship" <?= $job['type'] === 'internship' ? 'selected' : '' ?>>Internship</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Salary Range</label>
                    <input type="text" name="salary" value="<?= htmlspecialchars($job['salary'] ?? '') ?>" class="w-full border-gray-300 border rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-600 outline-none">
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">Job Description</label>
                <textarea name="description" rows="5" required class="w-full border-gray-300 border rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-600 outline-none"><?= htmlspecialchars($job['description']) ?></textarea>
            </div>

            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2.5 px-6 rounded-lg transition-colors">
                Save Updates
            </button>
        </form>
    </div>
</main>
</body>
</html>