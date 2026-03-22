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
                $stmt = $pdo->prepare("SELECT * FROM subjects WHERE id = ?");
                $stmt->execute([$_GET['id']]);
                echo json_encode($stmt->fetch());
            } else {
                $stmt = $pdo->query("SELECT * FROM subjects ORDER BY id");
                echo json_encode($stmt->fetchAll());
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            $stmt = $pdo->prepare("INSERT INTO subjects (subject_code, subject_name, credits) VALUES (?, ?, ?)");
            $stmt->execute([$data['subject_code'], $data['subject_name'], $data['credits']]);
            echo json_encode(['success' => true, 'message' => 'เพิ่มข้อมูลรายวิชาสำเร็จ']);
            break;

        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), true);
            $stmt = $pdo->prepare("UPDATE subjects SET subject_code=?, subject_name=?, credits=? WHERE id=?");
            $stmt->execute([$data['subject_code'], $data['subject_name'], $data['credits'], $data['id']]);
            echo json_encode(['success' => true, 'message' => 'แก้ไขข้อมูลรายวิชาสำเร็จ']);
            break;

        case 'DELETE':
            $id = $_GET['id'] ?? 0;
            $stmt = $pdo->prepare("DELETE FROM subjects WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'ลบข้อมูลรายวิชาสำเร็จ']);
            break;
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
}
