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
        'company_id',
        'owner_id',
        'name',
        'description',
        'start_date',
        'end_date',
        'status_id',
        'key_code'
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
                // Enrich project with additional details
                $project->tasks = $this->getProjectTasks($id);
                $project->milestones = $this->getProjectMilestones($id);
                $project->sprints = $this->getProjectSprints($id);
                $project->team_members = $this->getProjectTeamMembers($id);

                // Calculate project health metrics
                $project->health_metrics = $this->calculateProjectHealth($id);
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
     * @return array
     */
    public function getAllWithDetails(int $limit = 10, int $page = 1): array
    {
        try {
            $offset = ($page - 1) * $limit;

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
                    p.is_deleted = 0
                ORDER BY 
                    p.updated_at DESC
                LIMIT :limit OFFSET :offset";

            $stmt = $this->db->executeQuery($sql, [
                ':limit' => $limit,
                ':offset' => $offset
            ]);

            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            throw new RuntimeException("Failed to get projects: " . $e->getMessage());
        }
    }

    /**
     * Calculate project health metrics
     * 
     * @param int $projectId
     * @return array
     */
    public function calculateProjectHealth(int $projectId): array
    {
        try {
            // Task completion rate
            $taskSql = "SELECT 
                COUNT(*) as total_tasks,
                SUM(CASE WHEN status_id = 6 THEN 1 ELSE 0 END) as completed_tasks,
                SUM(CASE WHEN due_date < CURDATE() AND status_id != 6 THEN 1 ELSE 0 END) as overdue_tasks
            FROM tasks 
            WHERE project_id = :project_id AND is_deleted = 0";

            $taskStmt = $this->db->executeQuery($taskSql, [':project_id' => $projectId]);
            $taskMetrics = $taskStmt->fetch(PDO::FETCH_ASSOC);

            // Milestone completion rate
            $milestoneSql = "SELECT 
                COUNT(*) as total_milestones,
                SUM(CASE WHEN status_id = 3 THEN 1 ELSE 0 END) as completed_milestones,
                SUM(CASE WHEN due_date < CURDATE() AND status_id != 3 THEN 1 ELSE 0 END) as overdue_milestones
            FROM milestones 
            WHERE project_id = :project_id AND is_deleted = 0";

            $milestoneStmt = $this->db->executeQuery($milestoneSql, [':project_id' => $projectId]);
            $milestoneMetrics = $milestoneStmt->fetch(PDO::FETCH_ASSOC);

            // Calculate percentages
            $taskCompletionRate = $taskMetrics['total_tasks'] > 0
                ? round(($taskMetrics['completed_tasks'] / $taskMetrics['total_tasks']) * 100, 2)
                : 0;

            $milestoneCompletionRate = $milestoneMetrics['total_milestones'] > 0
                ? round(($milestoneMetrics['completed_milestones'] / $milestoneMetrics['total_milestones']) * 100, 2)
                : 0;

            return [
                'task_completion_rate' => $taskCompletionRate,
                'milestone_completion_rate' => $milestoneCompletionRate,
                'total_tasks' => $taskMetrics['total_tasks'],
                'completed_tasks' => $taskMetrics['completed_tasks'],
                'overdue_tasks' => $taskMetrics['overdue_tasks'],
                'total_milestones' => $milestoneMetrics['total_milestones'],
                'completed_milestones' => $milestoneMetrics['completed_milestones'],
                'overdue_milestones' => $milestoneMetrics['overdue_milestones'],
                'overall_health' => $this->calculateOverallProjectHealth(
                    $taskCompletionRate,
                    $milestoneCompletionRate
                )
            ];
        } catch (\Exception $e) {
            throw new RuntimeException("Failed to calculate project health: " . $e->getMessage());
        }
    }

    /**
     * Calculate overall project health score
     * 
     * @param float $taskCompletionRate
     * @param float $milestoneCompletionRate
     * @return string
     */
    private function calculateOverallProjectHealth(float $taskCompletionRate, float $milestoneCompletionRate): string
    {
        $averageCompletionRate = ($taskCompletionRate + $milestoneCompletionRate) / 2;

        return match (true) {
            $averageCompletionRate >= 85 => 'Excellent',
            $averageCompletionRate >= 70 => 'Good',
            $averageCompletionRate >= 50 => 'At Risk',
            default => 'Critical'
        };
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
                            WHERE project_id = :project_id_2
                        )
                    )
                    AND u.is_deleted = 0
                ORDER BY
                    u.first_name, u.last_name";

            $stmt = $this->db->executeQuery($sql, [
                ':project_id' => $projectId,
                ':project_id_2' => $projectId
            ]);

            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            throw new RuntimeException("Failed to get project team members: " . $e->getMessage());
        }
    }

    /**
     * Get recent tasks by user
     * 
     * @param int $userId
     * @param int $limit
     * @return array
     */
    public function getRecentByUser(int $userId, int $limit = 15): array
    {
        try {
            $sql = "SELECT t.*, 
                   p.name as project_name,
                   ts.name as status_name
            FROM tasks t
            LEFT JOIN projects p ON t.project_id = p.id
            LEFT JOIN statuses_task ts ON t.status_id = ts.id
            WHERE t.assigned_to = :user_id 
            AND t.is_deleted = 0
            ORDER BY 
                CASE 
                    WHEN t.due_date < CURDATE() THEN 0  -- Overdue
                    WHEN t.due_date = CURDATE() THEN 1  -- Due today
                    ELSE 2                             -- Due later
                END,
                t.due_date ASC,
                t.priority DESC
            LIMIT :limit";

            $stmt = $this->db->executeQuery($sql, [
                ':user_id' => $userId,
                ':limit' => $limit
            ]);

            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            throw new RuntimeException("Error fetching recent tasks: " . $e->getMessage());
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
        if (
            isset($data['start_date']) && isset($data['end_date']) &&
            !empty($data['start_date']) && !empty($data['end_date'])
        ) {
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
