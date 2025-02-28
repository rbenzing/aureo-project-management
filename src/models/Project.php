<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;
use RuntimeException;
use InvalidArgumentException;

/**
 * Project Model
 * 
 * Handles all project-related database operations
 */
class Project extends BaseModel
{
    protected string $table = 'projects';
    
    /**
     * Project properties
     */
    public ?int $id = null;
    public int $company_id;
    public int $owner_id;
    public string $name;
    public ?string $description = null;
    public ?string $start_date = null;
    public ?string $end_date = null;
    public int $status_id;
    public ?string $created_at = null;
    public ?string $updated_at = null;
    public bool $is_deleted = false;

    /**
     * Get project with full details
     * 
     * @param int $id
     * @return object|null
     */
    public function findWithDetails(int $id): ?object
    {
        $sql = "SELECT 
                p.*,
                c.name AS company_name,
                ps.name AS project_status,
                u.first_name AS owner_firstname,
                u.last_name AS owner_lastname
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
                AND p.is_deleted = 0";

        $stmt = $this->db->executeQuery($sql, [':project_id' => $id]);
        $project = $stmt->fetch(PDO::FETCH_OBJ);

        if ($project) {
            $project->tasks = $this->getProjectTasks($id);
        }

        return $project ?: null;
    }

    /**
     * Get all projects with full details
     * 
     * @param int $limit
     * @param int $page
     * @param array $filters
     * @return array
     */
    public function getAllWithDetails(int $limit = 10, int $page = 1, array $filters = []): array
    {
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT 
                p.*,
                c.name AS company_name,
                ps.name AS status,
                u.first_name AS owner_firstname,
                u.last_name AS owner_lastname
            FROM 
                projects p
            LEFT JOIN 
                project_statuses ps ON p.status_id = ps.id AND ps.is_deleted = 0
            LEFT JOIN 
                companies c ON c.id = p.company_id AND c.is_deleted = 0
            LEFT JOIN 
                users u ON u.id = p.owner_id AND u.is_deleted = 0
            WHERE 
                p.is_deleted = 0 ";

        // Add filters
        $params = [];
        if (!empty($filters)) {
            foreach ($filters as $key => $value) {
                $sql .= " AND p.$key = :$key";
                $params[":$key"] = $value;
            }
        }

        $sql .= " LIMIT :limit OFFSET :offset";
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;

        $stmt = $this->db->executeQuery($sql, $params);
        $projects = $stmt->fetchAll(PDO::FETCH_OBJ);

        // Add tasks to each project
        foreach ($projects as $project) {
            $project->tasks = $this->getProjectTasks($project->id);
        }

        return $projects;
    }

    /**
     * Get project tasks
     * 
     * @param int $projectId
     * @return array
     */
    public function getProjectTasks(int $projectId): array
    {
        $sql = "SELECT 
                t.*,
                ts.name AS status,
                u.first_name AS assignee_firstname,
                u.last_name AS assignee_lastname
            FROM 
                tasks t
            LEFT JOIN 
                task_statuses ts ON t.status_id = ts.id AND ts.is_deleted = 0
            LEFT JOIN 
                users u ON u.id = t.assigned_to AND u.is_deleted = 0
            WHERE 
                t.is_subtask = 0 
                AND t.project_id = :project_id 
                AND t.is_deleted = 0";

        $stmt = $this->db->executeQuery($sql, [':project_id' => $projectId]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Get project statuses
     * 
     * @return array
     */
    public function getAllStatuses(): array
    {
        $sql = "SELECT * FROM project_statuses WHERE is_deleted = 0";
        $stmt = $this->db->executeQuery($sql);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Get projects by company
     * 
     * @param int $companyId
     * @return array
     */
    public function getByCompanyId(int $companyId): array
    {
        return $this->getAll(['company_id' => $companyId]);
    }

    /**
     * Get project users
     * 
     * @return array
     */
    public function getUsers(): array
    {
        if (!$this->id) {
            throw new RuntimeException("Project ID is not set");
        }

        $sql = "SELECT DISTINCT u.* 
                FROM users u
                INNER JOIN tasks t ON u.id = t.assigned_to
                WHERE t.project_id = :project_id 
                AND u.is_deleted = 0";

        $stmt = $this->db->executeQuery($sql, [':project_id' => $this->id]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Get recent projects by user
     * 
     * @param int $userId
     * @param int $limit
     * @return array
     */
    public function getRecentByUser(int $userId, int $limit = 5): array
    {
        $sql = "SELECT DISTINCT p.* 
                FROM projects p
                WHERE p.owner_id = :user_id 
                AND p.is_deleted = 0
                ORDER BY p.created_at DESC
                LIMIT :limit";

        $stmt = $this->db->executeQuery($sql, [
            ':user_id' => $userId,
            ':limit' => $limit
        ]);

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Validate project data before save
     * 
     * @param array $data
     * @throws InvalidArgumentException
     */
    protected function beforeSave(array $data): void
    {
        parent::validate($data, $this->id);
        
        if (empty($data['name'])) {
            throw new InvalidArgumentException('Project name is required');
        }

        if (empty($data['company_id'])) {
            throw new InvalidArgumentException('Company ID is required');
        }

        if (empty($data['status_id'])) {
            throw new InvalidArgumentException('Status ID is required');
        }

        // Check unique name within company
        $sql = "SELECT COUNT(*) FROM projects 
                WHERE name = :name 
                AND company_id = :company_id 
                AND id != :id 
                AND is_deleted = 0";

        $stmt = $this->db->executeQuery($sql, [
            ':name' => $data['name'],
            ':company_id' => $data['company_id'],
            ':id' => $data['id'] ?? 0
        ]);

        if ($stmt->fetchColumn() > 0) {
            throw new InvalidArgumentException('Project name must be unique within the company');
        }

        // Validate dates
        if (!empty($data['start_date']) && !empty($data['end_date'])) {
            if (strtotime($data['end_date']) < strtotime($data['start_date'])) {
                throw new InvalidArgumentException('End date cannot be earlier than start date');
            }
        }
    }

    /**
     * Transforms a string into key code format
     */
    public function transformKeyCodeFormat($input) {
        // 1. Remove all non-alphabetic characters (this includes spaces).
        $filtered = preg_replace('/[^A-Za-z]/', '', $input);
    
        // 2. Extract the first 4 characters.
        $firstFour = substr($filtered, 0, 4);
    
        // 3. Convert the result to uppercase.
        $result = strtoupper($firstFour);
    
        return $result;
    }
}