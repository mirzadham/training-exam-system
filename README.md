# Training Exam System

A robust, lightweight, and secure PHP application for managing and delivering training assessment examinations. Built without heavy frameworks for maintainability and speed.

## Features
- **Participants**: Quick registration tied to active question banks with IC uniqueness validation.
- **Exam Engine**: Auto-grading, timed attempts, randomized questions, and AJAX auto-saving.
- **Admin Dashboard**: Live statistics, CSV exports for participants and results.
- **Question Banks**: Manage grouped questions, duration limits, and active statuses.
- **Security**: Built-in CSRF protection, session hardening, SQL injection prevention via PDO prepared statements, and XSS prevention (via `e()` htmlspecialchars helper).

---

## Local Development Setup (Laragon/XAMPP)

1. **Clone the Repository:**
   Place the project folder inside your local web service root (e.g., `C:\laragon\www\training-exam-system`).

2. **Database Setup:**
   - Open your MySQL client (HeidiSQL, phpMyAdmin, or CLI).
   - Create a new database: `CREATE DATABASE training_exam_system;`
   - Import the schema from `database/schema.sql`.
   - Ensure the database user has sufficient privileges.

3. **Configuration:**
   - Open `config/database.php`.
   - Update the connection string, username, and password if they differ from the defaults (default is `root` / no password).

4. **Seed the Admin Account:**
   - Run the seeding script via CLI: `php database/seed_admin.php`
   - Alternatively, you can browse to `http://localhost/training-exam-system/database/seed_admin.php` once (then delete the file for security).
   - The default admin credentials will be:
     - **Username:** `admin`
     - **Password:** `admin123`

5. **Start Testing:**
   - Visit `http://localhost/training-exam-system` to see the registration page.
   - Visit `http://localhost/training-exam-system/admin` to log into the admin backend.

---

## cPanel Deployment Preparation

When moving this application to a live cPanel production environment, follow these steps to ensure security and functionality:

### 1. File Upload
- Compress the project folder into a `.zip` file.
- Upload it via the **cPanel File Manager** to your `public_html` directory (or a subdomain folder).
- Extract the zip file.
- **Security Step:** Delete `database/schema.sql` and `database/seed_admin.php` from the live server!

### 2. Live Database Configuration
- In cPanel, go to **MySQL® Databases**.
- Create a new database (e.g., `yourprefix_examdb`).
- Create a new MySQL User and generate a strong password.
- Add the User to the Database with **All Privileges**.
- Open `config/database.php` in the File Manager Editor and update the `DB_NAME`, `DB_USER`, and `DB_PASS` constants to match your live cPanel credentials.

### 3. Database Import
- Go to **phpMyAdmin** in cPanel.
- Select your new database.
- Click **Import** and upload your local `database/schema.sql` file.
- *Optional:* If you want to migrate existing local data, export your local database first and import that instead of the blank schema.

### 4. PHP Version & Extensions
- Go to **Select PHP Version** in cPanel.
- Ensure the server is running **PHP 8.0** or higher.
- Ensure the following extensions are enabled:
    - `pdo_mysql`
    - `mbstring`
    - `json`

### 5. Final Hardening
- **HTTPS:** Ensure a free AutoSSL certificate is installed and active for your domain so all traffic (especially passwords and CSRF tokens) is encrypted.
- **Error Display:** Open `init.php` (if it exists) or check your cPanel MultiPHP INI settings to ensure `display_errors` is turned OFF for production environments. This prevents sensitive paths from leaking on fatal errors.

---
*Built for the Training Assessment System Project.*
