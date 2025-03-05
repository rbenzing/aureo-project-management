START TRANSACTION;

-- Disable foreign key checks and unique checks temporarily for faster inserts
SET FOREIGN_KEY_CHECKS = 0;
SET UNIQUE_CHECKS = 0;

-- Clear existing data
TRUNCATE TABLE sprint_tasks;
TRUNCATE TABLE sprints;
TRUNCATE TABLE tasks;
TRUNCATE TABLE milestones;
TRUNCATE TABLE projects;
TRUNCATE TABLE role_permissions;
TRUNCATE TABLE company_admins;
TRUNCATE TABLE users;
TRUNCATE TABLE roles;
TRUNCATE TABLE companies;

-- Create roles with specific permission sets
INSERT INTO roles (id, name, description) VALUES
(1, 'Super Admin', 'Full system access'),
(2, 'Company Admin', 'Company level access'),
(3, 'Project Manager', 'Project management access'),
(4, 'Team Lead', 'Team management access'),
(5, 'Senior Developer', 'Senior development access'),
(6, 'Developer', 'Development access'),
(7, 'QA Lead', 'Quality assurance lead access'),
(8, 'QA Engineer', 'Quality assurance access'),
(9, 'Product Owner', 'Product management access'),
(10, 'Scrum Master', 'Scrum process management');

-- Assign permissions to roles
-- Super Admin gets all permissions
INSERT INTO role_permissions (role_id, permission_id)
SELECT 1, id FROM permissions;

-- Company Admin permissions
INSERT INTO role_permissions (role_id, permission_id)
SELECT 2, id FROM permissions WHERE name IN (
    'manage_projects', 'view_projects', 'create_projects', 'edit_projects',
    'manage_users', 'view_users', 'create_users', 'edit_users',
    'manage_roles', 'view_roles',
    'manage_companies', 'view_dashboard'
);

-- Project Manager permissions
INSERT INTO role_permissions (role_id, permission_id)
SELECT 3, id FROM permissions WHERE name IN (
    'manage_projects', 'view_projects', 'edit_projects',
    'manage_tasks', 'view_tasks', 'create_tasks', 'edit_tasks',
    'manage_milestones', 'view_milestones', 'create_milestones', 'edit_milestones',
    'manage_sprints', 'view_sprints', 'edit_sprints',
    'view_dashboard'
);

-- Team Lead permissions
INSERT INTO role_permissions (role_id, permission_id)
SELECT 4, id FROM permissions WHERE name IN (
    'view_projects', 'edit_projects',
    'manage_tasks', 'view_tasks', 'create_tasks', 'edit_tasks',
    'view_milestones', 'edit_milestones',
    'view_sprints', 'edit_sprints',
    'view_dashboard'
);

-- Senior Developer permissions
INSERT INTO role_permissions (role_id, permission_id)
SELECT 5, id FROM permissions WHERE name IN (
    'view_projects',
    'manage_tasks', 'view_tasks', 'create_tasks', 'edit_tasks',
    'view_milestones',
    'view_sprints',
    'view_dashboard'
);

-- Sample companies
INSERT INTO companies (name, address, phone, email, website) VALUES
('Tech Innovators', '123 Innovation Way', '555-0101', 'contact@techinnovators.com', 'techinnovators.com'),
('Digital Solutions', '456 Digital Drive', '555-0102', 'info@digitalsolutions.com', 'digitalsolutions.com'),
('Creative Tech', '789 Creative Court', '555-0103', 'hello@creativetech.com', 'creativetech.com'),
('Cloud Systems', '321 Cloud Lane', '555-0104', 'info@cloudsystems.com', 'cloudsystems.com'),
('Data Dynamics', '654 Data Drive', '555-0105', 'contact@datadynamics.com', 'datadynamics.com');

-- Generate 25 users across different roles and companies
INSERT INTO users (company_id, role_id, first_name, last_name, email, phone, password_hash, is_active)
WITH RECURSIVE numbers AS (
    SELECT 1 as n
    UNION ALL
    SELECT n + 1 FROM numbers WHERE n < 25
)
SELECT 
    1 + (n % 5), -- company_id
    1 + (n % 10), -- role_id
    CASE 
        WHEN n % 5 = 0 THEN 'John'
        WHEN n % 5 = 1 THEN 'Sarah'
        WHEN n % 5 = 2 THEN 'Michael'
        WHEN n % 5 = 3 THEN 'Emma'
        ELSE 'David'
    END,
    CASE 
        WHEN n % 5 = 0 THEN 'Smith'
        WHEN n % 5 = 1 THEN 'Johnson'
        WHEN n % 5 = 2 THEN 'Williams'
        WHEN n % 5 = 3 THEN 'Brown'
        ELSE 'Jones'
    END,
    CONCAT('user', n, '@example.com'),
    CONCAT('555-', LPAD(n, 4, '0')),
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    1
FROM numbers;

-- Generate 20 projects
INSERT INTO projects (key_code, company_id, owner_id, name, description, start_date, end_date, status_id)
WITH RECURSIVE numbers AS (
    SELECT 1 as n
    UNION ALL
    SELECT n + 1 FROM numbers WHERE n < 20
)
SELECT 
    CONCAT('PRJ', LPAD(n, 2, '0')),
    1 + (n % 5), -- company_id
    1 + (n % 25), -- owner_id
    CONCAT('Project ', CHAR(64 + n)), -- Project A, B, C, etc.
    CONCAT('Description for Project ', CHAR(64 + n)),
    DATE_ADD('2024-01-01', INTERVAL n MONTH),
    DATE_ADD('2024-01-01', INTERVAL (n + 6) MONTH),
    1 + (n % 6) -- status_id
FROM numbers;

-- Generate epics and milestones
DELIMITER //

CREATE TEMPORARY PROCEDURE generate_epics_and_milestones()
BEGIN
    DECLARE project_id INT DEFAULT 1;
    DECLARE epic_count INT DEFAULT 1;
    DECLARE milestone_count INT DEFAULT 1;
    
    -- For each project
    WHILE project_id <= 20 DO
        -- Generate 10 epics per project
        SET epic_count = 1;
        WHILE epic_count <= 10 DO
            INSERT INTO milestones (
                title, 
                description, 
                milestone_type,
                start_date,
                due_date,
                project_id,
                status_id
            ) VALUES (
                CONCAT('Epic ', epic_count, ' - Project ', project_id),
                CONCAT('Description for Epic ', epic_count),
                'epic',
                DATE_ADD('2024-01-01', INTERVAL epic_count MONTH),
                DATE_ADD('2024-01-01', INTERVAL (epic_count + 3) MONTH),
                project_id,
                1 + (epic_count % 6)
            );
            
            -- Get the last inserted epic ID
            SET @epic_id = LAST_INSERT_ID();
            
            -- Generate 20 milestones per epic
            SET milestone_count = 1;
            WHILE milestone_count <= 20 DO
                INSERT INTO milestones (
                    title,
                    description,
                    milestone_type,
                    start_date,
                    due_date,
                    project_id,
                    epic_id,
                    status_id
                ) VALUES (
                    CONCAT('Milestone ', milestone_count, ' - Epic ', epic_count),
                    CONCAT('Description for Milestone ', milestone_count),
                    'milestone',
                    DATE_ADD('2024-01-01', INTERVAL milestone_count WEEK),
                    DATE_ADD('2024-01-01', INTERVAL (milestone_count + 2) WEEK),
                    project_id,
                    @epic_id,
                    1 + (milestone_count % 6)
                );
                
                SET milestone_count = milestone_count + 1;
            END WHILE;
            
            SET epic_count = epic_count + 1;
        END WHILE;
        
        SET project_id = project_id + 1;
    END WHILE;
END //

DELIMITER ;

-- Execute the procedure
CALL generate_epics_and_milestones();

-- Generate sprints and tasks
DELIMITER //

CREATE TEMPORARY PROCEDURE generate_sprints_and_tasks()
BEGIN
    DECLARE project_id INT DEFAULT 1;
    DECLARE sprint_count INT DEFAULT 1;
    DECLARE task_count INT DEFAULT 1;
    
    -- For each project
    WHILE project_id <= 20 DO
        -- Generate 10 sprints per project
        SET sprint_count = 1;
        WHILE sprint_count <= 10 DO
            INSERT INTO sprints (
                project_id,
                name,
                description,
                start_date,
                end_date,
                status_id
            ) VALUES (
                project_id,
                CONCAT('Sprint ', sprint_count, ' - Project ', project_id),
                CONCAT('Description for Sprint ', sprint_count),
                DATE_ADD('2024-01-01', INTERVAL (sprint_count * 2 - 2) WEEK),
                DATE_ADD('2024-01-01', INTERVAL (sprint_count * 2) WEEK),
                1 + (sprint_count % 5)
            );
            
            -- Get the last inserted sprint ID
            SET @sprint_id = LAST_INSERT_ID();
            
            -- Generate 50 tasks per sprint
            SET task_count = 1;
            WHILE task_count <= 50 DO
                INSERT INTO tasks (
                    project_id,
                    assigned_to,
                    title,
                    description,
                    priority,
                    status_id,
                    estimated_time,
                    start_date,
                    due_date
                ) VALUES (
                    project_id,
                    1 + (task_count % 25), -- assigned_to (cycles through users)
                    CONCAT('Task ', task_count, ' - Sprint ', sprint_count),
                    CONCAT('Description for Task ', task_count),
                    CASE task_count % 4
                        WHEN 0 THEN 'none'
                        WHEN 1 THEN 'low'
                        WHEN 2 THEN 'medium'
                        ELSE 'high'
                    END,
                    1 + (task_count % 7), -- status_id
                    (task_count % 8 + 1) * 3600, -- estimated_time (in seconds)
                    DATE_ADD('2024-01-01', INTERVAL (sprint_count * 2 - 2) WEEK),
                    DATE_ADD('2024-01-01', INTERVAL (sprint_count * 2) WEEK)
                );
                
                -- Link task to sprint
                INSERT INTO sprint_tasks (sprint_id, task_id) VALUES
                (@sprint_id, LAST_INSERT_ID());
                
                SET task_count = task_count + 1;
            END WHILE;
            
            SET sprint_count = sprint_count + 1;
        END WHILE;
        
        SET project_id = project_id + 1;
    END WHILE;
END //

DELIMITER ;

-- Execute the procedure
CALL generate_sprints_and_tasks();

-- Drop temporary procedures
DROP PROCEDURE IF EXISTS generate_epics_and_milestones;
DROP PROCEDURE IF EXISTS generate_sprints_and_tasks;

-- Re-enable foreign key checks and unique checks
SET FOREIGN_KEY_CHECKS = 1;
SET UNIQUE_CHECKS = 1;

COMMIT;

-- Verify data counts
SELECT 'Users' as type, COUNT(*) as count FROM users
UNION ALL
SELECT 'Projects', COUNT(*) FROM projects
UNION ALL
SELECT 'Epics', COUNT(*) FROM milestones WHERE milestone_type = 'epic'
UNION ALL
SELECT 'Milestones', COUNT(*) FROM milestones WHERE milestone_type = 'milestone'
UNION ALL
SELECT 'Sprints', COUNT(*) FROM sprints
UNION ALL
SELECT 'Tasks', COUNT(*) FROM tasks;