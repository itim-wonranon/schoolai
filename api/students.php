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
                $stmt = $pdo->prepare("SELECT s.*, c.class_name, c.study_track FROM students s LEFT JOIN classes c ON s.class_id = c.id WHERE s.id = ?");
                $stmt->execute([$_GET['id']]);
                echo json_encode($stmt->fetch());
            } else {
                $stmt = $pdo->query("SELECT s.*, c.class_name, c.study_track FROM students s LEFT JOIN classes c ON s.class_id = c.id ORDER BY s.id");
                echo json_encode($stmt->fetchAll());
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            $stmt = $pdo->prepare("INSERT INTO students (student_code, first_name, last_name, birthdate, class_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$data['student_code'], $data['first_name'], $data['last_name'], $data['birthdate'] ?: null, $data['class_id'] ?: null]);
            echo json_encode(['success' => true, 'message' => 'เพิ่มข้อมูลนักเรียนสำเร็จ']);
            break;

        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), true);
            $stmt = $pdo->prepare("UPDATE students SET student_code=?, first_name=?, last_name=?, birthdate=?, class_id=? WHERE id=?");
            $stmt->execute([$data['student_code'], $data['first_name'], $data['last_name'], $data['birthdate'] ?: null, $data['class_id'] ?: null, $data['id']]);
            echo json_encode(['success' => true, 'message' => 'แก้ไขข้อมูลนักเรียนสำเร็จ']);
            break;

        case 'DELETE':
            $id = $_GET['id'] ?? 0;
            $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'ลบข้อมูลนักเรียนสำเร็จ']);
            break;
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
}
