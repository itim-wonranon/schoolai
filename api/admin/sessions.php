<?php
require_once __DIR__ . '/../../includes/session_check.php';
requireRole('admin');

header('Content-Type: application/json');
$pdo = getDB();
$query = $_GET['query'] ?? '';

try {
    if ($query === 'count') {
        // In a real system with Redis/Database sessions, we'd query those.
        // For simple PHP sessions, we'll estimate based on last_login activity within 15 mins
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE last_login > DATE_SUB(NOW(), INTERVAL 15 MINUTE) AND is_active = 1");
        $stmt->execute();
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode($res);
    } else {
        // List active users (simplified for now)
        $stmt = $pdo->prepare("SELECT id, username, display_name, role, last_login FROM users WHERE last_login > DATE_SUB(NOW(), INTERVAL 30 MINUTE) ORDER BY last_login DESC");
        $stmt->execute();
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
