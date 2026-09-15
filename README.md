# ACCEZZ

Web-based campus security system for **OTP-authenticated vehicle entry**, shuttle bus monitoring, visitors, and entry/exit logging.

## Requirements

- PHP 8.0+ (with PDO MySQL)
- MySQL 5.7+ / MariaDB 10.3+
- Apache (XAMPP / WAMP / Laragon) or PHP built-in server

## Setup (XAMPP / WAMP / Laragon)

1. **Place the project** in your web root, e.g.:
   - `C:\xampp\htdocs\fb 2`
   - or keep it under your Documents path and point a virtual host / alias at this folder

2. **Create the database**
   - Open phpMyAdmin → Import → select `database/schema.sql`
   - Or from CLI:
     ```bash
     mysql -u root < database/schema.sql
     ```

3. **Configure environment**
   - Copy `.env.example` to `.env` (a starter `.env` is included)
   - Set `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` to match your MySQL
   - Adjust `APP_URL` if your folder name / port differs

4. **Open the app**
   - Example: `http://localhost/fb%202/login.php`
   - Or: `http://localhost/fb%202/`

5. **Optional password reset helper**
   - Visit `http://localhost/fb%202/install.php` once if seeded passwords do not verify (regenerates admin/security hashes with PHP `password_hash`)
   - Delete or protect `install.php` after use


## OTP flow

1. User registers a vehicle → admin approves it  
2. User requests OTP on their dashboard (single-use, configurable expiry)  
3. Security personnel enters the OTP on **Verify OTP** → entry is logged, OTP marked used  
4. Security records exit by plate (or visitor exit)

## Roles (RBAC)

| Role | Access |
|------|--------|
| Admin | Full management, approvals, reports, settings, audit |
| Security | OTP verify, exit logging, visitors, today's history |
| Student | Vehicles, OTP, own history, profile |
| Staff | Vehicles, OTP, history, profile |
| Driver | Vehicle/shuttle, OTP, shuttle status, OTP history, profile |

Unauthorized roles are blocked by `require_role()` on every protected page/controller.

## Folder structure

```
config/          App + DB config, .env loader
controllers/     Auth, Admin, Security, Vehicle, OTP, Shuttle actions
models/          User, Vehicle, Otp, EntryExitLog, Visitor, Shuttle, AuditLog
views/           Role dashboards and pages (admin, security, student, staff, driver)
includes/        Bootstrap, auth/RBAC, helpers, layout, nav
assets/          CSS / JS
database/        schema.sql (tables + seed admin)
```

## Security features

- Password hashing (`password_hash` / `password_verify`)
- Session auth + role checks
- CSRF tokens on POST forms
- Prepared statements (PDO)
- XSS escaping via `e()`
- OTP one-time use + expiry + auto-invalidation
- Audit logging for key actions

## Key URLs

- Login: `/login.php`
- Register: `/register.php`
- Admin dashboard: `/views/admin/dashboard.php`
- Security verify OTP: `/views/security/verify_otp.php`

## Notes

- PHPMailer is not bundled; OTP is displayed in-app (email can be added later).
- Vehicle approval can be toggled under **Admin → Settings**.
- Reports support CSV export of entry/exit logs.
