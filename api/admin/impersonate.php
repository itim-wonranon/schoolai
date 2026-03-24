<?php
require_once __DIR__ . '/../../includes/session_check.php';

header('Content-Type: application/json');
$pdo = getDB();
$action = $_REQUEST['action'] ?? '';

try {
    if ($action === 'start') {
        requireRole('admin'); // Only admins can start
        $user_id = $_POST['user_id'] ?? 0;
        
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND is_suspended = 0 LIMIT 1");
        $stmt->execute([$user_id]);
        $target = $stmt->fetch();
        
        if (!$target) {
            echo json_encode(['success' => false, 'message' => 'ไม่พบผู้ใช้ที่ต้องการจำลองสิทธิ์ หรือผู้ใช้นั้นถูกระงับอยู่']);
            exit;
        }

        // Save current admin info to restore later
        if (!isset($_SESSION['original_admin_id'])) {
            $_SESSION['original_admin_id'] = $_SESSION['user_id'];
            $_SESSION['original_admin_name'] = $_SESSION['display_name'];
        }

        // Log the event
        logActivity('impersonation_start', "เริ่มการสวมรอยเป็นผู้ใช้: " . $target['username'], ['target_id' => $user_id]);

        // Switch identity
        $_SESSION['user_id'] = $target['id'];
        $_SESSION['username'] = $target['username'];
        $_SESSION['role'] = $target['role'];
        $_SESSION['role_id'] = $target['role_id'];
        $_SESSION['ref_id'] = $target['ref_id'];
        $_SESSION['display_name'] = $target['display_name'] ?? $target['username'];

        echo json_encode(['success' => true]);
    } 
    elseif ($action === 'stop') {
        if (!isset($_SESSION['original_admin_id'])) {
             header('Location: ../../index.php');
             exit;
        }

        $admin_id = $_SESSION['original_admin_id'];
        
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$admin_id]);
        $admin = $stmt->fetch();

        logActivity('impersonation_stop', "ยกเลิกการสวมรอย");

        // Restore identity
        $_SESSION['user_id'] = $admin['id'];
        $_SESSION['username'] = $admin['username'];
        $_SESSION['role'] = $admin['role'];
        $_SESSION['role_id'] = $admin['role_id'];
        $_SESSION['ref_id'] = $admin['ref_id'];
        $_SESSION['display_name'] = $admin['display_name'] ?? $admin['username'];

        unset($_SESSION['original_admin_id']);
        unset($_SESSION['original_admin_name']);

        if (isset($_GET['action'])) {
            header('Location: ../../admin/users.php');
            exit;
        }
        echo json_encode(['success' => true]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
