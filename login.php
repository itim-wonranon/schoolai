<?php
require_once __DIR__ . '/includes/session_check.php';

// Initialize secure session
// initSecureSession(); // Already called in session_check.php

// Redirect if already logged in
if (isLoggedIn()) {
    $role = getUserRole();
    if ($role === 'admin') {
        header('Location: index.php');
    } else {
        header('Location: my_schedule.php');
    }
    exit;
}

$error = '';
$err_type = $_GET['error'] ?? '';
if ($err_type === 'account_suspended') $error = 'บัญชีของคุณถูกระงับการใช้งาน กรุณาติดต่อผู้ดูแลระบบ';
elseif ($err_type === 'session_expired') $error = 'เซสชั่นของคุณหมดอายุ กรุณาเข้าสู่ระบบใหม่';
elseif ($err_type === 'unauthorized') $error = 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = getDB();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $csrf_token = $_POST[CSRF_TOKEN_NAME] ?? '';

    // 1. Validate CSRF Token
    if (!validateCsrfToken($csrf_token)) {
        $error = 'การขอรับข้อมูลไม่ถูกต้อง (Invalid CSRF)';
    } 
    // 2. Check for empty fields
    elseif (empty($username) || empty($password)) {
        $error = 'กรุณากรอกชื่อผู้ใช้และรหัสผ่าน';
    } 
    else {
        // 3. Authenticate User
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND is_active = 1 LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            // Check for suspension
            if ($user['is_suspended']) {
                logActivity('login_failed', "Suspended user tried to login: $username");
                header('Location: login.php?error=account_suspended');
                exit;
            }

            // Success: Regenerate session for session fixation protection
            session_regenerate_id(true);
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['role_id'] = $user['role_id'];
            $_SESSION['ref_id'] = $user['ref_id'];
            $_SESSION['display_name'] = $user['display_name'] ?? $user['username'];
            
            // Set session fingerprint and activity
            $_SESSION['_fingerprint'] = createSessionFingerprint();
            $_SESSION['_last_activity'] = time();
            $_SESSION['_created_at'] = time();

            // Update last login
            $stmtUpdate = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $stmtUpdate->execute([$user['id']]);

            // Audit Log
            logActivity('login_success', 'User logged in successfully');

            if ($user['role'] === 'admin') {
                header('Location: index.php');
            } else {
                header('Location: my_schedule.php');
            }
            exit;
        } else {
            // Failure: Show generic error
            logActivity('login_failed', "Failed login attempt for username: $username");
            $error = 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง';
        }
    }
}

// Generate new CSRF token for the form
$token = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ - โรงเรียนสาธิตวิทยา</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="/schoolai/assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="login-wrapper">
        <div class="login-card">
            <div class="login-logo">
                <i class="bi bi-mortarboard-fill"></i>
                <h1>โรงเรียนสาธิตวิทยา</h1>
                <p>ระบบบริหารจัดการโรงเรียน</p>
            </div>

            <?php if ($error): ?>
                <div class="login-error">
                    <i class="bi bi-exclamation-circle-fill"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" id="loginForm">
                <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $token; ?>">
                
                <div class="mb-3">
                    <label for="username" class="form-label">ชื่อผู้ใช้</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" class="form-control" id="username" name="username"
                               value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                               placeholder="กรอกชื่อผู้ใช้" required autofocus autocomplete="username">
                    </div>
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label">รหัสผ่าน</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" class="form-control" id="password" name="password"
                               placeholder="กรอกรหัสผ่าน" required autocomplete="current-password">
                    </div>
                </div>
                <button type="submit" class="btn-login">
                    <i class="bi bi-box-arrow-in-right"></i> เข้าสู่ระบบ
                </button>
            </form>

            <div class="mt-4 text-center" style="font-size:0.8rem;color:#94A3B8;">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> โรงเรียนสาธิตวิทยา</p>
            </div>
        </div>
    </div>
</body>
</html>
