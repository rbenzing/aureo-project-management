SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pms`
--

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
  `entity_type` varchar(50) DEFAULT NULL COMMENT 'Type of entity (project, task, user, etc.)',
  `entity_id` int(20) UNSIGNED DEFAULT NULL COMMENT 'ID of the related entity',
  `method` varchar(10) NOT NULL,
  `path` varchar(255) NOT NULL,
  `query_string` text DEFAULT NULL,
  `referer` varchar(255) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) NOT NULL,
  `request_data` longtext DEFAULT NULL CHECK (json_valid(`request_data`)),
  `description` text DEFAULT NULL COMMENT 'Human-readable description of the activity',
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Additional structured data about the activity' CHECK (json_valid(`metadata`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_session_id` (`session_id`),
  KEY `idx_event_type` (`event_type`),
  KEY `idx_created_at` (`created_at`),
  KEY `fk_activity_logs_user` (`user_id`),
  KEY `idx_entity` (`entity_type`,`entity_id`),
  KEY `idx_user_created` (`user_id`,`created_at`),
  KEY `idx_user_event_created` (`user_id`,`event_type`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  KEY `fk_companies_user` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  KEY `fk_company_projects_project` (`project_id`)
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
  KEY `fk_csrf_tokens_user` (`user_id`)
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
  KEY `fk_milestones_epic` (`epic_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  KEY `fk_milestone_tasks_task` (`task_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `description`, `is_deleted`, `created_at`, `updated_at`) VALUES
(1, 'view_dashboard', 'Can view dashboard', 0, '2025-03-07 03:24:39', '2025-03-07 03:24:39'),
(2, 'view_projects', 'Can view projects', 0, '2025-03-07 03:24:39', '2025-03-07 03:24:39'),
(3, 'create_projects', 'Can create projects', 0, '2025-03-07 03:24:39', '2025-03-07 03:24:39'),
(4, 'edit_projects', 'Can edit projects', 0, '2025-03-07 03:24:39', '2025-03-07 03:24:39'),
(5, 'delete_projects', 'Can delete projects', 0, '2025-03-07 03:24:39', '2025-03-07 03:24:39'),
(6, 'manage_projects', 'Can manage all projects', 0, '2025-03-07 03:24:39', '2025-03-07 03:24:39'),
(7, 'view_tasks', 'Can view tasks', 0, '2025-03-07 03:24:39', '2025-03-07 03:24:39'),
(8, 'create_tasks', 'Can create tasks', 0, '2025-03-07 03:24:39', '2025-03-07 03:24:39'),
(9, 'edit_tasks', 'Can edit tasks', 0, '2025-03-07 03:24:39', '2025-03-07 03:24:39'),
(10, 'delete_tasks', 'Can delete tasks', 0, '2025-03-07 03:24:39', '2025-03-07 03:24:39'),
(11, 'manage_tasks', 'Can manage all tasks', 0, '2025-03-07 03:24:39', '2025-03-07 03:24:39'),
(12, 'view_users', 'Can view users', 0, '2025-03-07 03:24:39', '2025-03-07 03:24:39'),
(13, 'create_users', 'Can create users', 0, '2025-03-07 03:24:39', '2025-03-07 03:24:39'),
(14, 'edit_users', 'Can edit users', 0, '2025-03-07 03:24:39', '2025-03-07 03:24:39'),
(15, 'delete_users', 'Can delete users', 0, '2025-03-07 03:24:39', '2025-03-07 03:24:39'),
(16, 'manage_users', 'Can manage all users', 0, '2025-03-07 03:24:39', '2025-03-07 03:24:39'),
(17, 'view_roles', 'Can view roles', 0, '2025-03-07 03:24:39', '2025-03-07 03:24:39'),
(18, 'create_roles', 'Can create roles', 0, '2025-03-07 03:24:39', '2025-03-07 03:24:39'),
(19, 'edit_roles', 'Can edit roles', 0, '2025-03-07 03:24:39', '2025-03-07 03:24:39'),
(20, 'delete_roles', 'Can delete roles', 0, '2025-03-07 03:24:39', '2025-03-07 03:24:39'),
(21, 'manage_roles', 'Can manage all roles', 0, '2025-03-07 03:24:39', '2025-03-07 03:24:39'),
(22, 'view_companies', 'Can view companies', 0, '2025-03-07 03:24:39', '2025-03-07 03:24:39'),
(23, 'create_companies', 'Can create companies', 0, '2025-03-07 03:24:39', '2025-03-07 03:24:39'),
(24, 'edit_companies', 'Can edit companies', 0, '2025-03-07 03:24:39', '2025-03-07 03:24:39'),
(25, 'delete_companies', 'Can delete companies', 0, '2025-03-07 03:24:39', '2025-03-07 03:24:39'),
(26, 'manage_companies', 'Can manage all companies', 0, '2025-03-07 03:24:39', '2025-03-07 03:24:39'),
(27, 'view_milestones', 'Can view milestones', 0, '2025-03-07 03:24:39', '2025-03-07 03:24:39'),
(28, 'create_milestones', 'Can create milestones', 0, '2025-03-07 03:24:39', '2025-03-07 03:24:39'),
(29, 'edit_milestones', 'Can edit milestones', 0, '2025-03-07 03:24:39', '2025-03-07 03:24:39'),
(30, 'delete_milestones', 'Can delete milestones', 0, '2025-03-07 03:24:39', '2025-03-07 03:24:39'),
(31, 'manage_milestones', 'Can manage all milestones', 0, '2025-03-07 03:24:39', '2025-03-07 03:24:39'),
(32, 'view_sprints', 'Can view sprints', 0, '2025-03-07 03:24:39', '2025-03-07 03:24:39'),
(33, 'create_sprints', 'Can create sprints', 0, '2025-03-07 03:24:39', '2025-03-07 03:24:39'),
(34, 'edit_sprints', 'Can edit sprints', 0, '2025-03-07 03:24:39', '2025-03-07 03:24:39'),
(35, 'delete_sprints', 'Can delete sprints', 0, '2025-03-07 03:24:39', '2025-03-07 03:24:39'),
(36, 'manage_sprints', 'Can manage all sprints', 0, '2025-03-07 03:24:39', '2025-03-07 03:24:39'),
(37, 'view_templates', 'Can view templates', 0, '2025-03-09 16:20:43', '2025-06-07 20:28:33'),
(38, 'create_templates', 'Can create templates', 0, '2025-03-09 16:20:43', '2025-06-07 20:28:33'),
(39, 'edit_templates', 'Can edit templates', 0, '2025-03-09 16:20:43', '2025-06-07 20:28:33'),
(40, 'delete_templates', 'Can delete templates', 0, '2025-03-09 16:20:43', '2025-06-07 20:28:01'),
(41, 'manage_templates', 'Can manage all templates', 0, '2025-03-09 16:20:43', '2025-06-07 20:28:33'),
(42, 'view_settings', 'Can view application settings', 0, '2025-06-07 19:47:18', '2025-06-07 19:47:18'),
(43, 'manage_settings', 'Can manage application settings', 0, '2025-06-07 19:47:18', '2025-06-07 19:47:18'),
(44, 'view_time_tracking', 'Can view time tracking', 0, '2025-06-08 03:02:13', '2025-06-08 03:02:13'),
(45, 'create_time_tracking', 'Can create time entries', 0, '2025-06-08 03:02:13', '2025-06-08 03:02:13'),
(46, 'edit_time_tracking', 'Can edit time entries', 0, '2025-06-08 03:02:13', '2025-06-08 03:02:13'),
(47, 'delete_time_tracking', 'Can delete time entries', 0, '2025-06-08 03:02:13', '2025-06-08 03:02:13'),
(48, 'manage_time_tracking', 'Can manage all time tracking', 0, '2025-06-08 03:02:13', '2025-06-08 03:02:13'),
(49, 'manage_sprint_settings', 'Manage sprint configuration settings', 0, '2025-06-19 02:08:21', '2025-06-19 02:08:21'),
(50, 'manage_task_settings', 'Manage task configuration settings', 0, '2025-06-19 02:08:21', '2025-06-19 02:08:21'),
(51, 'manage_milestone_settings', 'Manage milestone configuration settings', 0, '2025-06-19 02:08:21', '2025-06-19 02:08:21'),
(52, 'manage_project_settings', 'Manage project configuration settings', 0, '2025-06-19 02:08:21', '2025-06-19 02:08:21'),
(53, 'edit_settings', 'Edit general application settings', 0, '2025-06-19 02:08:21', '2025-06-19 02:08:21'),
(54, 'edit_security_settings', 'Edit security configuration settings', 0, '2025-06-19 02:08:21', '2025-06-19 02:08:21'),
(55, 'view_activity', 'Can view activity logs and audit trail', 0, '2025-07-24 21:54:19', '2025-07-24 21:54:19');

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
  KEY `idx_projects_dates` (`start_date`,`end_date`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `guid`, `name`, `description`, `is_deleted`) VALUES
(1, 'b4058df2-fb03-11ef-99ad-e454e8e51d1c', 'admin', 'Administrator with full access', 0),

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
  KEY `permission_id` (`permission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`role_id`, `permission_id`, `created_at`) VALUES
(1, 1, '2025-07-24 18:00:16'),
(1, 2, '2025-07-24 18:00:16'),
(1, 3, '2025-07-24 18:00:16'),
(1, 4, '2025-07-24 18:00:16'),
(1, 5, '2025-07-24 18:00:16'),
(1, 6, '2025-07-24 18:00:16'),
(1, 7, '2025-07-24 18:00:16'),
(1, 8, '2025-07-24 18:00:16'),
(1, 9, '2025-07-24 18:00:16'),
(1, 10, '2025-07-24 18:00:16'),
(1, 11, '2025-07-24 18:00:16'),
(1, 12, '2025-07-24 18:00:16'),
(1, 13, '2025-07-24 18:00:16'),
(1, 14, '2025-07-24 18:00:16'),
(1, 15, '2025-07-24 18:00:16'),
(1, 16, '2025-07-24 18:00:16'),
(1, 17, '2025-07-24 18:00:16'),
(1, 18, '2025-07-24 18:00:16'),
(1, 19, '2025-07-24 18:00:16'),
(1, 20, '2025-07-24 18:00:16'),
(1, 21, '2025-07-24 18:00:16'),
(1, 22, '2025-07-24 18:00:16'),
(1, 23, '2025-07-24 18:00:16'),
(1, 24, '2025-07-24 18:00:16'),
(1, 25, '2025-07-24 18:00:16'),
(1, 26, '2025-07-24 18:00:16'),
(1, 27, '2025-07-24 18:00:16'),
(1, 28, '2025-07-24 18:00:16'),
(1, 29, '2025-07-24 18:00:16'),
(1, 30, '2025-07-24 18:00:16'),
(1, 31, '2025-07-24 18:00:16'),
(1, 32, '2025-07-24 18:00:16'),
(1, 33, '2025-07-24 18:00:16'),
(1, 34, '2025-07-24 18:00:16'),
(1, 35, '2025-07-24 18:00:16'),
(1, 36, '2025-07-24 18:00:16'),
(1, 37, '2025-07-24 18:00:16'),
(1, 38, '2025-07-24 18:00:16'),
(1, 39, '2025-07-24 18:00:16'),
(1, 40, '2025-07-24 18:00:16'),
(1, 41, '2025-07-24 18:00:16'),
(1, 42, '2025-07-24 18:00:16'),
(1, 43, '2025-07-24 18:00:16'),
(1, 44, '2025-07-24 18:00:16'),
(1, 45, '2025-07-24 18:00:16'),
(1, 46, '2025-07-24 18:00:16'),
(1, 47, '2025-07-24 18:00:16'),
(1, 48, '2025-07-24 18:00:16'),
(1, 49, '2025-07-24 18:00:16'),
(1, 50, '2025-07-24 18:00:16'),
(1, 51, '2025-07-24 18:00:16'),
(1, 52, '2025-07-24 18:00:16'),
(1, 53, '2025-07-24 18:00:16'),
(1, 54, '2025-07-24 18:00:16'),
(1, 55, '2025-07-24 18:00:16'),
(2, 37, '2025-03-09 12:22:08'),
(2, 38, '2025-03-09 12:22:08'),
(2, 39, '2025-03-09 12:22:08'),
(2, 55, '2025-07-24 17:54:19');

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
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rate_limits`
--

DROP TABLE IF EXISTS `rate_limits`;
CREATE TABLE IF NOT EXISTS `rate_limits` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `identifier` varchar(255) NOT NULL COMMENT 'User ID, IP address, or other identifier',
  `action` varchar(100) NOT NULL COMMENT 'Action being rate limited (login, api_request, etc.)',
  `attempts` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `window_start` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_rate_limit` (`identifier`,`action`),
  KEY `idx_rate_limits_expires` (`expires_at`),
  KEY `idx_rate_limits_identifier` (`identifier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category` varchar(50) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_setting` (`category`,`setting_key`),
  KEY `idx_category` (`category`)
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `category`, `setting_key`, `setting_value`, `description`, `created_at`, `updated_at`) VALUES
(1, 'time_intervals', 'time_unit', 'minutes', 'Default time unit for time tracking (minutes, seconds, hours, days)', '2025-06-07 19:29:24', '2025-06-07 19:29:24'),
(2, 'time_intervals', 'time_precision', '15', 'Time precision/increment for time inputs', '2025-06-07 19:29:24', '2025-06-07 19:29:24'),
(3, 'projects', 'default_task_type', 'task', 'Default task type when creating new tasks', '2025-06-07 19:29:24', '2025-06-18 01:24:27'),
(4, 'projects', 'auto_assign_creator', '1', 'Automatically assign task creator as assignee', '2025-06-07 19:29:24', '2025-06-18 01:24:27'),
(5, 'projects', 'require_project_for_tasks', '0', 'Require project selection when creating tasks', '2025-06-07 19:29:24', '2025-06-07 19:29:24'),
(6, 'tasks', 'default_priority', 'medium', 'Default priority for new tasks', '2025-06-07 19:29:24', '2025-06-18 01:24:27'),
(7, 'tasks', 'auto_estimate_enabled', '0', 'Enable automatic time estimation based on similar tasks', '2025-06-07 19:29:24', '2025-06-07 19:29:24'),
(8, 'tasks', 'story_points_enabled', '1', 'Enable story points for agile estimation', '2025-06-07 19:29:24', '2025-06-18 01:24:27'),
(9, 'milestones', 'auto_create_from_sprints', '0', 'Automatically create milestones from sprint completions', '2025-06-07 19:29:24', '2025-06-07 19:29:24'),
(10, 'milestones', 'milestone_notification_days', '7', 'Days before milestone due date to send notifications', '2025-06-07 19:29:24', '2025-06-18 01:24:27'),
(11, 'sprints', 'default_sprint_length', '14', 'Default sprint length in days', '2025-06-07 19:29:24', '2025-06-18 01:24:27'),
(12, 'sprints', 'auto_start_next_sprint', '0', 'Automatically start next sprint when current ends', '2025-06-07 19:29:24', '2025-06-07 19:29:24'),
(13, 'sprints', 'sprint_planning_enabled', '1', 'Enable sprint planning features', '2025-06-07 19:29:24', '2025-06-18 01:24:27'),
(14, 'general', 'results_per_page', '25', NULL, '2025-06-18 01:24:27', '2025-06-18 01:24:27'),
(15, 'general', 'date_format', 'Y-m-d', NULL, '2025-06-18 01:24:27', '2025-06-18 01:24:27'),
(16, 'general', 'default_timezone', 'America/New_York', NULL, '2025-06-18 01:24:27', '2025-06-18 01:24:27'),
(17, 'general', 'session_timeout', '3600', NULL, '2025-06-18 01:24:27', '2025-06-18 01:24:27'),
(18, 'general', 'time_unit', 'minutes', NULL, '2025-06-18 01:24:27', '2025-06-18 01:24:27'),
(19, 'general', 'time_precision', '15', NULL, '2025-06-18 01:24:27', '2025-06-18 01:24:27'),
(20, 'templates', 'project_show_quick_templates', '1', NULL, '2025-06-18 01:24:27', '2025-06-18 01:24:27'),
(21, 'templates', 'project_show_custom_templates', '1', NULL, '2025-06-18 01:24:27', '2025-06-18 01:24:27'),
(22, 'templates', 'task_show_quick_templates', '1', NULL, '2025-06-18 01:24:27', '2025-06-18 01:24:27'),
(23, 'templates', 'task_show_custom_templates', '1', NULL, '2025-06-18 01:24:27', '2025-06-18 01:24:27'),
(24, 'templates', 'milestone_show_quick_templates', '1', NULL, '2025-06-18 01:24:27', '2025-06-18 01:24:27'),
(25, 'templates', 'milestone_show_custom_templates', '1', NULL, '2025-06-18 01:24:27', '2025-06-18 01:24:27'),
(26, 'templates', 'sprint_show_quick_templates', '1', NULL, '2025-06-18 01:24:27', '2025-06-18 01:24:27'),
(27, 'templates', 'sprint_show_custom_templates', '1', NULL, '2025-06-18 01:24:27', '2025-06-18 01:24:27'),
(28, 'security', 'session_samesite', 'Lax', NULL, '2025-06-18 01:24:27', '2025-06-18 01:24:27'),
(29, 'security', 'validate_session_domain', '1', NULL, '2025-06-18 01:24:27', '2025-06-18 01:24:27'),
(30, 'security', 'regenerate_session_on_auth', '1', NULL, '2025-06-18 01:24:27', '2025-06-18 01:24:27'),
(31, 'security', 'csrf_protection_enabled', '1', NULL, '2025-06-18 01:24:27', '2025-06-18 01:24:27'),
(32, 'security', 'csrf_ajax_protection', '1', NULL, '2025-06-18 01:24:27', '2025-06-18 01:24:27'),
(33, 'security', 'csrf_token_lifetime', '3600', NULL, '2025-06-18 01:24:27', '2025-06-18 01:24:27'),
(34, 'security', 'max_input_size', '1048576', NULL, '2025-06-18 01:24:27', '2025-06-18 01:24:27'),
(35, 'security', 'strict_input_validation', '1', NULL, '2025-06-18 01:24:27', '2025-06-18 01:24:27'),
(36, 'security', 'html_sanitization', '1', NULL, '2025-06-18 01:24:27', '2025-06-18 01:24:27'),
(37, 'security', 'validate_redirects', '1', NULL, '2025-06-18 01:24:27', '2025-06-18 01:24:27'),
(38, 'security', 'allowed_redirect_domains', '', NULL, '2025-06-18 01:24:27', '2025-06-18 01:24:27'),
(39, 'security', 'enable_csp', '1', NULL, '2025-06-18 01:24:27', '2025-06-18 01:24:27'),
(40, 'security', 'csp_policy', 'moderate', NULL, '2025-06-18 01:24:27', '2025-06-18 01:24:27'),
(41, 'security', 'additional_headers', '1', NULL, '2025-06-18 01:24:27', '2025-06-18 01:24:27'),
(42, 'security', 'hide_error_details', '1', NULL, '2025-06-18 01:24:27', '2025-06-18 01:24:27'),
(43, 'security', 'log_security_events', '1', NULL, '2025-06-18 01:24:27', '2025-06-18 01:24:27'),
(44, 'security', 'rate_limit_attempts', '60', NULL, '2025-06-18 01:24:27', '2025-06-18 01:24:27'),
(45, 'sprints', 'default_length', '2', NULL, '2025-06-19 22:50:05', '2025-06-19 22:50:05'),
(46, 'sprints', 'estimation_method', 'story_points', NULL, '2025-06-19 22:50:05', '2025-06-19 22:50:05'),
(47, 'sprints', 'default_capacity', '40', NULL, '2025-06-19 22:50:05', '2025-06-19 22:50:05'),
(48, 'sprints', 'include_weekends', '1', NULL, '2025-06-19 22:50:05', '2025-06-19 22:50:05'),
(49, 'sprints', 'auto_assign_subtasks', '0', NULL, '2025-06-19 22:50:05', '2025-06-19 22:50:05');

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
  `sprint_goal` text DEFAULT NULL,
  `planning_date` datetime DEFAULT NULL,
  `review_date` datetime DEFAULT NULL,
  `retrospective_date` datetime DEFAULT NULL,
  `capacity_hours` int(11) DEFAULT NULL,
  `capacity_story_points` int(11) DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `is_deleted` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `status_id` (`status_id`),
  KEY `guid` (`guid`),
  KEY `fk_sprints_project` (`project_id`),
  KEY `idx_sprints_dates` (`start_date`,`end_date`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sprint_milestones`
--

DROP TABLE IF EXISTS `sprint_milestones`;
CREATE TABLE IF NOT EXISTS `sprint_milestones` (
  `id` int(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `sprint_id` int(20) UNSIGNED NOT NULL,
  `milestone_id` int(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_sprint_milestone` (`sprint_id`,`milestone_id`),
  KEY `milestone_id` (`milestone_id`)
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
  KEY `sprint_id` (`sprint_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `statuses_sprint`
--

INSERT INTO `statuses_sprint` (`id`, `name`, `description`, `is_deleted`, `created_at`, `updated_at`) VALUES
(1, 'planning', 'Sprint is in planning phase', 0, '2025-02-23 23:21:32', '2025-02-23 23:21:32'),
(2, 'active', 'Sprint is currently active', 0, '2025-02-23 23:21:32', '2025-02-23 23:21:32'),
(3, 'completed', 'Sprint has been completed', 0, '2025-02-23 23:21:32', '2025-02-23 23:21:32'),
(4, 'cancelled', 'Sprint was cancelled', 0, '2025-02-23 23:21:32', '2025-02-23 23:21:32'),
(5, 'delayed', 'Sprint has been delayed', 0, '2025-02-23 23:23:05', '2025-02-23 23:23:05'),
(6, 'review', 'Sprint in review phase', 0, '2025-06-19 02:09:43', '2025-06-19 02:09:43');

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
-- Table structure for table `tasks`
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
  KEY `idx_tasks_due_date` (`due_date`),
  KEY `idx_tasks_priority_status` (`priority`,`status_id`),
  KEY `idx_tasks_assigned_status` (`assigned_to`,`status_id`),
  KEY `idx_backlog_priority` (`backlog_priority`),
  KEY `idx_task_type` (`task_type`),
  KEY `idx_ready_for_sprint` (`is_ready_for_sprint`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  KEY `fk_task_comments_user` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `task_history`
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
  KEY `fk_task_history_user` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `templates`
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
  KEY `fk_templates_company` (`company_id`),
  KEY `template_type` (`template_type`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `templates`
--

INSERT INTO `templates` (`id`, `guid`, `name`, `description`, `template_type`, `company_id`, `is_default`, `is_deleted`, `created_at`, `updated_at`) VALUES
(1, '98de6d05-fd02-11ef-8172-e454e8e51d1c', 'Basic Project', '# Project Overview\nProvide a brief overview of the project.\n\n## Objectives\n- List key objectives\n- What are we trying to accomplish?\n\n## Scope\n- What\'s included\n- What\'s excluded\n\n## Timeline\n- Start date\n- Expected end date\n- Key milestones', 'project', NULL, 1, 0, '2025-03-09 16:21:43', '2025-03-09 16:21:43'),
(2, '98de6f5b-fd02-11ef-8172-e454e8e51d1c', 'Website Development', '# Website Development Project\n\n## Project Overview\nThis project involves designing and developing a website for [Client].\n\n## Requirements\n- Responsive design for mobile and desktop\n- Content management system\n- Contact forms\n- Analytics integration\n\n## Deliverables\n- Wireframes\n- Visual designs\n- Development\n- Testing\n- Deployment\n\n## Timeline\n- Discovery: [Date]\n- Design: [Date]\n- Development: [Date]\n- Testing: [Date]\n- Launch: [Date]', 'project', NULL, 0, 0, '2025-03-09 16:21:43', '2025-03-09 16:21:43'),
(3, '98de6fe4-fd02-11ef-8172-e454e8e51d1c', 'Software Development', '# Software Development Project\n\n## Project Overview\nThis project involves developing a software application for [Purpose].\n\n## Requirements\n- Feature 1\n- Feature 2\n- Feature 3\n\n## Technical Specifications\n- Programming language: \n- Database: \n- Hosting environment: \n\n## Timeline\n- Requirements gathering: [Date]\n- Development sprint 1: [Date]\n- Development sprint 2: [Date]\n- Testing: [Date]\n- Deployment: [Date]', 'project', NULL, 0, 0, '2025-03-09 16:21:43', '2025-03-09 16:21:43'),
(4, 'bfc40ea5-4d5f-11f0-a774-e454e8e51d1c', 'DevTeam Sprint', 'The default dev team sprint configuration', 'sprint', NULL, 1, 0, '2025-06-19 22:50:05', '2025-06-19 22:50:05');

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
  KEY `fk_time_entries_user` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
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
  KEY `idx_users_fullname` (`first_name`,`last_name`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `guid`, `company_id`, `role_id`, `first_name`, `last_name`, `email`, `phone`, `password_hash`, `is_active`, `activation_token`, `activation_token_expires_at`, `reset_password_token`, `reset_password_token_expires_at`, `is_deleted`) VALUES
(1, 'b40b3681-fb03-11ef-99ad-e454e8e51d1c', NULL, 1, 'Admin', 'User', 'admin@aureo.us', NULL, '$argon2id$v=19$m=65536,t=4,p=1$dkcub0FTU2NENTRmdXBSeQ$cAZna9wkkfUbCM5PPRjh3KvEjga+dP56xqnOvfbam0U', 1, NULL, '2025-02-24 20:13:39', NULL, NULL, 0);

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
  KEY `fk_user_companies_company` (`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_favorites`
--

DROP TABLE IF EXISTS `user_favorites`;
CREATE TABLE IF NOT EXISTS `user_favorites` (
  `id` int(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(20) UNSIGNED NOT NULL,
  `favorite_type` enum('project','task','milestone','sprint','page') NOT NULL,
  `favorite_id` int(20) UNSIGNED DEFAULT NULL COMMENT 'ID of the favorited item (null for page favorites)',
  `page_url` varchar(255) DEFAULT NULL COMMENT 'URL for page favorites',
  `page_title` varchar(255) NOT NULL COMMENT 'Display title for the favorite',
  `page_icon` varchar(50) DEFAULT NULL COMMENT 'Icon class or emoji for the favorite',
  `sort_order` int(10) UNSIGNED DEFAULT 0 COMMENT 'User-defined sort order',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_favorite` (`user_id`,`favorite_type`,`favorite_id`,`page_url`),
  KEY `idx_user_favorites_user` (`user_id`),
  KEY `idx_user_favorites_type` (`favorite_type`),
  KEY `idx_user_favorites_sort` (`user_id`,`sort_order`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  KEY `fk_user_projects_project` (`project_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `milestones`
--
ALTER TABLE `milestones` ADD FULLTEXT KEY `ft_milestone_title_description` (`title`,`description`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects` ADD FULLTEXT KEY `ft_project_name_description` (`name`,`description`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks` ADD FULLTEXT KEY `ft_task_title_description` (`title`,`description`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `fk_activity_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `companies`
--
ALTER TABLE `companies`
  ADD CONSTRAINT `fk_companies_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `company_projects`
--
ALTER TABLE `company_projects`
  ADD CONSTRAINT `fk_company_projects_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_company_projects_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `csrf_tokens`
--
ALTER TABLE `csrf_tokens`
  ADD CONSTRAINT `fk_csrf_tokens_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `milestones`
--
ALTER TABLE `milestones`
  ADD CONSTRAINT `fk_milestones_epic` FOREIGN KEY (`epic_id`) REFERENCES `milestones` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_milestones_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_milestones_status` FOREIGN KEY (`status_id`) REFERENCES `statuses_milestone` (`id`);

--
-- Constraints for table `milestone_tasks`
--
ALTER TABLE `milestone_tasks`
  ADD CONSTRAINT `fk_milestone_tasks_milestone` FOREIGN KEY (`milestone_id`) REFERENCES `milestones` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_milestone_tasks_task` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `fk_projects_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  ADD CONSTRAINT `fk_projects_owner` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_projects_status` FOREIGN KEY (`status_id`) REFERENCES `statuses_project` (`id`);

--
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `fk_role_permissions_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_role_permissions_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sessions`
--
ALTER TABLE `sessions`
  ADD CONSTRAINT `fk_sessions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sprints`
--
ALTER TABLE `sprints`
  ADD CONSTRAINT `fk_sprints_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_sprints_status` FOREIGN KEY (`status_id`) REFERENCES `statuses_sprint` (`id`);

--
-- Constraints for table `sprint_milestones`
--
ALTER TABLE `sprint_milestones`
  ADD CONSTRAINT `sprint_milestones_ibfk_1` FOREIGN KEY (`sprint_id`) REFERENCES `sprints` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sprint_milestones_ibfk_2` FOREIGN KEY (`milestone_id`) REFERENCES `milestones` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sprint_tasks`
--
ALTER TABLE `sprint_tasks`
  ADD CONSTRAINT `fk_sprint_tasks_sprint` FOREIGN KEY (`sprint_id`) REFERENCES `sprints` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_sprint_tasks_task` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `fk_tasks_assigned_to` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_tasks_parent` FOREIGN KEY (`parent_task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_tasks_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_tasks_status` FOREIGN KEY (`status_id`) REFERENCES `statuses_task` (`id`);

--
-- Constraints for table `task_comments`
--
ALTER TABLE `task_comments`
  ADD CONSTRAINT `fk_task_comments_task` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_task_comments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `task_history`
--
ALTER TABLE `task_history`
  ADD CONSTRAINT `fk_task_history_task` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_task_history_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `templates`
--
ALTER TABLE `templates`
  ADD CONSTRAINT `fk_templates_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`);

--
-- Constraints for table `time_entries`
--
ALTER TABLE `time_entries`
  ADD CONSTRAINT `fk_time_entries_task` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_time_entries_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);

--
-- Constraints for table `user_companies`
--
ALTER TABLE `user_companies`
  ADD CONSTRAINT `fk_user_companies_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_user_companies_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_favorites`
--
ALTER TABLE `user_favorites`
  ADD CONSTRAINT `fk_user_favorites_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_projects`
--
ALTER TABLE `user_projects`
  ADD CONSTRAINT `fk_user_projects_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_user_projects_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
