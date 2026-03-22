<?php
require_once __DIR__ . '/../../includes/session_check.php';
requireRole('admin');

header('Content-Type: application/json');
$pdo = getDB();

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $stmt = $pdo->query("SELECT * FROM announcements ORDER BY created_at DESC");
        echo json_encode($stmt->fetchAll());
    } 
    elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $pdo->prepare("INSERT INTO announcements (title, content, target_role) VALUES (?, ?, ?)");
        $stmt->execute([$data['title'], $data['content'], $data['target_role']]);
        
        logActivity('announcement_create', "Broadcasted announcement: " . $data['title']);
        echo json_encode(['success' => true]);
    }
    elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $pdo->prepare("DELETE FROM announcements WHERE id = ?");
        $stmt->execute([$data['id']]);
        echo json_encode(['success' => true]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
