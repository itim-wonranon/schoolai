<?php
require_once __DIR__ . '/../../includes/session_check.php';
requireRole('admin');

header('Content-Type: application/json');
$pdo = getDB();
$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        if (isset($_GET['id'])) {
            $stmt = $pdo->prepare("SELECT * FROM roles WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
        } else {
            $stmt = $pdo->query("SELECT * FROM roles ORDER BY id ASC");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        }
    } 
    elseif ($method === 'PUT') {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'];
        $permissions = json_encode($data['permissions']);
        
        $stmt = $pdo->prepare("UPDATE roles SET permissions = ? WHERE id = ?");
        $stmt->execute([$permissions, $id]);
        
        logActivity('role_update_permissions', "Updated permissions for role ID: $id", ['permissions' => $data['permissions']]);
        echo json_encode(['success' => true]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
