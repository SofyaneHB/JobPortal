<?php
session_start();
header('Content-Type: application/json');

require_once "../config/db.php";
require_once "../includes/functions.php";

if (!is_logged_in() || ($_SESSION['user_role'] ?? '') !== 'candidate') {
    echo json_encode(['count' => 0]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$_SESSION['user_id']]);
    echo json_encode(['count' => (int) $stmt->fetchColumn()]);
} catch (PDOException $e) {
    echo json_encode(['count' => 0]);
}