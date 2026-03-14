# User Manual
## Secure Login System

**Platform:** XAMPP (Windows) — Apache + PHP 8.x + MySQL 5.7+
**Project Folder:** `C:\xampp\htdocs\class project\`
**Access URL:** `http://localhost/class%20project/`

---

## Part 1 – Configuration & Setup

### 1.1 Prerequisites
Ensure the following tools are installed on your machine:

| Tool | Download |
|------|----------|
| XAMPP (Apache + MySQL + PHP) | [https://www.apachefriends.org](https://www.apachefriends.org) |
| Web Browser (Chrome, Firefox, Edge) | — |

---

### 1.2 Copying Project Files

1. Open Windows Explorer and navigate to:
   ```
   C:\xampp\htdocs\
   ```
2. Copy the entire **`class project`** folder into `htdocs`.  
   Your project tree should look like this:
   ```
   C:\xampp\htdocs\class project\
   ├── assets\
   │   ├── style.css
   │   └── script.js
   ├── config.php
   ├── database.sql
   ├── index.php
   ├── register.php
   ├── login.php
   ├── logout.php
   ├── profile.php
   ├── forgot_password.php
   └── reset_password.php
   ```

---

### 1.3 Starting XAMPP Services

1. Open **XAMPP Control Panel** (run as Administrator).
2. Click **Start** next to **Apache**.
3. Click **Start** next to **MySQL**.
4. Both rows should turn **green** (Running).

---

### 1.4 Creating the Database

1. Open your browser and go to:
   ```
   http://localhost/phpmyadmin
   ```
2. Click **Import** from the top navigation bar.
3. Click **Choose File** and select:
   ```
   C:\xampp\htdocs\class project\database.sql
   ```
4. Scroll down and click **Go**.
5. You should see: **"Import has been successfully finished."**
6. In the left panel, you will now see the `secure_login_db` database with two tables:
   - `users`
   - `password_resets`

---

### 1.5 Configuring Database Credentials

If your MySQL has a root password set (non-default), open `config.php` and update:

```php
define('DB_USER', 'root');   // Your MySQL username
define('DB_PASS', '');       // Your MySQL password
```

Save the file.

---

## Part 2 – Using the System

### 2.1 Accessing the Application

Open your browser and visit:
```
http://localhost/class%20project/
```
This takes you to the **Landing Page**, which shows the system's features and links to Register / Log In.

---

### 2.2 Registering a New User

1. From the landing page, click **Register** (top right or big button).
2. Fill in the form:
   - **Username** — 3–50 characters, letters/numbers/underscores only.
   - **Email Address** — must be a valid format.
   - **Password** — minimum 8 characters (use the strength meter as a guide).
   - **Confirm Password** — must match the password exactly.
3. Click **🚀 Create Account**.
4. On success, you are redirected to the Login page with a green success banner:  
   *"Account created successfully! Please log in."*

**Common errors:**
| Error shown | Cause |
|------------|-------|
| Username must be at least 3 characters | Username too short |
| Passwords do not match | Confirm password field differs |
| That email is already registered | Account already exists — try logging in |

---

### 2.3 Logging In

1. Navigate to `http://localhost/class%20project/login.php` (or click **Log In** from the landing page).
2. Enter your **Username or Email** and **Password**.
3. Click **🔑 Log In**.
4. On success, you are redirected to your **Dashboard / Profile page**.

**Brute-force protection:**  
After **5 consecutive failed attempts**, your account is locked for **15 minutes**. An error message will show the earliest unlock time.

---

### 2.4 Viewing Your Profile

After logging in, the **Dashboard** page (`profile.php`) is shown automatically.  
It displays:

| Card | Information |
|------|-------------|
| 👤 Username | Your chosen username (gradient style) |
| 📧 Email Address | Your registered email |
| 📅 Member Since | Account creation date and time |
| 🏅 Days as Member | Number of days since account creation |
| 🆔 Account ID | Your unique numeric ID |
| 🔒 Password | Masked display + Reset Password link |

> **Note:** This page is **protected**. If you try to access it without being logged in, you are redirected to the Login page.

---

### 2.5 Resetting Your Password

**Step 1 — Request a Reset Token**

1. From the Login page, click **Forgot password?** (below the password field).
2. Enter your **Registered Email Address**.
3. Click **📤 Send Reset Token**.
4. A yellow box appears displaying your **64-character reset token** (in a real-world system this would be emailed to you).
5. **Click the token** to copy it to your clipboard.
6. Click **Enter Reset Token →**.

**Step 2 — Set a New Password**

1. You are taken to `reset_password.php`.
2. Your email is pre-filled (if you followed the link).
3. **Paste** your token into the **Reset Token** field.
4. Enter and confirm your **new password** (minimum 8 characters).
5. Click **🔏 Set New Password**.
6. On success, you are redirected to the Login page with a success message.

> Tokens expire after **1 hour** and are single-use — they are deleted immediately after a successful reset.

---

### 2.6 Logging Out

1. From the Dashboard, click the **🚪 Logout** button (top right).
2. A confirmation dialog appears: *"Are you sure you want to log out?"*
3. Click **OK**.
4. Your session is completely destroyed and you are redirected to the Login page.

---

## Part 3 – Configuration Reference

| Setting | Location | Default | Description |
|---------|----------|---------|-------------|
| `DB_HOST` | `config.php` | `localhost` | MySQL server host |
| `DB_NAME` | `config.php` | `secure_login_db` | Database name |
| `DB_USER` | `config.php` | `root` | MySQL username |
| `DB_PASS` | `config.php` | `""` | MySQL password |
| `MAX_LOGIN_ATTEMPTS` | `config.php` | `5` | Failed logins before lockout |
| `LOCKOUT_DURATION_MINUTES` | `config.php` | `15` | Account lockout period |
| `RESET_TOKEN_EXPIRY_HOURS` | `config.php` | `1` | Reset token lifetime |

---

## Part 4 – Troubleshooting

| Problem | Solution |
|---------|----------|
| **"A database error occurred"** on any page | Ensure MySQL is running in XAMPP. Verify `DB_USER` and `DB_PASS` in `config.php`. |
| **"Import has been finished"** but tables not visible | Re-run the import and ensure you selected the correct `database.sql` file. |
| **Page shows 403 / 404** | Ensure Apache is running and the folder is inside `htdocs`. Check the URL path. |
| **Reset token not accepted** | Tokens expire after 1 hour. Request a new one from `forgot_password.php`. |
| **Account locked but shouldn't be** | Wait 15 minutes or manually run `UPDATE users SET failed_login_attempts=0, account_locked_until=NULL WHERE email='your@email.com';` in phpMyAdmin. |
| **CSS / JS not loading** | Confirm the `assets/` folder is in the same directory as `index.php` and files are not renamed. |
| **Blank white page** | Enable PHP error display temporarily: add `ini_set('display_errors', 1);` at the top of `config.php` to view error details. |

---

## Part 5 – Security Notes for Developers

- **Never** store passwords in plain text. All passwords are hashed using `password_hash($pw, PASSWORD_BCRYPT, ['cost' => 12])`.
- **All database queries** use PDO prepared statements — SQL injection is prevented.
- **All HTML output** is escaped via `htmlspecialchars()` — XSS is prevented.
- **CSRF tokens** are required on every state-changing form.
- **Session IDs** are regenerated upon login — session fixation is prevented.
- **`HTTPOnly`** flag is set on the session cookie — JavaScript cannot access it.
- Before deploying to **production**: set `DB_PASS` to a strong password, enable `session.cookie_secure=1` (HTTPS), and implement actual **email delivery** for password reset tokens (replace the on-screen demo display).
