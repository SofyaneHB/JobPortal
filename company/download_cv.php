<?php
session_start();

require_once "../config/db.php";
require_once "../includes/functions.php";

require_login(['company']);

$company_id = $_SESSION['company_id'] ?? null;
$app_id     = isset($_GET['app_id']) ? (int) $_GET['app_id'] : 0;

if (!$company_id || !$app_id) {
    http_response_code(403);
    die("Accès refusé.");
}

try {
    $stmt = $pdo->prepare("
        SELECT a.id, a.cv_path, a.candidate_id, u.full_name
        FROM applications a
        INNER JOIN jobs j ON a.job_id = j.id
        INNER JOIN users u ON a.candidate_id = u.id
        WHERE a.id = ? AND j.company_id = ?
        LIMIT 1
    ");
    $stmt->execute([$app_id, $company_id]);
    $application = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$application) {
        http_response_code(404);
        die("Candidature introuvable ou accès non autorisé.");
    }

    $cvFullPath = null;

    // Priorité 1 : Fichier stocké dans la colonne cv_path
    if (!empty($application['cv_path'])) {
        $path = $application['cv_path'];
        if (file_exists($path)) {
            $cvFullPath = realpath($path);
        } elseif (file_exists(__DIR__ . '/' . $path)) {
            $cvFullPath = realpath(__DIR__ . '/' . $path);
        } elseif (file_exists(__DIR__ . '/../' . $path)) {
            $cvFullPath = realpath(__DIR__ . '/../' . $path);
        }
    }

    // Priorité 2 : Recherche par ID candidat dans uploads/cv
    if (!$cvFullPath) {
        $candidateId = $application['candidate_id'];
        $possibleDirs = [
            realpath(__DIR__ . '/../uploads/cv'),
            realpath(__DIR__ . '/uploads/cv'),
            dirname(__DIR__) . '/uploads/cv'
        ];

        foreach ($possibleDirs as $cvDir) {
            if (!$cvDir || !is_dir($cvDir)) continue;

            $patterns = [
                $cvDir . "/*_" . $candidateId . ".*",
                $cvDir . "/*_" . $candidateId . "_*.*",
                $cvDir . "/" . $candidateId . ".*"
            ];
            foreach ($patterns as $pattern) {
                $files = glob($pattern);
                if (!empty($files)) {
                    $cvFullPath = realpath($files[0]);
                    break 2;
                }
            }
        }
    }

    // Priorité 3 : Recherche par Nom
    if (!$cvFullPath && !empty($application['full_name'])) {
        $safeName = preg_replace('/[^a-zA-Z0-9]/', '_', strtolower($application['full_name']));
        $possibleDirs = [
            realpath(__DIR__ . '/../uploads/cv'),
            realpath(__DIR__ . '/uploads/cv')
        ];
        foreach ($possibleDirs as $cvDir) {
            if (!$cvDir || !is_dir($cvDir)) continue;
            $files = glob($cvDir . "/*" . $safeName . "*.*");
            if (!empty($files)) {
                $cvFullPath = realpath($files[0]);
                break;
            }
        }
    }

    if (!$cvFullPath || !file_exists($cvFullPath)) {
        http_response_code(404);
        die("Fichier CV non trouvé sur le serveur.");
    }

    $filename = basename($cvFullPath);
    $filesize = filesize($cvFullPath);

    $finfo    = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($cvFullPath) ?: 'application/pdf';

    // ENTÊTES HTTP POUR OUVRIR DANS UN NOUVEL ONGLET (INLINE)
    header('Content-Description: File Transfer');
    header('Content-Type: ' . $mimeType);
    header('Content-Disposition: inline; filename="' . $filename . '"');
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: public, must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . $filesize);

    if (ob_get_level()) {
        ob_end_clean();
    }

    readfile($cvFullPath);
    exit;

} catch (PDOException $e) {
    http_response_code(500);
    die("Erreur serveur : " . $e->getMessage());
}

?>