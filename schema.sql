SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
 /*!40101 SET NAMES utf8mb4 */;

-- ----------------------------
-- Table structure for companies
-- ----------------------------
CREATE TABLE companies (
  id int(11) NOT NULL AUTO_INCREMENT,
  name varchar(255) NOT NULL,
  user_id int(11) DEFAULT NULL,
  address varchar(500) DEFAULT NULL,
  phone varchar(25) DEFAULT NULL,
  email varchar(255) NOT NULL,
  website varchar(255) DEFAULT NULL,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  updated_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  is_deleted tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE KEY email (email),
  KEY user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for milestone_statuses
-- ----------------------------
CREATE TABLE milestone_statuses (
  id int(11) NOT NULL AUTO_INCREMENT,
  name varchar(255) NOT NULL,
  description varchar(255) DEFAULT NULL,
  is_deleted tinyint(1) NOT NULL DEFAULT 0,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  updated_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
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
  id tinyint(4) NOT NULL AUTO_INCREMENT,
  name varchar(50) NOT NULL,
  description varchar(255) DEFAULT NULL,
  is_deleted tinyint(1) NOT NULL DEFAULT 0,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  updated_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
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
-- Table structure for permissions
-- ----------------------------
CREATE TABLE permissions (
  id int(11) NOT NULL AUTO_INCREMENT,
  name varchar(100) NOT NULL,
  description varchar(500) DEFAULT NULL,
  created_at timestamp NULL DEFAULT current_timestamp(),
  updated_at timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO permissions (name, description) VALUES
  ('manage_projects', 'Allows managing projects'),
  ('view_projects', 'Allows viewing projects'),
  ('create_projects', 'Allows creating new projects'),
  ('edit_projects', 'Allows editing existing projects'),
  ('delete_projects', 'Allows deleting projects'),
  ('manage_tasks', 'Allows managing tasks'),
  ('view_tasks', 'Allows viewing tasks'),
  ('create_tasks', 'Allows creating new tasks'),
  ('edit_tasks', 'Allows editing tasks'),
  ('delete_tasks', 'Allows deleting tasks'),
  ('manage_users', 'Allows managing users'),
  ('view_users', 'Allows viewing users'),
  ('create_users', 'Allows creating new users'),
  ('delete_users', 'Allows deleting users'),
  ('edit_users', 'Allows editing users'),
  ('manage_roles', 'Allows managing roles and permissions'),
  ('view_roles', 'Allows viewing roles'),
  ('create_roles', 'Allows creating new roles'),
  ('delete_roles', 'Allows deleting roles'),
  ('edit_roles', 'Allows editing roles'),
  ('manage_companies', 'Allows managing companies'),
  ('view_companies', 'Allows viewing companies'),
  ('create_companies', 'Allows creating new companies'),
  ('delete_companies', 'Allows deleting companies'),
  ('edit_companies', 'Allows editing companies'),
  ('manage_milestones', 'Allows managing milestones'),
  ('view_milestones', 'Allows viewing milestones'),
  ('create_milestones', 'Allows creating new milestones'),
  ('delete_milestones', 'Allows deleting milestones'),
  ('edit_milestones', 'Allows editing milestones'),
  ('view_dashboard', 'Allows viewing the dashboard');

-- ----------------------------
-- Table structure for roles
-- ----------------------------
CREATE TABLE roles (
  id int(11) NOT NULL AUTO_INCREMENT,
  name varchar(100) NOT NULL,
  description varchar(500) DEFAULT NULL,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  updated_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  is_deleted tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for role_permissions
-- ----------------------------
CREATE TABLE role_permissions (
  role_id int(11) NOT NULL,
  permission_id int(11) NOT NULL,
  PRIMARY KEY (role_id, permission_id),
  KEY permission_id (permission_id),
  CONSTRAINT role_permissions_ibfk_1 FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE CASCADE,
  CONSTRAINT role_permissions_ibfk_2 FOREIGN KEY (permission_id) REFERENCES permissions (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO role_permissions (role_id, permission_id) VALUES
  (1, 1),
  (1, 2),
  (1, 3),
  (1, 4),
  (1, 5),
  (1, 6),
  (1, 7),
  (1, 8),
  (1, 9),
  (1, 10),
  (1, 11),
  (1, 12),
  (1, 13),
  (1, 14),
  (1, 15),
  (1, 16),
  (1, 17),
  (1, 18),
  (1, 19),
  (1, 20),
  (1, 21),
  (1, 22),
  (1, 23),
  (1, 24),
  (1, 25),
  (1, 26),
  (1, 27),
  (1, 28),
  (1, 29),
  (1, 30),
  (1, 31);

-- ----------------------------
-- Table structure for users
-- ----------------------------
CREATE TABLE users (
  id int(11) NOT NULL AUTO_INCREMENT,
  company_id int(11) DEFAULT NULL,
  role_id int(11) NOT NULL,
  first_name varchar(100) NOT NULL,
  last_name varchar(100) NOT NULL,
  email varchar(255) NOT NULL,
  phone varchar(25) DEFAULT NULL,
  password_hash varchar(255) NOT NULL,
  is_active tinyint(1) NOT NULL DEFAULT 0,
  activation_token varchar(255) DEFAULT NULL,
  activation_token_expires_at datetime DEFAULT NULL,
  reset_password_token varchar(255) DEFAULT NULL,
  reset_password_token_expires_at datetime DEFAULT NULL,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  updated_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  is_deleted tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE KEY email (email),
  KEY idx_users_company_id (company_id),
  KEY idx_users_role_id (role_id),
  KEY idx_users_email (email),
  KEY activation_token (activation_token),
  KEY reset_password_token (reset_password_token),
  CONSTRAINT users_ibfk_1 FOREIGN KEY (company_id) REFERENCES companies (id) ON DELETE SET NULL,
  CONSTRAINT users_ibfk_2 FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for csrf_tokens
-- ----------------------------
CREATE TABLE csrf_tokens (
  id int(11) NOT NULL AUTO_INCREMENT,
  user_id int(11) DEFAULT NULL,
  token varchar(255) NOT NULL,
  session_id varchar(255) NOT NULL,
  expires_at timestamp NOT NULL,
  created_at timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (id),
  KEY idx_csrf_tokens_session_id (session_id),
  CONSTRAINT csrf_tokens_ibfk_1 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for projects
-- ----------------------------
CREATE TABLE projects (
  id int(11) NOT NULL AUTO_INCREMENT,
  company_id int(11) NOT NULL,
  owner_id int(11) NOT NULL,
  name varchar(255) NOT NULL,
  description varchar(500) DEFAULT NULL,
  start_date date DEFAULT NULL,
  end_date date DEFAULT NULL,
  status_id tinyint(4) NOT NULL DEFAULT 1,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  updated_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  is_deleted tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY status_id (status_id),
  KEY idx_projects_company_id (company_id),
  KEY owner_id (owner_id),
  CONSTRAINT projects_ibfk_1 FOREIGN KEY (company_id) REFERENCES companies (id) ON DELETE CASCADE,
  CONSTRAINT projects_ibfk_2 FOREIGN KEY (status_id) REFERENCES project_statuses (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for milestones
-- ----------------------------
CREATE TABLE milestones (
  id int(11) NOT NULL AUTO_INCREMENT,
  title varchar(255) NOT NULL,
  description varchar(255) DEFAULT NULL,
  milestone_type ENUM('epic','milestone') NOT NULL DEFAULT 'milestone',
  start_date date DEFAULT NULL,
  due_date date DEFAULT NULL,
  complete_date date DEFAULT NULL,
  epic_id int(11) DEFAULT NULL,
  project_id int(11) DEFAULT NULL,
  status_id int(11) NOT NULL,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  updated_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  is_deleted tinyint(1) DEFAULT 0,
  PRIMARY KEY (id),
  KEY project_id (project_id),
  KEY status_id (status_id),
  KEY epic_id (epic_id),
  CONSTRAINT fk_epic FOREIGN KEY (epic_id) REFERENCES milestones(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for tasks
-- ----------------------------
CREATE TABLE tasks (
  id int(11) NOT NULL AUTO_INCREMENT,
  project_id int(11) NOT NULL,
  assigned_to int(11) DEFAULT NULL,
  title varchar(255) NOT NULL,
  description TEXT DEFAULT NULL,
  priority enum('none','low','medium','high') NOT NULL DEFAULT 'none',
  status_id int(11) NOT NULL DEFAULT 1,
  estimated_time int(11) DEFAULT NULL,
  billable_time int(11) DEFAULT NULL,
  time_spent int(11) DEFAULT 0,
  start_date date DEFAULT NULL,
  due_date date DEFAULT NULL,
  complete_date date DEFAULT NULL,
  hourly_rate mediumint(9) DEFAULT NULL,
  is_hourly tinyint(1) DEFAULT 0,
  is_deleted tinyint(1) DEFAULT 0,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  updated_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  is_subtask tinyint(1) NOT NULL DEFAULT 0,
  parent_task_id int(11) DEFAULT NULL,
  PRIMARY KEY (id),
  KEY assigned_to (assigned_to),
  KEY idx_tasks_project_id (project_id),
  KEY fk_parent_task (parent_task_id),
  CONSTRAINT fk_parent_task FOREIGN KEY (parent_task_id) REFERENCES tasks (id) ON DELETE SET NULL,
  CONSTRAINT tasks_ibfk_1 FOREIGN KEY (project_id) REFERENCES projects (id) ON DELETE CASCADE,
  CONSTRAINT tasks_ibfk_2 FOREIGN KEY (assigned_to) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for task_statuses
-- ----------------------------
CREATE TABLE task_statuses (
  id tinyint(4) NOT NULL AUTO_INCREMENT,
  name varchar(50) NOT NULL,
  description varchar(255) DEFAULT NULL,
  is_deleted tinyint(1) NOT NULL DEFAULT 0,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  updated_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
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
-- Table structure for sessions
-- ----------------------------
CREATE TABLE sessions (
  id varchar(255) NOT NULL,
  user_id int(11) DEFAULT NULL,
  data text NOT NULL,
  ip_address varchar(45) DEFAULT NULL,
  user_agent varchar(1024) DEFAULT NULL,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  expires_at timestamp NULL DEFAULT NULL,
  last_accessed_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (id),
  KEY idx_sessions_user_id (user_id),
  CONSTRAINT sessions_ibfk_1 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for activity_logs
-- ----------------------------
CREATE TABLE activity_logs (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NULL,
    session_id VARCHAR(255) NOT NULL,
    event_type VARCHAR(50) NOT NULL,
    method VARCHAR(10) NOT NULL,
    path VARCHAR(255) NOT NULL,
    query_string TEXT NULL,
    referer VARCHAR(255) NULL,
    user_agent VARCHAR(1024) NULL,
    ip_address VARCHAR(45) NOT NULL,
    request_data JSON NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_user_id (user_id),
    KEY idx_session_id (session_id),
    KEY idx_event_type (event_type),
    KEY idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for sprint_statuses
-- ----------------------------
CREATE TABLE sprint_statuses (
    id tinyint(4) NOT NULL AUTO_INCREMENT,
    name varchar(50) NOT NULL,
    description varchar(255) DEFAULT NULL,
    is_deleted tinyint(1) NOT NULL DEFAULT 0,
    created_at timestamp NOT NULL DEFAULT current_timestamp(),
    updated_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
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
-- Table structure for sprints
-- ----------------------------
CREATE TABLE sprints (
    id int(11) NOT NULL AUTO_INCREMENT,
    project_id int(11) NOT NULL,
    name varchar(255) NOT NULL,
    description text DEFAULT NULL,
    start_date date NOT NULL,
    end_date date NOT NULL,
    status_id tinyint(4) NOT NULL DEFAULT 1,
    created_at timestamp NOT NULL DEFAULT current_timestamp(),
    updated_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    is_deleted tinyint(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    KEY idx_sprints_project_id (project_id),
    KEY idx_sprints_status_id (status_id),
    CONSTRAINT fk_sprints_project FOREIGN KEY (project_id) REFERENCES projects (id) ON DELETE CASCADE,
    CONSTRAINT fk_sprints_status FOREIGN KEY (status_id) REFERENCES sprint_statuses (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for sprint_tasks
-- ----------------------------
CREATE TABLE sprint_tasks (
    sprint_id int(11) NOT NULL,
    task_id int(11) NOT NULL,
    added_at timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (sprint_id, task_id),
    KEY idx_sprint_tasks_task_id (task_id),
    CONSTRAINT fk_sprint_tasks_sprint FOREIGN KEY (sprint_id) REFERENCES sprints (id) ON DELETE CASCADE,
    CONSTRAINT fk_sprint_tasks_task FOREIGN KEY (task_id) REFERENCES tasks (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;