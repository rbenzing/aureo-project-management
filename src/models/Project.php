<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Project
{
    private Database $db;

    public ?int $id = null;
    public int $company_id;
    public string $name;
    public ?string $description = null;
    public ?string $start_date = null;
    public ?string $end_date = null;
    public int $status_id;
    public ?int $owner_id = null;
    public ?string $created_at = null;
    public ?string $updated_at = null;
    public bool $is_deleted = false;
    public array $tasks = [];

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
     * Find a project by its ID.
     */
    public function find(int $id): ?self
    {
        $stmt = $this->db->executeQuery(
            "SELECT 
                p.id,
                p.name,
                p.description,
                p.company_id,
                p.status_id,
                c.name AS company_name,
                ps.name AS project_status,
                u.first_name AS owner_firstname,
                u.last_name AS owner_lastname,
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
                AND p.is_deleted = 0",
            [':project_id' => $id]
        );

        $projectData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$projectData) {
            return null;
        }

        $this->hydrate($projectData);
        $this->tasks = $this->getTasksByProjectId($id);

        return $this;
    }

    /**
     * Fetch all projects (paginated).
     */
    public function getAllPaginated(int $limit = 10, int $page = 1): ?array
    {
        $offset = ($page - 1) * $limit;
        $stmt = $this->db->executeQuery(
            "SELECT 
                p.id,
                p.name,
                p.description,
                p.company_id,
                p.status_id,
                p.owner_id,
                c.name AS company_name,
                ps.name AS status,
                u.first_name AS owner_firstname,
                u.last_name AS owner_lastname,
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
                p.is_deleted = 0
            LIMIT :limit OFFSET :offset",
            [
                ':limit' => $limit,
                ':offset' => $offset,
            ]
        );

        $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch tasks for each project
        foreach ($projects as &$project) {
            $project['tasks'] = $this->getTasksByProjectId($project['id']);
        }

        return $projects;
    }

    /**
     * Get all project statuses.
     */
    public function getAllStatuses(): array
    {
        $stmt = $this->db->executeQuery(
            "SELECT * FROM project_statuses WHERE is_deleted = 0"
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Fetch all projects from the database without pagination
     */
    public function getAll(): array
    {
        $stmt = $this->db->executeQuery(
            "SELECT 
                p.id,
                p.name,
                p.description,
                p.company_id,
                p.status_id,
                p.owner_id,
                c.name AS company_name,
                ps.name AS status,
                u.first_name AS owner_firstname,
                u.last_name AS owner_lastname,
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
                p.is_deleted = 0"
        );

        $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch tasks for each project
        foreach ($projects as &$project) {
            $project['tasks'] = $this->getTasksByProjectId($project['id']);
        }

        return $projects;
    }

    /**
     * Get the total of all projects
     */
    public function countAll(): int
    {
        $stmt = $this->db->executeQuery(
            "SELECT COUNT(*) as total FROM projects WHERE is_deleted = 0"
        );
        return (int)$stmt->fetchColumn();
    }

    /**
     * Fetch all projects associated with a specific company.
     */
    public function getByCompanyId(int $companyId): array
    {
        $stmt = $this->db->executeQuery(
            "SELECT * FROM projects WHERE company_id = :company_id AND is_deleted = 0",
            [':company_id' => $companyId]
        );

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Save or update a project.
     */
    public function save(): bool
    {
        if ($this->id) {
            $stmt = $this->db->executeQuery(
                "UPDATE projects
                 SET company_id = :company_id, name = :name, description = :description, status_id = :status_id, updated_at = NOW()
                 WHERE id = :id",
                [
                    ':id' => $this->id,
                    ':company_id' => $this->company_id,
                    ':name' => $this->name,
                    ':description' => $this->description,
                    ':status_id' => $this->status_id,
                ]
            );
        } else {
            $stmt = $this->db->executeQuery(
                "INSERT INTO projects (company_id, name, description, owner_id, status_id, created_at, updated_at)
                 VALUES (:company_id, :name, :description, :owner_id, :status_id, NOW(), NOW())",
                [
                    ':company_id' => $this->company_id,
                    ':name' => $this->name,
                    ':description' => $this->description,
                    ':status_id' => $this->status_id,
                    ':owner_id' => $this->owner_id,
                ]
            );

            if (!$this->id) {
                $this->id = $this->db->lastInsertId();
            }
        }

        return true;
    }

    /**
     * Fetch tasks associated with a specific project.
     */
    private function getTasksByProjectId(int $projectId): array
    {
        $stmt = $this->db->executeQuery(
            "SELECT 
                t.id,
                t.title,
                t.description,
                t.priority,
                ts.name AS status,
                t.project_id,
                t.assigned_to,
                u.first_name AS assignee_firstname,
                u.last_name AS assignee_lastname,
                t.due_date,
                t.is_hourly,
                t.hourly_rate,
                t.time_spent,
                t.start_date,
                t.complete_date
            FROM 
                tasks t
            LEFT JOIN 
                task_statuses ts ON t.status_id = ts.id AND ts.is_deleted = 0
            LEFT JOIN 
                users u ON u.id = t.assigned_to AND u.is_deleted = 0
            WHERE 
                t.is_subtask = 0 AND t.project_id = :project_id AND t.is_deleted = 0",
            [
                ':project_id' => $projectId,
            ]
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Soft delete a project by marking it as deleted.
     */
    public function delete(): bool
    {
        if (!$this->id) {
            throw new Exception("Project ID is not set.");
        }

        $stmt = $this->db->executeQuery(
            "UPDATE projects SET is_deleted = 1, updated_at = NOW() WHERE id = :id",
            [':id' => $this->id]
        );

        return true;
    }

    /**
     * Fetch users assigned to this project.
     */
    public function getUsers(): array
    {
        $stmt = $this->db->executeQuery(
            "SELECT u.* 
             FROM users u
             INNER JOIN tasks t ON u.id = t.assigned_to
             WHERE t.project_id = :project_id AND u.is_deleted = 0
             GROUP BY u.id",
            [':project_id' => $this->id]
        );

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Check if a project with the given name already exists for the same company (for validation).
     */
    public static function nameExistsInCompany(string $name, int $companyId, ?int $excludeId = null): bool
    {
        $query = "SELECT COUNT(*) FROM projects WHERE name = :name AND company_id = :company_id AND is_deleted = 0";
        $params = [':name' => $name, ':company_id' => $companyId];

        if ($excludeId) {
            $query .= " AND id != :id";
            $params[':id'] = $excludeId;
        }

        $stmt = Database::getInstance()->executeQuery($query, $params);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Get recent projects for a user.
     *
     * @param int $userId The user ID.
     * @return array An array of project objects.
     */
    public function getRecentProjectsByUserId(int $userId): array
    {
        $stmt = $this->db->executeQuery(
            "SELECT DISTINCT p.* 
             FROM projects p
             INNER JOIN users u ON u.id = p.owner_id
             WHERE u.id = :user_id AND u.is_deleted = 0
             ORDER BY p.created_at DESC
             LIMIT 5",
            [':user_id' => $userId]
        );

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}