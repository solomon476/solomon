<?php
/**
 * profile.php – Protected User Dashboard
 * Secure Login System
 *
 * Security measures:
 *  - Session authentication gate (redirects unauthenticated users)
 *  - All output escaped via e() to prevent XSS
 *  - User data re-fetched from DB (not trusted from session alone)
 */
session_start();
require_once 'config.php';

// ── Auth Gate ──────────────────────────────────────────────────────────────
if (!isset($_SESSION['user_id'])) {
    $_SESSION['flash_info'] = 'Please log in to view your profile.';
    header('Location: login.php');
    exit;
}

// ── Re-fetch fresh user data from DB ────────────────────────────────────
$db   = get_db();
$stmt = $db->prepare('SELECT id, username, email, created_at FROM users WHERE id = :id LIMIT 1');
$stmt->execute([':id' => $_SESSION['user_id']]);
$user = $stmt->fetch();

// Safety net: if somehow the user no longer exists in DB, destroy session
if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Format registration date nicely
$created = new DateTime($user['created_at']);
$created_formatted = $created->format('F j, Y \a\t g:i A');
$member_days = (new DateTime())->diff($created)->days;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Your SecureAuth profile dashboard.">
    <title>Dashboard – SecureAuth</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

    <!-- ── Navigation Bar ────────────────────────────────────────────── -->
    <nav class="dashboard-nav">
        <span class="brand">🔐 SecureAuth</span>
        <div class="nav-right">
            <div class="user-badge">
                Logged in as <strong><?= e($user['username']) ?></strong>
            </div>
            <a href="logout.php" class="btn btn-secondary" id="logout_btn"
               onclick="return confirm('Are you sure you want to log out?');">
                🚪 Logout
            </a>
        </div>
    </nav>

    <!-- ── Main Content ──────────────────────────────────────────────── -->
    <main class="dashboard-main">

        <!-- Hero greeting -->
        <div class="dashboard-hero">
            <h1>Hello, <span><?= e($user['username']) ?></span>! 👋</h1>
            <p>Welcome to your secure dashboard. Your account is protected and active.</p>
        </div>

        <!-- Info Cards Grid -->
        <div class="dashboard-grid">

            <!-- Username Card -->
            <div class="info-card">
                <div class="card-icon">👤</div>
                <h3>Username</h3>
                <div class="card-value gradient"><?= e($user['username']) ?></div>
            </div>

            <!-- Email Card -->
            <div class="info-card">
                <div class="card-icon">📧</div>
                <h3>Email Address</h3>
                <div class="card-value"><?= e($user['email']) ?></div>
            </div>

            <!-- Member Since Card -->
            <div class="info-card">
                <div class="card-icon">📅</div>
                <h3>Member Since</h3>
                <div class="card-value"><?= e($created_formatted) ?></div>
            </div>

            <!-- Membership Days Card -->
            <div class="info-card">
                <div class="card-icon">🏅</div>
                <h3>Days as Member</h3>
                <div class="card-value gradient"><?= e((string)$member_days) ?> day<?= $member_days !== 1 ? 's' : '' ?></div>
            </div>

            <!-- Account ID Card -->
            <div class="info-card">
                <div class="card-icon">🆔</div>
                <h3>Account ID</h3>
                <div class="card-value">#<?= e((string)$user['id']) ?></div>
            </div>

            <!-- Security Card -->
            <div class="info-card">
                <div class="card-icon">🔒</div>
                <h3>Password</h3>
                <div class="card-value" style="margin-bottom:0.75rem;">••••••••••••</div>
                <a href="forgot_password.php" class="btn btn-secondary" id="change_pw_btn"
                   style="width:100%;font-size:0.85rem;padding:0.55rem 1rem;">
                    🔄 Reset Password
                </a>
            </div>

        </div>
    </main>

    <script src="assets/script.js"></script>
</body>
</html>
