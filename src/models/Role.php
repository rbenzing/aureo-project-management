<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Role
{
    private Database $db;

    public ?int $id = null;
    public string $name;
    public ?string $description = null;
    public bool $is_deleted = false;
    public array $permissions = [];
    public ?string $created_at = null;
    public ?string $updated_at = null;

    public function __construct()
    {
        // Initialize the database connection
        $this->db = Database::getInstance();
    }

    /**
     * Hydrate the object with database row data.
     */
    private function hydrate(array $data): void
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    /**
     * Find a role by its ID.
     */
    public function find(int $id): ?object
    {
        $stmt = $this->db->executeQuery(
            "SELECT * FROM roles WHERE id = :id AND is_deleted = 0",
            [':id' => $id]
        );

        $roleData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$roleData) {
            return null;
        }

        $this->hydrate($roleData);
        return $this;
    }

    /**
     * Fetch all roles (paginated).
     */
    public function getAllPaginated(int $limit = 10, int $page = 1): array
    {
        $offset = ($page - 1) * $limit;
        $stmt = $this->db->executeQuery(
            "SELECT * FROM roles WHERE is_deleted = 0 LIMIT :limit OFFSET :offset",
            [
                ':limit' => $limit,
                ':offset' => $offset,
            ]
        );

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Fetch all roles without pagination.
     */
    public function getAll(): array
    {
        $stmt = $this->db->executeQuery(
            "SELECT * FROM roles WHERE is_deleted = 0"
        );

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Get the total of all roles
     */
    public function countAll(): int
    {
        $stmt = $this->db->executeQuery(
            "SELECT COUNT(*) as total FROM roles WHERE is_deleted = 0"
        );
        return (int)$stmt->fetchColumn();
    }

    /**
     * Save a new role to the database.
     */
    public function save(): bool
    {
        $stmt = $this->db->executeQuery(
            "INSERT INTO roles (name, description, created_at, updated_at)
             VALUES (:name, :description, NOW(), NOW())",
            [
                ':name' => $this->name,
                ':description' => $this->description,
            ]
        );

        $this->id = $this->db->lastInsertId();
        return true;
    }

    /**
     * Update an existing role in the database.
     */
    public function update(): bool
    {
        if (!$this->id) {
            throw new Exception("Role ID is not set.");
        }

        $stmt = $this->db->executeQuery(
            "UPDATE roles
             SET name = :name, description = :description, updated_at = NOW()
             WHERE id = :id",
            [
                ':id' => $this->id,
                ':name' => $this->name,
                ':description' => $this->description,
            ]
        );

        return true;
    }

    /**
     * Soft delete a role by marking it as deleted.
     */
    public function delete(): bool
    {
        if (!$this->id) {
            throw new Exception("Role ID is not set.");
        }

        $stmt = $this->db->executeQuery(
            "UPDATE roles SET is_deleted = 1, updated_at = NOW() WHERE id = :id",
            [':id' => $this->id]
        );

        return true;
    }

    /**
     * Assign a permission to this role.
     */
    public function assignPermission(int $permissionId): bool
    {
        if (!$this->id) {
            throw new Exception("Role ID is not set.");
        }

        $stmt = $this->db->executeQuery(
            "INSERT INTO role_permissions (role_id, permission_id)
             VALUES (:role_id, :permission_id)
             ON DUPLICATE KEY UPDATE role_id = :role_id, permission_id = :permission_id",
            [
                ':role_id' => $this->id,
                ':permission_id' => $permissionId,
            ]
        );

        return true;
    }

    /**
     * Remove a permission from this role.
     */
    public function removePermission(int $permissionId): bool
    {
        if (!$this->id) {
            throw new Exception("Role ID is not set.");
        }

        $stmt = $this->db->executeQuery(
            "DELETE FROM role_permissions
             WHERE role_id = :role_id AND permission_id = :permission_id",
            [
                ':role_id' => $this->id,
                ':permission_id' => $permissionId,
            ]
        );

        return true;
    }

    /**
     * Sync permissions for this role (replace existing permissions with new ones).
     */
    public function syncPermissions(array $permissionIds): bool
    {
        if (!$this->id) {
            throw new Exception("Role ID is not set.");
        }

        // Remove all existing permissions for the role
        $stmt = $this->db->executeQuery("DELETE FROM role_permissions WHERE role_id = :role_id", [':role_id' => $this->id]);

        // Add the new permissions
        if (!empty($permissionIds)) {
            $placeholders = implode(',', array_fill(0, count($permissionIds), '(?, ?)'));
            $query = "INSERT INTO role_permissions (role_id, permission_id) VALUES $placeholders";
            $stmt = $this->db->executeQuery($query);

            $params = [];
            foreach ($permissionIds as $permissionId) {
                $params[] = $this->id;
                $params[] = $permissionId;
            }

            $stmt->execute($params);
        }

        return true;
    }

    /**
     * Fetch permissions assigned to this role.
     */
    public function getPermissions(): array
    {
        if (!$this->id) {
            throw new Exception("Role ID is not set.");
        }

        $stmt = $this->db->executeQuery(
            "SELECT p.* 
             FROM permissions p
             INNER JOIN role_permissions rp ON p.id = rp.permission_id
             WHERE rp.role_id = :role_id",
            [':role_id' => $this->id]
        );

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Check if a role with the given name already exists (for validation).
     */
    public static function nameExists(string $name, ?int $excludeId = null): bool
    {
        $query = "SELECT COUNT(*) FROM roles WHERE name = :name AND is_deleted = 0";
        $params = [':name' => $name];

        if ($excludeId) {
            $query .= " AND id != :id";
            $params[':id'] = $excludeId;
        }

        $stmt = Database::getInstance()->executeQuery($query, $params);
        return $stmt->fetchColumn() > 0;
    }
}