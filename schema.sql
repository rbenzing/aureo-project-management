-- Database Name: project_management_system

-- Table: Companies
CREATE TABLE `companies` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `address` VARCHAR(500),  -- Changed from TEXT to VARCHAR
    `phone` VARCHAR(20),
    `email` VARCHAR(255) UNIQUE NOT NULL,
    `website` VARCHAR(255),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table: Users
CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `company_id` INT NOT NULL,
    `role_id` INT NOT NULL,
    `first_name` VARCHAR(100) NOT NULL,  -- Reduced size from 255
    `last_name` VARCHAR(100) NOT NULL,   -- Reduced size from 255
    `email` VARCHAR(255) UNIQUE NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL, -- Ensure strong hashing
    `is_active` BOOLEAN DEFAULT FALSE,
    `activation_token` VARCHAR(255),  -- Should be hashed & expire
    `activation_token_expires_at` DATETIME, -- Token expiry
    `reset_password_token` VARCHAR(255),  -- Should be hashed & expire
    `reset_password_token_expires_at` DATETIME, -- Token expiry
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `is_deleted` BOOLEAN DEFAULT FALSE, -- Soft delete
    FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`),
    FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`)
);

-- Indexes for better performance
CREATE INDEX idx_users_company_id ON users(company_id);
CREATE INDEX idx_users_role_id ON users(role_id);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_activation_token ON users(activation_token);
CREATE INDEX idx_users_reset_password_token ON users(reset_password_token);

-- Table: Roles
CREATE TABLE `roles` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,  -- Reduced from 255
    `description` VARCHAR(500),  -- Changed from TEXT
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO roles (name, description) VALUES
('admin', 'Full access to all features of the system'),
('manager', 'Can manage projects and tasks'),
('developer', 'Can view and update tasks assigned to them'),
('guest', 'Read-only access to certain parts of the system');

-- Table: Permissions
CREATE TABLE `permissions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `description` VARCHAR(500),  -- Changed from TEXT
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

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
('manage_roles', 'Allows managing roles and permissions')
('manage_companies', 'Allows managing companies'),
('view_dashboard', 'Allows viewing the dashboard');

-- Table: Role_Permissions (Many-to-Many Relationship)
CREATE TABLE `role_permissions` (
    `role_id` INT NOT NULL,
    `permission_id` INT NOT NULL,
    PRIMARY KEY (`role_id`, `permission_id`),
    FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`permission_id`) REFERENCES `permissions`(`id`) ON DELETE CASCADE
);

-- Admin Role Permissions
INSERT INTO role_permissions (role_id, permission_id)
SELECT 
    (SELECT id FROM roles WHERE name = 'admin') AS role_id,
    id AS permission_id
FROM permissions;

-- Manager Role Permissions
INSERT INTO role_permissions (role_id, permission_id)
SELECT 
    (SELECT id FROM roles WHERE name = 'manager') AS role_id,
    id AS permission_id
FROM permissions
WHERE name IN (
    'view_projects', 'create_projects', 'edit_projects', 'delete_projects',
    'view_tasks', 'create_tasks', 'edit_tasks', 'delete_tasks'
);

-- Developer Role Permissions
INSERT INTO role_permissions (role_id, permission_id)
SELECT 
    (SELECT id FROM roles WHERE name = 'developer') AS role_id,
    id AS permission_id
FROM permissions
WHERE name IN ('view_projects', 'view_tasks', 'edit_tasks');

-- Guest Role Permissions
INSERT INTO role_permissions (role_id, permission_id)
SELECT 
    (SELECT id FROM roles WHERE name = 'guest') AS role_id,
    id AS permission_id
FROM permissions
WHERE name IN ('view_projects', 'view_tasks');

-- Table: Project Statuses (Lookup Table for ENUM replacement)
CREATE TABLE `project_statuses` (
    `id` TINYINT AUTO_INCREMENT PRIMARY KEY,
    `status_name` VARCHAR(50) UNIQUE NOT NULL
);

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
    `description` VARCHAR(500), -- Changed from TEXT
    `start_date` DATE,
    `end_date` DATE,
    `status_id` TINYINT NOT NULL DEFAULT 1, -- Using lookup table instead of ENUM
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`),
    FOREIGN KEY (`status_id`) REFERENCES `project_statuses`(`id`)
);

CREATE INDEX idx_projects_company_id ON projects(company_id);

-- Table: Tasks
CREATE TABLE `tasks` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `project_id` INT NOT NULL,
    `assigned_to` INT, -- Nullable if the task is unassigned
    `title` VARCHAR(255) NOT NULL,
    `description` VARCHAR(500), -- Changed from TEXT
    `priority` ENUM('low', 'medium', 'high') DEFAULT 'medium',
    `status` ENUM('todo', 'in_progress', 'done') DEFAULT 'todo',
    `estimated_time` INT, -- Estimated time in minutes
    `time_spent` INT DEFAULT 0, -- Total time spent in minutes (calculated from time_tracking)
    `due_date` DATE,
    `is_deleted` BOOLEAN DEFAULT FALSE, -- Soft delete
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`),
    FOREIGN KEY (`assigned_to`) REFERENCES `users`(`id`) ON DELETE SET NULL
);

CREATE INDEX idx_tasks_project_id ON tasks(project_id);
CREATE INDEX idx_tasks_assigned_to ON tasks(assigned_to);

-- Table: Subtasks
CREATE TABLE `subtasks` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `task_id` INT NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `description` VARCHAR(500), -- Changed from TEXT
    `status` ENUM('todo', 'in_progress', 'done') DEFAULT 'todo',
    `estimated_time` INT, -- in minutes
    `time_spent` INT DEFAULT 0, -- in minutes
    `is_deleted` BOOLEAN DEFAULT FALSE, -- Soft delete
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`task_id`) REFERENCES `tasks`(`id`)
);

CREATE INDEX idx_subtasks_task_id ON subtasks(task_id);

-- Table: Time Tracking
CREATE TABLE `time_tracking` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `task_id` INT, -- Nullable if tracking time for a subtask
    `subtask_id` INT, -- Nullable if tracking time for a task
    `start_time` DATETIME NOT NULL,
    `end_time` DATETIME,
    `duration` INT GENERATED ALWAYS AS (TIMESTAMPDIFF(MINUTE, start_time, end_time)) STORED, -- Auto-calculate duration
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`),
    FOREIGN KEY (`task_id`) REFERENCES `tasks`(`id`),
    FOREIGN KEY (`subtask_id`) REFERENCES `subtasks`(`id`)
);

CREATE INDEX idx_time_tracking_user_id ON time_tracking(user_id);
CREATE INDEX idx_time_tracking_task_id ON time_tracking(task_id);
CREATE INDEX idx_time_tracking_subtask_id ON time_tracking(subtask_id);

-- Table: Sessions
CREATE TABLE `sessions` (
    `id` VARCHAR(255) PRIMARY KEY,
    `user_id` INT NOT NULL,
    `ip_address` VARCHAR(45),
    `user_agent` VARCHAR(1024),  -- Changed from TEXT
    `is_active` BOOLEAN DEFAULT TRUE, -- Track active sessions
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `last_accessed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, -- Tracks last access
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
);

CREATE INDEX idx_sessions_user_id ON sessions(user_id);

-- Table: CSRF Tokens
CREATE TABLE `csrf_tokens` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `token` VARCHAR(255) NOT NULL,
    `user_id` INT NOT NULL,
    `expires_at` DATETIME NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
);

CREATE INDEX idx_csrf_tokens_user_id ON csrf_tokens(user_id);