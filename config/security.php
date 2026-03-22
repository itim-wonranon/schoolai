<?php
// ============================================
// Security Configuration & Helpers
// ============================================

// --- Rate Limiting ---
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_MINUTES', 15);

// --- Session Security ---
define('SESSION_IDLE_TIMEOUT', 1800);       // 30 minutes
define('SESSION_REGENERATE_INTERVAL', 300); // 5 minutes

// --- CSRF ---
define('CSRF_TOKEN_NAME', 'csrf_token');

/**
 * Initialize a secure session with hardened cookie params.
 */
function initSecureSession(): void {
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

    session_set_cookie_params([
        'lifetime' => 0,                    // session cookie (expires on browser close)
        'path'     => '/schoolai/',
        'domain'   => '',
        'secure'   => $isHttps,              // only send over HTTPS when available
        'httponly'  => true,                  // JS cannot read session cookie
        'samesite' => 'Strict',              // CSRF protection at cookie level
    ]);

    session_name('SCHOOLAI_SESSID');         // custom session name (hide PHP)
    session_start();
}

/**
 * Generate a CSRF token and store it in the session.
 */
function generateCsrfToken(): string {
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

/**
 * Validate an incoming CSRF token against the session token.
 */
function validateCsrfToken(?string $token): bool {
    if (empty($token) || empty($_SESSION[CSRF_TOKEN_NAME])) {
        return false;
    }
    $valid = hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
    // Regenerate token after validation to prevent reuse
    unset($_SESSION[CSRF_TOKEN_NAME]);
    return $valid;
}

/**
 * Create a session fingerprint from the User-Agent.
 */
function createSessionFingerprint(): string {
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    return hash('sha256', $ua);
}

/**
 * Check and enforce idle-timeout + fingerprint validation.
 * Call this on every authenticated page load.
 * Returns false if the session is invalid / expired.
 */
function validateSession(): bool {
    // --- Fingerprint check ---
    if (isset($_SESSION['_fingerprint'])) {
        if ($_SESSION['_fingerprint'] !== createSessionFingerprint()) {
            return false; // possible session hijack
        }
    }

    // --- Idle timeout ---
    if (isset($_SESSION['_last_activity'])) {
        if (time() - $_SESSION['_last_activity'] > SESSION_IDLE_TIMEOUT) {
            return false; // idle too long
        }
    }
    $_SESSION['_last_activity'] = time();

    // --- Periodic session ID regeneration ---
    if (!isset($_SESSION['_created_at'])) {
        $_SESSION['_created_at'] = time();
    } elseif (time() - $_SESSION['_created_at'] > SESSION_REGENERATE_INTERVAL) {
        session_regenerate_id(true);
        $_SESSION['_created_at'] = time();
    }

    return true;
}

/**
 * Record a failed login attempt.
 */
function recordFailedAttempt(PDO $pdo, string $username): void {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $stmt = $pdo->prepare("INSERT INTO login_attempts (ip_address, username) VALUES (?, ?)");
    $stmt->execute([$ip, $username]);
}

/**
 * Check whether the current IP / username is rate-limited.
 */
function isRateLimited(PDO $pdo, string $username): bool {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $since = date('Y-m-d H:i:s', time() - (LOGIN_LOCKOUT_MINUTES * 60));

    // Check by IP
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) FROM login_attempts WHERE ip_address = ? AND attempted_at > ?"
    );
    $stmt->execute([$ip, $since]);
    if ((int) $stmt->fetchColumn() >= MAX_LOGIN_ATTEMPTS) {
        return true;
    }

    // Check by username
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) FROM login_attempts WHERE username = ? AND attempted_at > ?"
    );
    $stmt->execute([$username, $since]);
    if ((int) $stmt->fetchColumn() >= MAX_LOGIN_ATTEMPTS) {
        return true;
    }

    return false;
}

/**
 * Purge old login attempts (housekeeping).
 */
function purgeOldAttempts(PDO $pdo): void {
    $cutoff = date('Y-m-d H:i:s', time() - (LOGIN_LOCKOUT_MINUTES * 60 * 4));
    $pdo->prepare("DELETE FROM login_attempts WHERE attempted_at < ?")->execute([$cutoff]);
}
