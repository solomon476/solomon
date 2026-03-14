<?php
/**
 * config.php
 * Central configuration file for the Secure Login System.
 * Included by every PHP page that needs a database connection.
 */

// ── Session Security Configuration ────────────────────────────────────────────
// Must be called BEFORE session_start() in any file that includes this.
ini_set('session.cookie_httponly', 1);   // Block JS access to the session cookie
ini_set('session.cookie_samesite', 'Strict'); // Prevent CSRF via cross-site requests
ini_set('session.use_strict_mode', 1);   // Reject uninitialized session IDs
// In a production environment with HTTPS, also set:
// ini_set('session.cookie_secure', 1);

// ── Database Credentials ────────────────────────────────────────────────────
define('DB_HOST', 'localhost');
define('DB_NAME', 'secure_login_db');
define('DB_USER', 'root');       // Change to your MySQL username
define('DB_PASS', '');           // Change to your MySQL password
define('DB_CHARSET', 'utf8mb4');

// ── Application Constants ───────────────────────────────────────────────────
define('MAX_LOGIN_ATTEMPTS', 5);       // Lock after 5 failed attempts
define('LOCKOUT_DURATION_MINUTES', 15); // Lock duration in minutes
define('RESET_TOKEN_EXPIRY_HOURS', 1); // Password reset token validity

// ── PDO Connection ──────────────────────────────────────────────────────────
function get_db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            DB_HOST, DB_NAME, DB_CHARSET
        );
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false, // Use real prepared statements
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Generic error shown to users; real error only in server logs.
            error_log('Database connection failed: ' . $e->getMessage());
            die('<p style="color:red;text-align:center;margin-top:50px;">A database error occurred. Please try again later.</p>');
        }
    }
    return $pdo;
}

// ── CSRF Helper Functions ───────────────────────────────────────────────────

/**
 * Generate (or return existing) CSRF token stored in the session.
 */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate the CSRF token from a POST request.
 * Terminates the script if the token is missing or invalid.
 */
function verify_csrf(): void {
    $submitted = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $submitted)) {
        http_response_code(403);
        die('CSRF token validation failed. Please go back and try again.');
    }
}

// ── HTML Escape Helper ──────────────────────────────────────────────────────

/**
 * Safely escape a value for output in HTML context (prevents XSS).
 */
function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
