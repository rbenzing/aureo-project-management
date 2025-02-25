<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
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
     * Get permissions by role ID
     * 
     * @param int $roleId
     * @return array
     * @throws RuntimeException
     */
    public function getByRoleId(int $roleId): array
    {
        $sql = "SELECT p.*
                FROM role_permissions rp
                JOIN permissions p ON rp.permission_id = p.id
                WHERE rp.role_id = :role_id 
                AND p.is_deleted = 0";

        $stmt = $this->db->executeQuery($sql, [':role_id' => $roleId]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
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
            $sql = "INSERT INTO role_permissions (role_id, permission_id) VALUES (:role_id, :permission_id)";
            foreach ($permissionIds as $permissionId) {
                $this->db->executeInsertUpdate($sql, [
                    ':role_id' => $roleId,
                    ':permission_id' => $permissionId
                ]);
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
        $permissions = $this->getAll();
        $grouped = [];

        foreach ($permissions as $permission) {
            $type = explode('_', $permission->name)[0];
            if (!isset($grouped[$type])) {
                $grouped[$type] = [];
            }
            $grouped[$type][] = $permission;
        }

        return $grouped;
    }

    /**
     * Validate permission data before save
     * 
     * @param array $data
     * @throws InvalidArgumentException
     */
    protected function beforeSave(array $data): void
    {
        parent::validate($data, $this->id);
        
        if (empty($data['name'])) {
            throw new InvalidArgumentException('Permission name is required');
        }

        // Check for unique name
        $sql = "SELECT COUNT(*) FROM permissions WHERE name = :name AND id != :id AND is_deleted = 0";
        $stmt = $this->db->executeQuery($sql, [
            ':name' => $data['name'],
            ':id' => $data['id'] ?? 0
        ]);

        if ($stmt->fetchColumn() > 0) {
            throw new InvalidArgumentException('Permission name must be unique');
        }

        // Validate permission name format
        if (!preg_match('/^[a-z_]+$/', $data['name'])) {
            throw new InvalidArgumentException('Permission name must contain only lowercase letters and underscores');
        }
    }
}