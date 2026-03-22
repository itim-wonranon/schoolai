<?php
require_once __DIR__ . '/../../includes/session_check.php';
requireRole('admin');

header('Content-Type: application/json');
$pdo = getDB();
$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        if (isset($_GET['id'])) {
            $stmt = $pdo->prepare("SELECT * FROM subject_groups WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
        } else {
            // Count subjects in each group
            $sql = "SELECT g.*, (SELECT COUNT(*) FROM subjects s WHERE s.group_id = g.id) as subject_count 
                    FROM subject_groups g ORDER BY g.name ASC";
            $stmt = $pdo->query($sql);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        }
    } 
    elseif ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $pdo->prepare("INSERT INTO subject_groups (name, description) VALUES (?, ?)");
        $stmt->execute([$data['name'], $data['description']]);
        
        logActivity('curriculum_create', "Added subject group: " . $data['name']);
        echo json_encode(['success' => true, 'message' => 'เพิ่มกลุ่มสาระสำเร็จ']);
    }
    elseif ($method === 'PUT') {
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $pdo->prepare("UPDATE subject_groups SET name = ?, description = ? WHERE id = ?");
        $stmt->execute([$data['name'], $data['description'], $data['id']]);
        
        logActivity('curriculum_update', "Updated subject group ID: " . $data['id']);
        echo json_encode(['success' => true, 'message' => 'อัปเดตข้อมูลสำเร็จ']);
    }
    elseif ($method === 'DELETE') {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'];
        
        // Check if group is empty
        $check = $pdo->prepare("SELECT COUNT(*) FROM subjects WHERE group_id = ?");
        $check->execute([$id]);
        if ($check->fetchColumn() > 0) {
            echo json_encode(['success' => false, 'message' => 'ไม่สามารถลบได้เนื่องจากมีรายวิชาผูกอยู่กับกลุ่มสาระนี้']);
            exit;
        }
        
        $stmt = $pdo->prepare("DELETE FROM subject_groups WHERE id = ?");
        $stmt->execute([$id]);
        
        logActivity('curriculum_delete', "Deleted subject group ID: $id");
        echo json_encode(['success' => true]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
