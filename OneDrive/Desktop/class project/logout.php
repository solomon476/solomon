<?php
/**
 * logout.php – Session Destruction
 * Secure Login System
 *
 * Security measures:
 *  - Clears all session data
 *  - Deletes the session cookie
 *  - Destroys the server-side session
 *  - Redirects to the login page with a flash message
 */
session_start();
require_once 'config.php';

// 1. Unset all session variables
$_SESSION = [];

// 2. Delete the session cookie from the browser
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,         // Expire in the past to force deletion
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// 3. Destroy the session on the server
session_destroy();

// 4. Start a NEW session just to carry the flash message to the login page
session_start();
$_SESSION['flash_success'] = 'You have been logged out successfully.';

header('Location: login.php');
exit;
