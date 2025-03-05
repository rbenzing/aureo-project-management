SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- ----------------------------
-- Table structure for companies
-- ----------------------------
DROP TABLE IF EXISTS `companies`;
CREATE TABLE IF NOT EXISTS `companies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `address` varchar(500) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `website` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for company_admins
-- ----------------------------
CREATE TABLE company_admins (
  id INT NOT NULL AUTO_INCREMENT,
  company_id INT NOT NULL,
  user_id INT NOT NULL,
  is_primary TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (id),
  UNIQUE KEY unique_company_user (company_id, user_id),
  KEY idx_company_admins_user_id (user_id),
  CONSTRAINT fk_company_admins_company FOREIGN KEY (company_id) REFERENCES companies (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for milestone_statuses
-- ----------------------------
CREATE TABLE milestone_statuses (
  id INT NOT NULL AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,
  description VARCHAR(255) DEFAULT NULL,
  is_deleted TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT current_timestamp(),
  updated_at TIMESTAMP NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (id),
  UNIQUE KEY name (name) USING HASH
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO milestone_statuses (name, description) VALUES
  ('not_started', 'Not Started'),
  ('in_progress', 'In Progress'),
  ('completed', 'Completed'),
  ('on_hold', 'On Hold'),
  ('delayed', 'Delayed'),
  ('cancelled', 'Cancelled');

-- ----------------------------
-- Table structure for project_statuses
-- ----------------------------
CREATE TABLE project_statuses (
  id INT NOT NULL AUTO_INCREMENT,
  name VARCHAR(50) NOT NULL,
  description VARCHAR(255) DEFAULT NULL,
  is_deleted TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT current_timestamp(),
  updated_at TIMESTAMP NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (id),
  UNIQUE KEY name (name) USING HASH
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO project_statuses (name, description) VALUES
  ('ready', 'Ready'),
  ('in_progress', 'In Progress'),
  ('completed', 'Completed'),
  ('on_hold', 'On Hold'),
  ('delayed', 'Delayed'),
  ('cancelled', 'Cancelled');

-- ----------------------------
-- Table structure for task_statuses
-- ----------------------------
CREATE TABLE task_statuses (
  id INT NOT NULL AUTO_INCREMENT,
  name VARCHAR(50) NOT NULL,
  description VARCHAR(255) DEFAULT NULL,
  is_deleted TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT current_timestamp(),
  updated_at TIMESTAMP NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (id),
  UNIQUE KEY name (name) USING HASH
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO task_statuses (name, description) VALUES
  ('open', 'Open'),
  ('in_progress', 'In Progress'),
  ('on_hold', 'On Hold'),
  ('in_review', 'In Review'),
  ('closed', 'Closed'),
  ('completed', 'Completed'),
  ('cancelled', 'Cancelled');

-- ----------------------------
-- Table structure for sprint_statuses
-- ----------------------------
CREATE TABLE sprint_statuses (
    id INT NOT NULL AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    description VARCHAR(255) DEFAULT NULL,
    is_deleted TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT current_timestamp(),
    updated_at TIMESTAMP NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (id),
    UNIQUE KEY name (name) USING HASH
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO sprint_statuses (name, description) VALUES
  ('planning', 'Sprint is in planning phase'),
  ('active', 'Sprint is currently active'),
  ('delayed', 'Sprint has been delayed'),
  ('completed', 'Sprint has been completed'),
  ('cancelled', 'Sprint was cancelled');

-- ----------------------------
-- Table structure for permissions
-- ----------------------------
CREATE TABLE permissions (
  id INT NOT NULL AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  description VARCHAR(500) DEFAULT NULL,
  created_at TIMESTAMP NULL DEFAULT current_timestamp(),
  updated_at TIMESTAMP NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (id),
  UNIQUE KEY unique_permission_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `permissions` (`id`, `name`, `description`) VALUES
(1, 'view_projects', 'Allows viewing projects'),
(2, 'create_projects', 'Allows creating new projects'),
(3, 'edit_projects', 'Allows editing existing projects'),
(4, 'delete_projects', 'Allows deleting projects'),
(5, 'view_tasks', 'Allows viewing tasks'),
(6, 'create_tasks', 'Allows creating new tasks'),
(7, 'edit_tasks', 'Allows editing tasks'),
(8, 'delete_tasks', 'Allows deleting tasks'),
(9, 'manage_users', 'Allows managing users'),
(10, 'manage_roles', 'Allows managing roles and permissions'),
(11, 'manage_companies', 'Allows managing companies'),
(12, 'view_dashboard', 'Allows viewing the dashboard'),
(13, 'view_users', 'Allows viewing users'),
(14, 'create_users', 'Allows creating new users'),
(15, 'delete_users', 'Allows deleting users'),
(16, 'edit_users', 'Allows editing users'),
(17, 'view_milestones', 'Allows viewing milestones'),
(18, 'create_milestones', 'Allows creating milestones'),
(19, 'edit_milestones', 'Allows editing milestones'),
(20, 'delete_milestones', 'Allows deleting milestones'),
(21, 'manage_milestones', 'Allows managing milestones'),
(22, 'edit_roles', 'Allows editing roles'),
(23, 'create_roles', 'Allows creating new roles'),
(24, 'delete_roles', 'Allows deleting roles'),
(25, 'manage_tasks', 'Allows managing tasks'),
(26, 'manage_projects', 'Allows managing projects'),
(27, 'view_roles', 'Allows viewing roles'),
(28, 'view_sprints', 'Allows viewing sprints'),
(29, 'edit_sprints', 'Allows editing sprints'),
(30, 'manage_sprints', 'Allows managing sprints'),
(31, 'delete_sprints', 'Allows deleting sprints');

-- ----------------------------
-- Table structure for roles
-- ----------------------------
CREATE TABLE roles (
  id INT NOT NULL AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  description VARCHAR(500) DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT current_timestamp(),
  updated_at TIMESTAMP NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  is_deleted TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE KEY unique_role_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for role_permissions
-- ----------------------------
CREATE TABLE role_permissions (
  role_id INT NOT NULL,
  permission_id INT NOT NULL,
  PRIMARY KEY (role_id, permission_id),
  KEY permission_id (permission_id),
  CONSTRAINT role_permissions_ibfk_1 FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE CASCADE,
  CONSTRAINT role_permissions_ibfk_2 FOREIGN KEY (permission_id) REFERENCES permissions (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for users
-- ----------------------------
CREATE TABLE users (
  id INT NOT NULL AUTO_INCREMENT,
  company_id INT DEFAULT NULL,
  role_id INT NOT NULL,
  first_name VARCHAR(100) NOT NULL,
  last_name VARCHAR(100) NOT NULL,
  email VARCHAR(255) NOT NULL,
  phone VARCHAR(25) DEFAULT NULL,
  password_hash VARCHAR(255) NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 0,
  activation_token VARCHAR(255) DEFAULT NULL,
  activation_token_expires_at DATETIME DEFAULT NULL,
  reset_password_token VARCHAR(255) DEFAULT NULL,
  reset_password_token_expires_at DATETIME DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT current_timestamp(),
  updated_at TIMESTAMP NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  is_deleted TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE KEY email (email),
  KEY idx_users_company_id (company_id),
  KEY idx_users_role_id (role_id),
  KEY idx_users_email (email),
  KEY activation_token (activation_token),
  KEY reset_password_token (reset_password_token),
  CONSTRAINT users_ibfk_1 FOREIGN KEY (company_id) REFERENCES companies (id) ON DELETE SET NULL,
  CONSTRAINT users_ibfk_2 FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Complete the company_admins foreign key now that users table exists
ALTER TABLE company_admins
ADD CONSTRAINT fk_company_admins_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE;

-- ----------------------------
-- Table structure for csrf_tokens
-- ----------------------------
CREATE TABLE csrf_tokens (
  id INT NOT NULL AUTO_INCREMENT,
  user_id INT DEFAULT NULL,
  token VARCHAR(255) NOT NULL,
  session_id VARCHAR(255) NOT NULL,
  expires_at TIMESTAMP NOT NULL,
  created_at TIMESTAMP NULL DEFAULT current_timestamp(),
  PRIMARY KEY (id),
  KEY idx_csrf_tokens_session_id (session_id),
  KEY idx_csrf_tokens_token (token),
  CONSTRAINT csrf_tokens_ibfk_1 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for projects
-- ----------------------------
CREATE TABLE projects (
  id INT NOT NULL AUTO_INCREMENT,
  key_code VARCHAR(4) NOT NULL,
  company_id INT NOT NULL,
  owner_id INT NOT NULL,
  name VARCHAR(255) NOT NULL,
  description VARCHAR(500) DEFAULT NULL,
  start_date DATE DEFAULT NULL,
  end_date DATE DEFAULT NULL,
  status_id INT NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT current_timestamp(),
  updated_at TIMESTAMP NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  is_deleted TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE KEY unique_company_key_code (company_id, key_code),
  KEY status_id (status_id),
  KEY idx_projects_company_id (company_id),
  KEY idx_projects_owner_id (owner_id),
  CONSTRAINT projects_ibfk_1 FOREIGN KEY (company_id) REFERENCES companies (id) ON DELETE CASCADE,
  CONSTRAINT projects_ibfk_2 FOREIGN KEY (status_id) REFERENCES project_statuses (id),
  CONSTRAINT projects_ibfk_3 FOREIGN KEY (owner_id) REFERENCES users (id) ON DELETE RESTRICT,
  CONSTRAINT check_project_dates CHECK (end_date IS NULL OR start_date IS NULL OR end_date >= start_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for milestones
-- ----------------------------
CREATE TABLE milestones (
  id INT NOT NULL AUTO_INCREMENT,
  title VARCHAR(255) NOT NULL,
  description VARCHAR(255) DEFAULT NULL,
  milestone_type ENUM('epic','milestone') NOT NULL DEFAULT 'milestone',
  start_date DATE DEFAULT NULL,
  due_date DATE DEFAULT NULL,
  complete_date DATE DEFAULT NULL,
  epic_id INT DEFAULT NULL,
  project_id INT DEFAULT NULL,
  status_id INT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT current_timestamp(),
  updated_at TIMESTAMP NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  is_deleted TINYINT(1) DEFAULT 0,
  PRIMARY KEY (id),
  KEY project_id (project_id),
  KEY status_id (status_id),
  KEY epic_id (epic_id),
  KEY idx_milestones_dates (start_date, due_date, complete_date),
  CONSTRAINT fk_epic FOREIGN KEY (epic_id) REFERENCES milestones(id) ON DELETE SET NULL,
  CONSTRAINT fk_milestone_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
  CONSTRAINT fk_milestone_status FOREIGN KEY (status_id) REFERENCES milestone_statuses(id),
  CONSTRAINT check_milestone_dates CHECK (due_date IS NULL OR start_date IS NULL OR due_date >= start_date),
  CONSTRAINT check_milestone_complete CHECK (complete_date IS NULL OR start_date IS NULL OR complete_date >= start_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for tasks
-- ----------------------------
CREATE TABLE tasks (
  id INT NOT NULL AUTO_INCREMENT,
  project_id INT NOT NULL,
  assigned_to INT DEFAULT NULL,
  title VARCHAR(255) NOT NULL,
  description TEXT DEFAULT NULL,
  priority ENUM('none','low','medium','high') NOT NULL DEFAULT 'none',
  status_id INT NOT NULL DEFAULT 1,
  estimated_time INT DEFAULT NULL COMMENT 'In minutes',
  billable_time INT DEFAULT NULL COMMENT 'In minutes',
  time_spent INT DEFAULT 0 COMMENT 'In minutes',
  start_date DATE DEFAULT NULL,
  due_date DATE DEFAULT NULL,
  complete_date DATE DEFAULT NULL,
  hourly_rate DECIMAL(10,2) DEFAULT NULL,
  is_hourly TINYINT(1) DEFAULT 0,
  is_deleted TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT current_timestamp(),
  updated_at TIMESTAMP NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  is_subtask TINYINT(1) NOT NULL DEFAULT 0,
  parent_task_id INT DEFAULT NULL,
  PRIMARY KEY (id),
  KEY assigned_to (assigned_to),
  KEY idx_tasks_project_id (project_id),
  KEY fk_parent_task (parent_task_id),
  KEY status_id (status_id),
  KEY idx_tasks_dates (start_date, due_date, complete_date),
  KEY idx_tasks_priority (priority),
  CONSTRAINT fk_parent_task FOREIGN KEY (parent_task_id) REFERENCES tasks (id) ON DELETE SET NULL,
  CONSTRAINT tasks_ibfk_1 FOREIGN KEY (project_id) REFERENCES projects (id) ON DELETE CASCADE,
  CONSTRAINT tasks_ibfk_2 FOREIGN KEY (assigned_to) REFERENCES users (id) ON DELETE SET NULL,
  CONSTRAINT tasks_ibfk_3 FOREIGN KEY (status_id) REFERENCES task_statuses (id),
  CONSTRAINT check_task_dates CHECK (due_date IS NULL OR start_date IS NULL OR due_date >= start_date),
  CONSTRAINT check_task_complete CHECK (complete_date IS NULL OR start_date IS NULL OR complete_date >= start_date),
  CONSTRAINT check_task_times CHECK (billable_time IS NULL OR estimated_time IS NULL OR billable_time <= estimated_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for sessions
-- ----------------------------
CREATE TABLE sessions (
  id VARCHAR(255) NOT NULL,
  user_id INT DEFAULT NULL,
  data TEXT NOT NULL,
  ip_address VARCHAR(45) DEFAULT NULL,
  user_agent VARCHAR(1024) DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT current_timestamp(),
  expires_at TIMESTAMP NULL DEFAULT NULL,
  last_accessed_at TIMESTAMP NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (id),
  KEY idx_sessions_user_id (user_id),
  KEY idx_sessions_expires_at (expires_at),
  CONSTRAINT sessions_ibfk_1 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for activity_logs
-- ----------------------------
DROP TABLE IF EXISTS `activity_logs`;
CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `session_id` varchar(255) NOT NULL,
  `event_type` varchar(50) NOT NULL,
  `method` varchar(10) NOT NULL,
  `path` varchar(255) NOT NULL,
  `query_string` text DEFAULT NULL,
  `referer` varchar(255) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) NOT NULL,
  `request_data` longtext DEFAULT NULL CHECK (json_valid(`request_data`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_session_id` (`session_id`),
  KEY `idx_event_type` (`event_type`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT activity_logs_ibfk_1 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2070 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for sprints
-- ----------------------------
CREATE TABLE sprints (
    id INT NOT NULL AUTO_INCREMENT,
    project_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status_id INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT current_timestamp(),
    updated_at TIMESTAMP NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    is_deleted TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    KEY idx_sprints_project_id (project_id),
    KEY idx_sprints_status_id (status_id),
    KEY idx_sprints_dates (start_date, end_date),
    CONSTRAINT fk_sprints_project FOREIGN KEY (project_id) REFERENCES projects (id) ON DELETE CASCADE,
    CONSTRAINT fk_sprints_status FOREIGN KEY (status_id) REFERENCES sprint_statuses (id),
    CONSTRAINT check_sprint_dates CHECK (end_date >= start_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for sprint_tasks
-- ----------------------------
CREATE TABLE sprint_tasks (
    sprint_id INT NOT NULL,
    task_id INT NOT NULL,
    added_at TIMESTAMP NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (sprint_id, task_id),
    KEY idx_sprint_tasks_task_id (task_id),
    CONSTRAINT fk_sprint_tasks_sprint FOREIGN KEY (sprint_id) REFERENCES sprints (id) ON DELETE CASCADE,
    CONSTRAINT fk_sprint_tasks_task FOREIGN KEY (task_id) REFERENCES tasks (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;