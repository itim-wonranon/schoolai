<?php
require_once __DIR__ . '/../../includes/session_check.php';
requireRole('admin');

header('Content-Type: application/json');
$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$importType = $_POST['import_type'] ?? '';
$file = $_FILES['import_file'] ?? null;

if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'กรุณาอัปโหลดไฟล์ที่ถูกต้อง']);
    exit;
}

$handle = fopen($file['tmp_name'], 'r');
if ($handle === false) {
    echo json_encode(['success' => false, 'message' => 'ไม่สามารถเปิดไฟล์ได้']);
    exit;
}

// Skip header
$header = fgetcsv($handle);
$results = [];
$rowNum = 2; // Data starts at row 2

try {
    while (($row = fgetcsv($handle)) !== false) {
        if (empty($row[0])) continue; // Skip empty rows

        $status = 'success';
        $message = 'สำเร็จ';
        $identifier = $row[0]; // Usually ID (T001 or S001)

        $pdo->beginTransaction();
        try {
            if ($importType === 'teachers') {
                // row: [id, name, specialization, phone, email, username, password]
                $tId = $row[0];
                $name = $row[1];
                $spec = $row[2] ?? '';
                $phone = $row[3] ?? '';
                $email = $row[4] ?? '';
                $user = $row[5];
                $pass = $row[6] ?? 'teacher123';

                // 1. Create User
                $stmtU = $pdo->prepare("INSERT INTO users (username, password_hash, display_name, role, role_id, ref_id) VALUES (?, ?, ?, 'teacher', (SELECT id FROM roles WHERE role_key = 'teacher'), ?)");
                $stmtU->execute([$user, password_hash($pass, PASSWORD_DEFAULT), $name, $tId]);
                
                // 2. Create Teacher
                $stmtT = $pdo->prepare("INSERT INTO teachers (teacher_id, name, specialization, phone, email) VALUES (?, ?, ?, ?, ?)");
                $stmtT->execute([$tId, $name, $spec, $phone, $email]);

            } elseif ($importType === 'students') {
                // row: [id, name, class, phone, parents, username, password]
                $sId = $row[0];
                $name = $row[1];
                $class = $row[2] ?? '';
                $phone = $row[3] ?? '';
                $parent = $row[4] ?? '';
                $user = $row[5];
                $pass = $row[6] ?? 'student123';

                // 1. Create User
                $stmtU = $pdo->prepare("INSERT INTO users (username, password_hash, display_name, role, role_id, ref_id) VALUES (?, ?, ?, 'student', (SELECT id FROM roles WHERE role_key = 'student'), ?)");
                $stmtU->execute([$user, password_hash($pass, PASSWORD_DEFAULT), $name, $sId]);
                
                // 2. Create Student
                $stmtS = $pdo->prepare("INSERT INTO students (student_current_id, name, class, phone, parents_phone) VALUES (?, ?, ?, ?, ?)");
                $stmtS->execute([$sId, $name, $class, $phone, $parent]);
            }

            $pdo->commit();
        } catch (Exception $rowEx) {
            $pdo->rollBack();
            $status = 'error';
            $message = $rowEx->getMessage();
        }

        $results[] = [
            'row' => $rowNum++,
            'identifier' => $identifier,
            'status' => $status,
            'message' => $message
        ];
    }

    logActivity('bulk_import', "ดำเนินการนำเข้าข้อมูลจำนวนมาก: $importType", ['rows' => count($results)]);
    echo json_encode(['success' => true, 'details' => $results]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    fclose($handle);
}
