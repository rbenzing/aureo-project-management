<?php
// file: Models/Task.php
declare(strict_types=1);

namespace App\Models;

use PDO;
use App\Utils\Time;
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
     * Define fillable fields
     */
    protected array $fillable = [
        'project_id',
        'assigned_to',
        'title',
        'description',
        'priority',
        'status_id',
        'estimated_time',
        'billable_time',
        'time_spent',
        'start_date',
        'due_date',
        'complete_date',
        'hourly_rate',
        'is_hourly',
        'is_subtask',
        'parent_task_id'
    ];

    /**
     * Define searchable fields
     */
    protected array $searchable = [
        'title',
        'description'
    ];

    /**
     * Define validation rules
     */
    protected array $validationRules = [
        'title' => ['required', 'string'],
        'project_id' => ['required'],
        'status_id' => ['required']
    ];

    /**
     * Get task with full details
     * @param int $id
     * @return object|null
     */
    public function findWithDetails(int $id): ?object
    {
        try {
            $sql = "SELECT t.*,
                p.name as project_name,
                ts.name as status_name,
                u.first_name,
                u.last_name
            FROM tasks t
            LEFT JOIN projects p ON t.project_id = p.id
            LEFT JOIN statuses_task ts ON t.status_id = ts.id
            LEFT JOIN users u ON t.assigned_to = u.id
            WHERE t.id = :id AND t.is_deleted = 0";

            $stmt = $this->db->executeQuery($sql, [':id' => $id]);
            $task = $stmt->fetch(PDO::FETCH_OBJ);

            if ($task) {
                $task->subtasks = $this->getSubtasks($id);
                $task->time_entries = $this->getTaskTimeEntries($id);
                $task->comments = $this->getTaskComments($id);
                $task->milestones = $this->getTaskMilestones($id);
                $task->history = $this->getTaskHistory($id);

                // Calculate task metrics
                $task->metrics = $this->calculateTaskMetrics($task);

                // Format times for display
                $task->formatted_estimated_time = Time::formatSeconds($task->estimated_time);
                $task->formatted_time_spent = Time::formatSeconds($task->time_spent);
                $task->formatted_billable_time = Time::formatSeconds($task->billable_time);
            }

            return $task ?: null;
        } catch (\Exception $e) {
            throw new RuntimeException("Error fetching task details: " . $e->getMessage());
        }
    }

    /**
     * Get tasks with full details
     * @return ?array
     */
    public function getAllWithDetails(int $limit = 10, int $page = 1): ?array
    {
        try {
            $offset = ($page - 1) * $limit;

            $sql = "SELECT t.*,
                p.name as project_name,
                ts.name as status_name,
                u.first_name,
                u.last_name
            FROM tasks t
            LEFT JOIN projects p ON t.project_id = p.id
            LEFT JOIN statuses_task ts ON t.status_id = ts.id
            LEFT JOIN users u ON t.assigned_to = u.id
            WHERE t.is_deleted = 0 AND p.is_deleted = 0 
            ORDER BY t.due_date ASC, t.priority DESC
            LIMIT :limit OFFSET :offset";

            $stmt = $this->db->executeQuery($sql, [
                ':limit' => $limit,
                ':offset' => $offset,
            ]);
            $tasks = $stmt->fetchAll(PDO::FETCH_OBJ);

            // Format times for display
            foreach ($tasks as $task) {
                $task->formatted_estimated_time = Time::formatSeconds($task->estimated_time);
                $task->formatted_time_spent = Time::formatSeconds($task->time_spent);
                $task->formatted_billable_time = Time::formatSeconds($task->billable_time);
            }

            return $tasks ?: null;
        } catch (\Exception $e) {
            throw new RuntimeException("Error fetching tasks details: " . $e->getMessage());
        }
    }

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
        try {
            $offset = ($page - 1) * $limit;

            $sql = "SELECT t.*, 
                       p.name as project_name,
                       ts.name as status_name,
                       u.first_name, 
                       u.last_name
                FROM tasks t
                LEFT JOIN projects p ON t.project_id = p.id
                LEFT JOIN statuses_task ts ON t.status_id = ts.id
                LEFT JOIN users u ON t.assigned_to = u.id
                WHERE t.assigned_to = :user_id 
                AND t.is_deleted = 0
                ORDER BY t.due_date ASC, t.priority DESC
                LIMIT :limit OFFSET :offset";

            $stmt = $this->db->executeQuery($sql, [
                ':user_id' => $userId,
                ':limit' => $limit,
                ':offset' => $offset
            ]);
            $tasks = $stmt->fetchAll(PDO::FETCH_OBJ);

            // Format times for display
            foreach ($tasks as $task) {
                $task->formatted_estimated_time = Time::formatSeconds($task->estimated_time);
                $task->formatted_time_spent = Time::formatSeconds($task->time_spent);
            }

            return $tasks;
        } catch (\Exception $e) {
            throw new RuntimeException("Error fetching user tasks: " . $e->getMessage());
        }
    }

    /**
     * Get tasks by project
     * 
     * @param int $projectId
     * @return array
     */
    public function getByProjectId(int $projectId): array
    {
        try {
            $sql = "SELECT 
                    t.*,
                    ts.name AS status_name,
                    u.first_name,
                    u.last_name
                FROM 
                    tasks t
                LEFT JOIN
                    statuses_task ts ON t.status_id = ts.id
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
        } catch (\Exception $e) {
            throw new RuntimeException("Error fetching project tasks: " . $e->getMessage());
        }
    }

    /**
     * Get task subtasks
     * 
     * @param int $taskId
     * @return array
     */
    public function getSubtasks(int $taskId): array
    {
        try {
            $sql = "SELECT t.*,
                       ts.name as status_name,
                       u.first_name,
                       u.last_name
                FROM tasks t
                LEFT JOIN statuses_task ts ON t.status_id = ts.id
                LEFT JOIN users u ON t.assigned_to = u.id
                WHERE t.parent_task_id = :task_id
                AND t.is_subtask = 1
                AND t.is_deleted = 0
                ORDER BY t.priority DESC, t.due_date ASC";

            $stmt = $this->db->executeQuery($sql, [':task_id' => $taskId]);
            $subtasks = $stmt->fetchAll(PDO::FETCH_OBJ);

            // Format times for subtasks
            foreach ($subtasks as $subtask) {
                $subtask->formatted_time_spent = Time::formatSeconds($subtask->time_spent);
                $subtask->formatted_estimated_time = Time::formatSeconds($subtask->estimated_time);
            }

            return $subtasks;
        } catch (\Exception $e) {
            throw new RuntimeException("Error fetching subtasks: " . $e->getMessage());
        }
    }

    /**
     * Get task statuses
     * 
     * @return array
     */
    public function getTaskStatuses(): array
    {
        try {
            $sql = "SELECT * FROM statuses_task WHERE is_deleted = 0";
            $stmt = $this->db->executeQuery($sql);
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            throw new RuntimeException("Error fetching task statuses: " . $e->getMessage());
        }
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
        try {
            $sql = "SELECT t.*, 
                       p.name as project_name,
                       ts.name as status_name
                FROM tasks t
                LEFT JOIN projects p ON t.project_id = p.id
                LEFT JOIN statuses_task ts ON t.status_id = ts.id
                WHERE t.assigned_to = :user_id 
                AND t.is_deleted = 0
                ORDER BY t.updated_at DESC 
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
     * Get task time entries
     * 
     * @param int $taskId
     * @return array
     */
    public function getTaskTimeEntries(int $taskId): array
    {
        try {
            $sql = "SELECT te.*,
                       u.first_name,
                       u.last_name
                FROM time_entries te
                LEFT JOIN users u ON te.user_id = u.id
                WHERE te.task_id = :task_id
                ORDER BY te.start_time DESC";

            $stmt = $this->db->executeQuery($sql, [':task_id' => $taskId]);
            $entries = $stmt->fetchAll(PDO::FETCH_OBJ);

            // Format duration
            foreach ($entries as $entry) {
                $entry->formatted_duration = Time::formatSeconds($entry->duration);
            }

            return $entries;
        } catch (\Exception $e) {
            throw new RuntimeException("Error fetching time entries: " . $e->getMessage());
        }
    }

    /**
     * Create a time entry
     * 
     * @param array $data
     * @return int The new time entry ID
     */
    public function createTimeEntry(array $data): int
    {
        try {
            $fields = array_keys($data);
            $placeholders = array_map(fn($field) => ":$field", $fields);

            $sql = sprintf(
                "INSERT INTO time_entries (%s) VALUES (%s)",
                implode(', ', $fields),
                implode(', ', $placeholders)
            );

            $params = array_combine($placeholders, array_values($data));

            $success = $this->db->executeInsertUpdate($sql, $params);

            if (!$success) {
                throw new RuntimeException("Failed to create time entry");
            }

            return $this->db->lastInsertId();
        } catch (\Exception $e) {
            throw new RuntimeException("Error creating time entry: " . $e->getMessage());
        }
    }

    /**
     * Get task comments
     * 
     * @param int $taskId
     * @return array
     */
    public function getTaskComments(int $taskId): array
    {
        try {
            $sql = "SELECT tc.*,
                       u.first_name,
                       u.last_name
                FROM task_comments tc
                LEFT JOIN users u ON tc.user_id = u.id
                WHERE tc.task_id = :task_id
                AND tc.is_deleted = 0
                ORDER BY tc.created_at DESC";

            $stmt = $this->db->executeQuery($sql, [':task_id' => $taskId]);
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            throw new RuntimeException("Error fetching task comments: " . $e->getMessage());
        }
    }

    /**
     * Add a comment to a task
     * 
     * @param int $taskId
     * @param int $userId
     * @param string $content
     * @return int The new comment ID
     */
    public function addComment(int $taskId, int $userId, string $content): int
    {
        try {
            $sql = "INSERT INTO task_comments (task_id, user_id, content)
                    VALUES (:task_id, :user_id, :content)";

            $success = $this->db->executeInsertUpdate($sql, [
                ':task_id' => $taskId,
                ':user_id' => $userId,
                ':content' => $content
            ]);

            if (!$success) {
                throw new RuntimeException("Failed to add comment");
            }

            return $this->db->lastInsertId();
        } catch (\Exception $e) {
            throw new RuntimeException("Error adding comment: " . $e->getMessage());
        }
    }

    /**
     * Add a history entry
     * 
     * @param int $taskId
     * @param int $userId
     * @param string $action
     * @param string|null $fieldChanged
     * @param string|null $oldValue
     * @param string|null $newValue
     * @return int The new history entry ID
     */
    public function addHistoryEntry(
        int $taskId,
        int $userId,
        string $action,
        ?string $fieldChanged = null,
        ?string $oldValue = null,
        ?string $newValue = null
    ): int {
        try {
            $sql = "INSERT INTO task_history (task_id, user_id, action, field_changed, old_value, new_value)
                    VALUES (:task_id, :user_id, :action, :field_changed, :old_value, :new_value)";

            $success = $this->db->executeInsertUpdate($sql, [
                ':task_id' => $taskId,
                ':user_id' => $userId,
                ':action' => $action,
                ':field_changed' => $fieldChanged,
                ':old_value' => $oldValue,
                ':new_value' => $newValue
            ]);

            if (!$success) {
                throw new RuntimeException("Failed to add history entry");
            }

            return $this->db->lastInsertId();
        } catch (\Exception $e) {
            throw new RuntimeException("Error adding history entry: " . $e->getMessage());
        }
    }

    /**
     * Get task history
     * 
     * @param int $taskId
     * @return array
     */
    public function getTaskHistory(int $taskId): array
    {
        try {
            $sql = "SELECT th.*,
                       u.first_name,
                       u.last_name
                FROM task_history th
                LEFT JOIN users u ON th.user_id = u.id
                WHERE th.task_id = :task_id
                ORDER BY th.created_at DESC";

            $stmt = $this->db->executeQuery($sql, [':task_id' => $taskId]);
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            throw new RuntimeException("Error fetching task history: " . $e->getMessage());
        }
    }

    /**
     * Get task milestones
     * 
     * @param int $taskId
     * @return array
     */
    public function getTaskMilestones(int $taskId): array
    {
        try {
            $sql = "SELECT m.*,
                       ms.name as status_name
                FROM milestones m
                JOIN milestone_tasks mt ON m.id = mt.milestone_id
                LEFT JOIN statuses_milestone ms ON m.status_id = ms.id
                WHERE mt.task_id = :task_id
                AND m.is_deleted = 0";

            $stmt = $this->db->executeQuery($sql, [':task_id' => $taskId]);
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            throw new RuntimeException("Error fetching task milestones: " . $e->getMessage());
        }
    }

    /**
     * Get total time spent for a user
     * 
     * @param int $userId
     * @return int Total time spent in seconds
     */
    public function getTotalTimeSpent(int $userId): int
    {
        try {
            $sql = "SELECT COALESCE(SUM(time_spent), 0) 
                    FROM tasks 
                    WHERE assigned_to = :user_id 
                    AND is_deleted = 0";

            $stmt = $this->db->executeQuery($sql, [':user_id' => $userId]);
            return (int) $stmt->fetchColumn();
        } catch (\Exception $e) {
            throw new RuntimeException("Error calculating total time spent: " . $e->getMessage());
        }
    }

    /**
     * Get total billable time for a user
     * 
     * @param int $userId
     * @return int Total billable time in seconds
     */
    public function getTotalBillableTime(int $userId): int
    {
        try {
            $sql = "SELECT COALESCE(SUM(billable_time), 0) 
                    FROM tasks 
                    WHERE assigned_to = :user_id 
                    AND is_hourly = 1 
                    AND is_deleted = 0";

            $stmt = $this->db->executeQuery($sql, [':user_id' => $userId]);
            return (int) $stmt->fetchColumn();
        } catch (\Exception $e) {
            throw new RuntimeException("Error calculating total billable time: " . $e->getMessage());
        }
    }

    /**
     * Get time spent this week for a user
     * 
     * @param int $userId
     * @return int Time spent this week in seconds
     */
    public function getWeeklyTimeSpent(int $userId): int
    {
        try {
            $sql = "SELECT COALESCE(SUM(duration), 0)
                    FROM time_entries 
                    WHERE user_id = :user_id 
                    AND start_time BETWEEN 
                        DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY) 
                        AND DATE_ADD(DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY), INTERVAL 6 DAY)";

            $stmt = $this->db->executeQuery($sql, [':user_id' => $userId]);
            return (int) $stmt->fetchColumn();
        } catch (\Exception $e) {
            throw new RuntimeException("Error calculating weekly time spent: " . $e->getMessage());
        }
    }

    /**
     * Get time spent this month for a user
     * 
     * @param int $userId
     * @return int Time spent this month in seconds
     */
    public function getMonthlyTimeSpent(int $userId): int
    {
        try {
            $sql = "SELECT COALESCE(SUM(duration), 0)
                    FROM time_entries 
                    WHERE user_id = :user_id 
                    AND start_time BETWEEN 
                        DATE_FORMAT(CURDATE(), '%Y-%m-01') 
                        AND LAST_DAY(CURDATE())";

            $stmt = $this->db->executeQuery($sql, [':user_id' => $userId]);
            return (int) $stmt->fetchColumn();
        } catch (\Exception $e) {
            throw new RuntimeException("Error calculating monthly time spent: " . $e->getMessage());
        }
    }

    /**
     * Organize tasks by status
     * 
     * @param array $tasks
     * @return array
     */
    private function organizeTasksByStatus(array $tasks): array
    {
        try {
            // Get status mappings
            $statusMap = [];
            $statuses = $this->getTaskStatuses();
            foreach ($statuses as $status) {
                $normalizedKey = strtolower(str_replace(' ', '_', $status->name));
                $statusMap[$status->id] = $normalizedKey;
            }

            // Organize by status
            $organized = [];
            foreach ($statusMap as $id => $key) {
                $organized[$key] = [];
            }

            // Default fallbacks in case statuses are missing
            if (!isset($organized['open'])) $organized['open'] = [];
            if (!isset($organized['in_progress'])) $organized['in_progress'] = [];
            if (!isset($organized['completed'])) $organized['completed'] = [];

            foreach ($tasks as $task) {
                $statusKey = $statusMap[$task->status_id] ?? 'other';
                if (!isset($organized[$statusKey])) {
                    $organized[$statusKey] = [];
                }
                $organized[$statusKey][] = $task;
            }

            return $organized;
        } catch (\Exception $e) {
            throw new RuntimeException("Error organizing tasks by status: " . $e->getMessage());
        }
    }

    /**
     * Calculate task metrics
     * 
     * @param object $task
     * @return array
     */
    private function calculateTaskMetrics(object $task): array
    {
        $now = new \DateTime();
        $dueDate = $task->due_date ? new \DateTime($task->due_date) : null;
        $startDate = $task->start_date ? new \DateTime($task->start_date) : null;

        // Time tracking metrics
        $estimatedTime = $task->estimated_time ?? 0;
        $timeSpent = $task->time_spent ?? 0;
        $timeRemaining = max(0, $estimatedTime - $timeSpent);

        // Progress calculation
        $progressPercentage = $estimatedTime > 0
            ? round(($timeSpent / $estimatedTime) * 100, 2)
            : 0;

        // Deadline metrics
        $isOverdue = Time::isOverdue($task->due_date, $task->status_id, [5, 6]);
        $daysUntilDue = Time::daysRemaining($task->due_date);
        $daysInProgress = $startDate ? $now->diff($startDate)->days : 0;

        return [
            'estimated_time' => $estimatedTime,
            'time_spent' => $timeSpent,
            'time_remaining' => $timeRemaining,
            'progress_percentage' => $progressPercentage,
            'is_overdue' => $isOverdue,
            'days_until_due' => $daysUntilDue,
            'days_in_progress' => $daysInProgress,
            'complexity' => $this->calculateTaskComplexity($task)
        ];
    }

    /**
     * Calculate task complexity
     * 
     * @param object $task
     * @return string
     */
    private function calculateTaskComplexity(object $task): string
    {
        $complexityFactors = 0;

        // Estimated time complexity
        $complexityFactors += match (true) {
            ($task->estimated_time ?? 0) > 14400 => 3, // > 4 hours
            ($task->estimated_time ?? 0) > 7200 => 2,  // > 2 hours
            ($task->estimated_time ?? 0) > 3600 => 1,  // > 1 hour
            default => 0
        };

        // Priority complexity
        $complexityFactors += match ($task->priority) {
            'high' => 2,
            'medium' => 1,
            default => 0
        };

        // Subtask complexity
        if ($task->is_subtask) {
            $complexityFactors += 1;
        }

        // Milestone association complexity
        if (!empty($task->milestones)) {
            $complexityFactors += 1;
        }

        return match (true) {
            $complexityFactors >= 4 => 'Very High',
            $complexityFactors >= 3 => 'High',
            $complexityFactors >= 2 => 'Medium',
            $complexityFactors >= 1 => 'Low',
            default => 'Very Low'
        };
    }

    /**
     * Validate task data before save
     * 
     * @param array $data
     * @param int|null $id
     * @throws InvalidArgumentException
     */
    protected function validate(array $data, ?int $id = null): void
    {
        parent::validate($data, $id);

        // Validate parent task relationship
        if (!empty($data['parent_task_id'])) {
            // Ensure parent task exists and is not a subtask
            $sql = "SELECT is_subtask FROM tasks WHERE id = :id AND is_deleted = 0";
            $stmt = $this->db->executeQuery($sql, [':id' => $data['parent_task_id']]);
            $parentTask = $stmt->fetch(PDO::FETCH_OBJ);

            if (!$parentTask) {
                throw new InvalidArgumentException('Invalid parent task');
            }

            if ($parentTask->is_subtask) {
                throw new InvalidArgumentException('A subtask cannot be a parent task');
            }

            // Prevent circular references
            if ($id && $id == $data['parent_task_id']) {
                throw new InvalidArgumentException('A task cannot be its own parent');
            }
        }

        // Validate time-related fields
        if (isset($data['time_spent']) && $data['time_spent'] < 0) {
            throw new InvalidArgumentException('Time spent cannot be negative');
        }

        if (isset($data['estimated_time']) && $data['estimated_time'] < 0) {
            throw new InvalidArgumentException('Estimated time cannot be negative');
        }

        // Validate priority
        if (isset($data['priority']) && !in_array($data['priority'], ['none', 'low', 'medium', 'high'])) {
            throw new InvalidArgumentException('Invalid priority value');
        }
    }
}
