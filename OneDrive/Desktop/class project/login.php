<?php
/**
 * login.php – User Authentication
 * Secure Login System
 *
 * Security measures implemented:
 *  - CSRF token validation
 *  - Brute-force lockout (MAX_LOGIN_ATTEMPTS over LOCKOUT_DURATION_MINUTES)
 *  - password_verify() for bcrypt comparison
 *  - session_regenerate_id() on successful login (session fixation prevention)
 *  - Generic error messages (no username/password distinction)
 *  - Parameterised PDO queries
 */
session_start();
require_once 'config.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: profile.php');
    exit;
}

$error = '';

// ── Pull and clear any flash messages set by other pages ──────────────────
$flash_success = $_SESSION['flash_success'] ?? '';
$flash_info    = $_SESSION['flash_info']    ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_info']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. CSRF check
    verify_csrf();

    // 2. Collect inputs (allow login by username OR email)
    $identifier = trim($_POST['identifier'] ?? '');
    $password   = $_POST['password']        ?? '';

    if (empty($identifier) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $db = get_db();

        // 3. Look up user by username or email
        $stmt = $db->prepare(
            'SELECT id, username, email, password_hash, failed_login_attempts, account_locked_until
             FROM users
             WHERE username = :id OR email = :id
             LIMIT 1'
        );
        $stmt->execute([':id' => $identifier]);
        $user = $stmt->fetch();

        if ($user) {
            // 4. Check account lockout
            if ($user['account_locked_until'] !== null &&
                new DateTime() < new DateTime($user['account_locked_until'])) {
                $unlock = new DateTime($user['account_locked_until']);
                $error  = 'Your account is temporarily locked due to too many failed attempts. '
                        . 'Please try again after ' . $unlock->format('H:i:s') . '.';
            } elseif (password_verify($password, $user['password_hash'])) {
                // 5. SUCCESS: reset failed attempts, start session
                $db->prepare('UPDATE users SET failed_login_attempts = 0, account_locked_until = NULL WHERE id = :id')
                   ->execute([':id' => $user['id']]);

                // Prevent session fixation
                session_regenerate_id(true);

                $_SESSION['user_id']  = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email']    = $user['email'];

                header('Location: profile.php');
                exit;
            } else {
                // 6. FAILURE: increment attempt counter
                $attempts = $user['failed_login_attempts'] + 1;
                $locked_until = null;

                if ($attempts >= MAX_LOGIN_ATTEMPTS) {
                    $locked_until = (new DateTime())
                        ->modify('+' . LOCKOUT_DURATION_MINUTES . ' minutes')
                        ->format('Y-m-d H:i:s');
                    $error = 'Too many failed attempts. Your account has been locked for '
                           . LOCKOUT_DURATION_MINUTES . ' minutes.';
                } else {
                    $remaining = MAX_LOGIN_ATTEMPTS - $attempts;
                    $error = 'Invalid username or password. ' . $remaining . ' attempt(s) remaining before lock.';
                }

                $db->prepare(
                    'UPDATE users SET failed_login_attempts = :a, account_locked_until = :l WHERE id = :id'
                )->execute([':a' => $attempts, ':l' => $locked_until, ':id' => $user['id']]);
            }
        } else {
            // Generic message — don't reveal whether username exists
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Log in to your SecureAuth account.">
    <title>Log In – SecureAuth</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="page-center">
        <div class="auth-card">

            <!-- Logo -->
            <div class="logo">
                <div class="logo-icon">🔑</div>
                <h1>Welcome Back</h1>
                <p class="subtitle">Sign in to continue to SecureAuth</p>
            </div>

            <!-- Flash messages from other pages -->
            <?php if ($flash_success): ?>
                <div class="alert alert-success" role="alert">✅ <?= e($flash_success) ?></div>
            <?php endif; ?>
            <?php if ($flash_info): ?>
                <div class="alert alert-info" role="alert">ℹ️ <?= e($flash_info) ?></div>
            <?php endif; ?>

            <!-- Login error -->
            <?php if ($error): ?>
                <div class="alert alert-error" role="alert">
                    <span>⚠️</span> <?= e($error) ?>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form id="login_form" method="POST" action="login.php" novalidate>
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

                <!-- Username or Email -->
                <div class="form-group">
                    <label for="identifier">Username or Email</label>
                    <div class="input-wrapper">
                        <span class="input-icon">👤</span>
                        <input
                            type="text"
                            id="identifier"
                            name="identifier"
                            placeholder="john_doe or you@example.com"
                            value="<?= e($_POST['identifier'] ?? '') ?>"
                            autocomplete="username"
                            required>
                    </div>
                    <span class="field-error" id="identifier_error"></span>
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <span class="input-icon">🔒</span>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Your password"
                            autocomplete="current-password"
                            required>
                        <button type="button" class="toggle-password" data-target="password" aria-label="Toggle password visibility">👁️</button>
                    </div>
                    <span class="field-error" id="password_error"></span>
                </div>

                <!-- Forgot password link -->
                <div style="text-align:right; margin-top:-0.5rem; margin-bottom:1rem;">
                    <a href="forgot_password.php" style="font-size:0.85rem;">Forgot password?</a>
                </div>

                <button type="submit" class="btn btn-primary" id="login_submit">
                    🔑 Log In
                </button>
            </form>

            <div class="auth-footer">
                Don't have an account? <a href="register.php">Register</a>
            </div>
        </div>
    </div>

    <script src="assets/script.js"></script>
</body>
</html>
