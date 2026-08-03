<?php

require "../includes/functions.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. On détruit la session actuelle
logout_user();

// 2. On réinitialise une session propre UNIQUEMENT pour porter le message flash
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

set_flash('success', 'Vous avez été déconnecté avec succès.');

// 3. Redirection vers la page d'accueil (celle de l'image image_d7c35b.png)
redirect("../Public/index.php");

?>