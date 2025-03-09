<?php
// file: Models/Permission.php
declare(strict_types=1);

namespace App\Models;

use PDO;
use RuntimeException;
use InvalidArgumentException;

/**
 * Permission Model
 * 
 * Handles all permission-related database operations
 */
class Permission extends BaseModel
{
    protected string $table = 'permissions';
    
    /**
     * Permission properties
     */
    public ?int $id = null;
    public string $name;
    public ?string $description = null;
    public ?string $created_at = null;
    public ?string $updated_at = null;
    
    /**
     * Define fillable fields
     */
    protected array $fillable = [
        'name', 'description'
    ];
    
    /**
     * Define searchable fields
     */
    protected array $searchable = [
        'name', 'description'
    ];
    
    /**
     * Define validation rules
     */
    protected array $validationRules = [
        'name' => ['required', 'string', 'unique'],
        'description' => ['string']
    ];

    /**
     * Get permissions by role ID
     * 
     * @param int $roleId
     * @return array
     * @throws RuntimeException
     */
    public function getByRoleId(int $roleId): array
    {
        try {
            $sql = "SELECT p.*
                    FROM role_permissions rp
                    JOIN permissions p ON rp.permission_id = p.id
                    WHERE rp.role_id = :role_id 
                    AND p.is_deleted = 0";

            $stmt = $this->db->executeQuery($sql, [':role_id' => $roleId]);
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            throw new RuntimeException("Failed to get permissions for role: " . $e->getMessage());
        }
    }

    /**
     * Associate permissions with a role
     * 
     * @param int $roleId
     * @param array $permissionIds
     * @return bool
     * @throws RuntimeException
     */
    public function assignToRole(int $roleId, array $permissionIds): bool
    {
        try {
            $this->db->beginTransaction();

            // First, remove existing permissions
            $sql = "DELETE FROM role_permissions WHERE role_id = :role_id";
            $this->db->executeInsertUpdate($sql, [':role_id' => $roleId]);

            // Then, add new permissions
            if (!empty($permissionIds)) {
                foreach ($permissionIds as $permissionId) {
                    $sql = "INSERT INTO role_permissions (role_id, permission_id) 
                            VALUES (:role_id, :permission_id)";
                    $this->db->executeInsertUpdate($sql, [
                        ':role_id' => $roleId,
                        ':permission_id' => $permissionId
                    ]);
                }
            }

            $this->db->commit();
            return true;

        } catch (\Exception $e) {
            $this->db->rollBack();
            throw new RuntimeException("Failed to assign permissions: " . $e->getMessage());
        }
    }

    /**
     * Get permissions grouped by type
     * 
     * @return array
     */
    public function getGroupedPermissions(): array
    {
        try {
            $permissions = $this->getAll(['is_deleted' => 0])['records'];
            $grouped = [];

            foreach ($permissions as $permission) {
                $parts = explode('_', $permission->name);
                $type = $parts[0] ?? 'other';  // Default group is 'other'
                
                if (!isset($grouped[$type])) {
                    $grouped[$type] = [];
                }
                $grouped[$type][] = $permission;
            }

            // Sort groups alphabetically
            ksort($grouped);
            
            return $grouped;
        } catch (\Exception $e) {
            throw new RuntimeException("Failed to group permissions: " . $e->getMessage());
        }
    }
    
    /**
     * Check if permission exists by name
     * 
     * @param string $name
     * @return bool
     */
    public function existsByName(string $name): bool
    {
        try {
            $sql = "SELECT COUNT(*) FROM permissions WHERE name = :name AND is_deleted = 0";
            $stmt = $this->db->executeQuery($sql, [':name' => $name]);
            return (bool)$stmt->fetchColumn();
        } catch (\Exception $e) {
            throw new RuntimeException("Failed to check if permission exists: " . $e->getMessage());
        }
    }
    
    /**
     * Get permission by name
     * 
     * @param string $name
     * @return object|null
     */
    public function getByName(string $name): ?object
    {
        try {
            $sql = "SELECT * FROM permissions WHERE name = :name AND is_deleted = 0";
            $stmt = $this->db->executeQuery($sql, [':name' => $name]);
            $result = $stmt->fetch(PDO::FETCH_OBJ);
            return $result ?: null;
        } catch (\Exception $e) {
            throw new RuntimeException("Failed to get permission by name: " . $e->getMessage());
        }
    }
    
    /**
     * Create a new permission if it doesn't exist
     * 
     * @param string $name
     * @param string|null $description
     * @return int Permission ID
     */
    public function createIfNotExists(string $name, ?string $description = null): int
    {
        try {
            // Check if permission already exists
            $permission = $this->getByName($name);
            if ($permission) {
                return $permission->id;
            }
            
            // Create new permission
            $permissionData = [
                'name' => $name,
                'description' => $description
            ];
            
            return $this->create($permissionData);
        } catch (\Exception $e) {
            throw new RuntimeException("Failed to create permission: " . $e->getMessage());
        }
    }

    /**
     * Bulk create permissions
     * 
     * @param array $permissions Array of permission data
     * @return array Created permission IDs
     */
    public function bulkCreate(array $permissions): array
    {
        try {
            $this->db->beginTransaction();
            
            $createdIds = [];
            foreach ($permissions as $permission) {
                // Check if permission already exists
                $existing = $this->getByName($permission['name']);
                if ($existing) {
                    $createdIds[] = $existing->id;
                    continue;
                }
                
                // Create new permission
                $createdIds[] = $this->create([
                    'name' => $permission['name'],
                    'description' => $permission['description'] ?? null
                ]);
            }
            
            $this->db->commit();
            return $createdIds;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw new RuntimeException("Failed to bulk create permissions: " . $e->getMessage());
        }
    }
}