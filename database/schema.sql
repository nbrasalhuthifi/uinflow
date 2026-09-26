-- UniFlow Graduate Studies - CLEAN INSTALL SCHEMA
-- Database is intentionally named uniflow_graduate to avoid stale
-- InnoDB tablespaces from older UniFlow installations.
-- Import this file from the first line when installing this build.

SET FOREIGN_KEY_CHECKS = 0;
DROP DATABASE IF EXISTS uniflow_graduate;
SET FOREIGN_KEY_CHECKS = 1;

CREATE DATABASE uniflow_graduate
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
USE uniflow_graduate;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(180) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('ADMIN','STAFF','SUPERVISOR','COMMITTEE') NOT NULL DEFAULT 'STAFF',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB;

CREATE TABLE departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(180) NOT NULL,
    code VARCHAR(30) NOT NULL UNIQUE
) ENGINE = InnoDB;

CREATE TABLE programs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    department_id INT NOT NULL,
    name VARCHAR(180) NOT NULL,
    degree ENUM('MASTER','PHD') NOT NULL,
    duration_years DECIMAL(3,1) NOT NULL DEFAULT 2.0,
    required_credits INT NOT NULL DEFAULT 6,
    UNIQUE KEY uq_program_name (department_id, name),
    CONSTRAINT fk_program_department
        FOREIGN KEY (department_id) REFERENCES departments(id)
) ENGINE = InnoDB;

CREATE TABLE academic_years (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(30) NOT NULL UNIQUE,
    is_current TINYINT(1) NOT NULL DEFAULT 0
) ENGINE = InnoDB;

CREATE TABLE applicants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    application_no VARCHAR(40) NOT NULL UNIQUE,
    full_name VARCHAR(180) NOT NULL,
    phone VARCHAR(30),
    email VARCHAR(190),
    national_id VARCHAR(40),
    previous_degree VARCHAR(180),
    previous_gpa DECIMAL(4,2),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_applicant_name (full_name)
) ENGINE = InnoDB;

CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_no VARCHAR(40) NOT NULL UNIQUE,
    full_name VARCHAR(180) NOT NULL,
    national_id VARCHAR(40),
    phone VARCHAR(30),
    email VARCHAR(190),
    photo_path VARCHAR(255),
    program_id INT NOT NULL,
    admission_date DATE,
    status ENUM('ACTIVE','GRADUATED','SUSPENDED') NOT NULL DEFAULT 'ACTIVE',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_student_program
        FOREIGN KEY (program_id) REFERENCES programs(id),
    INDEX idx_student_name (full_name),
    INDEX idx_student_program (program_id)
) ENGINE = InnoDB;

CREATE TABLE applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    applicant_id INT NOT NULL,
    program_id INT NOT NULL,
    application_type ENUM('NEW','TRANSFER') NOT NULL DEFAULT 'NEW',
    status ENUM('PENDING','APPROVED','REJECTED') NOT NULL DEFAULT 'PENDING',
    notes TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_application_applicant
        FOREIGN KEY (applicant_id) REFERENCES applicants(id) ON DELETE CASCADE,
    CONSTRAINT fk_application_program
        FOREIGN KEY (program_id) REFERENCES programs(id),
    INDEX idx_application_status (status)
) ENGINE = InnoDB;

CREATE TABLE supervisors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    academic_rank VARCHAR(100),
    specialization VARCHAR(180),
    photo_path VARCHAR(255),
    CONSTRAINT fk_supervisor_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE = InnoDB;

CREATE TABLE student_supervisors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    supervisor_id INT NOT NULL,
    supervisor_role ENUM('PRIMARY','CO_SUPERVISOR') NOT NULL DEFAULT 'PRIMARY',
    assigned_at DATE NOT NULL,
    UNIQUE KEY uq_student_supervisor_role (student_id, supervisor_id, supervisor_role),
    CONSTRAINT fk_ss_student
        FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    CONSTRAINT fk_ss_supervisor
        FOREIGN KEY (supervisor_id) REFERENCES supervisors(id) ON DELETE RESTRICT
) ENGINE = InnoDB;

CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    program_id INT NOT NULL,
    code VARCHAR(30) NOT NULL,
    name VARCHAR(180) NOT NULL,
    credits TINYINT NOT NULL,
    is_required TINYINT(1) NOT NULL DEFAULT 1,
    UNIQUE KEY uq_course_code (program_id, code),
    CONSTRAINT fk_course_program
        FOREIGN KEY (program_id) REFERENCES programs(id)
) ENGINE = InnoDB;

CREATE TABLE study_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    academic_year_id INT NOT NULL,
    status ENUM('DRAFT','APPROVED','IN_PROGRESS','COMPLETED') NOT NULL DEFAULT 'DRAFT',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_student_year (student_id, academic_year_id),
    CONSTRAINT fk_plan_student
        FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    CONSTRAINT fk_plan_year
        FOREIGN KEY (academic_year_id) REFERENCES academic_years(id)
) ENGINE = InnoDB;

CREATE TABLE student_courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    study_plan_id INT NOT NULL,
    grade DECIMAL(5,2) NULL,
    status ENUM('REGISTERED','PASSED','FAILED') NOT NULL DEFAULT 'REGISTERED',
    registered_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_plan_course (study_plan_id, course_id),
    CONSTRAINT fk_sc_student
        FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    CONSTRAINT fk_sc_course
        FOREIGN KEY (course_id) REFERENCES courses(id),
    CONSTRAINT fk_sc_plan
        FOREIGN KEY (study_plan_id) REFERENCES study_plans(id) ON DELETE CASCADE
) ENGINE = InnoDB;

CREATE TABLE research_proposals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    supervisor_id INT NULL,
    title VARCHAR(300) NOT NULL,
    abstract TEXT,
    status ENUM('SUBMITTED','UNDER_REVIEW','APPROVED','REJECTED') NOT NULL DEFAULT 'SUBMITTED',
    submitted_at DATE NOT NULL,
    CONSTRAINT fk_research_student
        FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    CONSTRAINT fk_research_supervisor
        FOREIGN KEY (supervisor_id) REFERENCES supervisors(id) ON DELETE SET NULL
) ENGINE = InnoDB;

CREATE TABLE theses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    proposal_id INT NULL,
    title VARCHAR(300) NOT NULL,
    start_date DATE NOT NULL,
    status ENUM('ACTIVE','READY_FOR_DEFENSE','DEFENDED','GRADUATED','CANCELLED') NOT NULL DEFAULT 'ACTIVE',
    CONSTRAINT fk_thesis_student
        FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    CONSTRAINT fk_thesis_proposal
        FOREIGN KEY (proposal_id) REFERENCES research_proposals(id) ON DELETE SET NULL
) ENGINE = InnoDB;

CREATE TABLE committees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    thesis_id INT NOT NULL,
    committee_type ENUM('PROPOSAL','DEFENSE') NOT NULL,
    meeting_date DATE NOT NULL,
    status ENUM('PLANNED','COMPLETED','CANCELLED') NOT NULL DEFAULT 'PLANNED',
    UNIQUE KEY uq_thesis_committee_type (thesis_id, committee_type),
    CONSTRAINT fk_committee_thesis
        FOREIGN KEY (thesis_id) REFERENCES theses(id) ON DELETE CASCADE
) ENGINE = InnoDB;

CREATE TABLE committee_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    committee_id INT NOT NULL,
    user_id INT NOT NULL,
    member_role VARCHAR(100) NOT NULL,
    UNIQUE KEY uq_committee_user (committee_id, user_id),
    CONSTRAINT fk_member_committee
        FOREIGN KEY (committee_id) REFERENCES committees(id) ON DELETE CASCADE,
    CONSTRAINT fk_member_user
        FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE = InnoDB;

CREATE TABLE defenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    thesis_id INT NOT NULL UNIQUE,
    defense_date DATE NOT NULL,
    location VARCHAR(200),
    result ENUM('PENDING','PASS','PASS_WITH_CHANGES','FAIL') NOT NULL DEFAULT 'PENDING',
    notes TEXT,
    CONSTRAINT fk_defense_thesis
        FOREIGN KEY (thesis_id) REFERENCES theses(id) ON DELETE CASCADE
) ENGINE = InnoDB;

CREATE TABLE documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NULL,
    thesis_id INT NULL,
    document_type VARCHAR(100) NOT NULL,
    title VARCHAR(200) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    uploaded_by INT NULL,
    uploaded_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_document_student
        FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    CONSTRAINT fk_document_thesis
        FOREIGN KEY (thesis_id) REFERENCES theses(id) ON DELETE CASCADE,
    CONSTRAINT fk_document_user
        FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE = InnoDB;

CREATE TABLE notifications (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    student_id INT NULL,
    channel ENUM('IN_APP','EMAIL','WHATSAPP') NOT NULL DEFAULT 'IN_APP',
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('QUEUED','SENT','FAILED') NOT NULL DEFAULT 'QUEUED',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    sent_at TIMESTAMP NULL,
    CONSTRAINT fk_notification_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_notification_student
        FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE SET NULL
) ENGINE = InnoDB;

CREATE TABLE audit_logs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(100) NOT NULL,
    entity VARCHAR(100) NOT NULL,
    entity_id INT NULL,
    details TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_audit_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_audit_created (created_at)
) ENGINE = InnoDB;

-- UniFlow Production Hardening / Feature Pack
ALTER TABLE users
    ADD COLUMN last_login_at DATETIME NULL AFTER created_at,
    ADD COLUMN password_changed_at DATETIME NULL AFTER last_login_at;
ALTER TABLE notifications ADD COLUMN read_at DATETIME NULL AFTER sent_at;
ALTER TABLE audit_logs
    ADD COLUMN ip_address VARCHAR(64) NULL AFTER details,
    ADD COLUMN user_agent VARCHAR(500) NULL AFTER ip_address;

CREATE TABLE semesters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    academic_year_id INT NOT NULL,
    name VARCHAR(60) NOT NULL,
    starts_on DATE NULL,
    ends_on DATE NULL,
    is_current TINYINT(1) NOT NULL DEFAULT 0,
    UNIQUE KEY uq_semester_year_name (academic_year_id, name),
    CONSTRAINT fk_semester_year FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE role_permissions (
    role ENUM('ADMIN','STAFF','SUPERVISOR','COMMITTEE') NOT NULL,
    permission VARCHAR(100) NOT NULL,
    PRIMARY KEY (role, permission)
) ENGINE=InnoDB;

CREATE TABLE user_permissions (
    user_id INT NOT NULL,
    permission VARCHAR(100) NOT NULL,
    PRIMARY KEY (user_id, permission),
    CONSTRAINT fk_user_permission_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE password_reset_tokens (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token_hash CHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_reset_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE notification_preferences (
    user_id INT NOT NULL,
    channel ENUM('IN_APP','EMAIL','WHATSAPP') NOT NULL,
    enabled TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (user_id, channel),
    CONSTRAINT fk_np_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT IGNORE INTO role_permissions (role, permission) VALUES
('ADMIN','users.manage'),('ADMIN','users.view'),('ADMIN','permissions.manage'),('ADMIN','academic.manage'),('ADMIN','documents.manage'),('ADMIN','reports.view'),('ADMIN','audit.view'),('ADMIN','notifications.manage'),('ADMIN','students.manage'),('ADMIN','research.manage'),('ADMIN','theses.manage'),('ADMIN','committees.manage'),('ADMIN','graduation.approve'),
('STAFF','users.view'),('STAFF','academic.manage'),('STAFF','documents.manage'),('STAFF','reports.view'),('STAFF','notifications.manage'),('STAFF','students.manage'),('STAFF','research.manage'),('STAFF','theses.manage'),('STAFF','committees.manage'),('STAFF','graduation.approve'),
('SUPERVISOR','students.view'),('SUPERVISOR','research.manage'),('SUPERVISOR','theses.view'),('SUPERVISOR','committees.view'),('SUPERVISOR','documents.view'),
('COMMITTEE','students.view'),('COMMITTEE','theses.view'),('COMMITTEE','committees.manage'),('COMMITTEE','documents.view');

INSERT IGNORE INTO semesters (academic_year_id, name, starts_on, ends_on, is_current)
SELECT id, 'الفصل الأول', CONCAT(LEFT(name,4),'-09-01'), CONCAT(CAST(LEFT(name,4) AS UNSIGNED)+1,'-01-31'), is_current FROM academic_years WHERE name='2026/2027';
INSERT IGNORE INTO semesters (academic_year_id, name, starts_on, ends_on, is_current)
SELECT id, 'الفصل الثاني', CONCAT(CAST(LEFT(name,4) AS UNSIGNED)+1,'-02-01'), CONCAT(CAST(LEFT(name,4) AS UNSIGNED)+1,'-06-30'), 0 FROM academic_years WHERE name='2026/2027';
ALTER TABLE student_courses ADD COLUMN semester_id INT NULL AFTER study_plan_id;
ALTER TABLE student_courses ADD CONSTRAINT fk_sc_semester FOREIGN KEY (semester_id) REFERENCES semesters(id) ON DELETE SET NULL;
CREATE TABLE course_programs (
    course_id INT NOT NULL,
    program_id INT NOT NULL,
    PRIMARY KEY(course_id, program_id),
    CONSTRAINT fk_cp_course FOREIGN KEY(course_id) REFERENCES courses(id) ON DELETE CASCADE,
    CONSTRAINT fk_cp_program FOREIGN KEY(program_id) REFERENCES programs(id) ON DELETE CASCADE
) ENGINE=InnoDB;
INSERT IGNORE INTO course_programs(course_id,program_id) SELECT id,program_id FROM courses;
