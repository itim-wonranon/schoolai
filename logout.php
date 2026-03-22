<?php
require_once __DIR__ . '/config/security.php';

// Initialize secure session to access it
initSecureSession();

// 1. Unset all session variables
$_SESSION = [];

// 2. Delete the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Destroy the session
session_destroy();

// 4. Redirect to login
header('Location: login.php');
exit;
