<?php
require_once __DIR__ . '/../../includes/session_check.php';
requireRole('admin');

header('Content-Type: application/json');
$pdo = getDB();

try {
    $action = $_GET['action'] ?? '';
    $search = $_GET['search'] ?? '';
    
    $sql = "SELECT l.*, u.username, u.display_name 
            FROM activity_logs l 
            LEFT JOIN users u ON l.user_id = u.id 
            WHERE 1=1";
    $params = [];
    
    if ($action) {
        $sql .= " AND l.action = ?";
        $params[] = $action;
    }
    if ($search) {
        $sql .= " AND (l.description LIKE ? OR u.username LIKE ? OR u.display_name LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    $sql .= " ORDER BY l.created_at DESC LIMIT 200";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
