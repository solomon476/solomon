<?php
/**
 * reset_password.php – Password Reset Execution
 * Secure Login System
 *
 * Security measures:
 *  - CSRF token validation
 *  - Token compared against SHA-256 hash stored in DB (never stored raw)
 *  - Token expiry enforced server-side
 *  - Token is single-use: deleted immediately after successful reset
 *  - New password hashed with bcrypt (cost 12)
 *  - Failed login attempts reset on successful password change
 */
session_start();
require_once 'config.php';

// Redirect authenticated users
if (isset($_SESSION['user_id'])) {
    header('Location: profile.php');
    exit;
}

$errors  = [];
$success = false;

// Pre-fill email from query string (passed by forgot_password.php demo link)
$prefill_email = filter_var($_GET['email'] ?? '', FILTER_SANITIZE_EMAIL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $email        = trim($_POST['email']        ?? '');
    $token        = trim($_POST['token']        ?? '');
    $new_password = $_POST['new_password']       ?? '';
    $confirm_pw   = $_POST['confirm_password']   ?? '';

    // ── Validation ──────────────────────────────────────────────────
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (strlen($token) !== 64) {
        $errors[] = 'The reset token must be exactly 64 characters. Please copy it carefully.';
    }
    if (strlen($new_password) < 8) {
        $errors[] = 'New password must be at least 8 characters.';
    }
    if ($new_password !== $confirm_pw) {
        $errors[] = 'Passwords do not match.';
    }

    if (empty($errors)) {
        $db = get_db();

        // ── Look up user and join their reset token ────────────────
        $stmt = $db->prepare(
            'SELECT u.id, pr.id AS reset_id, pr.reset_token, pr.expires_at
             FROM users u
             JOIN password_resets pr ON pr.user_id = u.id
             WHERE u.email = :email
             LIMIT 1'
        );
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch();

        if (!$row) {
            $errors[] = 'No pending reset request found for that email address.';
        } elseif (new DateTime() > new DateTime($row['expires_at'])) {
            // Token expired — clean it up
            $db->prepare('DELETE FROM password_resets WHERE id = :rid')
               ->execute([':rid' => $row['reset_id']]);
            $errors[] = 'This reset token has expired. Please request a new one.';
        } elseif (!hash_equals($row['reset_token'], hash('sha256', $token))) {
            $errors[] = 'Invalid reset token. Please check and try again.';
        } else {
            // ── SUCCESS: update password & clean up token ──────────
            $new_hash = password_hash($new_password, PASSWORD_BCRYPT, ['cost' => 12]);

            $db->prepare(
                'UPDATE users
                 SET password_hash = :hash, failed_login_attempts = 0, account_locked_until = NULL
                 WHERE id = :uid'
            )->execute([':hash' => $new_hash, ':uid' => $row['id']]);

            // Delete the used token (single-use enforcement)
            $db->prepare('DELETE FROM password_resets WHERE id = :rid')
               ->execute([':rid' => $row['reset_id']]);

            $success = true;
            $_SESSION['flash_success'] = 'Password reset successfully! Please log in with your new password.';
            header('Location: login.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Set a new password for your SecureAuth account.">
    <title>Set New Password – SecureAuth</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="page-center">
        <div class="auth-card">

            <div class="logo">
                <div class="logo-icon">🔏</div>
                <h1>Set New Password</h1>
                <p class="subtitle">Enter your reset token and choose a new password</p>
            </div>

            <!-- Errors -->
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error" role="alert">
                    <span>⚠️</span>
                    <ul style="margin:0;padding-left:1rem;">
                        <?php foreach ($errors as $err): ?>
                            <li><?= e($err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form id="reset_form" method="POST" action="reset_password.php" novalidate>
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

                <!-- Email -->
                <div class="form-group">
                    <label for="email">Registered Email</label>
                    <div class="input-wrapper">
                        <span class="input-icon">📧</span>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            placeholder="you@example.com"
                            value="<?= e($_POST['email'] ?? $prefill_email) ?>"
                            autocomplete="email"
                            required>
                    </div>
                </div>

                <!-- Reset Token -->
                <div class="form-group">
                    <label for="token">Reset Token</label>
                    <div class="input-wrapper">
                        <span class="input-icon">🎟️</span>
                        <input
                            type="text"
                            id="token"
                            name="token"
                            placeholder="Paste your 64-character token here"
                            value="<?= e($_POST['token'] ?? '') ?>"
                            maxlength="64"
                            autocomplete="off"
                            required>
                    </div>
                </div>

                <!-- New Password -->
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <div class="input-wrapper">
                        <span class="input-icon">🔒</span>
                        <input
                            type="password"
                            id="new_password"
                            name="new_password"
                            placeholder="At least 8 characters"
                            autocomplete="new-password"
                            required>
                        <button type="button" class="toggle-password" data-target="new_password" aria-label="Toggle new password visibility">👁️</button>
                    </div>
                    <span class="field-error" id="new_password_error"></span>
                    <div class="strength-meter">
                        <div class="strength-meter-fill" id="strength_fill"></div>
                    </div>
                    <span class="strength-label" id="strength_label"></span>
                </div>

                <!-- Confirm New Password -->
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <div class="input-wrapper">
                        <span class="input-icon">🔒</span>
                        <input
                            type="password"
                            id="confirm_password"
                            name="confirm_password"
                            placeholder="Repeat your new password"
                            autocomplete="new-password"
                            required>
                        <button type="button" class="toggle-password" data-target="confirm_password" aria-label="Toggle confirm password visibility">👁️</button>
                    </div>
                    <span class="field-error" id="confirm_password_error"></span>
                </div>

                <button type="submit" class="btn btn-primary" id="reset_submit">
                    🔏 Set New Password
                </button>
            </form>

            <div class="auth-footer">
                <a href="forgot_password.php">← Request a new token</a>
                &nbsp;·&nbsp;
                <a href="login.php">Log In</a>
            </div>
        </div>
    </div>

    <script src="assets/script.js"></script>
</body>
</html>
