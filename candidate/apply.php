<?php
session_start();

require_once "../config/db.php";
require_once "../includes/helpers.php";

require_login(['candidate']);

$user_id = $_SESSION['user_id'];

// Récupère l'ID du poste (GET ou POST)

$job_id = $_GET['id'] ?? $_POST['job_id'] ?? null;

if (!$job_id) {
    $_SESSION['error_message'] = "Invalid job ID!";
    redirect("dashboard.php");
    exit;
}

// Vérifie s'il a déjà postulé

$stmt = $pdo->prepare("SELECT id FROM applications WHERE job_id = ? AND candidate_id = ?");
$stmt->execute([$job_id, $user_id]);

if ($stmt->rowCount() > 0) {
    $_SESSION['error_message'] = "You already applied for this job!";
    redirect("applications.php");
    exit;
}

// Insertion de la candidature
try {
    $stmt = $pdo->prepare("
        INSERT INTO applications (job_id, candidate_id, status, applied_at)
        VALUES (?, ?, 'pending', NOW())
    ");
    $stmt->execute([$job_id, $user_id]);
    $application_id = $pdo->lastInsertId();

    //  UPLOAD DU CV — DÉPLACÉ AVANT LE EXIT
    if (isset($_FILES['cv_file']) && $_FILES['cv_file']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = "../uploads/cv/";
        
        // Crée le dossier s'il n'existe pas
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        // Validation du type de fichier
        $allowedTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];
        $fileType = mime_content_type($_FILES['cv_file']['tmp_name']);
        
        if (in_array($fileType, $allowedTypes)) {
            $extension = strtolower(pathinfo($_FILES['cv_file']['name'], PATHINFO_EXTENSION));
            $safeName  = time() . "_" . $user_id . "_" . bin2hex(random_bytes(4)) . "." . $extension;
            $uploadPath = $uploadDir . $safeName;
            
            if (move_uploaded_file($_FILES['cv_file']['tmp_name'], $uploadPath)) {
                // Enregistre le chemin dans la base
                $stmt = $pdo->prepare("UPDATE applications SET cv_path = ? WHERE id = ?");
                $stmt->execute([$uploadPath, $application_id]);
            }
        }
    }

    // ── Notification au recruteur ──
    $stmtJob = $pdo->prepare("
        SELECT j.title, c.user_id AS company_user_id 
        FROM jobs j 
        JOIN companies c ON j.company_id = c.id 
        WHERE j.id = ?
        LIMIT 1
    ");
    $stmtJob->execute([$job_id]);
    $jobInfo = $stmtJob->fetch(PDO::FETCH_ASSOC);

    if ($jobInfo) {
        $stmtName = $pdo->prepare("SELECT full_name FROM users WHERE id = ? LIMIT 1");
        $stmtName->execute([$user_id]);
        $candidateName = $stmtName->fetchColumn() ?? 'Un candidat';

        $notifMsg = "Nouvelle candidature reçue de « " . $candidateName . " » pour le poste « " . htmlspecialchars($jobInfo['title']) . " ».";

        $stmtNotif = $pdo->prepare("
            INSERT INTO notifications (user_id, message, type, link, is_read, created_at)
            VALUES (?, ?, 'new_application', ?, 0, NOW())
        ");
        $stmtNotif->execute([
            $jobInfo['company_user_id'],
            $notifMsg,
            "applicants.php"
        ]);
    }

    $_SESSION['success_message'] = "Candidature envoyée avec succès !";
    redirect("applications.php");
    exit;

} catch (PDOException $e) {
    $_SESSION['error_message'] = "Erreur technique : " . $e->getMessage();
    redirect("dashboard.php");
    exit;
}