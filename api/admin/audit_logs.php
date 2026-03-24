<?php
require_once __DIR__ . '/../../includes/session_check.php';
requireRole('admin');

header('Content-Type: application/json');
$pdo = getDB();

try {
    $action = $_GET['action'] ?? '';
    $search = $_GET['search'] ?? '';
    $time = $_GET['time'] ?? 'all';
    $type = $_GET['type'] ?? '';

    if ($type === 'stats') {
        $total = $pdo->query("SELECT COUNT(*) FROM activity_logs")->fetchColumn();
        $today = $pdo->query("SELECT COUNT(*) FROM activity_logs WHERE DATE(created_at) = CURDATE()")->fetchColumn();
        $logins = $pdo->query("SELECT COUNT(*) FROM activity_logs WHERE action = 'login_success' AND DATE(created_at) = CURDATE()")->fetchColumn();
        $errors = $pdo->query("SELECT COUNT(*) FROM activity_logs WHERE action IN ('login_failed', 'unauthorized_access', 'login_blocked') AND DATE(created_at) = CURDATE()")->fetchColumn();
        echo json_encode([
            'total' => (int)$total,
            'today' => (int)$today,
            'logins' => (int)$logins,
            'errors' => (int)$errors
        ]);
        exit;
    }

    if ($type === 'translate') {
        $translations = [
            'User logged in successfully' => 'เข้าสู่ระบบสำเร็จ',
            'Failed login attempt for username: %' => 'เข้าสู่ระบบไม่สำเร็จ (ชื่อผู้ใช้หรือรหัสผ่านผิด): %',
            'Suspended user tried to login: %' => 'เข้าสู่ระบบไม่สำเร็จ (บัญชีถูกระงับ): %',
            'Suspended account attempted to access the system' => 'การเข้าสู่ระบบถูกระงับ (บัญชีถูกระงับการใช้งาน)',
            'Attempted access to restricted page with role: %' => 'พยายามเข้าถึงหน้าเว็บที่ไม่มีสิทธิ์โดยมีสิทธิ์: %',
            'Created new user: %' => 'สร้างผู้ใช้ใหม่: %',
            'Updated user ID: %' => 'อัปเดตข้อมูลผู้ใช้ ID: %',
            'Admin started impersonating user: %' => 'เริ่มการสวมรอยเป็นผู้ใช้: %',
            'Admin stopped impersonation' => 'ยกเลิกการสวมรอย',
            'Performed bulk import for %' => 'ดำเนินการนำเข้าข้อมูลจำนวนมาก: %',
            'Updated global system settings' => 'อัปเดตการตั้งค่าระบบส่วนกลาง',
            'Broadcasted announcement: %' => 'ส่งประกาศข่าวสาร: %',
            'Added schedule for %' => 'เพิ่มตารางเรียนสำหรับ %',
            'Updated schedule ID: %' => 'อัปเดตตารางเรียน ID: %',
            'Deleted schedule ID: %' => 'ลบตารางเรียน ID: %'
        ];

        foreach ($translations as $old => $new) {
            if (strpos($old, '%') === false) {
                $stmt = $pdo->prepare("UPDATE activity_logs SET description = ? WHERE description = ?");
                $stmt->execute([$new, $old]);
            } else {
                $baseOld = str_replace('%', '', $old);
                $baseNew = str_replace('%', '', $new);
                $stmt = $pdo->prepare("UPDATE activity_logs SET description = REPLACE(description, ?, ?) WHERE description LIKE ?");
                $stmt->execute([$baseOld, $baseNew, $baseOld . '%']);
            }
        }
        echo json_encode(['success' => true]);
        exit;
    }

    $sql = "SELECT l.*, u.username, u.display_name 
            FROM activity_logs l 
            LEFT JOIN users u ON l.user_id = u.id 
            WHERE 1=1";
    $params = [];
    
    if ($action) {
        $sql .= " AND l.action = ?";
        $params[] = $action;
    }
    if ($search) {
        $sql .= " AND (l.description LIKE ? OR u.username LIKE ? OR u.display_name LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    if ($time === 'today') {
        $sql .= " AND DATE(l.created_at) = CURDATE()";
    } elseif ($time === 'week') {
        $sql .= " AND l.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
    }
    
    $sql .= " ORDER BY l.created_at DESC LIMIT 200";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
