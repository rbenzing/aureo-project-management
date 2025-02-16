<?php
namespace App\Models;

use PDO;
use App\Core\Database;

class Subtask {
    private PDO $db;

    public function __construct() {
        // Initialize the database connection
        $this->db = Database::getInstance();
    }

    /**
     * Find a subtask by its ID.
     */
    public function find($id) {
        $stmt = $this->db->prepare("SELECT * FROM subtasks WHERE id = :id AND is_deleted = 0");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    /**
     * Get all subtasks for a specific task.
     */
    public function getByTaskId($taskId) {
        $stmt = $this->db->prepare("SELECT * FROM subtasks WHERE task_id = :task_id AND is_deleted = 0");
        $stmt->execute(['task_id' => $taskId]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Save a new subtask to the database.
     */
    public function save($data) {
        $stmt = $this->db->prepare("
            INSERT INTO subtasks (
                task_id, title, description, status, estimated_time, time_spent, is_deleted, created_at, updated_at
            ) VALUES (
                :task_id, :title, :description, :status, :estimated_time, :time_spent, :is_deleted, NOW(), NOW()
            )
        ");
        $stmt->execute([
            'task_id' => $data['task_id'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? 'todo',
            'estimated_time' => $data['estimated_time'] ?? null,
            'time_spent' => $data['time_spent'] ?? 0,
            'is_deleted' => 0,
        ]);
        return $this->db->lastInsertId();
    }

    /**
     * Update an existing subtask in the database.
     */
    public function update($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE subtasks
            SET 
                title = :title, 
                description = :description, 
                status = :status, 
                estimated_time = :estimated_time, 
                time_spent = :time_spent, 
                updated_at = NOW()
            WHERE id = :id
        ");
        $stmt->execute([
            'id' => $id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? 'todo',
            'estimated_time' => $data['estimated_time'] ?? null,
            'time_spent' => $data['time_spent'] ?? 0,
        ]);
    }

    /**
     * Soft delete a subtask by marking it as deleted.
     */
    public function delete($id) {
        $stmt = $this->db->prepare("UPDATE subtasks SET is_deleted = 1, updated_at = NOW() WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    /**
     * Check if a subtask exists for a specific task (for validation).
     */
    public static function existsForTask($taskId, $excludeId = null) {
        $db = \App\Core\Database::getInstance();
        $query = "SELECT COUNT(*) FROM subtasks WHERE task_id = :task_id AND is_deleted = 0";
        $params = ['task_id' => $taskId];

        if ($excludeId) {
            $query .= " AND id != :id";
            $params['id'] = $excludeId;
        }

        $stmt = $db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }
}