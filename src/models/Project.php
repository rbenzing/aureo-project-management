<?php
namespace App\Models;

use PDO;
use App\Core\Database;

class Project {
    private PDO $db;

    public function __construct() {
        // Initialize the database connection
        $this->db = Database::getInstance();
    }

    /**
     * Find a project by its ID.
     */
    public function find($id) {
        $stmt = $this->db->prepare("SELECT * FROM projects WHERE id = :id AND is_deleted = 0");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    /**
     * Fetch all projects (paginated).
     */
    public function getAllPaginated($limit = 10, $page = 1) {
        $offset = ($page - 1) * $limit;
        $stmt = $this->db->prepare("SELECT * FROM projects WHERE is_deleted = 0 LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Fetch all projects without pagination.
     */
    public function getAll() {
        $stmt = $this->db->prepare("SELECT * FROM projects WHERE is_deleted = 0");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Fetch all projects associated with a specific company.
     */
    public function getByCompanyId($companyId) {
        $stmt = $this->db->prepare("
            SELECT * FROM projects 
            WHERE company_id = :company_id AND is_deleted = 0
        ");
        $stmt->execute(['company_id' => $companyId]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Save a new project to the database.
     */
    public function save() {
        $stmt = $this->db->prepare("
            INSERT INTO projects (company_id, name, description, status_id, created_at, updated_at)
            VALUES (:company_id, :name, :description, :status_id, NOW(), NOW())
        ");
        $stmt->execute([
            'company_id' => $this->company_id,
            'name' => $this->name,
            'description' => $this->description ?? null,
            'status_id' => $this->status_id,
        ]);
        $this->id = $this->db->lastInsertId();
    }

    /**
     * Update an existing project in the database.
     */
    public function update() {
        $stmt = $this->db->prepare("
            UPDATE projects
            SET company_id = :company_id, name = :name, description = :description, status_id = :status_id, updated_at = NOW()
            WHERE id = :id
        ");
        $stmt->execute([
            'id' => $this->id,
            'company_id' => $this->company_id,
            'name' => $this->name,
            'description' => $this->description ?? null,
            'status_id' => $this->status_id,
        ]);
    }

    /**
     * Soft delete a project by marking it as deleted.
     */
    public function delete() {
        $stmt = $this->db->prepare("UPDATE projects SET is_deleted = 1, updated_at = NOW() WHERE id = :id");
        $stmt->execute(['id' => $this->id]);
    }

    /**
     * Fetch tasks associated with this project.
     */
    public function getTasks() {
        $stmt = $this->db->prepare("SELECT * FROM tasks WHERE project_id = :project_id AND is_deleted = 0");
        $stmt->execute(['project_id' => $this->id]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Fetch users assigned to this project.
     */
    public function getUsers() {
        $stmt = $this->db->prepare("
            SELECT u.* 
            FROM users u
            INNER JOIN tasks t ON u.id = t.assigned_to
            WHERE t.project_id = :project_id AND u.is_deleted = 0
            GROUP BY u.id
        ");
        $stmt->execute(['project_id' => $this->id]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Check if a project with the given name already exists for the same company (for validation).
     */
    public static function nameExistsInCompany($name, $companyId, $excludeId = null) {
        $query = "SELECT COUNT(*) FROM projects WHERE name = :name AND company_id = :company_id AND is_deleted = 0";
        $params = ['name' => $name, 'company_id' => $companyId];

        if ($excludeId) {
            $query .= " AND id != :id";
            $params['id'] = $excludeId;
        }

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }
}