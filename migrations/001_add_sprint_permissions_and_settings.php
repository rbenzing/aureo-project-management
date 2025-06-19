<?php
/**
 * Migration: Add Sprint Permissions and Enhanced Settings
 * 
 * This migration adds granular permissions for settings management
 * and enhances the sprint functionality with new database fields.
 * 
 * Date: 2025-01-19
 * Version: 001
 */

// Ensure this migration is not directly accessible via the web
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

// Include Composer's autoloader
require_once BASE_PATH . '/vendor/autoload.php';

// Load configuration
\App\Core\Config::init();

require_once BASE_PATH . '/src/Core/Database.php';
require_once BASE_PATH . '/src/Models/BaseModel.php';
require_once BASE_PATH . '/src/Models/Permission.php';

use App\Core\Database;
use App\Models\Permission;

class SprintPermissionsAndSettingsMigration
{
    private Database $db;
    private Permission $permissionModel;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->permissionModel = new Permission();
    }
    
    /**
     * Run the migration
     */
    public function up(): bool
    {
        try {
            echo "Starting Sprint Permissions and Settings Migration...\n";

            // Step 1: Add new granular permissions
            $this->addNewPermissions();

            // Step 2: Add sprint enhancement fields
            $this->addSprintFields();

            // Step 3: Create sprint-milestone relationship table
            $this->createSprintMilestoneTable();

            // Step 4: Update sprint statuses for SCRUM workflow
            $this->updateSprintStatuses();

            // Step 5: Assign new permissions to admin role
            $this->assignPermissionsToAdmin();

            echo "Migration completed successfully!\n";
            return true;

        } catch (Exception $e) {
            echo "Migration failed: " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    /**
     * Rollback the migration
     */
    public function down(): bool
    {
        try {
            echo "Rolling back Sprint Permissions and Settings Migration...\n";

            // Remove added fields
            $this->removeSprintFields();

            // Drop sprint-milestone table
            $this->dropSprintMilestoneTable();

            // Remove added permissions
            $this->removeNewPermissions();

            // Restore original sprint statuses
            $this->restoreSprintStatuses();

            echo "Migration rollback completed!\n";
            return true;

        } catch (Exception $e) {
            echo "Migration rollback failed: " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    /**
     * Add new granular permissions
     */
    private function addNewPermissions(): void
    {
        echo "Adding new granular permissions...\n";
        
        $newPermissions = [
            'manage_sprint_settings' => 'Manage sprint configuration settings',
            'manage_task_settings' => 'Manage task configuration settings',
            'manage_milestone_settings' => 'Manage milestone configuration settings',
            'manage_project_settings' => 'Manage project configuration settings',
            'edit_settings' => 'Edit general application settings',
            'edit_security_settings' => 'Edit security configuration settings'
        ];
        
        foreach ($newPermissions as $name => $description) {
            $this->permissionModel->createIfNotExists($name, $description);
            echo "  - Added permission: {$name}\n";
        }
    }
    
    /**
     * Add new fields to sprints table
     */
    private function addSprintFields(): void
    {
        echo "Adding new sprint fields...\n";
        
        $fields = [
            'sprint_goal' => 'ALTER TABLE sprints ADD COLUMN sprint_goal TEXT NULL AFTER description',
            'planning_date' => 'ALTER TABLE sprints ADD COLUMN planning_date DATETIME NULL AFTER sprint_goal',
            'review_date' => 'ALTER TABLE sprints ADD COLUMN review_date DATETIME NULL AFTER planning_date',
            'retrospective_date' => 'ALTER TABLE sprints ADD COLUMN retrospective_date DATETIME NULL AFTER review_date',
            'capacity_hours' => 'ALTER TABLE sprints ADD COLUMN capacity_hours INT NULL AFTER retrospective_date',
            'capacity_story_points' => 'ALTER TABLE sprints ADD COLUMN capacity_story_points INT NULL AFTER capacity_hours'
        ];
        
        foreach ($fields as $fieldName => $sql) {
            // Check if field already exists
            $checkSql = "SHOW COLUMNS FROM sprints LIKE '{$fieldName}'";
            $result = $this->db->executeQuery($checkSql);
            
            if ($result->rowCount() === 0) {
                $this->db->executeInsertUpdate($sql);
                echo "  - Added field: {$fieldName}\n";
            } else {
                echo "  - Field already exists: {$fieldName}\n";
            }
        }
    }
    
    /**
     * Create sprint-milestone relationship table
     */
    private function createSprintMilestoneTable(): void
    {
        echo "Creating sprint-milestone relationship table...\n";

        $sql = "CREATE TABLE IF NOT EXISTS sprint_milestones (
            id INT(20) UNSIGNED PRIMARY KEY AUTO_INCREMENT,
            sprint_id INT(20) UNSIGNED NOT NULL,
            milestone_id INT(20) UNSIGNED NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (sprint_id) REFERENCES sprints(id) ON DELETE CASCADE,
            FOREIGN KEY (milestone_id) REFERENCES milestones(id) ON DELETE CASCADE,
            UNIQUE KEY unique_sprint_milestone (sprint_id, milestone_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->db->executeInsertUpdate($sql);
        echo "  - Created sprint_milestones table\n";
    }
    
    /**
     * Update sprint statuses for SCRUM workflow
     */
    private function updateSprintStatuses(): void
    {
        echo "Updating sprint statuses for SCRUM workflow...\n";
        
        // Add 'review' status if it doesn't exist
        $checkSql = "SELECT id FROM statuses_sprint WHERE name = 'review'";
        $result = $this->db->executeQuery($checkSql);
        
        if ($result->rowCount() === 0) {
            $insertSql = "INSERT INTO statuses_sprint (name, description) VALUES ('review', 'Sprint in review phase')";
            $this->db->executeInsertUpdate($insertSql);
            echo "  - Added 'review' status\n";
        } else {
            echo "  - 'review' status already exists\n";
        }
        
        // Update existing status descriptions for clarity
        $updates = [
            'planning' => 'Sprint is in planning phase',
            'active' => 'Sprint is currently active',
            'completed' => 'Sprint has been completed',
            'cancelled' => 'Sprint was cancelled',
            'delayed' => 'Sprint has been delayed'
        ];
        
        foreach ($updates as $status => $description) {
            $updateSql = "UPDATE statuses_sprint SET description = :description WHERE name = :status";
            $this->db->executeInsertUpdate($updateSql, [
                ':description' => $description,
                ':status' => $status
            ]);
            echo "  - Updated '{$status}' status description\n";
        }
    }
    
    /**
     * Assign new permissions to admin role
     */
    private function assignPermissionsToAdmin(): void
    {
        echo "Assigning new permissions to admin role...\n";
        
        // Get admin role ID
        $adminRoleSql = "SELECT id FROM roles WHERE name = 'admin' LIMIT 1";
        $result = $this->db->executeQuery($adminRoleSql);
        $adminRole = $result->fetch(PDO::FETCH_OBJ);
        
        if (!$adminRole) {
            echo "  - Warning: Admin role not found\n";
            return;
        }
        
        // Get new permission IDs
        $newPermissions = [
            'manage_sprint_settings',
            'manage_task_settings', 
            'manage_milestone_settings',
            'manage_project_settings',
            'edit_settings',
            'edit_security_settings'
        ];
        
        foreach ($newPermissions as $permissionName) {
            $permissionSql = "SELECT id FROM permissions WHERE name = :name";
            $result = $this->db->executeQuery($permissionSql, [':name' => $permissionName]);
            $permission = $result->fetch(PDO::FETCH_OBJ);
            
            if ($permission) {
                // Check if already assigned
                $checkSql = "SELECT 1 FROM role_permissions WHERE role_id = :role_id AND permission_id = :permission_id";
                $checkResult = $this->db->executeQuery($checkSql, [
                    ':role_id' => $adminRole->id,
                    ':permission_id' => $permission->id
                ]);
                
                if ($checkResult->rowCount() === 0) {
                    $assignSql = "INSERT INTO role_permissions (role_id, permission_id) VALUES (:role_id, :permission_id)";
                    $this->db->executeInsertUpdate($assignSql, [
                        ':role_id' => $adminRole->id,
                        ':permission_id' => $permission->id
                    ]);
                    echo "  - Assigned '{$permissionName}' to admin role\n";
                } else {
                    echo "  - Permission '{$permissionName}' already assigned to admin\n";
                }
            }
        }
    }
    
    /**
     * Remove sprint fields (for rollback)
     */
    private function removeSprintFields(): void
    {
        echo "Removing sprint fields...\n";
        
        $fields = [
            'capacity_story_points',
            'capacity_hours', 
            'retrospective_date',
            'review_date',
            'planning_date',
            'sprint_goal'
        ];
        
        foreach ($fields as $field) {
            $sql = "ALTER TABLE sprints DROP COLUMN IF EXISTS {$field}";
            $this->db->executeInsertUpdate($sql);
            echo "  - Removed field: {$field}\n";
        }
    }
    
    /**
     * Drop sprint-milestone table (for rollback)
     */
    private function dropSprintMilestoneTable(): void
    {
        echo "Dropping sprint-milestone table...\n";
        $sql = "DROP TABLE IF EXISTS sprint_milestones";
        $this->db->executeInsertUpdate($sql);
        echo "  - Dropped sprint_milestones table\n";
    }
    
    /**
     * Remove new permissions (for rollback)
     */
    private function removeNewPermissions(): void
    {
        echo "Removing new permissions...\n";
        
        $permissions = [
            'manage_sprint_settings',
            'manage_task_settings',
            'manage_milestone_settings', 
            'manage_project_settings',
            'edit_settings',
            'edit_security_settings'
        ];
        
        foreach ($permissions as $permission) {
            $sql = "DELETE FROM permissions WHERE name = :name";
            $this->db->executeInsertUpdate($sql, [':name' => $permission]);
            echo "  - Removed permission: {$permission}\n";
        }
    }
    
    /**
     * Restore original sprint statuses (for rollback)
     */
    private function restoreSprintStatuses(): void
    {
        echo "Restoring original sprint statuses...\n";
        
        // Remove 'review' status
        $sql = "DELETE FROM statuses_sprint WHERE name = 'review'";
        $this->db->executeInsertUpdate($sql);
        echo "  - Removed 'review' status\n";
    }
}

// Run migration if called directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $migration = new SprintPermissionsAndSettingsMigration();
    
    $action = $argv[1] ?? 'up';
    
    if ($action === 'up') {
        $migration->up();
    } elseif ($action === 'down') {
        $migration->down();
    } else {
        echo "Usage: php " . basename(__FILE__) . " [up|down]\n";
        exit(1);
    }
}
