CREATE DATABASE IF NOT EXISTS cloudsoftera_feedback
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cloudsoftera_feedback;

CREATE TABLE IF NOT EXISTS users (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  employee_code VARCHAR(40) NULL UNIQUE,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('ADMIN','EMPLOYEE') NOT NULL DEFAULT 'EMPLOYEE',
  department VARCHAR(100) NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_users_role_active(role, is_active)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS feedback (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  reference_code VARCHAR(24) NOT NULL UNIQUE,
  submitted_by BIGINT UNSIGNED NULL,
  is_anonymous TINYINT(1) NOT NULL DEFAULT 0,
  department VARCHAR(100) NULL,
  category ENUM('Website / web application','Mobile application','Desktop software','Internal business software','API / integration','Other') NOT NULL DEFAULT 'Other',
  priority ENUM('Low','Normal','Medium','High') NOT NULL DEFAULT 'Normal',
  subject VARCHAR(160) NOT NULL,
  message TEXT NOT NULL,
  suggested_outcome TEXT NULL,
  rating TINYINT UNSIGNED NULL,
  questionnaire_json JSON NULL,
  status ENUM('Open','In progress','Resolved','Closed') NOT NULL DEFAULT 'Open',
  admin_notes TEXT NULL,
  resolved_at DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_feedback_user FOREIGN KEY (submitted_by) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_feedback_status_date(status, created_at),
  INDEX idx_feedback_category(category),
  INDEX idx_feedback_priority(priority),
  INDEX idx_feedback_department(department),
  FULLTEXT INDEX ft_feedback_search(subject, message)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS action_items (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  feedback_id BIGINT UNSIGNED NULL,
  title VARCHAR(180) NOT NULL,
  description TEXT NULL,
  owner_user_id BIGINT UNSIGNED NULL,
  owner_name VARCHAR(120) NULL,
  due_date DATE NULL,
  status ENUM('Planned','In progress','Completed','Cancelled') NOT NULL DEFAULT 'Planned',
  progress TINYINT UNSIGNED NOT NULL DEFAULT 0,
  created_by BIGINT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_action_feedback FOREIGN KEY (feedback_id) REFERENCES feedback(id) ON DELETE SET NULL,
  CONSTRAINT fk_action_owner FOREIGN KEY (owner_user_id) REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT fk_action_creator FOREIGN KEY (created_by) REFERENCES users(id),
  INDEX idx_action_status_due(status, due_date)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS audit_log (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  actor_user_id BIGINT UNSIGNED NULL,
  action VARCHAR(100) NOT NULL,
  entity_type VARCHAR(50) NOT NULL,
  entity_id BIGINT UNSIGNED NULL,
  details VARCHAR(500) NULL,
  ip_address VARCHAR(45) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_audit_actor FOREIGN KEY (actor_user_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_audit_created(created_at)
) ENGINE=InnoDB;
