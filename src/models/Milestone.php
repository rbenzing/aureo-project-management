<?php
// file: Models/Milestone.php
declare(strict_types=1);

namespace App\Models;

use PDO;
use InvalidArgumentException;

/**
 * Milestone Model
 * 
 * Handles all milestone-related database operations
 */
class Milestone extends BaseModel
{
    protected string $table = 'milestones';

    /**
     * Milestone properties
     */
    public ?int $id = null;
    public string $title;
    public ?string $description = null;
    public string $milestone_type = 'milestone';
    public ?string $start_date = null;
    public ?string $due_date = null;
    public ?string $complete_date = null;
    public ?int $epic_id = null;
    public int $project_id;
    public int $status_id;
    public bool $is_deleted = false;
    public ?string $created_at = null;
    public ?string $updated_at = null;

    /**
     * Define fillable fields
     */
    protected array $fillable = [
        'title',
        'description',
        'milestone_type',
        'start_date',
        'due_date',
        'complete_date',
        'epic_id',
        'project_id',
        'status_id'
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

    // Additional method to ensure status name is always populated
    public function findWithDetails(int $id): ?object
    {
        try {
            $sql = "SELECT 
            m.*,
            s.name AS status_name,
            p.name AS project_name
        FROM milestones m
        LEFT JOIN statuses_milestone s ON m.status_id = s.id
        LEFT JOIN projects p ON m.project_id = p.id
        WHERE m.id = :id AND m.is_deleted = 0";

            $stmt = $this->db->executeQuery($sql, [':id' => $id]);
            $milestone = $stmt->fetch(PDO::FETCH_OBJ);

            if ($milestone) {
                // Fetch tasks
                $milestone->tasks = $this->getTasks($id);

                // If epic, get related milestones
                if ($milestone->milestone_type === 'epic') {
                    $milestone->related_milestones = $this->getEpicMilestones($id);
                }

                // Ensure status_name is set
                $milestone->status_name = $milestone->status_name ?? 'Unknown';
            }

            return $milestone ?: null;
        } catch (\Exception $e) {
            throw new \RuntimeException("Failed to find milestone details: " . $e->getMessage());
        }
    }

    // Modify the find method similarly
    public function find(int $id): object|false
    {
        try {
            $sql = "SELECT 
            m.*,
            s.name AS status_name
        FROM milestones m
        LEFT JOIN statuses_milestone s ON m.status_id = s.id
        WHERE m.id = :id AND m.is_deleted = 0";

            $stmt = $this->db->executeQuery($sql, [':id' => $id]);
            $milestone = $stmt->fetch(PDO::FETCH_OBJ);

            // Ensure status_name is set
            if ($milestone) {
                $milestone->status_name = $milestone->status_name ?? 'Unknown';
            }

            return $milestone ?: false;
        } catch (\Exception $e) {
            throw new \RuntimeException("Failed to find milestone: " . $e->getMessage());
        }
    }

    /**
     * Get milestones with completion rates and time remaining
     *
     * @param int   $limit      Items per page
     * @param int   $page       Current page number
     * @param array $conditions Optional conditions to filter by
     * @return array
     */
    public function getAllWithProgress(int $limit = 10, int $page = 1, array $conditions = []): array
    {
        $limit  = (int) $limit; // ensure integer
        $page   = (int) $page;  // ensure integer
        $offset = ($page - 1) * $limit;

        // Start with a default clause to exclude deleted milestones
        $whereClauses = ['m.is_deleted = 0'];
        $params       = [];

        // Process additional conditions
        foreach ($conditions as $column => $value) {
            // Skip anything that doesn't match your allowed column format
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $column)) {
                continue;
            }

            // Process complex conditions (operators)
            if (is_array($value)) {
                $operator  = key($value);
                $condValue = $value[$operator];

                switch ($operator) {
                    case '>':
                        $whereClauses[]    = "m.{$column} > :{$column}";
                        $params[":{$column}"] = $condValue;
                        break;
                    case '<':
                        $whereClauses[]    = "m.{$column} < :{$column}";
                        $params[":{$column}"] = $condValue;
                        break;
                    default:
                        // Fallback to '=' if the operator isn't recognized
                        $whereClauses[]    = "m.{$column} = :{$column}";
                        $params[":{$column}"] = $condValue;
                }
            } else {
                // Simple equality
                $whereClauses[]    = "m.{$column} = :{$column}";
                $params[":{$column}"] = $value;
            }
        }

        // If for some reason the array is empty (which won’t happen in this code
        // because of "m.is_deleted = 0"), you can default to 1=1
        if (empty($whereClauses)) {
            $whereClause = '1=1';
        } else {
            $whereClause = implode(' AND ', $whereClauses);
        }

        $sql = "SELECT
                m.id,
                m.title,
                m.start_date,
                m.due_date,
                m.complete_date,
                m.status_id,
                m.project_id,
                m.milestone_type,
                m.epic_id,
                p.name AS project_name,
                s.name AS status_name,
                CASE
                    WHEN m.start_date IS NULL OR m.due_date IS NULL THEN NULL
                    WHEN DATEDIFF(m.due_date, m.start_date) = 0 THEN 100
                    WHEN m.complete_date IS NOT NULL THEN 100
                    ELSE 
                        LEAST(
                            (DATEDIFF(CURDATE(), m.start_date) * 100.0) / 
                            DATEDIFF(m.due_date, m.start_date),
                            100
                        )
                END AS completion_rate,
                CASE
                    WHEN m.due_date IS NULL THEN NULL
                    ELSE DATEDIFF(m.due_date, CURDATE())
                END AS time_remaining,
                (
                    SELECT COUNT(*)
                    FROM milestone_tasks mt
                    JOIN tasks t ON mt.task_id = t.id
                    WHERE mt.milestone_id = m.id
                      AND t.is_deleted = 0
                ) AS task_count
            FROM milestones m
            LEFT JOIN projects p ON m.project_id = p.id
            LEFT JOIN statuses_milestone s ON m.status_id = s.id
            WHERE {$whereClause}
            ORDER BY m.due_date ASC, m.title ASC
            LIMIT :limit OFFSET :offset";

        $params[':limit']  = $limit;
        $params[':offset'] = $offset;

        $stmt = $this->db->executeQuery($sql, $params);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Get milestone statuses
     * 
     * @return array
     */
    public function getMilestoneStatuses(): array
    {
        $sql = "SELECT * FROM statuses_milestone WHERE is_deleted = 0";
        $stmt = $this->db->executeQuery($sql);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Get epics for project
     * 
     * @param int $projectId
     * @return array
     */
    public function getProjectEpics(int $projectId): array
    {
        $sql = "SELECT m.*, s.name as status_name 
                FROM milestones m
                JOIN statuses_milestone s ON m.status_id = s.id
                WHERE m.project_id = :project_id 
                AND m.milestone_type = 'epic' 
                AND m.is_deleted = 0";

        $stmt = $this->db->executeQuery($sql, [':project_id' => $projectId]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Get milestones for epic
     * 
     * @param int $epicId
     * @return array
     */
    public function getEpicMilestones(int $epicId): array
    {
        $sql = "SELECT m.*, s.name as status_name
                FROM milestones m
                JOIN statuses_milestone s ON m.status_id = s.id
                WHERE m.epic_id = :epic_id 
                AND m.milestone_type = 'milestone' 
                AND m.is_deleted = 0";

        $stmt = $this->db->executeQuery($sql, [':epic_id' => $epicId]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Get tasks associated with a milestone
     * 
     * @param int $milestoneId
     * @return array
     */
    public function getTasks(int $milestoneId): array
    {
        $sql = "SELECT t.*, ts.name as status_name, u.first_name, u.last_name
                FROM tasks t
                JOIN milestone_tasks mt ON t.id = mt.task_id
                JOIN statuses_task ts ON t.status_id = ts.id
                LEFT JOIN users u ON t.assigned_to = u.id
                WHERE mt.milestone_id = :milestone_id
                AND t.is_deleted = 0";

        $stmt = $this->db->executeQuery($sql, [':milestone_id' => $milestoneId]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Add tasks to milestone
     * 
     * @param int $milestoneId
     * @param array $taskIds
     * @return bool
     */
    public function addTasks(int $milestoneId, array $taskIds): bool
    {
        try {
            $this->db->beginTransaction();

            // First remove existing associations to ensure clean state
            $sql = "DELETE FROM milestone_tasks WHERE milestone_id = :milestone_id";
            $this->db->executeInsertUpdate($sql, [':milestone_id' => $milestoneId]);

            // Then add new associations
            foreach ($taskIds as $taskId) {
                $sql = "INSERT INTO milestone_tasks (milestone_id, task_id) 
                        VALUES (:milestone_id, :task_id)";
                $this->db->executeInsertUpdate($sql, [
                    ':milestone_id' => $milestoneId,
                    ':task_id' => $taskId
                ]);
            }

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw new \RuntimeException("Failed to add tasks to milestone: " . $e->getMessage());
        }
    }

    /**
     * Calculate milestone progress based on completed tasks
     * 
     * @param int $milestoneId
     * @return float Percentage of completion (0-100)
     */
    public function calculateTaskProgress(int $milestoneId): float
    {
        $sql = "SELECT 
                    COUNT(t.id) as total_tasks,
                    SUM(CASE WHEN t.status_id = 6 THEN 1 ELSE 0 END) as completed_tasks
                FROM tasks t
                JOIN milestone_tasks mt ON t.id = mt.task_id
                WHERE mt.milestone_id = :milestone_id
                AND t.is_deleted = 0";

        $stmt = $this->db->executeQuery($sql, [':milestone_id' => $milestoneId]);
        $result = $stmt->fetch(PDO::FETCH_OBJ);

        if (!$result || $result->total_tasks == 0) {
            return 0;
        }

        return ($result->completed_tasks / $result->total_tasks) * 100;
    }

    /**
     * Validate milestone data before save
     * 
     * @param array $data
     * @param int|null $id
     * @throws InvalidArgumentException
     */
    protected function validate(array $data, ?int $id = null): void
    {
        parent::validate($data, $id);

        if (isset($data['start_date']) && isset($data['due_date']) && !empty($data['start_date']) && !empty($data['due_date'])) {
            if (strtotime($data['due_date']) < strtotime($data['start_date'])) {
                throw new InvalidArgumentException('Due date cannot be earlier than start date');
            }
        }

        if (isset($data['complete_date']) && !empty($data['complete_date'])) {
            if (isset($data['due_date']) && !empty($data['due_date']) && strtotime($data['complete_date']) > strtotime($data['due_date'])) {
                throw new InvalidArgumentException('Complete date cannot be later than due date');
            }
        }

        // Validate epic-milestone relationship to prevent circular references
        if (isset($data['epic_id']) && !empty($data['epic_id'])) {
            // Don't allow self-reference
            if (isset($id) && $data['epic_id'] == $id) {
                throw new InvalidArgumentException('A milestone cannot be its own epic');
            }

            // Check if it's a valid epic
            $sql = "SELECT milestone_type FROM milestones WHERE id = :epic_id AND is_deleted = 0";
            $stmt = $this->db->executeQuery($sql, [':epic_id' => $data['epic_id']]);
            $epic = $stmt->fetch(PDO::FETCH_OBJ);

            if (!$epic || $epic->milestone_type !== 'epic') {
                throw new InvalidArgumentException('Invalid epic ID');
            }

            // If this is an epic itself, check for circular references
            if (isset($data['milestone_type']) && $data['milestone_type'] === 'epic' && isset($id)) {
                $this->checkCircularEpicReference($id, $data['epic_id']);
            }
        }
    }

    /**
     * Check for circular epic references
     * 
     * @param int $currentId
     * @param int $newParentId
     * @throws InvalidArgumentException
     */
    private function checkCircularEpicReference(int $currentId, int $newParentId): void
    {
        // Check if any child milestones of current milestone have the new parent as a child
        $sql = "WITH RECURSIVE milestone_hierarchy AS (
                    SELECT id, epic_id FROM milestones WHERE id = :start_id
                    UNION ALL
                    SELECT m.id, m.epic_id 
                    FROM milestones m
                    JOIN milestone_hierarchy mh ON m.epic_id = mh.id
                )
                SELECT COUNT(*) FROM milestone_hierarchy WHERE id = :check_id";

        $stmt = $this->db->executeQuery($sql, [
            ':start_id' => $newParentId,
            ':check_id' => $currentId
        ]);

        if ($stmt->fetchColumn() > 0) {
            throw new InvalidArgumentException('Circular epic reference detected');
        }
    }
}
