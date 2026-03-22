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
                $stmt = $pdo->prepare("SELECT * FROM classrooms WHERE id = ?");
                $stmt->execute([$_GET['id']]);
                echo json_encode($stmt->fetch());
            } else {
                $stmt = $pdo->query("SELECT * FROM classrooms ORDER BY id");
                echo json_encode($stmt->fetchAll());
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            $stmt = $pdo->prepare("INSERT INTO classrooms (room_code, room_name) VALUES (?, ?)");
            $stmt->execute([$data['room_code'], $data['room_name']]);
            echo json_encode(['success' => true, 'message' => 'เพิ่มข้อมูลห้องเรียนสำเร็จ']);
            break;

        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), true);
            $stmt = $pdo->prepare("UPDATE classrooms SET room_code=?, room_name=? WHERE id=?");
            $stmt->execute([$data['room_code'], $data['room_name'], $data['id']]);
            echo json_encode(['success' => true, 'message' => 'แก้ไขข้อมูลห้องเรียนสำเร็จ']);
            break;

        case 'DELETE':
            $id = $_GET['id'] ?? 0;
            $stmt = $pdo->prepare("DELETE FROM classrooms WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'ลบข้อมูลห้องเรียนสำเร็จ']);
            break;
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
}
