<?php
// file: Models/Role.php
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
    public array $permissions = [];

    /**
     * Define fillable fields
     */
    protected array $fillable = [
        'name',
        'description'
    ];

    /**
     * Define searchable fields
     */
    protected array $searchable = [
        'name',
        'description'
    ];

    /**
     * Define validation rules
     */
    protected array $validationRules = [
        'name' => ['required', 'string', 'unique'],
        'description' => ['string']
    ];

    /**
     * Find role with permissions
     * 
     * @param int $id
     * @return object|null
     */
    public function findWithPermissions(int $id): ?object
    {
        try {
            $role = $this->find($id);

            if ($role) {
                $role->permissions = $this->getPermissions($id);
            }

            return $role;
        } catch (\Exception $e) {
            throw new RuntimeException("Failed to find role with permissions: " . $e->getMessage());
        }
    }

    /**
     * Get permissions for a role
     * 
     * @param int $roleId
     * @return array
     */
    public function getPermissions(int $roleId): array
    {
        try {
            $sql = "SELECT p.* 
                    FROM permissions p
                    INNER JOIN role_permissions rp ON p.id = rp.permission_id
                    WHERE rp.role_id = :role_id
                    AND p.is_deleted = 0";

            $stmt = $this->db->executeQuery($sql, [':role_id' => $roleId]);
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            throw new RuntimeException("Failed to get role permissions: " . $e->getMessage());
        }
    }

    /**
     * Get users for a role
     * 
     * @param int $roleId
     * @return array
     */
    public function getUsers(int $roleId): array
    {
        try {
            $sql = "SELECT u.*, 
                          c.name as company_name
                    FROM users u
                    LEFT JOIN companies c ON u.company_id = c.id
                    WHERE u.role_id = :role_id
                    AND u.is_deleted = 0";

            $stmt = $this->db->executeQuery($sql, [':role_id' => $roleId]);
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            throw new RuntimeException("Failed to get role users: " . $e->getMessage());
        }
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
                $placeholders = [];
                $params = [];

                foreach ($permissionIds as $index => $permissionId) {
                    $placeholders[] = "(:role_id, :permission_id_{$index})";
                    $params[":permission_id_{$index}"] = $permissionId;
                }

                $params[':role_id'] = $roleId;

                $sql = "INSERT INTO role_permissions (role_id, permission_id) VALUES " .
                    implode(',', $placeholders);

                $this->db->executeInsertUpdate($sql, $params);
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
        try {
            $sql = "INSERT INTO role_permissions (role_id, permission_id)
                    VALUES (:role_id, :permission_id)
                    ON DUPLICATE KEY UPDATE role_id = :role_id";

            return $this->db->executeInsertUpdate($sql, [
                ':role_id' => $roleId,
                ':permission_id' => $permissionId
            ]);
        } catch (\Exception $e) {
            throw new RuntimeException("Failed to assign permission: " . $e->getMessage());
        }
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
        try {
            $sql = "DELETE FROM role_permissions 
                    WHERE role_id = :role_id 
                    AND permission_id = :permission_id";

            return $this->db->executeInsertUpdate($sql, [
                ':role_id' => $roleId,
                ':permission_id' => $permissionId
            ]);
        } catch (\Exception $e) {
            throw new RuntimeException("Failed to remove permission: " . $e->getMessage());
        }
    }

    /**
     * Get all roles with permission counts
     * 
     * @return array
     */
    public function getAllWithPermissionCounts(): array
    {
        try {
            $sql = "SELECT r.*, 
                    (
                        SELECT COUNT(*) 
                        FROM role_permissions rp 
                        WHERE rp.role_id = r.id
                    ) as permission_count,
                    (
                        SELECT COUNT(*) 
                        FROM users u 
                        WHERE u.role_id = r.id AND u.is_deleted = 0
                    ) as user_count
                    FROM roles r
                    WHERE r.is_deleted = 0
                    ORDER BY r.name ASC";

            $stmt = $this->db->executeQuery($sql);
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            throw new RuntimeException("Failed to get roles with permission counts: " . $e->getMessage());
        }
    }

    /**
     * Check if a role has a specific permission
     * 
     * @param int $roleId
     * @param int $permissionId
     * @return bool
     */
    public function hasPermission(int $roleId, int $permissionId): bool
    {
        try {
            $sql = "SELECT COUNT(*) FROM role_permissions 
                    WHERE role_id = :role_id AND permission_id = :permission_id";

            $stmt = $this->db->executeQuery($sql, [
                ':role_id' => $roleId,
                ':permission_id' => $permissionId
            ]);

            return (bool)$stmt->fetchColumn();
        } catch (\Exception $e) {
            throw new RuntimeException("Failed to check permission: " . $e->getMessage());
        }
    }

    /**
     * Check if a role has a specific permission by name
     * 
     * @param int $roleId
     * @param string $permissionName
     * @return bool
     */
    public function hasPermissionByName(int $roleId, string $permissionName): bool
    {
        try {
            $sql = "SELECT COUNT(*) FROM role_permissions rp
                    JOIN permissions p ON rp.permission_id = p.id
                    WHERE rp.role_id = :role_id 
                    AND p.name = :permission_name
                    AND p.is_deleted = 0";

            $stmt = $this->db->executeQuery($sql, [
                ':role_id' => $roleId,
                ':permission_name' => $permissionName
            ]);

            return (bool)$stmt->fetchColumn();
        } catch (\Exception $e) {
            throw new RuntimeException("Failed to check permission by name: " . $e->getMessage());
        }
    }

    /**
     * Get all roles with additional details and pagination
     * 
     * @param int $page Current page number
     * @param int $limit Items per page
     * @return array
     */
    public function getAllWithDetails(int $page = 1, int $limit = 10): array
    {
        try {
            $offset = ($page - 1) * $limit;

            $sql = "SELECT r.*,
                    (SELECT COUNT(*) FROM users u WHERE u.role_id = r.id AND u.is_deleted = 0) as user_count,
                    (SELECT COUNT(*) FROM role_permissions rp WHERE rp.role_id = r.id) as permission_count
                FROM roles r
                WHERE r.is_deleted = 0
                ORDER BY r.created_at DESC
                LIMIT :limit OFFSET :offset";

            $stmt = $this->db->executeQuery($sql, [
                ':limit' => $limit,
                ':offset' => $offset
            ]);
            $roles = $stmt->fetchAll(PDO::FETCH_OBJ);

            // Get total count for pagination
            $countSql = "SELECT COUNT(*) FROM roles WHERE is_deleted = 0";
            $totalStmt = $this->db->executeQuery($countSql);
            $total = $totalStmt->fetchColumn();

            return [
                'records' => $roles,
                'total' => $total
            ];
        } catch (\Exception $e) {
            throw new RuntimeException("Failed to get roles with details: " . $e->getMessage());
        }
    }
}
