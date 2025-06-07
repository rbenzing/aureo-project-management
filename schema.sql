SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- --------------------------------------------------------
--
-- First, create all status tables which have no dependencies
--

--
-- Table structure for table `statuses_milestone`
--

DROP TABLE IF EXISTS `statuses_milestone`;
CREATE TABLE IF NOT EXISTS `statuses_milestone` (
  `id` int(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `is_deleted` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `statuses_milestone`
--

INSERT INTO `statuses_milestone` (`id`, `name`, `description`, `is_deleted`, `created_at`, `updated_at`) VALUES
(1, 'Not Started', 'The milestone has not yet been started.', 0, '2025-02-18 02:54:57', '2025-02-18 02:54:57'),
(2, 'In Progress', 'The milestone is currently being worked on.', 0, '2025-02-18 02:54:57', '2025-02-18 02:54:57'),
(3, 'Completed', 'The milestone has been successfully completed.', 0, '2025-02-18 02:54:57', '2025-02-18 02:54:57');

-- --------------------------------------------------------

--
-- Table structure for table `statuses_project`
--

DROP TABLE IF EXISTS `statuses_project`;
CREATE TABLE IF NOT EXISTS `statuses_project` (
  `id` int(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `is_deleted` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `statuses_project`
--

INSERT INTO `statuses_project` (`id`, `name`, `description`, `is_deleted`, `created_at`, `updated_at`) VALUES
(1, 'ready', 'Project is ready to start', 0, '2025-02-18 02:09:24', '2025-02-18 02:09:24'),
(2, 'in_progress', 'Project is in progress', 0, '2025-02-18 02:09:24', '2025-02-18 02:09:24'),
(3, 'completed', 'Project is completed', 0, '2025-02-18 02:09:24', '2025-02-18 02:09:24'),
(4, 'on_hold', 'Project is on hold', 0, '2025-02-18 02:09:24', '2025-02-18 02:09:24'),
(6, 'delayed', 'Project is delayed', 0, '2025-02-28 03:20:04', '2025-02-28 03:20:04'),
(7, 'cancelled', 'Project is cancelled', 0, '2025-02-28 03:20:12', '2025-02-28 03:20:12');

-- --------------------------------------------------------

--
-- Table structure for table `statuses_sprint`
--

DROP TABLE IF EXISTS `statuses_sprint`;
CREATE TABLE IF NOT EXISTS `statuses_sprint` (
  `id` int(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `is_deleted` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `statuses_sprint`
--

INSERT INTO `statuses_sprint` (`id`, `name`, `description`, `is_deleted`, `created_at`, `updated_at`) VALUES
(1, 'planning', 'Sprint is in planning phase', 0, '2025-02-23 23:21:32', '2025-02-23 23:21:32'),
(2, 'active', 'Sprint is currently active', 0, '2025-02-23 23:21:32', '2025-02-23 23:21:32'),
(3, 'completed', 'Sprint has been completed', 0, '2025-02-23 23:21:32', '2025-02-23 23:21:32'),
(4, 'cancelled', 'Sprint was cancelled', 0, '2025-02-23 23:21:32', '2025-02-23 23:21:32'),
(5, 'delayed', 'Sprint has been delayed', 0, '2025-02-23 23:23:05', '2025-02-23 23:23:05');

-- --------------------------------------------------------

--
-- Table structure for table `statuses_task`
--

DROP TABLE IF EXISTS `statuses_task`;
CREATE TABLE IF NOT EXISTS `statuses_task` (
  `id` int(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `is_deleted` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `statuses_task`
--

INSERT INTO `statuses_task` (`id`, `name`, `description`, `is_deleted`, `created_at`, `updated_at`) VALUES
(1, 'open', 'Task is open and ready for work', 0, '2025-02-18 02:10:29', '2025-02-18 02:10:29'),
(2, 'in_progress', 'Task is currently being worked on', 0, '2025-02-18 02:10:29', '2025-02-18 02:10:29'),
(3, 'on_hold', 'Task is temporarily on hold', 0, '2025-02-18 02:10:29', '2025-02-18 02:10:29'),
(4, 'in_review', 'Task is being reviewed', 0, '2025-02-18 02:10:29', '2025-02-18 02:10:29'),
(5, 'closed', 'Task has been closed', 0, '2025-02-18 02:10:29', '2025-02-18 02:10:29'),
(6, 'completed', 'Task has been completed', 0, '2025-02-18 02:10:29', '2025-02-18 02:10:29');

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
CREATE TABLE IF NOT EXISTS `permissions` (
  `id` int(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` varchar(500) DEFAULT NULL,
  `is_deleted` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Add default permissions
--
INSERT INTO `permissions` (`name`, `description`) VALUES
('view_dashboard', 'Can view dashboard'),
('view_projects', 'Can view projects'),
('create_projects', 'Can create projects'),
('edit_projects', 'Can edit projects'),
('delete_projects', 'Can delete projects'),
('manage_projects', 'Can manage all projects'),
('view_tasks', 'Can view tasks'),
('create_tasks', 'Can create tasks'),
('edit_tasks', 'Can edit tasks'),
('delete_tasks', 'Can delete tasks'),
('manage_tasks', 'Can manage all tasks'),
('view_users', 'Can view users'),
('create_users', 'Can create users'),
('edit_users', 'Can edit users'),
('delete_users', 'Can delete users'),
('manage_users', 'Can manage all users'),
('view_roles', 'Can view roles'),
('create_roles', 'Can create roles'),
('edit_roles', 'Can edit roles'),
('delete_roles', 'Can delete roles'),
('manage_roles', 'Can manage all roles'),
('view_companies', 'Can view companies'),
('create_companies', 'Can create companies'),
('edit_companies', 'Can edit companies'),
('delete_companies', 'Can delete companies'),
('manage_companies', 'Can manage all companies'),
('view_milestones', 'Can view milestones'),
('create_milestones', 'Can create milestones'),
('edit_milestones', 'Can edit milestones'),
('delete_milestones', 'Can delete milestones'),
('manage_milestones', 'Can manage all milestones'),
('view_sprints', 'Can view sprints'),
('create_sprints', 'Can create sprints'),
('edit_sprints', 'Can edit sprints'),
('delete_sprints', 'Can delete sprints'),
('manage_sprints', 'Can manage all sprints'),
('view_time_tracking', 'Can view time tracking'),
('create_time_tracking', 'Can create time entries'),
('edit_time_tracking', 'Can edit time entries'),
('delete_time_tracking', 'Can delete time entries'),
('manage_time_tracking', 'Can manage all time tracking'),
('view_templates', 'Can view templates'),
('create_templates', 'Can create templates'),
('edit_templates', 'Can edit templates'),
('delete_templates', 'Can delete templates'),
('manage_templates', 'Can manage all templates'),
('view_settings', 'Can view application settings'),
('manage_settings', 'Can manage application settings');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
CREATE TABLE IF NOT EXISTS `roles` (
  `id` int(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `guid` uuid NOT NULL DEFAULT uuid(),
  `name` varchar(100) NOT NULL,
  `description` varchar(500) DEFAULT NULL,
  `is_deleted` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `guid` (`guid`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Add default roles
--
INSERT INTO `roles` (`name`, `description`) VALUES
('admin', 'Administrator with full access'),
('manager', 'Project manager'),
('developer', 'Developer role'),
('client', 'Client with limited access');

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

DROP TABLE IF EXISTS `role_permissions`;
CREATE TABLE IF NOT EXISTS `role_permissions` (
  `role_id` int(20) UNSIGNED NOT NULL,
  `permission_id` int(20) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`role_id`,`permission_id`),
  KEY `permission_id` (`permission_id`),
  CONSTRAINT `fk_role_permissions_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_role_permissions_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users` (first with minimal constraints)
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `guid` uuid NOT NULL DEFAULT uuid(),
  `company_id` int(20) UNSIGNED DEFAULT NULL,
  `role_id` int(20) UNSIGNED NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `is_active` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `activation_token` varchar(255) DEFAULT NULL,
  `activation_token_expires_at` datetime DEFAULT NULL,
  `reset_password_token` varchar(255) DEFAULT NULL,
  `reset_password_token_expires_at` datetime DEFAULT NULL,
  `is_deleted` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_users_email` (`email`),
  KEY `activation_token` (`activation_token`),
  KEY `reset_password_token` (`reset_password_token`),
  KEY `guid` (`guid`),
  KEY `fk_users_role` (`role_id`),
  KEY `fk_users_company` (`company_id`),
  CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Create initial admin role permissions
--

-- First, get the admin role ID
SET @admin_role_id = (SELECT id FROM roles WHERE name = 'admin' LIMIT 1);

-- Then, add all permissions to the admin role
INSERT INTO role_permissions (role_id, permission_id)
SELECT @admin_role_id, id FROM permissions;

--
-- Dumping data for table `users` with minimal initial data
--

INSERT INTO `users` (`role_id`, `first_name`, `last_name`, `email`, `phone`, `password_hash`, `is_active`, `activation_token`, `activation_token_expires_at`, `reset_password_token`, `reset_password_token_expires_at`, `is_deleted`, `created_at`, `updated_at`) VALUES
(@admin_role_id, 'Russell', 'Benzing', 'rbenzing@gmail.com', NULL, '$argon2id$v=19$m=65536,t=4,p=1$dkcub0FTU2NENTRmdXBSeQ$cAZna9wkkfUbCM5PPRjh3KvEjga+dP56xqnOvfbam0U', 1, NULL, '2025-02-24 20:13:39', NULL, NULL, 0, '2025-02-23 20:13:39', '2025-02-26 01:39:55');

-- --------------------------------------------------------

--
-- Table structure for table `companies`
--

DROP TABLE IF EXISTS `companies`;
CREATE TABLE IF NOT EXISTS `companies` (
  `id` int(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `guid` uuid NOT NULL DEFAULT uuid(),
  `user_id` int(20) UNSIGNED DEFAULT NULL COMMENT 'Owner/creator of the company',
  `name` varchar(255) NOT NULL,
  `address` varchar(500) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `website` varchar(255) DEFAULT NULL,
  `is_deleted` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `guid` (`guid`),
  KEY `fk_companies_user` (`user_id`),
  CONSTRAINT `fk_companies_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Now add the foreign key to users table for company_id
--

ALTER TABLE `users` 
ADD CONSTRAINT `fk_users_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL;

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

DROP TABLE IF EXISTS `projects`;
CREATE TABLE IF NOT EXISTS `projects` (
  `id` int(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `guid` uuid NOT NULL DEFAULT uuid(),
  `company_id` int(20) UNSIGNED NOT NULL COMMENT 'Associated company',
  `owner_id` int(20) UNSIGNED NOT NULL COMMENT 'Project owner/manager',
  `status_id` int(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `key_code` varchar(10) DEFAULT NULL COMMENT 'Project key code (e.g., PMS)',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `is_deleted` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `status_id` (`status_id`),
  KEY `guid` (`guid`),
  KEY `fk_projects_company` (`company_id`),
  KEY `fk_projects_owner` (`owner_id`),
  CONSTRAINT `fk_projects_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  CONSTRAINT `fk_projects_owner` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_projects_status` FOREIGN KEY (`status_id`) REFERENCES `statuses_project` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tasks` (first without parent task constraint)
--

DROP TABLE IF EXISTS `tasks`;
CREATE TABLE IF NOT EXISTS `tasks` (
  `id` int(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `guid` uuid NOT NULL DEFAULT uuid(),
  `project_id` int(20) UNSIGNED NOT NULL COMMENT 'Associated project',
  `assigned_to` int(20) UNSIGNED DEFAULT NULL COMMENT 'Assignee user',
  `parent_task_id` int(20) UNSIGNED DEFAULT NULL COMMENT 'Parent task for subtasks',
  `is_subtask` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `priority` enum('none','low','medium','high') NOT NULL DEFAULT 'none',
  `status_id` int(20) UNSIGNED NOT NULL DEFAULT 1,
  `estimated_time` int(20) DEFAULT NULL COMMENT 'Estimated time in seconds',
  `billable_time` int(20) DEFAULT NULL COMMENT 'Billable time in seconds',
  `time_spent` int(20) UNSIGNED DEFAULT 0 COMMENT 'Time spent in seconds',
  `hourly_rate` mediumint(9) DEFAULT NULL,
  `is_hourly` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `start_date` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `complete_date` date DEFAULT NULL,
  `story_points` tinyint(3) UNSIGNED DEFAULT NULL COMMENT 'Story points for estimation (1-13 Fibonacci)',
  `acceptance_criteria` text DEFAULT NULL COMMENT 'Definition of done criteria',
  `task_type` enum('story','bug','task','epic') NOT NULL DEFAULT 'task' COMMENT 'Scrum task type',
  `backlog_priority` int(10) UNSIGNED DEFAULT NULL COMMENT 'Priority order in product backlog',
  `is_ready_for_sprint` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Ready for sprint planning',
  `is_deleted` tinyint(1) UNSIGNED DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `status_id` (`status_id`),
  KEY `guid` (`guid`),
  KEY `fk_tasks_project` (`project_id`),
  KEY `fk_tasks_assigned_to` (`assigned_to`),
  KEY `fk_tasks_parent` (`parent_task_id`),
  KEY `idx_backlog_priority` (`backlog_priority`),
  KEY `idx_task_type` (`task_type`),
  KEY `idx_ready_for_sprint` (`is_ready_for_sprint`),
  CONSTRAINT `fk_tasks_assigned_to` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_tasks_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_tasks_status` FOREIGN KEY (`status_id`) REFERENCES `statuses_task` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Now add the self-referencing foreign key for parent_task_id
--

ALTER TABLE `tasks` 
ADD CONSTRAINT `fk_tasks_parent` FOREIGN KEY (`parent_task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE;

-- --------------------------------------------------------

--
-- Table structure for table `sprints`
--

DROP TABLE IF EXISTS `sprints`;
CREATE TABLE IF NOT EXISTS `sprints` (
  `id` int(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `guid` uuid NOT NULL DEFAULT uuid(),
  `project_id` int(20) UNSIGNED NOT NULL COMMENT 'Associated project',
  `status_id` int(20) UNSIGNED NOT NULL DEFAULT 1,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `is_deleted` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `status_id` (`status_id`),
  KEY `guid` (`guid`),
  KEY `fk_sprints_project` (`project_id`),
  CONSTRAINT `fk_sprints_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_sprints_status` FOREIGN KEY (`status_id`) REFERENCES `statuses_sprint` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `milestones`
--

DROP TABLE IF EXISTS `milestones`;
CREATE TABLE IF NOT EXISTS `milestones` (
  `id` int(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `guid` uuid NOT NULL DEFAULT uuid(),
  `project_id` int(20) UNSIGNED NOT NULL COMMENT 'Associated project',
  `epic_id` int(20) UNSIGNED DEFAULT NULL COMMENT 'Parent milestone/epic',
  `status_id` int(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `milestone_type` enum('epic','milestone') NOT NULL DEFAULT 'milestone',
  `start_date` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `complete_date` date DEFAULT NULL,
  `is_deleted` tinyint(1) UNSIGNED DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `status_id` (`status_id`),
  KEY `guid` (`guid`),
  KEY `fk_milestones_project` (`project_id`),
  KEY `fk_milestones_epic` (`epic_id`),
  CONSTRAINT `fk_milestones_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_milestones_status` FOREIGN KEY (`status_id`) REFERENCES `statuses_milestone` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Now add the self-referencing foreign key for epic_id
--

ALTER TABLE `milestones` 
ADD CONSTRAINT `fk_milestones_epic` FOREIGN KEY (`epic_id`) REFERENCES `milestones` (`id`) ON DELETE SET NULL;

-- --------------------------------------------------------

--
-- Table structure for table `company_projects`
--

DROP TABLE IF EXISTS `company_projects`;
CREATE TABLE IF NOT EXISTS `company_projects` (
  `company_id` int(20) UNSIGNED NOT NULL,
  `project_id` int(20) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`company_id`,`project_id`),
  KEY `company_id` (`company_id`,`project_id`),
  KEY `fk_company_projects_project` (`project_id`),
  CONSTRAINT `fk_company_projects_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_company_projects_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `milestone_tasks`
--

DROP TABLE IF EXISTS `milestone_tasks`;
CREATE TABLE IF NOT EXISTS `milestone_tasks` (
  `milestone_id` int(20) UNSIGNED NOT NULL,
  `task_id` int(20) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`milestone_id`,`task_id`),
  KEY `milestone_id` (`milestone_id`,`task_id`),
  KEY `fk_milestone_tasks_task` (`task_id`),
  CONSTRAINT `fk_milestone_tasks_milestone` FOREIGN KEY (`milestone_id`) REFERENCES `milestones` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_milestone_tasks_task` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sprint_tasks`
--

DROP TABLE IF EXISTS `sprint_tasks`;
CREATE TABLE IF NOT EXISTS `sprint_tasks` (
  `sprint_id` int(20) UNSIGNED NOT NULL,
  `task_id` int(20) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`sprint_id`,`task_id`),
  KEY `idx_sprint_tasks_task_id` (`task_id`),
  KEY `sprint_id` (`sprint_id`),
  CONSTRAINT `fk_sprint_tasks_sprint` FOREIGN KEY (`sprint_id`) REFERENCES `sprints` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_sprint_tasks_task` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `csrf_tokens`
--

DROP TABLE IF EXISTS `csrf_tokens`;
CREATE TABLE IF NOT EXISTS `csrf_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `token` varchar(255) NOT NULL,
  `session_id` varchar(255) NOT NULL,
  `user_id` int(20) UNSIGNED DEFAULT NULL,
  `expires_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `session_id` (`session_id`,`user_id`),
  KEY `token` (`token`),
  KEY `fk_csrf_tokens_user` (`user_id`),
  CONSTRAINT `fk_csrf_tokens_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` int(20) UNSIGNED DEFAULT NULL,
  `data` text NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(1024) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL,
  `last_accessed_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_sessions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

DROP TABLE IF EXISTS `activity_logs`;
CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(20) UNSIGNED DEFAULT NULL,
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
  KEY `idx_session_id` (`session_id`),
  KEY `idx_event_type` (`event_type`),
  KEY `idx_created_at` (`created_at`),
  KEY `fk_activity_logs_user` (`user_id`),
  CONSTRAINT `fk_activity_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_companies`
--

DROP TABLE IF EXISTS `user_companies`;
CREATE TABLE IF NOT EXISTS `user_companies` (
  `user_id` int(20) UNSIGNED NOT NULL,
  `company_id` int(20) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`user_id`,`company_id`),
  KEY `user_id` (`user_id`,`company_id`),
  KEY `fk_user_companies_company` (`company_id`),
  CONSTRAINT `fk_user_companies_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_user_companies_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_projects`
--

DROP TABLE IF EXISTS `user_projects`;
CREATE TABLE IF NOT EXISTS `user_projects` (
  `user_id` int(20) UNSIGNED NOT NULL,
  `project_id` int(20) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`user_id`,`project_id`),
  KEY `user_id` (`user_id`,`project_id`),
  KEY `fk_user_projects_project` (`project_id`),
  CONSTRAINT `fk_user_projects_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_user_projects_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `time_entries`
--

DROP TABLE IF EXISTS `time_entries`;
CREATE TABLE IF NOT EXISTS `time_entries` (
  `id` int(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `task_id` int(20) UNSIGNED NOT NULL,
  `user_id` int(20) UNSIGNED NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime DEFAULT NULL,
  `duration` int(20) UNSIGNED DEFAULT NULL COMMENT 'Duration in seconds',
  `notes` text DEFAULT NULL,
  `is_billable` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_time_entries_task` (`task_id`),
  KEY `fk_time_entries_user` (`user_id`),
  CONSTRAINT `fk_time_entries_task` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_time_entries_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `task_comments`
--

DROP TABLE IF EXISTS `task_comments`;
CREATE TABLE IF NOT EXISTS `task_comments` (
  `id` int(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `task_id` int(20) UNSIGNED NOT NULL,
  `user_id` int(20) UNSIGNED NOT NULL,
  `content` text NOT NULL,
  `is_deleted` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_task_comments_task` (`task_id`),
  KEY `fk_task_comments_user` (`user_id`),
  CONSTRAINT `fk_task_comments_task` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_task_comments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for task audit logs
--

DROP TABLE IF EXISTS `task_history`;
CREATE TABLE IF NOT EXISTS `task_history` (
  `id` int(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `task_id` int(20) UNSIGNED NOT NULL,
  `user_id` int(20) UNSIGNED NOT NULL,
  `action` varchar(50) NOT NULL COMMENT 'create, update, status_change, assignment, etc.',
  `field_changed` varchar(50) DEFAULT NULL,
  `old_value` text DEFAULT NULL,
  `new_value` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_task_history_task` (`task_id`),
  KEY `fk_task_history_user` (`user_id`),
  CONSTRAINT `fk_task_history_task` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_task_history_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for project templates
--

DROP TABLE IF EXISTS `templates`;
CREATE TABLE IF NOT EXISTS `templates` (
  `id` int(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `guid` uuid NOT NULL DEFAULT uuid(),
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `template_type` enum('project','task','milestone','sprint') NOT NULL DEFAULT 'project',
  `company_id` int(20) UNSIGNED DEFAULT NULL COMMENT 'Can be organization-specific or null for global',
  `is_default` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `is_deleted` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `guid` (`guid`),
  KEY `template_type` (`template_type`),
  KEY `fk_templates_company` (`company_id`),
  CONSTRAINT `fk_templates_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `category` varchar(50) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_setting` (`category`, `setting_key`),
  KEY `idx_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Insert default settings
--
INSERT INTO `settings` (`category`, `setting_key`, `setting_value`, `description`) VALUES
('time_intervals', 'time_unit', 'minutes', 'Default time unit for time tracking (minutes, seconds, hours, days)'),
('time_intervals', 'time_precision', '15', 'Time precision/increment for time inputs'),
('projects', 'default_task_type', 'task', 'Default task type when creating new tasks'),
('projects', 'auto_assign_creator', '1', 'Automatically assign task creator as assignee'),
('projects', 'require_project_for_tasks', '0', 'Require project selection when creating tasks'),
('tasks', 'default_priority', 'medium', 'Default priority for new tasks'),
('tasks', 'auto_estimate_enabled', '0', 'Enable automatic time estimation based on similar tasks'),
('tasks', 'story_points_enabled', '1', 'Enable story points for agile estimation'),
('milestones', 'auto_create_from_sprints', '0', 'Automatically create milestones from sprint completions'),
('milestones', 'milestone_notification_days', '7', 'Days before milestone due date to send notifications'),
('sprints', 'default_sprint_length', '14', 'Default sprint length in days'),
('sprints', 'auto_start_next_sprint', '0', 'Automatically start next sprint when current ends'),
('sprints', 'sprint_planning_enabled', '1', 'Enable sprint planning features');

-- --------------------------------------------------------

--
-- Add indexes for searching and performance
--

-- Add full text search indexes
ALTER TABLE tasks ADD FULLTEXT INDEX ft_task_title_description (title, description);
ALTER TABLE projects ADD FULLTEXT INDEX ft_project_name_description (name, description);
ALTER TABLE milestones ADD FULLTEXT INDEX ft_milestone_title_description (title, description);

-- Add indexes for common queries
ALTER TABLE tasks ADD INDEX idx_tasks_due_date (due_date);
ALTER TABLE tasks ADD INDEX idx_tasks_priority_status (priority, status_id);
ALTER TABLE tasks ADD INDEX idx_tasks_assigned_status (assigned_to, status_id);
ALTER TABLE projects ADD INDEX idx_projects_dates (start_date, end_date);
ALTER TABLE sprints ADD INDEX idx_sprints_dates (start_date, end_date);
ALTER TABLE users ADD INDEX idx_users_fullname (first_name, last_name);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;