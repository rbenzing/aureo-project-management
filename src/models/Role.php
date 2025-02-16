<?php
namespace App\Models;

use PDO;
use App\Core\Database;

class Role {
    private PDO $db;

    public ?int $id = null;
    public string $name;
    public ?string $address = null;
    public ?string $phone = null;
    public string $email;
    public ?string $website = null;
    public bool $is_deleted = false;
    public ?string $created_at = null;
    public ?string $updated_at = null;

    public function __construct() {
        // Initialize the database connection
        $this->db = Database::getInstance();
    }

    /**
     * Find a role by its ID.
     */
    public function find($id) {
        $stmt = $this->db->prepare("SELECT * FROM roles WHERE id = :id AND is_deleted = 0");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    /**
     * Fetch all roles (paginated).
     */
    public function getAllPaginated($limit = 10, $page = 1) {
        $offset = ($page - 1) * $limit;
        $stmt = $this->db->prepare("SELECT * FROM roles WHERE is_deleted = 0 LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Fetch all roles without pagination.
     */
    public function getAll() {
        $stmt = $this->db->prepare("SELECT * FROM roles WHERE is_deleted = 0");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Save a new role to the database.
     */
    public function save() {
        $stmt = $this->db->prepare("
            INSERT INTO roles (name, description, created_at, updated_at)
            VALUES (:name, :description, NOW(), NOW())
        ");
        $stmt->execute([
            'name' => $this->name,
            'description' => $this->description ?? null,
        ]);
        $this->id = $this->db->lastInsertId();
    }

    /**
     * Update an existing role in the database.
     */
    public function update() {
        $stmt = $this->db->prepare("
            UPDATE roles
            SET name = :name, description = :description, updated_at = NOW()
            WHERE id = :id
        ");
        $stmt->execute([
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description ?? null,
        ]);
    }

    /**
     * Soft delete a role by marking it as deleted.
     */
    public function delete() {
        $stmt = $this->db->prepare("UPDATE roles SET is_deleted = 1, updated_at = NOW() WHERE id = :id");
        $stmt->execute(['id' => $this->id]);
    }

    /**
     * Assign a permission to this role.
     */
    public function assignPermission($permissionId) {
        $stmt = $this->db->prepare("
            INSERT INTO role_permissions (role_id, permission_id)
            VALUES (:role_id, :permission_id)
            ON DUPLICATE KEY UPDATE role_id = :role_id, permission_id = :permission_id
        ");
        $stmt->execute([
            'role_id' => $this->id,
            'permission_id' => $permissionId,
        ]);
    }

    /**
     * Remove a permission from this role.
     */
    public function removePermission($permissionId) {
        $stmt = $this->db->prepare("
            DELETE FROM role_permissions
            WHERE role_id = :role_id AND permission_id = :permission_id
        ");
        $stmt->execute([
            'role_id' => $this->id,
            'permission_id' => $permissionId,
        ]);
    }

    /**
     * Sync permissions for this role (replace existing permissions with new ones).
     */
    public function syncPermissions($permissionIds) {
        // Remove all existing permissions for the role
        $stmt = $this->db->prepare("DELETE FROM role_permissions WHERE role_id = :role_id");
        $stmt->execute(['role_id' => $this->id]);

        // Add the new permissions
        if (!empty($permissionIds)) {
            $placeholders = implode(',', array_fill(0, count($permissionIds), '(?, ?)'));
            $query = "INSERT INTO role_permissions (role_id, permission_id) VALUES $placeholders";
            $stmt = $this->db->prepare($query);

            $params = [];
            foreach ($permissionIds as $permissionId) {
                $params[] = $this->id;
                $params[] = $permissionId;
            }

            $stmt->execute($params);
        }
    }

    /**
     * Fetch permissions assigned to this role.
     */
    public function getPermissions() {
        $stmt = $this->db->prepare("
            SELECT p.* 
            FROM permissions p
            INNER JOIN role_permissions rp ON p.id = rp.permission_id
            WHERE rp.role_id = :role_id
        ");
        $stmt->execute(['role_id' => $this->id]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Check if a role with the given name already exists (for validation).
     */
    public static function nameExists($name, $excludeId = null) {
        $query = "SELECT COUNT(*) FROM roles WHERE name = :name AND is_deleted = 0";
        $params = ['name' => $name];

        if ($excludeId) {
            $query .= " AND id != :id";
            $params['id'] = $excludeId;
        }

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }
}