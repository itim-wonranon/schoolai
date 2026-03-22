<?php
require_once __DIR__ . '/../../includes/session_check.php';
requireRole('admin');

header('Content-Type: application/json');
$pdo = getDB();
$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        if (isset($_GET['id'])) {
            $stmt = $pdo->prepare("SELECT * FROM academic_years WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
        } else {
            $stmt = $pdo->query("SELECT * FROM academic_years ORDER BY year DESC, term DESC");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        }
    } 
    elseif ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $pdo->beginTransaction();
        
        // If set as current, unset others
        if ($data['is_current']) {
            $pdo->query("UPDATE academic_years SET is_current = 0");
        }
        
        $stmt = $pdo->prepare("INSERT INTO academic_years (year, term, is_current) VALUES (?, ?, ?)");
        $stmt->execute([$data['year'], $data['term'], $data['is_current']]);
        
        $pdo->commit();
        logActivity('academic_year_create', "Added academic year: " . $data['year'] . "/" . $data['term']);
        echo json_encode(['success' => true, 'message' => 'เพิ่มปีการศึกษาสำเร็จ']);
    }
    elseif ($method === 'PUT') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $pdo->beginTransaction();
        if ($data['is_current']) {
            $pdo->query("UPDATE academic_years SET is_current = 0");
        }
        
        $stmt = $pdo->prepare("UPDATE academic_years SET year = ?, term = ?, is_current = ? WHERE id = ?");
        $stmt->execute([$data['year'], $data['term'], $data['is_current'], $data['id']]);
        
        $pdo->commit();
        logActivity('academic_year_update', "Updated academic year ID: " . $data['id']);
        echo json_encode(['success' => true, 'message' => 'อัปเดตข้อมูลสำเร็จ']);
    }
    elseif ($method === 'PATCH') {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'];
        
        $pdo->beginTransaction();
        $pdo->query("UPDATE academic_years SET is_current = 0");
        $stmt = $pdo->prepare("UPDATE academic_years SET is_current = 1 WHERE id = ?");
        $stmt->execute([$id]);
        $pdo->commit();
        
        logActivity('academic_year_activate', "Set academic year ID $id as current");
        echo json_encode(['success' => true]);
    }
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
