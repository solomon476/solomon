/**
 * script.js – Client-side form validation & UI interactions
 * Secure Login System
 */

'use strict';

// ── Utility: Show / hide a field error ──────────────────────────────────────
function showError(input, message) {
    const errorEl = document.getElementById(input.id + '_error');
    input.classList.add('is-invalid');
    input.classList.remove('is-valid');
    if (errorEl) {
        errorEl.textContent = message;
        errorEl.classList.add('visible');
    }
    return false;
}

function clearError(input) {
    const errorEl = document.getElementById(input.id + '_error');
    input.classList.remove('is-invalid');
    input.classList.add('is-valid');
    if (errorEl) {
        errorEl.textContent = '';
        errorEl.classList.remove('visible');
    }
    return true;
}

// ── Utility: Toggle password visibility ────────────────────────────────────
document.querySelectorAll('.toggle-password').forEach(btn => {
    btn.addEventListener('click', () => {
        const targetId   = btn.dataset.target;
        const input      = document.getElementById(targetId);
        const isPassword = input.type === 'password';
        input.type       = isPassword ? 'text' : 'password';
        btn.textContent  = isPassword ? '🙈' : '👁️';
    });
});

// ── Password Strength Meter ────────────────────────────────────────────────
function checkPasswordStrength(password) {
    const meter = document.getElementById('strength_fill');
    const label = document.getElementById('strength_label');
    if (!meter) return;

    let score = 0;
    if (password.length >= 8)                       score++;
    if (/[a-z]/.test(password))                     score++;
    if (/[A-Z]/.test(password))                     score++;
    if (/[0-9]/.test(password))                     score++;
    if (/[^a-zA-Z0-9]/.test(password))              score++;

    meter.className = 'strength-meter-fill';
    if (password.length === 0) {
        meter.style.width = '0';
        label.textContent  = '';
    } else if (score <= 2) {
        meter.classList.add('weak');
        label.textContent  = '🔴 Weak';
    } else if (score <= 4) {
        meter.classList.add('medium');
        label.textContent  = '🟡 Medium';
    } else {
        meter.classList.add('strong');
        label.textContent  = '🟢 Strong';
    }
    return score;
}

// ── Registration Form Validation ───────────────────────────────────────────
const regForm = document.getElementById('register_form');
if (regForm) {
    const usernameInput  = document.getElementById('username');
    const emailInput     = document.getElementById('email');
    const passwordInput  = document.getElementById('password');
    const confirmInput   = document.getElementById('confirm_password');

    // Real-time username validation
    usernameInput?.addEventListener('input', () => {
        const val = usernameInput.value.trim();
        if (val.length < 3) return showError(usernameInput, 'Username must be at least 3 characters.');
        if (!/^[a-zA-Z0-9_]+$/.test(val)) return showError(usernameInput, 'Only letters, numbers, and underscores allowed.');
        clearError(usernameInput);
    });

    // Real-time email validation
    emailInput?.addEventListener('input', () => {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!re.test(emailInput.value.trim())) return showError(emailInput, 'Enter a valid email address.');
        clearError(emailInput);
    });

    // Real-time password strength
    passwordInput?.addEventListener('input', () => {
        checkPasswordStrength(passwordInput.value);
        if (passwordInput.value.length < 8) return showError(passwordInput, 'Password must be at least 8 characters.');
        clearError(passwordInput);
    });

    // Real-time confirm password match
    confirmInput?.addEventListener('input', () => {
        if (confirmInput.value !== passwordInput.value) return showError(confirmInput, 'Passwords do not match.');
        clearError(confirmInput);
    });

    // Prevent submit if client-side invalid
    regForm.addEventListener('submit', e => {
        let valid = true;
        const username = usernameInput.value.trim();
        const email    = emailInput.value.trim();
        const password = passwordInput.value;
        const confirm  = confirmInput.value;

        if (username.length < 3) {
            showError(usernameInput, 'Username must be at least 3 characters.'); valid = false;
        }
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            showError(emailInput, 'Enter a valid email address.'); valid = false;
        }
        if (password.length < 8) {
            showError(passwordInput, 'Password must be at least 8 characters.'); valid = false;
        }
        if (password !== confirm) {
            showError(confirmInput, 'Passwords do not match.'); valid = false;
        }
        if (!valid) e.preventDefault();
    });
}

// ── Login Form Validation ──────────────────────────────────────────────────
const loginForm = document.getElementById('login_form');
if (loginForm) {
    loginForm.addEventListener('submit', e => {
        let valid = true;
        const identifierInput = document.getElementById('identifier');
        const passwordInput   = document.getElementById('password');

        if (!identifierInput.value.trim()) {
            showError(identifierInput, 'Please enter your username or email.'); valid = false;
        }
        if (!passwordInput.value) {
            showError(passwordInput, 'Please enter your password.'); valid = false;
        }
        if (!valid) e.preventDefault();
    });
}

// ── Reset Password Form Validation ────────────────────────────────────────
const resetForm = document.getElementById('reset_form');
if (resetForm) {
    const passwordInput = document.getElementById('new_password');
    const confirmInput  = document.getElementById('confirm_password');

    passwordInput?.addEventListener('input', () => {
        checkPasswordStrength(passwordInput.value);
        if (passwordInput.value.length < 8) return showError(passwordInput, 'Password must be at least 8 characters.');
        clearError(passwordInput);
    });

    confirmInput?.addEventListener('input', () => {
        if (confirmInput.value !== passwordInput.value) return showError(confirmInput, 'Passwords do not match.');
        clearError(confirmInput);
    });

    resetForm.addEventListener('submit', e => {
        let valid = true;
        if (passwordInput.value.length < 8)  { showError(passwordInput, 'Password must be at least 8 characters.'); valid = false; }
        if (confirmInput.value !== passwordInput.value) { showError(confirmInput, 'Passwords do not match.'); valid = false; }
        if (!valid) e.preventDefault();
    });
}

// ── Auto-hide flash alerts after 6 seconds ────────────────────────────────
document.querySelectorAll('.alert').forEach(alert => {
    setTimeout(() => {
        alert.style.transition = 'opacity 0.5s ease';
        alert.style.opacity    = '0';
        setTimeout(() => alert.remove(), 500);
    }, 6000);
});
