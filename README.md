# ACCEZZ

A web-based campus security system for OTP-authenticated vehicle entry, shuttle bus tracking, visitor logging, and entry/exit monitoring.

## Requirements

- PHP 8.0 or higher (with PDO extension)
- MySQL 5.7+ or MariaDB 10.3+
- Web server (XAMPP, WAMP, or Laragon)

## Setup Instructions

1. **Move files:** Copy the `ACCEZZ` project folder into your web root (for example, `C:\xampp\htdocs\ACCEZZ`).

2. **Import database:**
   - Create a new MySQL database in phpMyAdmin.
   - Import the file located at `database/schema.sql`.

3. **Configure environment:**
   - Copy `.env.example` and rename it to `.env`.
   - Update `DB_NAME`, `DB_USER`, and `DB_PASS` to match your local database settings.
   - Update `APP_URL` to match your folder path (e.g., `http://localhost/ACCEZZ`).

4. **Launch application:**
   - Open `http://localhost/ACCEZZ/login.php` in your browser.
   - If default passwords do not verify on initial setup, visit `http://localhost/ACCEZZ/install.php` once to refresh password hashes, then delete `install.php`.

## How It Works

1. **Vehicle Registration:** Students and staff register their vehicles via their dashboard.
2. **OTP Request:** Users request a single-use entry OTP before entering campus.
3. **Verification:** Security personnel enter the OTP at the gate to verify the user and log entry.
4. **Exit Logging:** Security logs the vehicle departure upon exit.

## User Roles

- **Admin:** Full system control, vehicle approvals, audit logs, and reports.
- **Security:** Verifies entry OTPs, records exits, and logs visitor details.
- **Student & Staff:** Registers vehicles, requests OTPs, and views entry history.
- **Driver:** Manages shuttle status and generates shuttle OTPs.

## Security Features

- Password hashing via `password_hash()` and `password_verify()`
- PDO prepared statements to prevent SQL injection
- CSRF token verification on forms
- Input sanitization against XSS
- Single-use, expiring entry codes
-
