<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Permission
{
    private $db;

    public ?int $id = null;
    public string $name;
    public ?string $description = null;
    public ?string $created_at = null;
    public ?string $updated_at = null;

    public function __construct()
    {
        // Use the singleton instance of Database
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
     * Fetch all permissions.
     */
    public function getAll(): array
    {
        $stmt = $this->db->executeQuery("SELECT * FROM permissions WHERE is_deleted = 0");
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Find a permission by their ID.
     */
    public function find(int $id): ?self
    {
        $stmt = $this->db->executeQuery("SELECT * FROM permissions WHERE id = :id AND is_deleted = 0 LIMIT 1", [
            ':id' => $id,
        ]);
        $permissionData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$permissionData) {
            return null;
        }

        $this->hydrate($permissionData);
        return $this;
    }
    
    /**
     * Fetch permissions associated with a specific role.
     */
    public function getByRoleId(int $roleId): array
    {
        $stmt = $this->db->executeQuery("
            SELECT p.*
            FROM role_permissions rp
            JOIN permissions p ON rp.permission_id = p.id
            WHERE rp.role_id = :role_id AND p.is_deleted = 0
        ", [
            ':role_id' => $roleId,
        ]);

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Save or update a permission.
     */
    public function save(): bool
    {
        if ($this->id) {
            $stmt = $this->db->executeQuery("
                UPDATE permissions
                SET name = :name, description = :description, updated_at = NOW()
                WHERE id = :id
            ", [
                ':id' => $this->id,
                ':name' => $this->name,
                ':description' => $this->description,
            ]);
        } else {
            $stmt = $this->db->executeQuery("
                INSERT INTO permissions (name, description, created_at, updated_at)
                VALUES (:name, :description, NOW(), NOW())
            ", [
                ':name' => $this->name,
                ':description' => $this->description,
            ]);

            if (!$this->id) {
                $this->id = $this->db->getPdo()->lastInsertId();
            }
        }

        return true;
    }

    /**
     * Delete a permission (soft delete).
     */
    public function delete(): bool
    {
        $stmt = $this->db->executeQuery("UPDATE permissions SET is_deleted = 1, updated_at = NOW() WHERE id = :id", [
            ':id' => $this->id,
        ]);
        return $stmt->execute();
    }
}