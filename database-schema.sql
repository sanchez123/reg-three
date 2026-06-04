-- TIIR Party Registration System Database Schema
-- Single Admin System Configuration

-- Create Admin Users Table
CREATE TABLE admin_users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    phone VARCHAR(30) NULL,
    profile_photo VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
);

-- Create Members Table
CREATE TABLE members (
    id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(100) NOT NULL,
    mothers_name VARCHAR(100) NOT NULL,
    gender ENUM('Male', 'Female') NOT NULL,
    date_of_birth DATE NOT NULL,
    place_of_birth VARCHAR(100) NOT NULL,
    government_id VARCHAR(50),
    education ENUM('Ma dhigan dugsi (No formal education)', 'Dugsi Hoose (Primary School)', 'Dugsi Dhexe (Intermediate School)', 'Dugsi Sare (Secondary School)', 'Diploma', 'Bachelor Degree (Jaamacad – Heerka 1aad)', 'Master''s Degree (Jaamacad – Heerka 2aad)', 'PhD / Doctorate (Jaamacad – Heerka 3aad)') NOT NULL,
    occupation VARCHAR(100) NOT NULL,
    country VARCHAR(100) NOT NULL,
    state VARCHAR(100) NOT NULL,
    district VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20) NOT NULL,
    photo_path VARCHAR(255),
    security_code VARCHAR(10),
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    added_by INT,
    FOREIGN KEY (added_by) REFERENCES admin_users(id) ON DELETE SET NULL
);

-- Create Candidates Table
CREATE TABLE candidates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(100) NOT NULL,
    mothers_name VARCHAR(100) NOT NULL,
    gender ENUM('Male', 'Female') NOT NULL,
    date_of_birth DATE NOT NULL,
    place_of_birth VARCHAR(100) NOT NULL,
    government_id VARCHAR(50),
    education ENUM('Ma dhigan dugsi (No formal education)', 'Dugsi Hoose (Primary School)', 'Dugsi Dhexe (Intermediate School)', 'Dugsi Sare (Secondary School)', 'Diploma', 'Bachelor Degree (Jaamacad – Heerka 1aad)', 'Master''s Degree (Jaamacad – Heerka 2aad)', 'PhD / Doctorate (Jaamacad – Heerka 3aad)') NOT NULL,
    occupation VARCHAR(100) NOT NULL,
    country VARCHAR(100) NOT NULL,
    state VARCHAR(100) NOT NULL,
    district VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20) NOT NULL,
    photo_path VARCHAR(255),
    security_code VARCHAR(10),
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    added_by INT,
    FOREIGN KEY (added_by) REFERENCES admin_users(id) ON DELETE SET NULL
);

-- Create Audit Log Table
CREATE TABLE audit_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    admin_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    table_name VARCHAR(50),
    record_id INT,
    details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admin_users(id) ON DELETE CASCADE
);

-- Add Indexes
CREATE INDEX idx_admin_username ON admin_users(username);
CREATE INDEX idx_admin_email ON admin_users(email);
CREATE INDEX idx_members_email ON members(email);
CREATE INDEX idx_members_phone ON members(phone);
CREATE INDEX idx_members_status ON members(status);
CREATE INDEX idx_candidates_email ON candidates(email);
CREATE INDEX idx_candidates_phone ON candidates(phone);
CREATE INDEX idx_candidates_status ON candidates(status);

-- Insert Default Admin User (username: admin, password: admin123)
-- IMPORTANT: Replace <HASH> with the bcrypt hash from generate-hash.php
INSERT INTO admin_users (username, email, password, full_name, phone)
VALUES ('admin', 'admin@tiir.com', '<HASH_FROM_GENERATE_HASH_PHP>', 'Administrator', '+252123456789')
    ON DUPLICATE KEY UPDATE password = '<HASH_FROM_GENERATE_HASH_PHP>';

