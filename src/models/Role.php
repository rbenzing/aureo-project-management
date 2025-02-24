<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;
use RuntimeException;
use InvalidArgumentException;

/**
 * Role Model
 * 
 * Handles all role-related database operations
 */
class Role extends BaseModel
{
    protected string $table = 'roles';
    
    /**
     * Role properties
     */
    public ?int $id = null;
    public string $name;
    public ?string $description = null;
    public bool $is_deleted = false;
    public ?string $created_at = null;
    public ?string $updated_at = null;

    /**
     * Find role with permissions
     * 
     * @param int $id
     * @return object|null
     */
    public function findWithPermissions(int $id): ?object
    {
        $role = $this->find($id);
        
        if ($role) {
            $role->permissions = $this->getPermissions($id);
        }

        return $role;
    }

    /**
     * Get permissions for a role
     * 
     * @param int $roleId
     * @return array
     */
    public function getPermissions(int $roleId): array
    {
        $sql = "SELECT p.* 
                FROM permissions p
                INNER JOIN role_permissions rp ON p.id = rp.permission_id
                WHERE rp.role_id = :role_id
                AND p.is_deleted = 0";

        $stmt = $this->db->executeQuery($sql, [':role_id' => $roleId]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Assign permissions to role
     * 
     * @param int $roleId
     * @param array $permissionIds
     * @return bool
     * @throws RuntimeException
     */
    public function syncPermissions(int $roleId, array $permissionIds): bool
    {
        try {
            $this->db->beginTransaction();

            // Remove existing permissions
            $sql = "DELETE FROM role_permissions WHERE role_id = :role_id";
            $this->db->executeInsertUpdate($sql, [':role_id' => $roleId]);

            // Add new permissions if any
            if (!empty($permissionIds)) {
                $values = array_map(function($id) use ($roleId) {
                    return "($roleId, $id)";
                }, $permissionIds);

                $sql = "INSERT INTO role_permissions (role_id, permission_id) VALUES " . 
                       implode(',', $values);
                
                $this->db->executeInsertUpdate($sql);
            }

            $this->db->commit();
            return true;

        } catch (\Exception $e) {
            $this->db->rollBack();
            throw new RuntimeException("Failed to sync permissions: " . $e->getMessage());
        }
    }

    /**
     * Add single permission to role
     * 
     * @param int $roleId
     * @param int $permissionId
     * @return bool
     */
    public function assignPermission(int $roleId, int $permissionId): bool
    {
        $sql = "INSERT INTO role_permissions (role_id, permission_id)
                VALUES (:role_id, :permission_id)
                ON DUPLICATE KEY UPDATE role_id = :role_id";

        return $this->db->executeInsertUpdate($sql, [
            ':role_id' => $roleId,
            ':permission_id' => $permissionId
        ]);
    }

    /**
     * Remove single permission from role
     * 
     * @param int $roleId
     * @param int $permissionId
     * @return bool
     */
    public function removePermission(int $roleId, int $permissionId): bool
    {
        $sql = "DELETE FROM role_permissions 
                WHERE role_id = :role_id 
                AND permission_id = :permission_id";

        return $this->db->executeInsertUpdate($sql, [
            ':role_id' => $roleId,
            ':permission_id' => $permissionId
        ]);
    }

    /**
     * Get roles with aggregated data
     * 
     * @return array
     */
    public function getRolesWithStats(): array
    {
        $sql = "SELECT 
                    r.*,
                    COUNT(DISTINCT u.id) as user_count,
                    COUNT(DISTINCT rp.permission_id) as permission_count
                FROM roles r
                LEFT JOIN users u ON u.role_id = r.id AND u.is_deleted = 0
                LEFT JOIN role_permissions rp ON rp.role_id = r.id
                WHERE r.is_deleted = 0
                GROUP BY r.id";

        $stmt = $this->db->executeQuery($sql);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Validate role data before save
     * 
     * @param array $data
     * @throws InvalidArgumentException
     */
    protected function beforeSave(array $data): void
    {
        if (empty($data['name'])) {
            throw new InvalidArgumentException('Role name is required');
        }

        // Check for unique name
        $sql = "SELECT COUNT(*) FROM roles 
                WHERE name = :name 
                AND id != :id 
                AND is_deleted = 0";

        $stmt = $this->db->executeQuery($sql, [
            ':name' => $data['name'],
            ':id' => $data['id'] ?? 0
        ]);

        if ($stmt->fetchColumn() > 0) {
            throw new InvalidArgumentException('Role name must be unique');
        }

        // Validate role name format
        if (!preg_match('/^[a-zA-Z0-9_\- ]+$/', $data['name'])) {
            throw new InvalidArgumentException('Role name can only contain letters, numbers, spaces, underscores and hyphens');
        }
    }
}