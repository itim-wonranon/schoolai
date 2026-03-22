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
                $stmt = $pdo->prepare("SELECT * FROM teachers WHERE id = ?");
                $stmt->execute([$_GET['id']]);
                echo json_encode($stmt->fetch());
            } else {
                $stmt = $pdo->query("SELECT * FROM teachers ORDER BY id");
                echo json_encode($stmt->fetchAll());
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            $stmt = $pdo->prepare("INSERT INTO teachers (teacher_code, first_name, last_name, phone, department) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$data['teacher_code'], $data['first_name'], $data['last_name'], $data['phone'] ?? null, $data['department'] ?? null]);
            echo json_encode(['success' => true, 'message' => 'เพิ่มข้อมูลครูสำเร็จ']);
            break;

        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), true);
            $stmt = $pdo->prepare("UPDATE teachers SET teacher_code=?, first_name=?, last_name=?, phone=?, department=? WHERE id=?");
            $stmt->execute([$data['teacher_code'], $data['first_name'], $data['last_name'], $data['phone'] ?? null, $data['department'] ?? null, $data['id']]);
            echo json_encode(['success' => true, 'message' => 'แก้ไขข้อมูลครูสำเร็จ']);
            break;

        case 'DELETE':
            $id = $_GET['id'] ?? 0;
            $stmt = $pdo->prepare("DELETE FROM teachers WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'ลบข้อมูลครูสำเร็จ']);
            break;
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
}
