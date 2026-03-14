<?php
/**
 * forgot_password.php – Password Reset Request
 * Secure Login System
 *
 * Security measures:
 *  - CSRF token validation
 *  - Generic response regardless of whether email exists (prevents user enumeration)
 *  - Secure random token via random_bytes()
 *  - Token stored as SHA-256 hash in DB; raw token shown to user (simulating email delivery)
 *  - Old tokens for the user are deleted before inserting a new one
 *  - Token expires in RESET_TOKEN_EXPIRY_HOURS (defined in config.php)
 */
session_start();
require_once 'config.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: profile.php');
    exit;
}

$message = '';
$type    = 'info';  // alert type: info | success | error

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $email = trim($_POST['email'] ?? '');

    // Always show the same message to prevent user enumeration
    $generic_message = 'If that email is registered, a reset token has been generated. '
                     . 'Copy the token shown below and use it on the next page.';

    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $db   = get_db();
        $stmt = $db->prepare('SELECT id FROM users WHERE email = :e LIMIT 1');
        $stmt->execute([':e' => $email]);
        $user = $stmt->fetch();

        if ($user) {
            // 1. Delete any existing unused tokens for this user
            $db->prepare('DELETE FROM password_resets WHERE user_id = :uid')
               ->execute([':uid' => $user['id']]);

            // 2. Generate a cryptographically secure raw token
            $raw_token   = bin2hex(random_bytes(32));          // 64-char hex string
            $hashed_token = hash('sha256', $raw_token);        // Store hash, expose raw
            $expires_at  = (new DateTime())
                ->modify('+' . RESET_TOKEN_EXPIRY_HOURS . ' hour')
                ->format('Y-m-d H:i:s');

            // 3. Store hashed token
            $ins = $db->prepare(
                'INSERT INTO password_resets (user_id, reset_token, expires_at)
                 VALUES (:uid, :token, :exp)'
            );
            $ins->execute([
                ':uid'   => $user['id'],
                ':token' => $hashed_token,
                ':exp'   => $expires_at,
            ]);

            // NOTE: In a real system you would email $raw_token to the user.
            // For this local demo, we display it directly on screen.
            $message = $generic_message;
            $type    = 'success';

            // Pass raw token to display (simulates email delivery in this demo)
            $_SESSION['demo_reset_token'] = $raw_token;
            $_SESSION['demo_reset_email'] = $email;
        } else {
            // User not found — still show the generic message
            $message = $generic_message;
            $type    = 'success';
        }
    } else {
        $message = 'Please enter a valid email address.';
        $type    = 'error';
    }
}

// Retrieve demo token if just generated
$demo_token = $_SESSION['demo_reset_token'] ?? null;
$demo_email = $_SESSION['demo_reset_email'] ?? null;
unset($_SESSION['demo_reset_token'], $_SESSION['demo_reset_email']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Request a password reset for your SecureAuth account.">
    <title>Forgot Password – SecureAuth</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="page-center">
        <div class="auth-card">

            <div class="logo">
                <div class="logo-icon">🔄</div>
                <h1>Reset Password</h1>
                <p class="subtitle">Enter your email to receive a reset token</p>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?= e($type) ?>" role="alert">
                    <?= $type === 'success' ? '✅' : '⚠️' ?> <?= e($message) ?>
                </div>
            <?php endif; ?>

            <!-- Demo: show generated token (replaces email delivery) -->
            <?php if ($demo_token): ?>
                <div class="alert alert-warning" role="alert" style="flex-direction:column;gap:0.4rem;">
                    <strong>📋 Demo Mode – Your Reset Token:</strong>
                    <code id="reset_token_display" style="
                        background:rgba(255,255,255,0.08);
                        padding:0.5rem 0.75rem;
                        border-radius:6px;
                        font-size:0.78rem;
                        word-break:break-all;
                        display:block;
                        margin-top:0.25rem;
                        cursor:pointer;
                    "
                    title="Click to copy"
                    onclick="navigator.clipboard.writeText(this.textContent).then(()=>this.style.color='#4ade80')"
                    ><?= e($demo_token) ?></code>
                    <small style="color:var(--text-secondary);">
                        ⏱️ Valid for <?= RESET_TOKEN_EXPIRY_HOURS ?> hour. Click token to copy, then proceed below.
                    </small>
                    <a href="reset_password.php?email=<?= urlencode($demo_email ?? '') ?>"
                       class="btn btn-primary" id="go_to_reset" style="width:100%;margin-top:0.5rem;">
                        Enter Reset Token →
                    </a>
                </div>
            <?php endif; ?>

            <?php if (!$demo_token): ?>
            <form method="POST" action="forgot_password.php" novalidate>
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

                <div class="form-group">
                    <label for="email">Registered Email Address</label>
                    <div class="input-wrapper">
                        <span class="input-icon">📧</span>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            placeholder="you@example.com"
                            autocomplete="email"
                            required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" id="reset_request_submit">
                    📤 Send Reset Token
                </button>
            </form>
            <?php endif; ?>

            <div class="auth-footer">
                <a href="login.php">← Back to Log In</a>
            </div>
        </div>
    </div>

    <script src="assets/script.js"></script>
</body>
</html>
