<?php
require_once __DIR__ . '/../includes/session_check.php';
require_once __DIR__ . '/../config/db.php';
requireLogin();

header('Content-Type: application/json');
$pdo = getDB();
$method = $_SERVER['REQUEST_METHOD'];

// Helper to handle image upload
function handleImageUpload($file) {
    if (!$file || $file['error'] !== UPLOAD_ERR_OK) return null;
    
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($ext, $allowed)) return null;
    
    $filename = uniqid('prof_') . '.' . $ext;
    $target = __DIR__ . '/../assets/uploads/teachers/' . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $target)) {
        return 'assets/uploads/teachers/' . $filename;
    }
    return null;
}

// In PHP, multipart/form-data only works naturally with POST
// For updates, we can send a POST request with an ID
if ($method === 'POST' && isset($_POST['action']) && $_POST['action'] === 'UPDATE') {
    $method = 'PUT';
}

try {
    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                $stmt = $pdo->prepare("SELECT t.*, c.class_name as homeroom_class_name 
                                     FROM teachers t 
                                     LEFT JOIN classes c ON t.homeroom_class_id = c.id 
                                     WHERE t.id = ?");
                $stmt->execute([$_GET['id']]);
                echo json_encode($stmt->fetch());
            } else {
                $stmt = $pdo->query("SELECT t.*, c.class_name as homeroom_class_name 
                                   FROM teachers t 
                                   LEFT JOIN classes c ON t.homeroom_class_id = c.id 
                                   ORDER BY t.id");
                echo json_encode($stmt->fetchAll());
            }
            break;

        case 'POST':
            $imagePath = handleImageUpload($_FILES['profile_image'] ?? null);
            
            $stmt = $pdo->prepare("INSERT INTO teachers (teacher_code, first_name, last_name, phone, department, teaching_level, profile_image, homeroom_class_id) 
                                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $_POST['teacher_code'], 
                $_POST['first_name'], 
                $_POST['last_name'], 
                $_POST['phone'] ?? null, 
                $_POST['department'] ?? null,
                $_POST['teaching_level'] ?? 'middle',
                $imagePath,
                !empty($_POST['homeroom_class_id']) ? $_POST['homeroom_class_id'] : null
            ]);
            echo json_encode(['success' => true, 'message' => 'เพิ่มข้อมูลครูสำเร็จ']);
            break;

        case 'PUT':
            // Since we're using POST with action=UPDATE workaround
            $id = $_POST['id'];
            $imagePath = handleImageUpload($_FILES['profile_image'] ?? null);
            
            $sql = "UPDATE teachers SET teacher_code=?, first_name=?, last_name=?, phone=?, department=?, teaching_level=?, homeroom_class_id=? ";
            $params = [
                $_POST['teacher_code'], 
                $_POST['first_name'], 
                $_POST['last_name'], 
                $_POST['phone'] ?? null, 
                $_POST['department'] ?? null,
                $_POST['teaching_level'] ?? 'middle',
                !empty($_POST['homeroom_class_id']) ? $_POST['homeroom_class_id'] : null
            ];
            
            if ($imagePath) {
                $sql .= ", profile_image=? ";
                $params[] = $imagePath;
            }
            
            $sql .= " WHERE id=?";
            $params[] = $id;
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            echo json_encode(['success' => true, 'message' => 'แก้ไขข้อมูลครูสำเร็จ']);
            break;

        case 'DELETE':
            $id = $_GET['id'] ?? 0;
            // Optionally delete image file
            $stmt = $pdo->prepare("SELECT profile_image FROM teachers WHERE id = ?");
            $stmt->execute([$id]);
            $img = $stmt->fetchColumn();
            if ($img && file_exists(__DIR__ . '/../' . $img)) {
                unlink(__DIR__ . '/../' . $img);
            }
            
            $stmt = $pdo->prepare("DELETE FROM teachers WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'ลบข้อมูลครูสำเร็จ']);
            break;
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
}
