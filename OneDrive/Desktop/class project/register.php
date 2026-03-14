<?php
/**
 * register.php – User Registration
 * Secure Login System
 *
 * Security measures implemented:
 *  - CSRF token validation
 *  - Server-side input validation & sanitisation
 *  - Password hashing via password_hash() (bcrypt, cost 12)
 *  - Duplicate username/email checked via unique DB constraint
 *  - Parameterised PDO queries (no SQL injection)
 */
session_start();
require_once 'config.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: profile.php');
    exit;
}

$errors  = [];
$success = '';
$old     = []; // Repopulate form fields on error

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. CSRF check
    verify_csrf();

    // 2. Collect & sanitise inputs
    $username        = trim($_POST['username']        ?? '');
    $email           = trim($_POST['email']           ?? '');
    $password        = $_POST['password']              ?? '';
    $confirm_password = $_POST['confirm_password']    ?? '';

    $old = compact('username', 'email');

    // 3. Server-side validation
    if (strlen($username) < 3 || strlen($username) > 50) {
        $errors[] = 'Username must be between 3 and 50 characters.';
    }
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = 'Username may only contain letters, numbers, and underscores.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please provide a valid email address.';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long.';
    }
    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match.';
    }

    // 4. Check uniqueness in DB (only if basic validation passes)
    if (empty($errors)) {
        $db   = get_db();
        $stmt = $db->prepare('SELECT id FROM users WHERE username = :u OR email = :e LIMIT 1');
        $stmt->execute([':u' => $username, ':e' => $email]);
        if ($stmt->fetch()) {
            $errors[] = 'That username or email address is already registered. Please try another.';
        }
    }

    // 5. Insert new user
    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $db   = get_db();
        $ins  = $db->prepare(
            'INSERT INTO users (username, email, password_hash) VALUES (:u, :e, :h)'
        );
        $ins->execute([':u' => $username, ':e' => $email, ':h' => $hash]);

        // Flash success message to the login page
        $_SESSION['flash_success'] = 'Account created successfully! Please log in.';
        header('Location: login.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Create a new account on SecureAuth.">
    <title>Register – SecureAuth</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="page-center">
        <div class="auth-card">

            <!-- Logo -->
            <div class="logo">
                <div class="logo-icon">🔐</div>
                <h1>Create Account</h1>
                <p class="subtitle">Join SecureAuth — it's free</p>
            </div>

            <!-- Server-side error messages -->
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

            <!-- Registration Form -->
            <form id="register_form" method="POST" action="register.php" novalidate>
                <!-- CSRF Token -->
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

                <!-- Username -->
                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-wrapper">
                        <span class="input-icon">👤</span>
                        <input
                            type="text"
                            id="username"
                            name="username"
                            placeholder="e.g. john_doe"
                            value="<?= e($old['username'] ?? '') ?>"
                            autocomplete="username"
                            maxlength="50"
                            required>
                    </div>
                    <span class="field-error" id="username_error"></span>
                </div>

                <!-- Email -->
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-wrapper">
                        <span class="input-icon">📧</span>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            placeholder="you@example.com"
                            value="<?= e($old['email'] ?? '') ?>"
                            autocomplete="email"
                            required>
                    </div>
                    <span class="field-error" id="email_error"></span>
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
                            placeholder="At least 8 characters"
                            autocomplete="new-password"
                            required>
                        <button type="button" class="toggle-password" data-target="password" aria-label="Toggle password visibility">👁️</button>
                    </div>
                    <span class="field-error" id="password_error"></span>
                    <!-- Strength Meter -->
                    <div class="strength-meter">
                        <div class="strength-meter-fill" id="strength_fill"></div>
                    </div>
                    <span class="strength-label" id="strength_label"></span>
                </div>

                <!-- Confirm Password -->
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <div class="input-wrapper">
                        <span class="input-icon">🔒</span>
                        <input
                            type="password"
                            id="confirm_password"
                            name="confirm_password"
                            placeholder="Repeat your password"
                            autocomplete="new-password"
                            required>
                        <button type="button" class="toggle-password" data-target="confirm_password" aria-label="Toggle confirm password visibility">👁️</button>
                    </div>
                    <span class="field-error" id="confirm_password_error"></span>
                </div>

                <button type="submit" class="btn btn-primary" id="register_submit">
                    🚀 Create Account
                </button>
            </form>

            <div class="auth-footer">
                Already have an account? <a href="login.php">Log In</a>
            </div>
        </div>
    </div>

    <script src="assets/script.js"></script>
</body>
</html>
