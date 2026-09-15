-- ACCEZZ — Vehicle Entry & Shuttle Bus Monitoring System
-- MySQL 5.7+ / MariaDB 10.3+
-- Import via phpMyAdmin or: mysql -u root < database/schema.sql

CREATE DATABASE IF NOT EXISTS vehicle_entry_system
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE vehicle_entry_system;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS audit_logs;
DROP TABLE IF EXISTS entry_exit_logs;
DROP TABLE IF EXISTS otps;
DROP TABLE IF EXISTS visitors;
DROP TABLE IF EXISTS shuttles;
DROP TABLE IF EXISTS vehicles;
DROP TABLE IF EXISTS settings;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------------
-- Users
-- ---------------------------------------------------------------------------
CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(120) NOT NULL,
  email VARCHAR(160) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  phone VARCHAR(30) DEFAULT NULL,
  role ENUM('admin','security','student','staff','driver') NOT NULL,
  student_id VARCHAR(50) DEFAULT NULL,
  staff_id VARCHAR(50) DEFAULT NULL,
  department VARCHAR(120) DEFAULT NULL,
  status ENUM('pending','active','inactive','rejected') NOT NULL DEFAULT 'pending',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  last_login_at DATETIME DEFAULT NULL,
  INDEX idx_users_role (role),
  INDEX idx_users_status (status)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------------
-- Vehicles
-- ---------------------------------------------------------------------------
CREATE TABLE vehicles (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  plate_number VARCHAR(30) NOT NULL,
  make VARCHAR(80) DEFAULT NULL,
  model VARCHAR(80) DEFAULT NULL,
  color VARCHAR(40) DEFAULT NULL,
  vehicle_type ENUM('car','motorcycle','van','bus','other') NOT NULL DEFAULT 'car',
  is_shuttle TINYINT(1) NOT NULL DEFAULT 0,
  status ENUM('pending','approved','rejected','inactive') NOT NULL DEFAULT 'pending',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_plate (plate_number),
  INDEX idx_vehicles_user (user_id),
  CONSTRAINT fk_vehicles_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------------
-- Shuttles (extends vehicle for drivers)
-- ---------------------------------------------------------------------------
CREATE TABLE shuttles (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  vehicle_id INT UNSIGNED NOT NULL UNIQUE,
  driver_id INT UNSIGNED NOT NULL,
  route_name VARCHAR(120) DEFAULT NULL,
  capacity INT UNSIGNED DEFAULT NULL,
  shuttle_status ENUM('available','in_transit','offline','maintenance') NOT NULL DEFAULT 'offline',
  last_status_at DATETIME DEFAULT NULL,
  notes TEXT DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_shuttles_vehicle FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE,
  CONSTRAINT fk_shuttles_driver FOREIGN KEY (driver_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------------
-- OTPs
-- ---------------------------------------------------------------------------
CREATE TABLE otps (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  vehicle_id INT UNSIGNED NOT NULL,
  otp_code VARCHAR(10) NOT NULL,
  purpose ENUM('entry','exit') NOT NULL DEFAULT 'entry',
  expires_at DATETIME NOT NULL,
  used_at DATETIME DEFAULT NULL,
  status ENUM('active','used','expired','revoked') NOT NULL DEFAULT 'active',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_otps_code (otp_code),
  INDEX idx_otps_user (user_id),
  INDEX idx_otps_status (status),
  CONSTRAINT fk_otps_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_otps_vehicle FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------------
-- Entry / Exit logs
-- ---------------------------------------------------------------------------
CREATE TABLE entry_exit_logs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED DEFAULT NULL,
  vehicle_id INT UNSIGNED DEFAULT NULL,
  visitor_id INT UNSIGNED DEFAULT NULL,
  otp_id INT UNSIGNED DEFAULT NULL,
  plate_number VARCHAR(30) NOT NULL,
  log_type ENUM('entry','exit') NOT NULL,
  verified_by INT UNSIGNED DEFAULT NULL,
  notes VARCHAR(255) DEFAULT NULL,
  logged_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_logs_date (logged_at),
  INDEX idx_logs_plate (plate_number),
  INDEX idx_logs_type (log_type),
  CONSTRAINT fk_logs_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT fk_logs_vehicle FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE SET NULL,
  CONSTRAINT fk_logs_otp FOREIGN KEY (otp_id) REFERENCES otps(id) ON DELETE SET NULL,
  CONSTRAINT fk_logs_verifier FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------------
-- Visitors (walk-in / guest registration by security)
-- ---------------------------------------------------------------------------
CREATE TABLE visitors (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(120) NOT NULL,
  phone VARCHAR(30) DEFAULT NULL,
  plate_number VARCHAR(30) NOT NULL,
  purpose VARCHAR(255) DEFAULT NULL,
  host_name VARCHAR(120) DEFAULT NULL,
  registered_by INT UNSIGNED DEFAULT NULL,
  status ENUM('on_campus','exited') NOT NULL DEFAULT 'on_campus',
  entry_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  exit_at DATETIME DEFAULT NULL,
  INDEX idx_visitors_plate (plate_number),
  INDEX idx_visitors_status (status),
  CONSTRAINT fk_visitors_registrar FOREIGN KEY (registered_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Add visitor FK after visitors table exists
ALTER TABLE entry_exit_logs
  ADD CONSTRAINT fk_logs_visitor FOREIGN KEY (visitor_id) REFERENCES visitors(id) ON DELETE SET NULL;

-- ---------------------------------------------------------------------------
-- System settings (key/value)
-- ---------------------------------------------------------------------------
CREATE TABLE settings (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  setting_key VARCHAR(80) NOT NULL UNIQUE,
  setting_value TEXT NOT NULL,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------------
-- Audit logs
-- ---------------------------------------------------------------------------
CREATE TABLE audit_logs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED DEFAULT NULL,
  action VARCHAR(80) NOT NULL,
  entity_type VARCHAR(60) DEFAULT NULL,
  entity_id INT UNSIGNED DEFAULT NULL,
  details TEXT DEFAULT NULL,
  ip_address VARCHAR(45) DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_audit_user (user_id),
  INDEX idx_audit_action (action),
  CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------------
-- Seed: default settings
-- ---------------------------------------------------------------------------
INSERT INTO settings (setting_key, setting_value) VALUES
  ('otp_expiry_minutes', '5'),
  ('otp_length', '6'),
  ('campus_name', 'Campus Security Gate'),
  ('allow_registration', '1'),
  ('require_vehicle_approval', '1');

-- ---------------------------------------------------------------------------
-- Seed: default administrator
-- Email: admin@campus.edu
-- Password: Admin@123
-- Hash generated with bcrypt (password_hash compatible)
-- ---------------------------------------------------------------------------
INSERT INTO users (full_name, email, password_hash, phone, role, status, department) VALUES
(
  'System Administrator',
  'admin@campus.edu',
  '$2b$10$Rc0hlcwcfqDfqz44cg9LnO4DAGU11tm25AUYAbZy4fUB23ZSoGZFG',
  '0000000000',
  'admin',
  'active',
  'Security Office'
);

-- Demo security account — password: Security@123
INSERT INTO users (full_name, email, password_hash, phone, role, status, department) VALUES
(
  'Gate Security Officer',
  'security@campus.edu',
  '$2b$10$bYeFMMugR2b6CtZ0Mi91Pe90OLVbRicddyvHPEwDINxswe0R3rSK6',
  '0000000001',
  'security',
  'active',
  'Security Office'
);
