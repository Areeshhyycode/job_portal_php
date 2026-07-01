-- ============================================================
--  JOB PORTAL - MySQL Schema + Seed Data
--  Run this in phpMyAdmin (Import) or:  mysql -u root < database.sql
-- ============================================================

DROP DATABASE IF EXISTS job_portal;
CREATE DATABASE job_portal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE job_portal;

-- ---------- Users (seekers, companies, admin) ----------
CREATE TABLE users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(120) NOT NULL,
    email         VARCHAR(160) NOT NULL UNIQUE,
    password      VARCHAR(255) NOT NULL,
    role          ENUM('seeker','company','admin') NOT NULL DEFAULT 'seeker',

    -- seeker profile
    phone         VARCHAR(40)  DEFAULT NULL,
    location      VARCHAR(120) DEFAULT NULL,
    headline      VARCHAR(160) DEFAULT NULL,
    bio           TEXT         DEFAULT NULL,
    resume_path   VARCHAR(255) DEFAULT NULL,

    -- company profile
    company_name  VARCHAR(160) DEFAULT NULL,
    website       VARCHAR(200) DEFAULT NULL,
    about         TEXT         DEFAULT NULL,

    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------- Jobs ----------
CREATE TABLE jobs (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    company_id   INT NOT NULL,
    title        VARCHAR(160) NOT NULL,
    description  TEXT NOT NULL,
    location     VARCHAR(120) NOT NULL,
    job_type     ENUM('Full-time','Part-time','Contract','Internship','Remote') NOT NULL DEFAULT 'Full-time',
    category     VARCHAR(80)  NOT NULL,
    salary_min   INT DEFAULT NULL,
    salary_max   INT DEFAULT NULL,
    status       ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_job_company FOREIGN KEY (company_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------- Applications ----------
CREATE TABLE applications (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    job_id       INT NOT NULL,
    seeker_id    INT NOT NULL,
    cover_letter TEXT DEFAULT NULL,
    resume_path  VARCHAR(255) DEFAULT NULL,
    status       ENUM('applied','shortlisted','rejected') NOT NULL DEFAULT 'applied',
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_apply (job_id, seeker_id),
    CONSTRAINT fk_app_job    FOREIGN KEY (job_id)    REFERENCES jobs(id)  ON DELETE CASCADE,
    CONSTRAINT fk_app_seeker FOREIGN KEY (seeker_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------- Saved Jobs ----------
CREATE TABLE saved_jobs (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    job_id     INT NOT NULL,
    seeker_id  INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_save (job_id, seeker_id),
    CONSTRAINT fk_save_job    FOREIGN KEY (job_id)    REFERENCES jobs(id)  ON DELETE CASCADE,
    CONSTRAINT fk_save_seeker FOREIGN KEY (seeker_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
--  SEED DATA
--  NOTE: every seeded account's password is  ->  password123
--  (hash below is a real bcrypt hash of "password123")
-- ============================================================
SET @pw = '$2y$10$UaMPxLpQTsQ4V8prItHRgeQYMCz32M/HYPQ8Rep4k.81zWQeGC3Mu';

-- Admin
INSERT INTO users (name, email, password, role)
VALUES ('Site Admin', 'admin@jobportal.test', @pw, 'admin');

-- Companies
INSERT INTO users (name, email, password, role, company_name, website, about, location)
VALUES
('Acme Recruiter', 'hr@acme.test', @pw, 'company', 'Acme Corp', 'https://acme.test', 'We build rockets and hire the best.', 'Karachi'),
('TechNova HR',    'jobs@technova.test', @pw, 'company', 'TechNova', 'https://technova.test', 'A fast-growing software house.', 'Lahore');

-- Job Seekers
INSERT INTO users (name, email, password, role, phone, location, headline, bio)
VALUES
('Ayesha Khan', 'ayesha@seeker.test', @pw, 'seeker', '0300-1234567', 'Karachi', 'Frontend Developer', 'React & UI enthusiast.'),
('Bilal Ahmed', 'bilal@seeker.test',  @pw, 'seeker', '0321-7654321', 'Lahore',  'Backend Developer',  'PHP / Node engineer.');

-- Jobs (company_id 2 = Acme, 3 = TechNova). One pending to test admin approval.
INSERT INTO jobs (company_id, title, description, location, job_type, category, salary_min, salary_max, status)
VALUES
(2, 'Senior PHP Developer', 'Build and maintain our core job portal platform using PHP 8, MySQL and clean architecture. Experience with PDO, REST and Git required.', 'Karachi', 'Full-time', 'Software', 120000, 200000, 'approved'),
(2, 'UI/UX Designer', 'Design delightful, accessible interfaces for web and mobile. Figma proficiency a must.', 'Remote', 'Remote', 'Design', 90000, 150000, 'approved'),
(3, 'React Frontend Engineer', 'Own the frontend of our SaaS product. React, TypeScript, Tailwind.', 'Lahore', 'Full-time', 'Software', 100000, 180000, 'approved'),
(3, 'Marketing Intern', 'Support our marketing team with content and social campaigns.', 'Lahore', 'Internship', 'Marketing', 25000, 40000, 'pending');
