-- Database Name: project_management_system

-- Table: Companies
CREATE TABLE `companies` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `address` VARCHAR(500),
    `phone` VARCHAR(20),
    `email` VARCHAR(255) UNIQUE NOT NULL,
    `website` VARCHAR(255),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Table: Roles
CREATE TABLE `roles` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `description` VARCHAR(500),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO roles (name, description) VALUES
('admin', 'Full access to all features of the system'),
('manager', 'Can manage projects and tasks'),
('developer', 'Can view and update tasks assigned to them'),
('guest', 'Read-only access to certain parts of the system');

-- Table: Users
CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `company_id` INT NOT NULL,
    `role_id` INT NOT NULL,
    `first_name` VARCHAR(100) NOT NULL,
    `last_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(255) UNIQUE NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `is_active` BOOLEAN DEFAULT FALSE,
    `activation_token` VARCHAR(255),
    `activation_token_expires_at` DATETIME,
    `reset_password_token` VARCHAR(255),
    `reset_password_token_expires_at` DATETIME,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `is_deleted` BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE INDEX idx_users_company_id ON users(company_id);
CREATE INDEX idx_users_role_id ON users(role_id);
CREATE INDEX idx_users_email ON users(email);

-- Table: Permissions
CREATE TABLE `permissions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `description` VARCHAR(500),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO permissions (name, description) VALUES
('view_projects', 'Allows viewing projects'),
('create_projects', 'Allows creating new projects'),
('edit_projects', 'Allows editing existing projects'),
('delete_projects', 'Allows deleting projects'),
('view_tasks', 'Allows viewing tasks'),
('create_tasks', 'Allows creating new tasks'),
('edit_tasks', 'Allows editing tasks'),
('delete_tasks', 'Allows deleting tasks'),
('manage_users', 'Allows managing users'),
('manage_roles', 'Allows managing roles and permissions'),
('manage_companies', 'Allows managing companies'),
('view_dashboard', 'Allows viewing the dashboard');

-- Table: Role_Permissions
CREATE TABLE `role_permissions` (
    `role_id` INT NOT NULL,
    `permission_id` INT NOT NULL,
    PRIMARY KEY (`role_id`, `permission_id`),
    FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`permission_id`) REFERENCES `permissions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Table: Project Statuses
CREATE TABLE `project_statuses` (
    `id` TINYINT AUTO_INCREMENT PRIMARY KEY,
    `status_name` VARCHAR(50) UNIQUE NOT NULL
) ENGINE=InnoDB;

INSERT INTO `project_statuses` (status_name) VALUES
('not_started'),
('in_progress'),
('completed'),
('on_hold');

-- Table: Projects
CREATE TABLE `projects` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `company_id` INT NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `description` VARCHAR(500),
    `start_date` DATE,
    `end_date` DATE,
    `status_id` TINYINT NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`status_id`) REFERENCES `project_statuses`(`id`)
) ENGINE=InnoDB;

CREATE INDEX idx_projects_company_id ON projects(company_id);

-- Table: Tasks
CREATE TABLE `tasks` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `project_id` INT NOT NULL,
    `assigned_to` INT,
    `title` VARCHAR(255) NOT NULL,
    `description` VARCHAR(500),
    `priority` ENUM('low', 'medium', 'high') DEFAULT 'medium',
    `status` ENUM('todo', 'in_progress', 'done') DEFAULT 'todo',
    `estimated_time` INT,
    `time_spent` INT DEFAULT 0,
    `due_date` DATE,
    `is_deleted` BOOLEAN DEFAULT FALSE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`assigned_to`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE INDEX idx_tasks_project_id ON tasks(project_id);

-- Table: Subtasks
CREATE TABLE `subtasks` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `task_id` INT NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `description` VARCHAR(500),
    `status` ENUM('todo', 'in_progress', 'done') DEFAULT 'todo',
    `estimated_time` INT,
    `time_spent` INT DEFAULT 0,
    `is_deleted` BOOLEAN DEFAULT FALSE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`task_id`) REFERENCES `tasks`(`id`)
) ENGINE=InnoDB;

CREATE INDEX idx_subtasks_task_id ON subtasks(task_id);

-- Table: Sessions
CREATE TABLE `sessions` (
    `id` VARCHAR(255) PRIMARY KEY,
    `user_id` INT NOT NULL,
    `ip_address` VARCHAR(45),
    `user_agent` VARCHAR(1024),
    `is_active` BOOLEAN DEFAULT TRUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `last_accessed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE INDEX idx_sessions_user_id ON sessions(user_id);

-- Table: CSRF Tokens
CREATE TABLE `csrf_tokens` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `token` VARCHAR(255) NOT NULL,
    `user_id` INT NOT NULL,
    `expires_at` DATETIME NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE INDEX idx_csrf_tokens_user_id ON csrf_tokens(user_id);
