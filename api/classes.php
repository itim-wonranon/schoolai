<?php
require_once __DIR__ . '/../includes/session_check.php';
require_once __DIR__ . '/../config/db.php';
requireLogin();

header('Content-Type: application/json');
$pdo = getDB();
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                $stmt = $pdo->prepare("SELECT c.*, CONCAT(t.first_name, ' ', t.last_name) as homeroom_teacher_name 
                                     FROM classes c 
                                     LEFT JOIN teachers t ON c.homeroom_teacher_id = t.id 
                                     WHERE c.id = ?");
                $stmt->execute([$_GET['id']]);
                echo json_encode($stmt->fetch());
            } else {
                $stmt = $pdo->query("SELECT c.*, CONCAT(t.first_name, ' ', t.last_name) as homeroom_teacher_name 
                                   FROM classes c 
                                   LEFT JOIN teachers t ON c.homeroom_teacher_id = t.id 
                                   ORDER BY c.id");
                echo json_encode($stmt->fetchAll());
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            $stmt = $pdo->prepare("INSERT INTO classes (class_code, class_name, homeroom_teacher_id) VALUES (?, ?, ?)");
            $stmt->execute([$data['class_code'], $data['class_name'], $data['homeroom_teacher_id'] ?: null]);
            echo json_encode(['success' => true, 'message' => 'เพิ่มข้อมูลชั้นเรียนสำเร็จ']);
            break;

        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), true);
            $stmt = $pdo->prepare("UPDATE classes SET class_code=?, class_name=?, homeroom_teacher_id=? WHERE id=?");
            $stmt->execute([$data['class_code'], $data['class_name'], $data['homeroom_teacher_id'] ?: null, $data['id']]);
            echo json_encode(['success' => true, 'message' => 'แก้ไขข้อมูลชั้นเรียนสำเร็จ']);
            break;

        case 'DELETE':
            $id = $_GET['id'] ?? 0;
            $stmt = $pdo->prepare("DELETE FROM classes WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'ลบข้อมูลชั้นเรียนสำเร็จ']);
            break;
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
}
