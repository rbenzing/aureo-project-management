-- Migration: Add template configurations table for sprint templates
-- Date: 2025-06-19
-- Description: Create template_configurations table to store sprint-specific template settings

-- Create template_configurations table
CREATE TABLE IF NOT EXISTS `template_configurations` (
  `id` int(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `template_id` int(20) UNSIGNED NOT NULL,
  `project_id` int(20) UNSIGNED DEFAULT NULL COMMENT 'Project-specific configuration',
  `sprint_length` int(2) UNSIGNED DEFAULT 2 COMMENT 'Sprint length in weeks',
  `estimation_method` enum('hours','story_points','both') DEFAULT 'hours' COMMENT 'Estimation method preference',
  `default_capacity` int(3) UNSIGNED DEFAULT 40 COMMENT 'Default team capacity in hours or story points',
  `include_weekends` tinyint(1) UNSIGNED DEFAULT 0 COMMENT 'Include weekends in sprint calculations',
  `auto_assign_subtasks` tinyint(1) UNSIGNED DEFAULT 1 COMMENT 'Automatically assign subtasks when parent is assigned',
  `ceremony_settings` json DEFAULT NULL COMMENT 'SCRUM ceremony configuration',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_template_config_template` (`template_id`),
  KEY `fk_template_config_project` (`project_id`),
  CONSTRAINT `fk_template_config_template` FOREIGN KEY (`template_id`) REFERENCES `templates` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_template_config_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default sprint template configurations
INSERT INTO `template_configurations` (
    `template_id`, 
    `project_id`, 
    `sprint_length`, 
    `estimation_method`, 
    `default_capacity`, 
    `include_weekends`, 
    `auto_assign_subtasks`, 
    `ceremony_settings`
) 
SELECT 
    t.id,
    NULL,
    2,
    'hours',
    40,
    0,
    1,
    JSON_OBJECT(
        'planning', JSON_OBJECT('enabled', true, 'duration_hours', 2, 'participants', JSON_ARRAY('team', 'product_owner', 'scrum_master')),
        'daily_standup', JSON_OBJECT('enabled', true, 'duration_minutes', 15, 'time', '09:00'),
        'review', JSON_OBJECT('enabled', true, 'duration_hours', 1, 'participants', JSON_ARRAY('team', 'stakeholders')),
        'retrospective', JSON_OBJECT('enabled', true, 'duration_hours', 1, 'participants', JSON_ARRAY('team', 'scrum_master'))
    )
FROM `templates` t 
WHERE t.template_type = 'sprint' 
AND t.is_deleted = 0
AND NOT EXISTS (
    SELECT 1 FROM `template_configurations` tc WHERE tc.template_id = t.id
);

-- Create some default sprint templates if they don't exist
INSERT IGNORE INTO `templates` (`name`, `description`, `template_type`, `company_id`, `is_default`) VALUES
('Standard Development Sprint', 'Standard 2-week development sprint with full SCRUM ceremonies and capacity planning.', 'sprint', NULL, 1),
('Bug Fix Sprint', 'Focused sprint for addressing technical debt and critical bug fixes.', 'sprint', NULL, 0),
('Research Sprint', 'Exploration and research-focused sprint with flexible capacity and extended planning.', 'sprint', NULL, 0),
('Release Sprint', 'Final sprint before major release with emphasis on testing and documentation.', 'sprint', NULL, 0);

-- Add configurations for the new default templates
INSERT INTO `template_configurations` (
    `template_id`, 
    `project_id`, 
    `sprint_length`, 
    `estimation_method`, 
    `default_capacity`, 
    `include_weekends`, 
    `auto_assign_subtasks`, 
    `ceremony_settings`
) 
SELECT 
    t.id,
    NULL,
    CASE 
        WHEN t.name = 'Standard Development Sprint' THEN 2
        WHEN t.name = 'Bug Fix Sprint' THEN 1
        WHEN t.name = 'Research Sprint' THEN 3
        WHEN t.name = 'Release Sprint' THEN 2
        ELSE 2
    END,
    CASE 
        WHEN t.name = 'Research Sprint' THEN 'story_points'
        ELSE 'hours'
    END,
    CASE 
        WHEN t.name = 'Bug Fix Sprint' THEN 30
        WHEN t.name = 'Research Sprint' THEN 20
        ELSE 40
    END,
    0,
    CASE 
        WHEN t.name = 'Bug Fix Sprint' THEN 0
        ELSE 1
    END,
    CASE 
        WHEN t.name = 'Standard Development Sprint' THEN JSON_OBJECT(
            'planning', JSON_OBJECT('enabled', true, 'duration_hours', 2, 'participants', JSON_ARRAY('team', 'product_owner', 'scrum_master')),
            'daily_standup', JSON_OBJECT('enabled', true, 'duration_minutes', 15, 'time', '09:00'),
            'review', JSON_OBJECT('enabled', true, 'duration_hours', 1, 'participants', JSON_ARRAY('team', 'stakeholders')),
            'retrospective', JSON_OBJECT('enabled', true, 'duration_hours', 1, 'participants', JSON_ARRAY('team', 'scrum_master'))
        )
        WHEN t.name = 'Bug Fix Sprint' THEN JSON_OBJECT(
            'planning', JSON_OBJECT('enabled', true, 'duration_hours', 1, 'participants', JSON_ARRAY('team', 'tech_lead')),
            'daily_standup', JSON_OBJECT('enabled', true, 'duration_minutes', 10, 'time', '09:00'),
            'review', JSON_OBJECT('enabled', false),
            'retrospective', JSON_OBJECT('enabled', true, 'duration_hours', 0.5, 'participants', JSON_ARRAY('team'))
        )
        WHEN t.name = 'Research Sprint' THEN JSON_OBJECT(
            'planning', JSON_OBJECT('enabled', true, 'duration_hours', 3, 'participants', JSON_ARRAY('team', 'product_owner', 'stakeholders')),
            'daily_standup', JSON_OBJECT('enabled', false),
            'review', JSON_OBJECT('enabled', true, 'duration_hours', 2, 'participants', JSON_ARRAY('team', 'stakeholders')),
            'retrospective', JSON_OBJECT('enabled', true, 'duration_hours', 1.5, 'participants', JSON_ARRAY('team', 'product_owner'))
        )
        WHEN t.name = 'Release Sprint' THEN JSON_OBJECT(
            'planning', JSON_OBJECT('enabled', true, 'duration_hours', 1.5, 'participants', JSON_ARRAY('team', 'product_owner', 'qa_lead')),
            'daily_standup', JSON_OBJECT('enabled', true, 'duration_minutes', 20, 'time', '09:00'),
            'review', JSON_OBJECT('enabled', true, 'duration_hours', 1.5, 'participants', JSON_ARRAY('team', 'stakeholders', 'qa_team')),
            'retrospective', JSON_OBJECT('enabled', true, 'duration_hours', 1, 'participants', JSON_ARRAY('team', 'scrum_master'))
        )
        ELSE JSON_OBJECT(
            'planning', JSON_OBJECT('enabled', true, 'duration_hours', 2, 'participants', JSON_ARRAY('team', 'product_owner')),
            'daily_standup', JSON_OBJECT('enabled', true, 'duration_minutes', 15, 'time', '09:00'),
            'review', JSON_OBJECT('enabled', true, 'duration_hours', 1, 'participants', JSON_ARRAY('team', 'stakeholders')),
            'retrospective', JSON_OBJECT('enabled', true, 'duration_hours', 1, 'participants', JSON_ARRAY('team'))
        )
    END
FROM `templates` t 
WHERE t.template_type = 'sprint' 
AND t.name IN ('Standard Development Sprint', 'Bug Fix Sprint', 'Research Sprint', 'Release Sprint')
AND NOT EXISTS (
    SELECT 1 FROM `template_configurations` tc WHERE tc.template_id = t.id
);
