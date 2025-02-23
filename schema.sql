SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


CREATE TABLE companies (
  id int(11) NOT NULL,
  name varchar(255) NOT NULL,
  user_id int(11) DEFAULT NULL,
  address varchar(500) DEFAULT NULL,
  phone varchar(25) DEFAULT NULL,
  email varchar(255) NOT NULL,
  website varchar(255) DEFAULT NULL,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  updated_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  is_deleted tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE csrf_tokens (
  id int(11) NOT NULL,
  token varchar(255) NOT NULL,
  user_id int(11) NOT NULL,
  expires_at datetime NOT NULL,
  created_at timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE milestones (
  id int(11) NOT NULL,
  title varchar(255) NOT NULL,
  description varchar(255) DEFAULT NULL,
  due_date date DEFAULT NULL,
  complete_date date DEFAULT NULL,
  status_id int(11) NOT NULL,
  project_id int(11) NOT NULL,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  updated_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  is_deleted tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE milestone_statuses (
  id int(11) NOT NULL,
  name varchar(255) NOT NULL,
  description varchar(255) DEFAULT NULL,
  is_deleted tinyint(1) NOT NULL DEFAULT 0,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  updated_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO milestone_statuses (id, name, description, is_deleted, created_at, updated_at) VALUES
(1, 'Not Started', 'The milestone has not yet been started.', 0, '2025-02-18 02:54:57', '2025-02-18 02:54:57'),
(2, 'In Progress', 'The milestone is currently being worked on.', 0, '2025-02-18 02:54:57', '2025-02-18 02:54:57'),
(3, 'Completed', 'The milestone has been successfully completed.', 0, '2025-02-18 02:54:57', '2025-02-18 02:54:57');

CREATE TABLE permissions (
  id int(11) NOT NULL,
  name varchar(100) NOT NULL,
  description varchar(500) DEFAULT NULL,
  created_at timestamp NULL DEFAULT current_timestamp(),
  updated_at timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO permissions (id, name, description, created_at, updated_at) VALUES
(1, 'view_projects', 'Allows viewing projects', '2025-02-15 23:26:59', '2025-02-15 23:26:59'),
(2, 'create_projects', 'Allows creating new projects', '2025-02-15 23:26:59', '2025-02-15 23:26:59'),
(3, 'edit_projects', 'Allows editing existing projects', '2025-02-15 23:26:59', '2025-02-15 23:26:59'),
(4, 'delete_projects', 'Allows deleting projects', '2025-02-15 23:26:59', '2025-02-15 23:26:59'),
(5, 'view_tasks', 'Allows viewing tasks', '2025-02-15 23:26:59', '2025-02-15 23:26:59'),
(6, 'create_tasks', 'Allows creating new tasks', '2025-02-15 23:26:59', '2025-02-15 23:26:59'),
(7, 'edit_tasks', 'Allows editing tasks', '2025-02-15 23:26:59', '2025-02-15 23:26:59'),
(8, 'delete_tasks', 'Allows deleting tasks', '2025-02-15 23:26:59', '2025-02-15 23:26:59'),
(9, 'manage_users', 'Allows managing users', '2025-02-15 23:26:59', '2025-02-15 23:26:59'),
(10, 'manage_roles', 'Allows managing roles and permissions', '2025-02-15 23:26:59', '2025-02-15 23:26:59'),
(11, 'manage_companies', 'Allows managing companies', '2025-02-15 23:26:59', '2025-02-15 23:26:59'),
(12, 'view_dashboard', 'Allows viewing the dashboard', '2025-02-15 23:26:59', '2025-02-15 23:26:59'),
(13, 'view_users', 'Allows viewing users', '2025-02-19 01:10:52', '2025-02-19 01:12:14'),
(14, 'create_users', 'Allows creating new users', '2025-02-19 01:11:00', '2025-02-19 01:12:24'),
(15, 'delete_users', 'Allows deleting users', '2025-02-19 01:11:08', '2025-02-19 01:12:30'),
(16, 'edit_users', 'Allows editing users', '2025-02-19 01:11:47', '2025-02-19 01:12:41');

CREATE TABLE projects (
  id int(11) NOT NULL,
  company_id int(11) NOT NULL,
  owner_id int(11) NOT NULL,
  name varchar(255) NOT NULL,
  description varchar(500) DEFAULT NULL,
  start_date date DEFAULT NULL,
  end_date date DEFAULT NULL,
  status_id tinyint(4) NOT NULL DEFAULT 1,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  updated_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  is_deleted tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE project_statuses (
  id tinyint(4) NOT NULL,
  name varchar(50) NOT NULL,
  description varchar(255) DEFAULT NULL,
  is_deleted tinyint(1) NOT NULL DEFAULT 0,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  updated_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO project_statuses (id, name, description, is_deleted, created_at, updated_at) VALUES
(1, 'ready', '', 0, '2025-02-18 02:09:24', '2025-02-18 02:09:24'),
(2, 'in_progress', '', 0, '2025-02-18 02:09:24', '2025-02-18 02:09:24'),
(3, 'completed', '', 0, '2025-02-18 02:09:24', '2025-02-18 02:09:24'),
(4, 'on_hold', '', 0, '2025-02-18 02:09:24', '2025-02-18 02:09:24');

CREATE TABLE roles (
  id int(11) NOT NULL,
  name varchar(100) NOT NULL,
  description varchar(500) DEFAULT NULL,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  updated_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  is_deleted tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE role_permissions (
  role_id int(11) NOT NULL,
  permission_id int(11) NOT NULL
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
(1, 16);

CREATE TABLE sessions (
  id varchar(255) NOT NULL,
  user_id int(11) NOT NULL,
  data text NOT NULL,
  ip_address varchar(45) DEFAULT NULL,
  user_agent varchar(1024) DEFAULT NULL,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  expires_at timestamp NULL DEFAULT NULL,
  last_accessed_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tasks (
  id int(11) NOT NULL,
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
  parent_task_id int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE task_statuses (
  id tinyint(4) NOT NULL,
  name varchar(50) NOT NULL,
  description varchar(255) DEFAULT NULL,
  is_deleted tinyint(1) NOT NULL DEFAULT 0,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  updated_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO task_statuses (id, name, description, is_deleted, created_at, updated_at) VALUES
(1, 'open', NULL, 0, '2025-02-18 02:10:29', '2025-02-18 02:10:29'),
(2, 'in_progress', NULL, 0, '2025-02-18 02:10:29', '2025-02-18 02:10:29'),
(3, 'on_hold', NULL, 0, '2025-02-18 02:10:29', '2025-02-18 02:10:29'),
(4, 'in_review', NULL, 0, '2025-02-18 02:10:29', '2025-02-18 02:10:29'),
(5, 'closed', NULL, 0, '2025-02-18 02:10:29', '2025-02-18 02:10:29'),
(6, 'completed', NULL, 0, '2025-02-18 02:10:29', '2025-02-18 02:10:29');

CREATE TABLE users (
  id int(11) NOT NULL,
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
  is_deleted tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

ALTER TABLE companies
  ADD PRIMARY KEY (id),
  ADD UNIQUE KEY email (email),
  ADD KEY user_id (user_id);

ALTER TABLE csrf_tokens
  ADD PRIMARY KEY (id),
  ADD KEY idx_csrf_tokens_user_id (user_id);

ALTER TABLE milestones
  ADD PRIMARY KEY (id),
  ADD KEY project_id (project_id),
  ADD KEY status_id (status_id);

ALTER TABLE milestone_statuses
  ADD PRIMARY KEY (id),
  ADD UNIQUE KEY name (name) USING HASH;

ALTER TABLE permissions
  ADD PRIMARY KEY (id);

ALTER TABLE projects
  ADD PRIMARY KEY (id),
  ADD KEY status_id (status_id),
  ADD KEY idx_projects_company_id (company_id),
  ADD KEY owner_id (owner_id);

ALTER TABLE project_statuses
  ADD PRIMARY KEY (id),
  ADD UNIQUE KEY name (name) USING BTREE;

ALTER TABLE roles
  ADD PRIMARY KEY (id);

ALTER TABLE role_permissions
  ADD PRIMARY KEY (role_id,permission_id),
  ADD KEY permission_id (permission_id);

ALTER TABLE sessions
  ADD PRIMARY KEY (id),
  ADD KEY idx_sessions_user_id (user_id);

ALTER TABLE tasks
  ADD PRIMARY KEY (id),
  ADD KEY assigned_to (assigned_to),
  ADD KEY idx_tasks_project_id (project_id),
  ADD KEY fk_parent_task (parent_task_id);

ALTER TABLE task_statuses
  ADD PRIMARY KEY (id),
  ADD UNIQUE KEY name (name) USING BTREE;

ALTER TABLE users
  ADD PRIMARY KEY (id),
  ADD UNIQUE KEY email (email),
  ADD KEY idx_users_company_id (company_id),
  ADD KEY idx_users_role_id (role_id),
  ADD KEY idx_users_email (email),
  ADD KEY activation_token (activation_token),
  ADD KEY reset_password_token (reset_password_token);


ALTER TABLE companies
  MODIFY id int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE csrf_tokens
  MODIFY id int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE milestones
  MODIFY id int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE milestone_statuses
  MODIFY id int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE permissions
  MODIFY id int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE projects
  MODIFY id int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE project_statuses
  MODIFY id tinyint(4) NOT NULL AUTO_INCREMENT;

ALTER TABLE roles
  MODIFY id int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE tasks
  MODIFY id int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE task_statuses
  MODIFY id tinyint(4) NOT NULL AUTO_INCREMENT;

ALTER TABLE users
  MODIFY id int(11) NOT NULL AUTO_INCREMENT;


ALTER TABLE csrf_tokens
  ADD CONSTRAINT csrf_tokens_ibfk_1 FOREIGN KEY (user_id) REFERENCES `users` (id) ON DELETE CASCADE;

ALTER TABLE projects
  ADD CONSTRAINT projects_ibfk_1 FOREIGN KEY (company_id) REFERENCES companies (id) ON DELETE CASCADE,
  ADD CONSTRAINT projects_ibfk_2 FOREIGN KEY (status_id) REFERENCES project_statuses (id);

ALTER TABLE role_permissions
  ADD CONSTRAINT role_permissions_ibfk_1 FOREIGN KEY (role_id) REFERENCES `roles` (id) ON DELETE CASCADE,
  ADD CONSTRAINT role_permissions_ibfk_2 FOREIGN KEY (permission_id) REFERENCES permissions (id) ON DELETE CASCADE;

ALTER TABLE sessions
  ADD CONSTRAINT sessions_ibfk_1 FOREIGN KEY (user_id) REFERENCES `users` (id) ON DELETE CASCADE;

ALTER TABLE tasks
  ADD CONSTRAINT fk_parent_task FOREIGN KEY (parent_task_id) REFERENCES tasks (id) ON DELETE CASCADE,
  ADD CONSTRAINT tasks_ibfk_1 FOREIGN KEY (project_id) REFERENCES projects (id) ON DELETE CASCADE,
  ADD CONSTRAINT tasks_ibfk_2 FOREIGN KEY (assigned_to) REFERENCES `users` (id) ON DELETE SET NULL;

ALTER TABLE users
  ADD CONSTRAINT users_ibfk_1 FOREIGN KEY (company_id) REFERENCES companies (id) ON DELETE CASCADE,
  ADD CONSTRAINT users_ibfk_2 FOREIGN KEY (role_id) REFERENCES `roles` (id) ON DELETE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
