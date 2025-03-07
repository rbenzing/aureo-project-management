<?php
// file: Models/Project.php
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
     * Define fillable fields
     */
    protected array $fillable = [
        'company_id', 'owner_id', 'name', 'description', 
        'start_date', 'end_date', 'status_id', 'key_code'
    ];
    
    /**
     * Define searchable fields
     */
    protected array $searchable = [
        'name', 'description'
    ];
    
    /**
     * Define validation rules
     */
    protected array $validationRules = [
        'name' => ['required', 'string'],
        'company_id' => ['required'],
        'owner_id' => ['required'],
        'status_id' => ['required']
    ];

    /**
     * Get project with full details
     * 
     * @param int $id
     * @return object|null
     */
    public function findWithDetails(int $id): ?object
    {
        try {
            $sql = "SELECT 
                    p.*,
                    c.name AS company_name,
                    ps.name AS status_name,
                    u.first_name AS owner_firstname,
                    u.last_name AS owner_lastname
                FROM 
                    projects p
                LEFT JOIN 
                    statuses_project ps ON p.status_id = ps.id
                LEFT JOIN 
                    companies c ON c.id = p.company_id
                LEFT JOIN 
                    users u ON u.id = p.owner_id
                WHERE 
                    p.id = :project_id
                    AND p.is_deleted = 0";

            $stmt = $this->db->executeQuery($sql, [':project_id' => $id]);
            $project = $stmt->fetch(PDO::FETCH_OBJ);

            if ($project) {
                $project->tasks = $this->getProjectTasks($id);
                $project->milestones = $this->getProjectMilestones($id);
                $project->sprints = $this->getProjectSprints($id);
                $project->team_members = $this->getProjectTeamMembers($id);
            }

            return $project ?: null;
        } catch (\Exception $e) {
            throw new RuntimeException("Failed to get project details: " . $e->getMessage());
        }
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
        try {
            $offset = ($page - 1) * $limit;
            
            // Build WHERE clause from filters
            $whereClauses = ['p.is_deleted = 0'];
            $params = [];
            
            foreach ($filters as $key => $value) {
                if (in_array($key, $this->fillable)) {
                    $whereClauses[] = "p.$key = :$key";
                    $params[":$key"] = $value;
                }
            }
            
            $whereClause = implode(' AND ', $whereClauses);

            $sql = "SELECT 
                    p.*,
                    c.name AS company_name,
                    ps.name AS status_name,
                    u.first_name AS owner_firstname,
                    u.last_name AS owner_lastname,
                    (
                        SELECT COUNT(*) FROM tasks t 
                        WHERE t.project_id = p.id AND t.is_deleted = 0
                    ) as task_count,
                    (
                        SELECT COUNT(*) FROM milestones m 
                        WHERE m.project_id = p.id AND m.is_deleted = 0
                    ) as milestone_count
                FROM 
                    projects p
                LEFT JOIN 
                    statuses_project ps ON p.status_id = ps.id
                LEFT JOIN 
                    companies c ON c.id = p.company_id
                LEFT JOIN 
                    users u ON u.id = p.owner_id
                WHERE 
                    $whereClause
                ORDER BY 
                    p.updated_at DESC
                LIMIT :limit OFFSET :offset";

            $params[':limit'] = $limit;
            $params[':offset'] = $offset;
            
            $stmt = $this->db->executeQuery($sql, $params);
            $projects = $stmt->fetchAll(PDO::FETCH_OBJ);
            
            return $projects;
        } catch (\Exception $e) {
            throw new RuntimeException("Failed to get projects: " . $e->getMessage());
        }
    }

    /**
     * Get project tasks
     * 
     * @param int $projectId
     * @return array
     */
    public function getProjectTasks(int $projectId): array
    {
        try {
            $sql = "SELECT 
                    t.*,
                    ts.name AS status_name,
                    u.first_name AS assignee_firstname,
                    u.last_name AS assignee_lastname
                FROM 
                    tasks t
                LEFT JOIN 
                    statuses_task ts ON t.status_id = ts.id
                LEFT JOIN 
                    users u ON u.id = t.assigned_to
                WHERE 
                    t.is_subtask = 0 
                    AND t.project_id = :project_id 
                    AND t.is_deleted = 0
                ORDER BY
                    t.priority DESC, t.due_date ASC";

            $stmt = $this->db->executeQuery($sql, [':project_id' => $projectId]);
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            throw new RuntimeException("Failed to get project tasks: " . $e->getMessage());
        }
    }
    
    /**
     * Get project milestones
     * 
     * @param int $projectId
     * @return array
     */
    public function getProjectMilestones(int $projectId): array
    {
        try {
            $sql = "SELECT 
                    m.*,
                    s.name AS status_name
                FROM 
                    milestones m
                LEFT JOIN 
                    statuses_milestone s ON m.status_id = s.id
                WHERE 
                    m.project_id = :project_id
                    AND m.is_deleted = 0
                ORDER BY
                    m.due_date ASC, m.title ASC";

            $stmt = $this->db->executeQuery($sql, [':project_id' => $projectId]);
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            throw new RuntimeException("Failed to get project milestones: " . $e->getMessage());
        }
    }
    
    /**
     * Get project sprints
     * 
     * @param int $projectId
     * @return array
     */
    public function getProjectSprints(int $projectId): array
    {
        try {
            $sql = "SELECT 
                    s.*,
                    ss.name AS status_name,
                    (
                        SELECT COUNT(*) FROM sprint_tasks st
                        JOIN tasks t ON st.task_id = t.id
                        WHERE st.sprint_id = s.id AND t.is_deleted = 0
                    ) as task_count
                FROM 
                    sprints s
                LEFT JOIN 
                    statuses_sprint ss ON s.status_id = ss.id
                WHERE 
                    s.project_id = :project_id
                    AND s.is_deleted = 0
                ORDER BY
                    s.start_date DESC";

            $stmt = $this->db->executeQuery($sql, [':project_id' => $projectId]);
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            throw new RuntimeException("Failed to get project sprints: " . $e->getMessage());
        }
    }
    
    /**
     * Get project team members
     * 
     * @param int $projectId
     * @return array
     */
    public function getProjectTeamMembers(int $projectId): array
    {
        try {
            $sql = "SELECT DISTINCT 
                    u.*,
                    r.name AS role_name
                FROM 
                    users u
                LEFT JOIN
                    roles r ON u.role_id = r.id
                WHERE 
                    (
                        u.id IN (
                            SELECT assigned_to FROM tasks 
                            WHERE project_id = :project_id AND is_deleted = 0
                        )
                        OR
                        u.id IN (
                            SELECT user_id FROM user_projects 
                            WHERE project_id = :project_id
                        )
                    )
                    AND u.is_deleted = 0
                ORDER BY
                    u.first_name, u.last_name";

            $stmt = $this->db->executeQuery($sql, [
                ':project_id' => $projectId
            ]);
            
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            throw new RuntimeException("Failed to get project team members: " . $e->getMessage());
        }
    }

    /**
     * Get project statuses
     * 
     * @return array
     */
    public function getAllStatuses(): array
    {
        try {
            $sql = "SELECT * FROM statuses_project WHERE is_deleted = 0";
            $stmt = $this->db->executeQuery($sql);
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            throw new RuntimeException("Failed to get project statuses: " . $e->getMessage());
        }
    }

    /**
     * Get projects by company
     * 
     * @param int $companyId
     * @return array
     */
    public function getByCompanyId(int $companyId): array
    {
        try {
            // Get both directly associated projects and those through company_projects
            $sql = "SELECT DISTINCT p.*, ps.name as status_name
                    FROM projects p
                    LEFT JOIN statuses_project ps ON p.status_id = ps.id
                    WHERE (
                        p.company_id = :company_id
                        OR p.id IN (
                            SELECT project_id FROM company_projects 
                            WHERE company_id = :company_id
                        )
                    )
                    AND p.is_deleted = 0
                    ORDER BY p.name ASC";
                    
            $stmt = $this->db->executeQuery($sql, [':company_id' => $companyId]);
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            throw new RuntimeException("Failed to get company projects: " . $e->getMessage());
        }
    }

    /**
     * Get project users
     * 
     * @return array
     */
    public function getUsers(): array
    {
        try {
            if (!$this->id) {
                throw new RuntimeException("Project ID is not set");
            }

            $sql = "SELECT DISTINCT u.* 
                    FROM users u
                    WHERE (
                        u.id IN (
                            SELECT assigned_to FROM tasks
                            WHERE project_id = :project_id AND is_deleted = 0
                        )
                        OR
                        u.id IN (
                            SELECT user_id FROM user_projects
                            WHERE project_id = :project_id
                        )
                    )
                    AND u.is_deleted = 0
                    ORDER BY u.first_name, u.last_name";

            $stmt = $this->db->executeQuery($sql, [':project_id' => $this->id]);
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            throw new RuntimeException("Failed to get project users: " . $e->getMessage());
        }
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
        try {
            $sql = "SELECT DISTINCT p.*, ps.name as status_name
                    FROM projects p
                    LEFT JOIN statuses_project ps ON p.status_id = ps.id
                    WHERE (
                        p.owner_id = :owner_id
                        OR p.id IN (
                            SELECT project_id FROM user_projects WHERE user_id = :project_user_id
                        )
                        OR p.id IN (
                            SELECT DISTINCT project_id FROM tasks 
                            WHERE assigned_to = :assigned_to AND is_deleted = 0
                        )
                    )
                    AND p.is_deleted = 0
                    ORDER BY 
                        CASE WHEN p.owner_id = :user_id THEN 0 ELSE 1 END,
                        p.updated_at DESC
                    LIMIT :limit";

            $stmt = $this->db->executeQuery($sql, [
                ':owner_id' => $userId,
                ':project_user_id' => $userId,
                ':assigned_to' => $userId,
                ':user_id' => $userId,
                ':limit' => $limit
            ]);

            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            throw new RuntimeException("Failed to get recent projects: " . $e->getMessage());
        }
    }
    
    /**
     * Add a user to the project team
     * 
     * @param int $userId
     * @return bool
     */
    public function addTeamMember(int $userId): bool
    {
        try {
            if (!$this->id) {
                throw new RuntimeException("Project ID is not set");
            }
            
            $sql = "INSERT INTO user_projects (user_id, project_id) 
                    VALUES (:user_id, :project_id)
                    ON DUPLICATE KEY UPDATE user_id = :user_id";
                    
            return $this->db->executeInsertUpdate($sql, [
                ':user_id' => $userId,
                ':project_id' => $this->id
            ]);
        } catch (\Exception $e) {
            throw new RuntimeException("Failed to add team member: " . $e->getMessage());
        }
    }
    
    /**
     * Remove a user from the project team
     * 
     * @param int $userId
     * @return bool
     */
    public function removeTeamMember(int $userId): bool
    {
        try {
            if (!$this->id) {
                throw new RuntimeException("Project ID is not set");
            }
            
            $sql = "DELETE FROM user_projects 
                    WHERE user_id = :user_id AND project_id = :project_id";
                    
            return $this->db->executeInsertUpdate($sql, [
                ':user_id' => $userId,
                ':project_id' => $this->id
            ]);
        } catch (\Exception $e) {
            throw new RuntimeException("Failed to remove team member: " . $e->getMessage());
        }
    }

    /**
     * Transforms a string into key code format
     * 
     * @param string $input
     * @return string
     */
    public function transformKeyCodeFormat(string $input): string
    {
        // 1. Remove all non-alphabetic characters (this includes spaces)
        $filtered = preg_replace('/[^A-Za-z]/', '', $input);
    
        // 2. Extract the first 4 characters
        $firstFour = substr($filtered, 0, 4);
    
        // 3. Convert the result to uppercase
        $result = strtoupper($firstFour);
    
        return $result;
    }
    
    /**
     * Get project progress percentage
     * 
     * @return float
     */
    public function getProgressPercentage(): float
    {
        try {
            if (!$this->id) {
                throw new RuntimeException("Project ID is not set");
            }
            
            // Calculate based on completed tasks
            $sql = "SELECT 
                    COUNT(*) as total_tasks,
                    SUM(CASE WHEN status_id = 6 THEN 1 ELSE 0 END) as completed_tasks
                    FROM tasks
                    WHERE project_id = :project_id
                    AND is_deleted = 0";
                    
            $stmt = $this->db->executeQuery($sql, [':project_id' => $this->id]);
            $taskStats = $stmt->fetch(PDO::FETCH_OBJ);
            
            if (!$taskStats || $taskStats->total_tasks == 0) {
                return 0;
            }
            
            return round(($taskStats->completed_tasks / $taskStats->total_tasks) * 100, 2);
        } catch (\Exception $e) {
            throw new RuntimeException("Failed to calculate project progress: " . $e->getMessage());
        }
    }

    /**
     * Validate project data before save
     * 
     * @param array $data
     * @param int|null $id
     * @throws InvalidArgumentException
     */
    protected function validate(array $data, ?int $id = null): void
    {
        parent::validate($data, $id);
        
        // Validate dates
        if (isset($data['start_date']) && isset($data['end_date']) && 
            !empty($data['start_date']) && !empty($data['end_date'])) {
            if (strtotime($data['end_date']) < strtotime($data['start_date'])) {
                throw new InvalidArgumentException('End date cannot be earlier than start date');
            }
        }
        
        // Validate name uniqueness within a company if company_id is provided
        if (isset($data['name']) && isset($data['company_id'])) {
            $sql = "SELECT COUNT(*) FROM projects 
                    WHERE name = :name 
                    AND company_id = :company_id 
                    AND is_deleted = 0";
            
            $params = [
                ':name' => $data['name'],
                ':company_id' => $data['company_id']
            ];
            
            if ($id !== null) {
                $sql .= " AND id != :id";
                $params[':id'] = $id;
            }
            
            $stmt = $this->db->executeQuery($sql, $params);
            if ($stmt->fetchColumn() > 0) {
                throw new InvalidArgumentException('A project with this name already exists in this company');
            }
        }
    }
}