<?php
session_start();
header('Content-Type: application/json');

require_once "../config/db.php";
require_once "../includes/functions.php";

if (!is_logged_in() || ($_SESSION['user_role'] ?? '') !== 'candidate') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);
$notif_id = isset($data['id']) ? (int) $data['id'] : 0;

try {
    if ($notif_id === 0) {
        // Mark all as read
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$user_id]);
    } else {
        // Mark single as read
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
        $stmt->execute([$notif_id, $user_id]);
    }
    
    // Get new unread count
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$user_id]);
    $unread = (int) $stmt->fetchColumn();
    
    echo json_encode(['success' => true, 'unread_count' => $unread]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}