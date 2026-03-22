<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/security.php';

// Global base URL
$base_url = '/schoolai';

// Initialize secure session
initSecureSession();

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if the current user is suspended
 */
function isSuspended() {
    if (!isset($_SESSION['user_id'])) return false;
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT is_suspended FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    return $user && isset($user['is_suspended']) && $user['is_suspended'] == 1;
}

/**
 * Check if an admin is currently impersonating another user
 */
function isImpersonating() {
    return isset($_SESSION['original_admin_id']);
}

/**
 * Log an activity to the audit trail
 */
function logActivity($action, $description = '', $details = null) {
    if (!isset($_SESSION['user_id'])) return;
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address, details) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $_SESSION['user_id'],
            $action,
            $description,
            $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            $details ? json_encode($details) : null
        ]);
    } catch (Exception $e) {
        // Silently fail logging if DB error to avoid crashing the app
    }
}

/**
 * Enforce login and validate session security (timeout, fingerprint, suspension)
 */
function requireLogin() {
    global $base_url;
    // 1. Not logged in
    if (!isLoggedIn()) {
        header("Location: $base_url/login.php");
        exit;
    }

    // 2. Security validation (Fingerprint, Timeout, Periodic Regeneration)
    if (!validateSession()) {
        session_unset();
        session_destroy();
        header("Location: $base_url/login.php?error=session_expired");
        exit;
    }

    // 3. Suspension check
    if (isSuspended()) {
        logActivity('login_blocked', 'Suspended account attempted to access the system');
        session_unset();
        session_destroy();
        header("Location: $base_url/login.php?error=account_suspended");
        exit;
    }
}

/**
 * Enforce role-based access
 */
function requireRole($allowed_roles) {
    global $base_url;
    requireLogin();
    
    if (!is_array($allowed_roles)) $allowed_roles = [$allowed_roles];
    
    $user_role = getUserRole();
    if (!in_array($user_role, $allowed_roles)) {
        logActivity('unauthorized_access', "Attempted access to restricted page with role: $user_role");
        
        http_response_code(403);
        echo '<div style="text-align:center;margin-top:100px;font-family:sans-serif;color:#1E293B;">';
        echo '<h1 style="color:#EF4444;">403 - ไม่มีสิทธิ์เข้าถึง</h1>';
        echo '<p>ขออภัย คุณไม่มีสิทธิ์ในการเข้าถึงหน้านี้</p>';
        echo '<a href="/schoolai/index.php" style="color:#8B6914;text-decoration:underline;">กลับหน้าหลัก</a>';
        echo '</div>';
        exit;
    }
}

function getUserRole() {
    return $_SESSION['role'] ?? '';
}

function getUserId() {
    return $_SESSION['user_id'] ?? 0;
}

function getRefId() {
    return $_SESSION['ref_id'] ?? 0;
}

function getDisplayName() {
    return $_SESSION['display_name'] ?? 'ผู้ใช้';
}
