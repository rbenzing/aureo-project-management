<?php

declare(strict_types=1);

namespace App\Models;

use PDO;
use RuntimeException;
use InvalidArgumentException;

/**
 * Sprint Model
 * 
 * Handles all sprint-related database operations
 */
class Sprint extends BaseModel
{
    protected string $table = 'sprints';
    
    /**
     * Sprint properties
     */
    public ?int $id = null;
    public int $project_id;
    public string $name;
    public ?string $description = null;
    public string $start_date;
    public string $end_date;
    public int $status_id = 1; // Default: Planning
    public bool $is_deleted = false;
    public ?string $created_at = null;
    public ?string $updated_at = null;

    /**
     * Properties that can be mass assigned
     */
    protected array $fillable = [
        'project_id',
        'name',
        'description',
        'start_date',
        'end_date',
        'status_id'
    ];


    /**
     * Get sprints with tasks
     * 
     * @param int $limit Items per page
     * @param int $page Current page number
     * @return array
     */
    public function getAllWithTasks(int $limit = 10, int $page = 1): array
    {
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT s.*, 
                    p.name as project_name,
                    ss.name as status_name,
                    COUNT(st.task_id) as task_count
                FROM sprints s
                LEFT JOIN projects p ON s.project_id = p.id
                LEFT JOIN sprint_statuses ss ON s.status_id = ss.id
                LEFT JOIN sprint_tasks st ON s.id = st.sprint_id
                WHERE s.is_deleted = 0
                GROUP BY s.id
                ORDER BY s.start_date DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->executeQuery($sql, [
            ':limit' => $limit,
            ':offset' => $offset,
        ]);

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Find sprint with details including tasks
     * 
     * @param int $id
     * @return object|null
     */
    public function findWithDetails(int $id): ?object
    {
        $sql = "SELECT s.*, 
                    p.name as project_name,
                    ss.name as status_name
                FROM sprints s
                LEFT JOIN projects p ON s.project_id = p.id
                LEFT JOIN sprint_statuses ss ON s.status_id = ss.id
                WHERE s.id = :id AND s.is_deleted = 0";

        $stmt = $this->db->executeQuery($sql, [':id' => $id]);
        $sprint = $stmt->fetch(PDO::FETCH_OBJ);
        
        if ($sprint) {
            $sprint->tasks = $this->getSprintTasks($id);
        }
        
        return $sprint ?: null;
    }

    /**
     * Get sprint statuses
     * 
     * @return array
     */
    public function getSprintStatuses(): array
    {
        $sql = "SELECT * FROM sprint_statuses WHERE is_deleted = 0";
        $stmt = $this->db->executeQuery($sql);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Get tasks for a sprint
     * 
     * @param int $sprintId
     * @return array
     */
    public function getSprintTasks(int $sprintId): array
    {
        $sql = "SELECT t.*, 
                    ts.name as status_name,
                    u.first_name, 
                    u.last_name
                FROM tasks t
                JOIN sprint_tasks st ON t.id = st.task_id
                LEFT JOIN task_statuses ts ON t.status_id = ts.id
                LEFT JOIN users u ON t.assigned_to = u.id
                WHERE st.sprint_id = :sprint_id AND t.is_deleted = 0
                ORDER BY t.priority DESC, t.due_date ASC";

        $stmt = $this->db->executeQuery($sql, [':sprint_id' => $sprintId]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Add tasks to sprint
     * 
     * @param int $sprintId
     * @param array $taskIds
     * @return bool
     */
    public function addTasks(int $sprintId, array $taskIds): bool
    {
        if (empty($taskIds)) {
            return true;
        }

        try {
            $this->db->beginTransaction();
            
            foreach ($taskIds as $taskId) {
                $sql = "INSERT IGNORE INTO sprint_tasks (sprint_id, task_id) 
                        VALUES (:sprint_id, :task_id)";
                
                $this->db->executeInsertUpdate($sql, [
                    ':sprint_id' => $sprintId,
                    ':task_id' => $taskId
                ]);
            }
            
            $this->db->commit();
            return true;
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw new RuntimeException("Failed to add tasks to sprint: " . $e->getMessage());
        }
    }

    /**
     * Remove task from sprint
     * 
     * @param int $sprintId
     * @param int $taskId
     * @return bool
     */
    public function removeTask(int $sprintId, int $taskId): bool
    {
        $sql = "DELETE FROM sprint_tasks WHERE sprint_id = :sprint_id AND task_id = :task_id";
        
        return $this->db->executeInsertUpdate($sql, [
            ':sprint_id' => $sprintId,
            ':task_id' => $taskId
        ]);
    }

    /**
     * Get active sprint for a project
     * 
     * @param int $projectId
     * @return object|null
     */
    public function getActiveSprintForProject(int $projectId): ?object
    {
        $sql = "SELECT s.*, ss.name as status_name
                FROM sprints s
                LEFT JOIN sprint_statuses ss ON s.status_id = ss.id
                WHERE s.project_id = :project_id 
                AND s.status_id = 2 -- Active status
                AND s.is_deleted = 0
                LIMIT 1";

        $stmt = $this->db->executeQuery($sql, [':project_id' => $projectId]);
        $sprint = $stmt->fetch(PDO::FETCH_OBJ);
        
        return $sprint ?: null;
    }

    /**
     * Get all sprints for a project
     * 
     * @param int $projectId
     * @param string|null $status
     * @return array
     * @throws RuntimeException
     */
    public function getProjectSprints(int $projectId, ?string $status = null): array
    {
        $sql = "SELECT s.*, 
               ss.name as status_name,
               task_counts.total_tasks,
               task_counts.completed_tasks
        FROM sprints s USE INDEX (idx_sprints_project_id)
        LEFT JOIN sprint_statuses ss ON s.status_id = ss.id
        LEFT JOIN (
            SELECT st.sprint_id,
                   COUNT(DISTINCT st.task_id) as total_tasks,
                   COUNT(DISTINCT CASE WHEN t.status_id = 6 THEN t.id END) as completed_tasks
            FROM sprint_tasks st
            LEFT JOIN tasks t ON st.task_id = t.id AND t.is_deleted = 0
            GROUP BY st.sprint_id
        ) task_counts ON s.id = task_counts.sprint_id
        WHERE s.project_id = :project_id 
        AND s.is_deleted = 0";

        $params = [':project_id' => $projectId];

        if ($status) {
            $sql .= " AND ss.name = :status";
            $params[':status'] = $status;
        }

        $sql .= " GROUP BY s.id ORDER BY s.start_date DESC";

        $stmt = $this->db->executeQuery($sql, $params);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Get sprint velocity (total completed tasks vs total tasks)
     * 
     * @param int $sprintId
     * @return array
     */
    public function getSprintVelocity(int $sprintId): array
    {
        $sql = "SELECT 
                    COUNT(t.id) as total_tasks,
                    SUM(CASE WHEN t.status_id = 6 THEN 1 ELSE 0 END) as completed_tasks
                FROM tasks t
                JOIN sprint_tasks st ON t.id = st.task_id
                WHERE st.sprint_id = :sprint_id AND t.is_deleted = 0";

        $stmt = $this->db->executeQuery($sql, [':sprint_id' => $sprintId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $totalTasks = (int)($result['total_tasks'] ?? 0);
        $completedTasks = (int)($result['completed_tasks'] ?? 0);
        
        $velocityPercentage = $totalTasks > 0 ? 
            round(($completedTasks / $totalTasks) * 100, 2) : 0;
            
        return [
            'total_tasks' => $totalTasks,
            'completed_tasks' => $completedTasks,
            'velocity_percentage' => $velocityPercentage
        ];
    }

    /**
     * Complete a sprint
     * 
     * @param int $sprintId
     * @return bool
     */
    public function completeSprint(int $sprintId): bool
    {
        return $this->update($sprintId, ['status_id' => 4]);
    }

    /**
     * Validate sprint data before save
     * 
     * @param array $data
     * @throws InvalidArgumentException
     */
    protected function beforeSave(array $data): void
    {
        parent::validate($data, $this->id);
        
        if (empty($data['name'])) {
            throw new InvalidArgumentException('Sprint name is required');
        }

        if (empty($data['project_id'])) {
            throw new InvalidArgumentException('Project ID is required');
        }

        if (empty($data['start_date'])) {
            throw new InvalidArgumentException('Start date is required');
        }

        if (empty($data['end_date'])) {
            throw new InvalidArgumentException('End date is required');
        }

        // Validate dates
        if (isset($data['start_date']) && isset($data['end_date'])) {
            if (strtotime($data['end_date']) < strtotime($data['start_date'])) {
                throw new InvalidArgumentException('End date cannot be earlier than start date');
            }
        }

        // Check for active sprint overlap
        if (isset($data['status_id']) && $data['status_id'] == 2) { // Active status
            $sql = "SELECT COUNT(*) FROM sprints 
                    WHERE project_id = :project_id 
                    AND status_id = 2 
                    AND id != :id 
                    AND is_deleted = 0";
            
            $stmt = $this->db->executeQuery($sql, [
                ':project_id' => $data['project_id'],
                ':id' => $id ?? 0
            ]);
            
            if ($stmt->fetchColumn() > 0) {
                throw new InvalidArgumentException('Another sprint is already active for this project');
            }
        }
    }
}