USE uniflow_graduate;

ALTER TABLE users ADD COLUMN IF NOT EXISTS last_login_at DATETIME NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS password_changed_at DATETIME NULL;
ALTER TABLE notifications ADD COLUMN IF NOT EXISTS read_at DATETIME NULL;
ALTER TABLE audit_logs ADD COLUMN IF NOT EXISTS ip_address VARCHAR(64) NULL;
ALTER TABLE audit_logs ADD COLUMN IF NOT EXISTS user_agent VARCHAR(500) NULL;

CREATE TABLE IF NOT EXISTS semesters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    academic_year_id INT NOT NULL,
    name VARCHAR(60) NOT NULL,
    starts_on DATE NULL,
    ends_on DATE NULL,
    is_current TINYINT(1) NOT NULL DEFAULT 0,
    UNIQUE KEY uq_semester_year_name (academic_year_id, name),
    CONSTRAINT fk_semester_year FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS role_permissions (
    role ENUM('ADMIN','STAFF','SUPERVISOR','COMMITTEE') NOT NULL,
    permission VARCHAR(100) NOT NULL,
    PRIMARY KEY (role, permission)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS user_permissions (
    user_id INT NOT NULL,
    permission VARCHAR(100) NOT NULL,
    PRIMARY KEY (user_id, permission),
    CONSTRAINT fk_user_permission_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS password_reset_tokens (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token_hash CHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_reset_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS notification_preferences (
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
ALTER TABLE student_courses ADD COLUMN IF NOT EXISTS semester_id INT NULL AFTER study_plan_id;
-- Add the FK only on clean installs where it does not already exist.
SET @fk_exists := (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA=DATABASE() AND TABLE_NAME='student_courses' AND CONSTRAINT_NAME='fk_sc_semester');
SET @sql := IF(@fk_exists=0,'ALTER TABLE student_courses ADD CONSTRAINT fk_sc_semester FOREIGN KEY (semester_id) REFERENCES semesters(id) ON DELETE SET NULL','SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
CREATE TABLE IF NOT EXISTS course_programs (
    course_id INT NOT NULL,
    program_id INT NOT NULL,
    PRIMARY KEY(course_id, program_id),
    CONSTRAINT fk_cp_course FOREIGN KEY(course_id) REFERENCES courses(id) ON DELETE CASCADE,
    CONSTRAINT fk_cp_program FOREIGN KEY(program_id) REFERENCES programs(id) ON DELETE CASCADE
) ENGINE=InnoDB;
INSERT IGNORE INTO course_programs(course_id,program_id) SELECT id,program_id FROM courses;
