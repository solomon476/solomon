# Testing Report
## Secure Login System

**Project:** Secure Login System
**Date:** March 2026
**Environment:** XAMPP (Apache + PHP 8.x + MySQL 5.7+)
**Test URL:** `http://localhost/class project/`

---

## 1. Functional Testing

Functional tests verify that each feature behaves correctly under normal and edge-case inputs.

| # | Feature | Test Case | Expected Result | Status |
|---|---------|-----------|-----------------|--------|
| F-01 | **Registration** | Submit valid username, email, password | User created, redirected to login with success message | ✅ PASS |
| F-02 | **Registration** | Submit username < 3 characters | Inline error: "Username must be at least 3 characters" | ✅ PASS |
| F-03 | **Registration** | Submit invalid email format | Inline error: "Enter a valid email address" | ✅ PASS |
| F-04 | **Registration** | Submit passwords that don't match | Inline error: "Passwords do not match" | ✅ PASS |
| F-05 | **Registration** | Submit already-registered email | Error: "That username or email is already registered" | ✅ PASS |
| F-06 | **Login** | Submit correct credentials | Session created, redirected to `/profile.php` | ✅ PASS |
| F-07 | **Login** | Submit incorrect password | Error: "Invalid username or password. X attempt(s) remaining" | ✅ PASS |
| F-08 | **Login** | Submit login by email (not username) | Authentication succeeds | ✅ PASS |
| F-09 | **Login** | Attempt login 5× with wrong password | Account locked for 15 minutes, lockout message shown | ✅ PASS |
| F-10 | **Profile** | Access `/profile.php` while logged in | Dashboard displays username, email, dates | ✅ PASS |
| F-11 | **Profile** | Access `/profile.php` while NOT logged in | Redirect to `/login.php` with info flash message | ✅ PASS |
| F-12 | **Logout** | Click Logout button | Session destroyed, cookie deleted, redirect to login | ✅ PASS |
| F-13 | **Logout** | Access `/profile.php` after logout | Redirect to login (session no longer valid) | ✅ PASS |
| F-14 | **Forgot Password** | Submit registered email | Token generated and displayed on screen | ✅ PASS |
| F-15 | **Forgot Password** | Submit unregistered email | Same generic success message (no enumeration) | ✅ PASS |
| F-16 | **Reset Password** | Submit valid token + new password | Password updated, redirected to login with success | ✅ PASS |
| F-17 | **Reset Password** | Submit expired token | Error: "This reset token has expired" | ✅ PASS |
| F-18 | **Reset Password** | Submit wrong token | Error: "Invalid reset token" | ✅ PASS |
| F-19 | **Reset Password** | Reuse a token after success | Error: "No pending reset request found" (token deleted) | ✅ PASS |

---

## 2. Integration Testing

Integration tests verify that all components work together seamlessly.

| # | Integration Point | Test Description | Status |
|---|-------------------|-----------------|--------|
| I-01 | **Frontend → Backend** | JS validation allows form to submit; PHP re-validates | ✅ PASS |
| I-02 | **PHP → MySQL (config.php)** | PDO connection established; query executes correctly | ✅ PASS |
| I-03 | **Register → Login** | After registration, user can immediately log in | ✅ PASS |
| I-04 | **Login → Session → Profile** | Session set on login, read correctly by profile.php | ✅ PASS |
| I-05 | **Forgot → Reset → Login** | Full password reset flow works end-to-end | ✅ PASS |
| I-06 | **Logout → Profile Guard** | After logout, profile page correctly redirects to login | ✅ PASS |
| I-07 | **DB Constraint → Register** | Duplicate username/email caught by UNIQUE constraint | ✅ PASS |

---

## 3. Security Testing

Security tests confirm the system's resistance to common web vulnerabilities (OWASP Top 10).

| # | Vulnerability | Test Method | Expected Behaviour | Status |
|---|---------------|-------------|-------------------|--------|
| S-01 | **SQL Injection** | Submit `' OR '1'='1` in login field | PDO prepared statements reject malformed input; no data returned | ✅ PASS |
| S-02 | **XSS (Stored)** | Register with `<script>alert(1)</script>` as username | Output escaped via `e()` / `htmlspecialchars`; script not executed | ✅ PASS |
| S-03 | **XSS (Reflected)** | Submit `<img src=x onerror=alert(1)>` in forms | All POST values re-displayed through `e()`; not executed | ✅ PASS |
| S-04 | **CSRF** | Submit a login/register form with a forged/missing CSRF token | Server returns 403 and terminates execution | ✅ PASS |
| S-05 | **Brute Force** | Attempt login 5 consecutive times with wrong password | Account locked for 15 minutes; correct credentials rejected during lockout | ✅ PASS |
| S-06 | **Password Storage** | Inspect `password_hash` column in DB directly | `$2y$12$...` (bcrypt hash); no plain text | ✅ PASS |
| S-07 | **Session Fixation** | Capture session ID before login; check if same after login | `session_regenerate_id(true)` issues a new ID on successful login | ✅ PASS |
| S-08 | **Cookie Security** | Inspect session cookie in browser DevTools | `HttpOnly` flag set; JavaScript cannot read it | ✅ PASS |
| S-09 | **User Enumeration (Reset)** | Submit unregistered email on forgot_password.php | Same success message shown regardless of email existence | ✅ PASS |
| S-10 | **Token Reuse** | Attempt to reuse a reset token after password change | Token deleted from DB; subsequent use fails gracefully | ✅ PASS |
| S-11 | **Direct Profile Access** | Navigate to profile.php URL without a session | Immediately redirected to login | ✅ PASS |

---

## 4. Usability Testing

Usability tests assess the user experience, clarity of feedback, and ease of navigation.

| # | Aspect | Observation | Status |
|---|--------|-------------|--------|
| U-01 | **Error Messages** | Inline field errors appear in real-time as user types | ✅ Clear |
| U-02 | **Password Strength** | Colour-coded strength meter updates live below the password field | ✅ Clear |
| U-03 | **Password Visibility** | Toggle 👁️ button shows/hides password in all relevant fields | ✅ Clear |
| U-04 | **Flash Messages** | Success/error messages appear at top of form and fade after 6 s | ✅ Clear |
| U-05 | **Navigation Flow** | Login ↔ Register links present on both pages; Back links on reset pages | ✅ Clear |
| U-06 | **Protected Content** | Dashboard clearly displays logged-in user's name and data | ✅ Clear |
| U-07 | **Logout Confirmation** | Browser confirm dialog prevents accidental logout | ✅ Clear |
| U-08 | **Mobile Responsiveness** | All pages render cleanly on a 375 px mobile viewport | ✅ Clear |
| U-09 | **Form Re-population** | On server-side error, non-sensitive fields (username, email) are re-filled | ✅ Clear |
| U-10 | **Loading & Transitions** | Card slide-up animation and hover effects provide visual continuity | ✅ Clear |

---

## 5. Summary

| Test Category | Total | Passed | Failed |
|---------------|-------|--------|--------|
| Functional    | 19    | 19     | 0      |
| Integration   | 7     | 7      | 0      |
| Security      | 11    | 11     | 0      |
| Usability     | 10    | 10     | 0      |
| **Total**     | **47**| **47** | **0**  |

> **All 47 test cases passed.** The system is functioning correctly and meets all security and usability requirements.
