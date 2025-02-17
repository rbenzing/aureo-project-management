<?php
namespace App\Models;

use PDO;
use App\Core\Database;

class Project {
    private PDO $db;

    public ?int $id = null;
    public int $company_id;
    public string $name;
    public ?string $description = null;
    public ?string $start_date = null;
    public ?string $end_date = null;
    public ?string $website = null;
    public int $status_id;
    public ?string $created_at = null;
    public ?string $updated_at = null;
    public bool $is_deleted = false;

    public function __construct() {
        // Initialize the database connection
        $this->db = Database::getInstance();
    }

    /**
     * Find a project by its ID.
     */
    public function find($id) {
        $stmt = $this->db->prepare("SELECT 
            p.id,
            p.name,
            p.description,
            c.name as 'company_name',
            ps.name AS 'status',
            u.first_name as 'owner_firstname',
            u.last_name as 'owner_lastname',
            p.start_date,
            p.end_date,
            p.created_at
        FROM 
            projects p
        LEFT JOIN 
            project_statuses ps ON p.status_id = ps.id AND ps.is_deleted = 0
        LEFT JOIN 
            companies c ON c.id = p.company_id AND c.is_deleted = 0
        LEFT JOIN 
            users u ON u.id = p.owner_id AND u.is_deleted = 0
        WHERE 
            p.id = :project_id
            AND p.is_deleted = 0");
        $stmt->execute([
            'project_id' => $id
        ]);
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
     * Get all project statuses
     */
    public function getAllStatuses() {
        $stmt = $this->db->prepare("SELECT * FROM project_statuses WHERE is_deleted = 0");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Fetch all projects without pagination.
     */
    public function getAll() {
        $stmt = $this->db->prepare("SELECT * FROM projects WHERE is_deleted = 0");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
     * Fetch tasks and subtasks grouped by status for a project.
     *
     * @param int $projectId The project ID.
     * @return array Tasks grouped by status.
     */
    public function getProjectTasks($projectId) {
        $query = "
            SELECT 
                t.id AS task_id,
                t.title AS task_title,
                t.description AS task_description,
                ts.name AS task_status,
                t.due_date AS task_due_date
            FROM 
                tasks t
            LEFT JOIN
                task_statuses ts ON t.status_id = ts.id AND ts.is_deleted = 0
            WHERE 
                t.project_id = :project_id
                AND t.is_deleted = 0
            GROUP BY 
                t.id, t.title, t.description, ts.name, t.due_date
            ORDER BY 
                FIELD(ts.name, 'to_do', 'in_progress', 'done');
        ";

        $stmt = $this->db->prepare($query);
        $stmt->execute(['project_id' => $projectId]);
        $rawData = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Organize tasks by status
        $tasks = [
            'to_do' => [],
            'in_progress' => [],
            'done' => [],
        ];

        foreach ($rawData as $row) {
            $task = [
                'id' => $row['task_id'],
                'title' => $row['task_title'],
                'description' => $row['task_description'],
                'status' => $row['task_status'],
                'due_date' => $row['task_due_date'],
                'subtasks' => json_decode($row['subtasks'], true) ?: [],
            ];

            $tasks[$row['task_status']][] = $task;
        }

        return $tasks;
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

    /**
     * Get recent projects for a user.
     *
     * @param int $userId The user ID.
     * @return array An array of project objects.
     */
    public function getRecentProjectsByUserId($userId) {
        $stmt = $this->db->prepare("
            SELECT DISTINCT p.* 
            FROM projects p
            INNER JOIN users u ON u.id = p.owner_id
            WHERE u.id = :user_id AND u.is_deleted = 0
            ORDER BY p.created_at DESC
            LIMIT 5
        ");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }    
}