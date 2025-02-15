<?php
namespace App\Models;

use PDO;

class Task {
    private $db;

    public function __construct() {
        // Initialize the database connection
        $this->db = \App\Core\Database::getInstance();
    }

    /**
     * Find a task by its ID.
     */
    public function find($id) {
        $stmt = $this->db->prepare("SELECT * FROM tasks WHERE id = :id AND is_deleted = 0");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    /**
     * Fetch all tasks assigned to a specific user (paginated).
     */
    public function getByUserIdPaginated($userId, $limit = 10, $page = 1) {
        $offset = ($page - 1) * $limit;
        $stmt = $this->db->prepare("
            SELECT * FROM tasks 
            WHERE assigned_to = :user_id AND is_deleted = 0 
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Fetch all tasks associated with a specific project.
     */
    public function getByProjectId($projectId) {
        $stmt = $this->db->prepare("
            SELECT * FROM tasks 
            WHERE project_id = :project_id AND is_deleted = 0
        ");
        $stmt->execute(['project_id' => $projectId]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Save a new task to the database.
     */
    public function save() {
        $stmt = $this->db->prepare("
            INSERT INTO tasks (project_id, assigned_to, title, description, priority, status, estimated_time, due_date, created_at, updated_at)
            VALUES (:project_id, :assigned_to, :title, :description, :priority, :status, :estimated_time, :due_date, NOW(), NOW())
        ");
        $stmt->execute([
            'project_id' => $this->project_id,
            'assigned_to' => $this->assigned_to ?? null,
            'title' => $this->title,
            'description' => $this->description ?? null,
            'priority' => $this->priority,
            'status' => $this->status,
            'estimated_time' => $this->estimated_time ?? null,
            'due_date' => $this->due_date ?? null,
        ]);
        $this->id = $this->db->lastInsertId();
    }

    /**
     * Update an existing task in the database.
     */
    public function update() {
        $stmt = $this->db->prepare("
            UPDATE tasks
            SET project_id = :project_id, assigned_to = :assigned_to, title = :title, description = :description,
                priority = :priority, status = :status, estimated_time = :estimated_time, due_date = :due_date,
                time_spent = :time_spent, updated_at = NOW()
            WHERE id = :id
        ");
        $stmt->execute([
            'id' => $this->id,
            'project_id' => $this->project_id,
            'assigned_to' => $this->assigned_to ?? null,
            'title' => $this->title,
            'description' => $this->description ?? null,
            'priority' => $this->priority,
            'status' => $this->status,
            'estimated_time' => $this->estimated_time ?? null,
            'due_date' => $this->due_date ?? null,
            'time_spent' => $this->time_spent ?? 0,
        ]);
    }

    /**
     * Soft delete a task by marking it as deleted.
     */
    public function delete() {
        $stmt = $this->db->prepare("UPDATE tasks SET is_deleted = 1, updated_at = NOW() WHERE id = :id");
        $stmt->execute(['id' => $this->id]);
    }

    /**
     * Fetch subtasks associated with this task.
     */
    public function getSubtasks() {
        $stmt = $this->db->prepare("SELECT * FROM subtasks WHERE task_id = :task_id AND is_deleted = 0");
        $stmt->execute(['task_id' => $this->id]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Fetch time tracking entries for this task.
     */
    public function getTimeEntries() {
        $stmt = $this->db->prepare("SELECT * FROM time_tracking WHERE task_id = :task_id");
        $stmt->execute(['task_id' => $this->id]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Check if a task with the given title already exists for the same project (for validation).
     */
    public static function titleExistsInProject($title, $projectId, $excludeId = null) {
        $db = \App\Core\Database::getInstance();
        $query = "SELECT COUNT(*) FROM tasks WHERE title = :title AND project_id = :project_id AND is_deleted = 0";
        $params = ['title' => $title, 'project_id' => $projectId];

        if ($excludeId) {
            $query .= " AND id != :id";
            $params['id'] = $excludeId;
        }

        $stmt = $db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }
}