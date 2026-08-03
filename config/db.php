<?php

// Lecture et analyse du fichier .env (Parseur .env minimaliste)

$envPath = __DIR__ . '/../.env'; // Chemin vers le fichier .env 

if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Ignorer les commentaires (lignes commençant par le caractère #)
        if (strpos(trim($line), '#') === 0) continue;
        
       // Séparation de la clé et de la valeur au niveau du signe '='
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            
            // Enregistrement des variables dans l'environnement du serveur (si elles n'existent pas déjà)
            if (!getenv($name)) {
                putenv("{$name}={$value}");
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }
}


// Configuration des paramètres de connexion
// Récupération des valeurs depuis l'environnement, ou utilisation des valeurs de secours (fallback)

$host     = getenv('DB_HOST') ?: "localhost";
$dbname   = getenv('DB_NAME') ?: "JobPortal";
$username = getenv('DB_USER') ?: "root";
$password = getenv('DB_PASS') !== false ? getenv('DB_PASS') : ""; 

//Création de la connexion PDO 
try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );

    // Activation du mode d'erreur par exception pour faciliter le suivi et le débogage des erreurs SQL
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch (PDOException $e) {
    // Arrêt brutal de l'exécution et affichage du message d'erreur en cas d'échec de connexion
    die("Connection Failed: " . $e->getMessage());
}