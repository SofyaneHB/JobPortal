<?php
session_start();

require_once "../config/db.php";
require_once "../includes/functions.php";
require_once "ai_service.php";

require_login(['candidate']);

$userId = $_SESSION['user_id'];

if (!isset($_FILES['cv_file'])) {
    $_SESSION['ai_error'] = "Aucun fichier reçu.";
    header("Location: ../candidate/applications.php");
    exit;
}

$file = $_FILES['cv_file'];
$errors = CVParser::validateFile($file);

if (!empty($errors)) {
    $_SESSION['ai_error'] = implode(" ", $errors);
    header("Location: ../candidate/applications.php");
    exit;
}

$dir = "../uploads/cv/";
if (!is_dir($dir)) mkdir($dir, 0755, true);

$safeFilename = preg_replace('/[^a-zA-Z0-9_.-]/', '', basename($file['name']));
$path = $dir . time() . "_" . $safeFilename;

if (move_uploaded_file($file["tmp_name"], $path)) {
    $ai = new AIService($pdo);
    $_SESSION['ai_result'] = $ai->analyzeCV($userId, $path, $file['type']);
} else {
    $_SESSION['ai_error'] = "Erreur d'écriture sur le serveur.";
}



header("Location: ../candidate/applications.php");
exit;