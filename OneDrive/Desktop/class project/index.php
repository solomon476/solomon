<?php
/**
 * index.php – Landing Page
 * Secure Login System
 */
session_start();
require_once 'config.php';

// If user is already logged in, redirect to profile
if (isset($_SESSION['user_id'])) {
    header('Location: profile.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="A secure login system demonstrating PHP, MySQL, session management, and modern security practices.">
    <title>SecureAuth – Secure Login System</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="dashboard-nav">
        <span class="brand">🔐 SecureAuth</span>
        <div class="nav-right">
            <a href="login.php"    class="btn btn-secondary" id="nav_login">Log In</a>
            <a href="register.php" class="btn btn-primary"   id="nav_register">Register</a>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="landing-hero">
        <h1>Secure Authentication,<br>Done Right.</h1>
        <p>A full-featured login system built with PHP &amp; MySQL. Featuring bcrypt hashing, session management, brute-force protection, and CSRF defence.</p>
        <div class="landing-buttons">
            <a href="register.php" class="btn btn-primary"   id="hero_register">🚀 Get Started</a>
            <a href="login.php"    class="btn btn-secondary" id="hero_login">Log In</a>
        </div>
    </section>

    <!-- Feature Cards -->
    <section class="features-grid">
        <div class="feature-card">
            <div class="feat-icon">🔑</div>
            <h3>Secure Authentication</h3>
            <p>Credentials verified against bcrypt-hashed passwords with generic error messages.</p>
        </div>
        <div class="feature-card">
            <div class="feat-icon">🛡️</div>
            <h3>Brute-Force Protection</h3>
            <p>Accounts are locked for 15 minutes after 5 consecutive failed login attempts.</p>
        </div>
        <div class="feature-card">
            <div class="feat-icon">🍪</div>
            <h3>Secure Sessions</h3>
            <p>Sessions use HTTPOnly cookies, strict-mode, and are regenerated on privilege change.</p>
        </div>
        <div class="feature-card">
            <div class="feat-icon">🔄</div>
            <h3>Password Reset</h3>
            <p>Tokenised reset flow with server-side expiry and single-use enforcement.</p>
        </div>
        <div class="feature-card">
            <div class="feat-icon">✅</div>
            <h3>Input Validation</h3>
            <p>Dual-layer validation: real-time JS checks and strict server-side sanitisation via PDO.</p>
        </div>
        <div class="feature-card">
            <div class="feat-icon">🚫</div>
            <h3>CSRF Protection</h3>
            <p>All state-changing forms include a server-generated CSRF token.</p>
        </div>
    </section>

    <script src="assets/script.js"></script>
</body>
</html>
