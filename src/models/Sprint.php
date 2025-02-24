<?php

declare(strict_types=1);

namespace App\Models;

use RuntimeException;
use InvalidArgumentException;
use PDO;

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
    public int $status_id;
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
     * Get sprint with its related project and tasks
     * 
     * @param int $id
     * @return object|null
     * @throws RuntimeException
     */
    public function findWithRelations(int $id): ?object
    {
        $sql = "SELECT s.*, 
                       p.name as project_name,
                       ss.name as status_name,
                       COUNT(DISTINCT st.task_id) as total_tasks,
                       COUNT(DISTINCT CASE WHEN t.status_id = 6 THEN t.id END) as completed_tasks
                FROM sprints s
                LEFT JOIN projects p ON s.project_id = p.id
                LEFT JOIN sprint_statuses ss ON s.status_id = ss.id
                LEFT JOIN sprint_tasks st ON s.id = st.sprint_id
                LEFT JOIN tasks t ON st.task_id = t.id
                WHERE s.id = :id AND s.is_deleted = 0
                GROUP BY s.id";

        $stmt = $this->db->executeQuery($sql, [':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        
        if ($result) {
            $result->tasks = $this->getSprintTasks($id);
        }
        
        return $result;
    }

    /**
     * Get tasks assigned to sprint
     * 
     * @param int $sprintId
     * @return array
     * @throws RuntimeException
     */
    public function getSprintTasks(int $sprintId): array
    {
        $sql = "SELECT t.*, 
                       u.first_name, 
                       u.last_name,
                       ts.name as status_name
                FROM tasks t
                JOIN sprint_tasks st ON t.id = st.task_id
                LEFT JOIN users u ON t.assigned_to = u.id
                LEFT JOIN task_statuses ts ON t.status_id = ts.id
                WHERE st.sprint_id = :sprint_id 
                AND t.is_deleted = 0
                ORDER BY t.priority DESC, t.due_date ASC";

        $stmt = $this->db->executeQuery($sql, [':sprint_id' => $sprintId]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
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
                       COUNT(DISTINCT st.task_id) as total_tasks,
                       COUNT(DISTINCT CASE WHEN t.status_id = 6 THEN t.id END) as completed_tasks
                FROM sprints s
                LEFT JOIN sprint_statuses ss ON s.status_id = ss.id
                LEFT JOIN sprint_tasks st ON s.id = st.sprint_id
                LEFT JOIN tasks t ON st.task_id = t.id
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
     * Add tasks to sprint
     * 
     * @param int $sprintId
     * @param array $taskIds
     * @return bool
     * @throws RuntimeException
     */
    public function addTasks(int $sprintId, array $taskIds): bool
    {
        try {
            $this->db->beginTransaction();

            // Remove existing tasks
            $sql = "DELETE FROM sprint_tasks 
                    WHERE sprint_id = :sprint_id 
                    AND task_id IN (" . implode(',', array_fill(0, count($taskIds), '?')) . ")";
            
            $params = array_merge([':sprint_id' => $sprintId], $taskIds);
            $this->db->executeInsertUpdate($sql, $params);

            // Add new tasks
            $values = [];
            $params = [];
            foreach ($taskIds as $i => $taskId) {
                $values[] = "(:sprint_id, :task_id_$i)";
                $params[":task_id_$i"] = $taskId;
            }
            $params[':sprint_id'] = $sprintId;

            $sql = "INSERT INTO sprint_tasks (sprint_id, task_id) VALUES " . implode(', ', $values);
            $this->db->executeInsertUpdate($sql, $params);

            $this->db->commit();
            return true;

        } catch (\Exception $e) {
            $this->db->rollBack();
            throw new RuntimeException("Failed to add tasks to sprint: " . $e->getMessage());
        }
    }

    // ... rest of the methods remain the same with updated return types and proper exception handling ...

    /**
     * Validate sprint data before save
     * 
     * @param array $data
     * @throws InvalidArgumentException
     */
    protected function beforeSave(array $data): void
    {
        if (empty($data['name'])) {
            throw new InvalidArgumentException('Sprint name is required');
        }

        if (empty($data['project_id'])) {
            throw new InvalidArgumentException('Project ID is required');
        }

        if (empty($data['start_date']) || empty($data['end_date'])) {
            throw new InvalidArgumentException('Start and end dates are required');
        }

        // Validate dates
        if (strtotime($data['end_date']) < strtotime($data['start_date'])) {
            throw new InvalidArgumentException('End date cannot be earlier than start date');
        }

        // Check for overlapping sprints
        $sql = "SELECT COUNT(*) FROM sprints 
                WHERE project_id = :project_id 
                AND id != :id
                AND is_deleted = 0
                AND status_id = (SELECT id FROM sprint_statuses WHERE name = 'active')
                AND (
                    (start_date BETWEEN :start_date AND :end_date)
                    OR (end_date BETWEEN :start_date AND :end_date)
                    OR (:start_date BETWEEN start_date AND end_date)
                )";

        $stmt = $this->db->executeQuery($sql, [
            ':project_id' => $data['project_id'],
            ':id' => $data['id'] ?? 0,
            ':start_date' => $data['start_date'],
            ':end_date' => $data['end_date']
        ]);

        if ($stmt->fetchColumn() > 0) {
            throw new InvalidArgumentException('Sprint dates overlap with an existing active sprint');
        }
    }
}