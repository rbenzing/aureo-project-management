<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Task
{
    private Database $db;

    public ?int $id = null;
    public int $project_id;
    public ?int $assigned_to = null;
    public string $title;
    public ?string $description = null;
    public string $priority;
    public int $status_id;
    public ?int $estimated_time = null;
    public ?int $time_spent = null;
    public ?string $due_date = null;
    public ?string $complete_date = null;
    public bool $is_deleted = false;
    public ?string $created_at = null;
    public ?string $updated_at = null;

    public function __construct()
    {
        // Initialize the database connection
        $this->db = Database::getInstance();
    }

    /**
     * Find a task by its ID.
     */
    public function find(int $id): ?object
    {
        $stmt = $this->db->executeQuery(
            "SELECT * FROM tasks WHERE id = :task_id AND is_deleted = 0",
            [':task_id' => $id]
        );

        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    /**
     * Get the total of all companies
     */
    public function countAll(): int
    {
        $stmt = $this->db->executeQuery(
            "SELECT COUNT(*) as total FROM tasks WHERE is_deleted = 0"
        );
        return (int)$stmt->fetchColumn();
    }

    /**
     * Fetch all tasks assigned to a specific user (paginated).
     */
    public function getByUserIdPaginated(int $userId, int $limit = 10, int $page = 1): array
    {
        $offset = ($page - 1) * $limit;
        $stmt = $this->db->executeQuery(
            "SELECT * FROM tasks 
             WHERE assigned_to = :user_id AND is_deleted = 0 
             LIMIT :limit OFFSET :offset",
            [
                ':user_id' => $userId,
                ':limit' => $limit,
                ':offset' => $offset,
            ]
        );

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Fetch tasks and subtasks grouped by status for a project.
     *
     * @param int $projectId The project ID.
     * @return array Tasks grouped by status.
     */
    public function getByProjectId(int $projectId): array
    {
        $query = "
            SELECT 
                t.id AS task_id,
                t.title AS task_title,
                t.description AS task_description,
                t.is_subtask,
                t.parent_task_id,
                ts.name AS task_status,
                t.due_date AS task_due_date
            FROM 
                tasks t
            LEFT JOIN
                task_statuses ts ON t.status_id = ts.id AND ts.is_deleted = 0
            WHERE 
                t.project_id = :project_id
                AND t.is_deleted = 0
            ORDER BY 
                t.is_subtask ASC,
                t.parent_task_id ASC,
                t.id ASC
        ";
        $stmt = $this->db->executeQuery($query, [':project_id' => $projectId]);
        $rawData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Organize tasks and subtasks
        $tasks = [
            'on_hold' => [],
            'open' => [],
            'in_progress' => [],
            'completed' => [],
        ];
        $subtasksByParent = [];

        // Separate tasks and subtasks
        foreach ($rawData as $row) {
            if ($row['is_subtask']) {
                // Collect subtasks by their parent_task_id
                $subtasksByParent[$row['parent_task_id']][] = $row['task_id'];
            } else {
                // Add parent tasks to their respective status groups
                $tasks[$row['task_status']][] = [
                    'id' => $row['task_id'],
                    'title' => $row['task_title'],
                    'description' => $row['task_description'],
                    'status' => $row['task_status'],
                    'due_date' => $row['task_due_date'],
                    'subtasks' => [], // Placeholder for subtasks
                ];
            }
        }

        // Assign subtasks to their parent tasks
        foreach ($tasks as &$statusGroup) {
            foreach ($statusGroup as &$task) {
                if (isset($subtasksByParent[$task['id']])) {
                    $task['subtasks'] = $subtasksByParent[$task['id']];
                }
            }
        }

        return $tasks;
    }

    /**
     * Save a new task to the database.
     */
    public function save(): bool
    {
        $stmt = $this->db->executeQuery(
            "INSERT INTO tasks (project_id, assigned_to, title, description, priority, status_id, estimated_time, due_date, created_at, updated_at)
             VALUES (:project_id, :assigned_to, :title, :description, :priority, :status_id, :estimated_time, :due_date, NOW(), NOW())",
            [
                ':project_id' => $this->project_id,
                ':assigned_to' => $this->assigned_to,
                ':title' => $this->title,
                ':description' => $this->description,
                ':priority' => $this->priority,
                ':status_id' => $this->status_id,
                ':estimated_time' => $this->estimated_time,
                ':due_date' => $this->due_date,
            ]
        );

        $this->id = $this->db->lastInsertId();
        return true;
    }

    /**
     * Update an existing task in the database.
     */
    public function update(): bool
    {
        if (!$this->id) {
            throw new Exception("Task ID is not set.");
        }

        $stmt = $this->db->executeQuery(
            "UPDATE tasks
             SET project_id = :project_id, assigned_to = :assigned_to, title = :title, description = :description,
                 priority = :priority, status_id = :status_id, estimated_time = :estimated_time, due_date = :due_date,
                 time_spent = :time_spent, complete_date = :complete_date, updated_at = NOW()
             WHERE id = :id",
            [
                ':id' => $this->id,
                ':project_id' => $this->project_id,
                ':assigned_to' => $this->assigned_to,
                ':title' => $this->title,
                ':description' => $this->description,
                ':priority' => $this->priority,
                ':status_id' => $this->status_id,
                ':estimated_time' => $this->estimated_time,
                ':due_date' => $this->due_date,
                ':time_spent' => $this->time_spent,
                ':complete_date' => $this->complete_date,
            ]
        );

        return true;
    }

    /**
     * Soft delete a task by marking it as deleted.
     */
    public function delete(): bool
    {
        if (!$this->id) {
            throw new Exception("Task ID is not set.");
        }

        $stmt = $this->db->executeQuery(
            "UPDATE tasks SET is_deleted = 1, updated_at = NOW() WHERE id = :id",
            [':id' => $this->id]
        );

        return true;
    }

    /**
     * Fetch task statuses.
     */
    public function getTaskStatuses(): array
    {
        $stmt = $this->db->executeQuery(
            "SELECT * FROM task_statuses WHERE is_deleted = 0"
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Fetch subtasks associated with this task.
     */
    public function getSubtasks(): array
    {
        if (!$this->id) {
            throw new Exception("Task ID is not set.");
        }

        $stmt = $this->db->executeQuery(
            "SELECT 
                 id,
                 title,
                 description,
                 status_id
             FROM 
                 tasks
             WHERE 
                 parent_task_id = :task_id
                 AND is_subtask = 1
                 AND is_deleted = 0",
            [':task_id' => $this->id]
        );

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Fetch time tracking entries for this task.
     */
    public function getTimeEntries(): array
    {
        if (!$this->id) {
            throw new Exception("Task ID is not set.");
        }

        $stmt = $this->db->executeQuery(
            "SELECT * FROM time_tracking WHERE task_id = :task_id",
            [':task_id' => $this->id]
        );

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Get recent tasks for a user.
     *
     * @param int $userId The user ID.
     * @return array An array of task objects.
     */
    public function getRecentTasksByUserId(int $userId): array
    {
        $stmt = $this->db->executeQuery(
            "SELECT * FROM tasks 
             WHERE assigned_to = :user_id 
             ORDER BY created_at DESC 
             LIMIT 5",
            [':user_id' => $userId]
        );

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}