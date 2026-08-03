<?php
session_start();
require "../config/db.php"; // Initialise putenv() avec les clés du .env

$client_id = getenv('GOOGLE_CLIENT_ID');
$redirect_uri = "http://localhost/Projet_Stage/auth/google_callback.php";

$google_auth_url = "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query([
    'client_id'     => $client_id,
    'redirect_uri'  => $redirect_uri,
    'response_type' => 'code',
    'scope'         => 'email profile',
    'access_type'   => 'online',
    'prompt'        => 'select_account' // FORCE GOOGLE À DEMANDER LE CHOIX DU COMPTE À CHAQUE FOIS
]);

header("Location: " . $google_auth_url);
exit;