<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;
use RuntimeException;
use InvalidArgumentException;

/**
 * Task Model
 * 
 * Handles all task-related database operations
 */
class Task extends BaseModel
{
    protected string $table = 'tasks';
    
    /**
     * Task properties
     */
    public ?int $id = null;
    public int $project_id;
    public ?int $assigned_to = null;
    public string $title;
    public ?string $description = null;
    public string $priority = 'none';
    public int $status_id;
    public ?int $estimated_time = null;
    public ?int $billable_time = null;
    public ?int $time_spent = 0;
    public ?string $start_date = null;
    public ?string $due_date = null;
    public ?string $complete_date = null;
    public ?int $hourly_rate = null;
    public bool $is_hourly = false;
    public bool $is_deleted = false;
    public bool $is_subtask = false;
    public ?int $parent_task_id = null;
    public ?string $created_at = null;
    public ?string $updated_at = null;

    /**
     * Get tasks by user ID
     * 
     * @param int $userId
     * @param int $limit
     * @param int $page
     * @return array
     */
    public function getByUserId(int $userId, int $limit = 10, int $page = 1): array
    {
        $sql = "SELECT t.*, 
                       p.name as project_name,
                       ts.name as status_name,
                       u.first_name, 
                       u.last_name
                FROM tasks t
                LEFT JOIN projects p ON t.project_id = p.id
                LEFT JOIN task_statuses ts ON t.status_id = ts.id
                LEFT JOIN users u ON t.assigned_to = u.id
                WHERE t.assigned_to = :user_id 
                AND t.is_deleted = 0
                ORDER BY t.due_date ASC, t.priority DESC";

        return $this->paginate($sql, [':user_id' => $userId], $page, $limit);
    }

    /**
     * Get tasks by project
     * 
     * @param int $projectId
     * @return array
     */
    public function getByProjectId(int $projectId): array
    {
        $sql = "SELECT 
                    t.*,
                    ts.name AS status_name,
                    u.first_name,
                    u.last_name
                FROM 
                    tasks t
                LEFT JOIN
                    task_statuses ts ON t.status_id = ts.id
                LEFT JOIN
                    users u ON t.assigned_to = u.id
                WHERE 
                    t.project_id = :project_id
                    AND t.is_deleted = 0
                ORDER BY 
                    t.is_subtask ASC,
                    t.parent_task_id ASC,
                    t.priority DESC,
                    t.due_date ASC";

        $stmt = $this->db->executeQuery($sql, [':project_id' => $projectId]);
        $tasks = $stmt->fetchAll(PDO::FETCH_OBJ);

        return $this->organizeTasksByStatus($tasks);
    }

    /**
     * Get task subtasks
     * 
     * @param int $taskId
     * @return array
     */
    public function getSubtasks(int $taskId): array
    {
        $sql = "SELECT t.*,
                       ts.name as status_name,
                       u.first_name,
                       u.last_name
                FROM tasks t
                LEFT JOIN task_statuses ts ON t.status_id = ts.id
                LEFT JOIN users u ON t.assigned_to = u.id
                WHERE t.parent_task_id = :task_id
                AND t.is_subtask = 1
                AND t.is_deleted = 0
                ORDER BY t.priority DESC, t.due_date ASC";

        $stmt = $this->db->executeQuery($sql, [':task_id' => $taskId]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Get task statuses
     * 
     * @return array
     */
    public function getTaskStatuses(): array
    {
        $sql = "SELECT * FROM task_statuses WHERE is_deleted = 0";
        $stmt = $this->db->executeQuery($sql);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Get recent tasks by user
     * 
     * @param int $userId
     * @param int $limit
     * @return array
     */
    public function getRecentByUser(int $userId, int $limit = 5): array
    {
        $sql = "SELECT t.*, 
                       p.name as project_name,
                       ts.name as status_name
                FROM tasks t
                LEFT JOIN projects p ON t.project_id = p.id
                LEFT JOIN task_statuses ts ON t.status_id = ts.id
                WHERE t.assigned_to = :user_id 
                AND t.is_deleted = 0
                ORDER BY t.created_at DESC 
                LIMIT :limit";

        $stmt = $this->db->executeQuery($sql, [
            ':user_id' => $userId,
            ':limit' => $limit
        ]);

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Get total time spent for a user
     * 
     * @param int $userId
     * @return int Total time spent in seconds
     */
    public function getTotalTimeSpent(int $userId): int
    {
        $sql = "SELECT COALESCE(SUM(time_spent), 0) 
                FROM tasks 
                WHERE assigned_to = :user_id 
                AND is_deleted = 0";

        $stmt = $this->db->executeQuery($sql, [':user_id' => $userId]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Get total billable time for a user
     * 
     * @param int $userId
     * @return int Total billable time in seconds
     */
    public function getTotalBillableTime(int $userId): int
    {
        $sql = "SELECT COALESCE(SUM(billable_time), 0) 
                FROM tasks 
                WHERE assigned_to = :user_id 
                AND is_hourly = 1 
                AND is_deleted = 0";

        $stmt = $this->db->executeQuery($sql, [':user_id' => $userId]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Get time spent this week for a user
     * 
     * @param int $userId
     * @return int Time spent this week in seconds
     */
    public function getWeeklyTimeSpent(int $userId): int
    {
        $sql = "SELECT COALESCE(SUM(time_spent), 0) 
                FROM tasks 
                WHERE assigned_to = :user_id 
                AND is_deleted = 0 
                AND complete_date BETWEEN 
                    DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY) 
                    AND DATE_ADD(DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY), INTERVAL 6 DAY)";

        $stmt = $this->db->executeQuery($sql, [':user_id' => $userId]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Get time spent this month for a user
     * 
     * @param int $userId
     * @return int Time spent this month in seconds
     */
    public function getMonthlyTimeSpent(int $userId): int
    {
        $sql = "SELECT COALESCE(SUM(time_spent), 0) 
                FROM tasks 
                WHERE assigned_to = :user_id 
                AND is_deleted = 0 
                AND complete_date BETWEEN 
                    DATE_FORMAT(CURDATE(), '%Y-%m-01') 
                    AND LAST_DAY(CURDATE())";

        $stmt = $this->db->executeQuery($sql, [':user_id' => $userId]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Organize tasks by status
     * 
     * @param array $tasks
     * @return array
     */
    private function organizeTasksByStatus(array $tasks): array
    {
        $organized = [
            'on_hold' => [],
            'open' => [],
            'in_progress' => [],
            'in_review' => [],
            'completed' => [],
            'cancelled' => []
        ];

        foreach ($tasks as $task) {
            $status = strtolower($task->status_name);
            if (!isset($organized[$status])) {
                $organized[$status] = [];
            }
            $organized[$status][] = $task;
        }

        return $organized;
    }

    /**
     * Validate task data before save
     * 
     * @param array $data
     * @throws InvalidArgumentException
     */
    protected function beforeSave(array $data): void
    {
        if (empty($data['title'])) {
            throw new InvalidArgumentException('Task title is required');
        }

        if (empty($data['project_id'])) {
            throw new InvalidArgumentException('Project ID is required');
        }

        if (empty($data['status_id'])) {
            throw new InvalidArgumentException('Status ID is required');
        }

        if (!empty($data['parent_task_id'])) {
            // Validate parent task exists and is not a subtask
            $parent = $this->find($data['parent_task_id']);
            if (!$parent || $parent->is_subtask) {
                throw new InvalidArgumentException('Invalid parent task');
            }
        }

        if (!empty($data['due_date'])) {
            if (strtotime($data['due_date']) < strtotime('today')) {
                throw new InvalidArgumentException('Due date cannot be in the past');
            }
        }

        if (!empty($data['time_spent']) && $data['time_spent'] < 0) {
            throw new InvalidArgumentException('Time spent cannot be negative');
        }

        if (!empty($data['estimated_time']) && $data['estimated_time'] < 0) {
            throw new InvalidArgumentException('Estimated time cannot be negative');
        }

        if (!empty($data['priority']) && !in_array($data['priority'], ['none', 'low', 'medium', 'high'])) {
            throw new InvalidArgumentException('Invalid priority value');
        }
    }
}