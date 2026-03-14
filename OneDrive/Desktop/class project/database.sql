-- =========================================================
-- Secure Login System - Database Setup Script
-- Target: MySQL 5.7+ / MariaDB 10.3+
-- Usage: Run this file in phpMyAdmin or via MySQL CLI:
--   mysql -u root -p < database.sql
-- =========================================================

-- Create and select the database
CREATE DATABASE IF NOT EXISTS secure_login_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE secure_login_db;

-- =========================================================
-- Table: users
-- Stores registered user accounts.
-- =========================================================
CREATE TABLE IF NOT EXISTS users (
    id                   INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    username             VARCHAR(50)     NOT NULL,
    email                VARCHAR(255)    NOT NULL,
    password_hash        VARCHAR(255)    NOT NULL,          -- bcrypt hash via password_hash()
    created_at           DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    failed_login_attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,  -- brute-force counter
    account_locked_until DATETIME        NULL DEFAULT NULL,  -- NULL = not locked
    PRIMARY KEY (id),
    UNIQUE KEY uq_username (username),
    UNIQUE KEY uq_email    (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- Table: password_resets
-- Temporarily holds single-use password reset tokens.
-- =========================================================
CREATE TABLE IF NOT EXISTS password_resets (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id    INT UNSIGNED NOT NULL,
    reset_token VARCHAR(64) NOT NULL,   -- SHA-256 hex token (64 chars)
    expires_at  DATETIME    NOT NULL,   -- Short-lived: 1 hour from creation
    PRIMARY KEY (id),
    UNIQUE KEY uq_token (reset_token),
    KEY fk_reset_user (user_id),
    CONSTRAINT fk_reset_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- (Optional) Seed: Insert a test user  
-- Password: Test@1234  (pre-hashed with bcrypt cost=12)
-- Remove or comment this block before production use.
-- =========================================================
-- INSERT INTO users (username, email, password_hash) VALUES
-- ('testuser', 'test@example.com', '$2y$12$examplehashgoeshereXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX');
