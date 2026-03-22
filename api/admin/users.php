<?php
require_once __DIR__ . '/../../includes/session_check.php';
requireRole('admin');

header('Content-Type: application/json');
$pdo = getDB();
$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        // Metadata lookups for dropdowns
        if (isset($_GET['type'])) {
            $type = $_GET['type'];
            if ($type === 'classes') {
                $stmt = $pdo->query("SELECT id, class_name FROM classes ORDER BY class_name");
                echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            } elseif ($type === 'teachers') {
                $stmt = $pdo->query("SELECT id, first_name, last_name FROM teachers ORDER BY first_name");
                echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            } elseif ($type === 'subjects') {
                $stmt = $pdo->query("SELECT id, subject_code, subject_name FROM subjects ORDER BY subject_name");
                echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            } elseif ($type === 'classrooms') {
                $stmt = $pdo->query("SELECT id, room_name FROM classrooms ORDER BY room_name");
                echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            }
            exit;
        }

        if (isset($_GET['id'])) {
            $stmt = $pdo->prepare("SELECT id, username, display_name, role, role_id, is_suspended, last_login FROM users WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
        } else {
            $search = $_GET['search'] ?? '';
            $role = $_GET['role'] ?? '';
            $status = $_GET['status'] ?? '';
            
            $sql = "SELECT id, username, display_name, role, role_id, is_suspended, last_login FROM users WHERE 1=1";
            $params = [];
            
            if ($search) {
                $sql .= " AND (username LIKE ? OR display_name LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }
            if ($role) {
                $sql .= " AND role = ?";
                $params[] = $role;
            }
            if ($status === 'suspended') {
                $sql .= " AND is_suspended = 1";
            } elseif ($status === 'active') {
                $sql .= " AND is_suspended = 0";
            }
            
            $sql .= " ORDER BY last_login DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        }
    } 
    elseif ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Check if username exists
        $check = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $check->execute([$data['username']]);
        if ($check->fetch()) {
            echo json_encode(['success' => false, 'message' => 'ชื่อผู้ใช้นี้มีอยู่ในระบบแล้ว']);
            exit;
        }
        
        $passHash = password_hash($data['password'] ?: '123456', PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, display_name, role, role_id, is_suspended, is_active) VALUES (?, ?, ?, ?, (SELECT id FROM roles WHERE role_key = ?), ?, 1)");
        $stmt->execute([
            $data['username'],
            $passHash,
            $data['display_name'],
            $data['role'],
            $data['role'],
            $data['is_suspended'] ?? 0
        ]);
        
        logActivity('user_create', "Created new user: " . $data['username']);
        echo json_encode(['success' => true, 'message' => 'สร้างผู้ใช้สำเร็จ']);
    }
    elseif ($method === 'PUT') {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'];
        
        $sql = "UPDATE users SET username = ?, display_name = ?, role = ?, role_id = (SELECT id FROM roles WHERE role_key = ?), is_suspended = ?";
        $params = [
            $data['username'],
            $data['display_name'],
            $data['role'],
            $data['role'],
            $data['is_suspended']
        ];
        
        if (!empty($data['password'])) {
            $sql .= ", password_hash = ?";
            $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        $sql .= " WHERE id = ?";
        $params[] = $id;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        logActivity('user_update', "Updated user ID: $id", ['username' => $data['username']]);
        echo json_encode(['success' => true, 'message' => 'อัปเดตข้อมูลสำเร็จ']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
