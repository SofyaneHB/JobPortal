<?php
session_start();

require_once "../config/db.php";
require_once "../includes/functions.php";

require_login(['candidate']);

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect("profile.php");  
    exit;
}

// Get and validate inputs
$full_name = clean_input($_POST['full_name'] ?? '');
$phone = clean_input($_POST['phone'] ?? '');
$address = clean_input($_POST['address'] ?? '');
$country = clean_input($_POST['country'] ?? '');
$skills = clean_input($_POST['skills'] ?? '');

if (empty($full_name)) {
    set_flash("error", "Le nom complet est requis.");
    redirect("profile.php"); 
    exit;
}

try {
    $stmt = $pdo->prepare("
        UPDATE users 
        SET full_name = ?, phone = ?, address = ?, country = ?, skills = ? 
        WHERE id = ?
    ");
    
    $result = $stmt->execute([$full_name, $phone, $address, $country, $skills, $user_id]);
    
    if ($result) {
        $_SESSION['user_name'] = $full_name;
        set_flash("success", "Profil mis à jour avec succès !");
    } else {
        set_flash("error", "Erreur lors de la mise à jour.");
    }
    
} catch (PDOException $e) {
    set_flash("error", "Erreur technique : " . $e->getMessage());
}

redirect("profile.php");  
exit;
?>