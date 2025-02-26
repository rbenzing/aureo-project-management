<?php

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
     * Get milestones with completion rates and time remaining
     * 
     * @param int $limit Items per page
     * @param int $page Current page number
     * @return array
     */
    public function getAllWithProgress(int $limit = 10, int $page = 1): array
    {
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT
            id,
            title,
            start_date,
            due_date,
            complete_date,
            status_id,
            project_id,
            CASE 
                WHEN start_date IS NULL OR due_date IS NULL THEN NULL
                WHEN DATEDIFF(due_date, start_date) = 0 THEN 100
                WHEN complete_date IS NOT NULL THEN 100
                ELSE 
                    LEAST(
                        (DATEDIFF(CURDATE(), start_date) * 100.0) / 
                        DATEDIFF(due_date, start_date), 
                        100
                    )
            END AS completion_rate,
            CASE 
                WHEN due_date IS NULL THEN NULL
                ELSE DATEDIFF(due_date, CURDATE())
            END AS time_remaining
        FROM milestones
        WHERE is_deleted = 0
        LIMIT :limit OFFSET :offset";

        $stmt = $this->db->executeQuery($sql, [
            ':limit' => $limit,
            ':offset' => $offset,
        ]);

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Get milestone statuses
     * 
     * @return array
     */
    public function getMilestoneStatuses(): array
    {
        $sql = "SELECT * FROM milestone_statuses WHERE is_deleted = 0";
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
        $sql = "SELECT * FROM milestones 
                WHERE project_id = :project_id 
                AND milestone_type = 'epic' 
                AND is_deleted = 0";

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
        $sql = "SELECT * FROM milestones 
                WHERE epic_id = :epic_id 
                AND milestone_type = 'milestone' 
                AND is_deleted = 0";

        $stmt = $this->db->executeQuery($sql, [':epic_id' => $epicId]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Validate milestone data before save
     * 
     * @param array $data
     * @throws InvalidArgumentException
     */
    protected function beforeSave(array $data): void
    {
        parent::validate($data, $this->id);

        if (empty($data['title'])) {
            throw new InvalidArgumentException('Milestone title is required');
        }

        if (empty($data['project_id'])) {
            throw new InvalidArgumentException('Project ID is required');
        }

        if (empty($data['status_id'])) {
            throw new InvalidArgumentException('Status ID is required');
        }

        if (!empty($data['start_date']) && !empty($data['due_date'])) {
            if (strtotime($data['due_date']) < strtotime($data['start_date'])) {
                throw new InvalidArgumentException('Due date cannot be earlier than start date');
            }
        }

        if (!empty($data['complete_date'])) {
            if (!empty($data['due_date']) && strtotime($data['complete_date']) > strtotime($data['due_date'])) {
                throw new InvalidArgumentException('Complete date cannot be later than due date');
            }
        }

        // Improved validation with cycle detection
        if (!empty($data['epic_id'])) {
            $epic = $this->find($data['epic_id']);
            if (!$epic || $epic->milestone_type !== 'epic') {
                throw new InvalidArgumentException('Invalid epic ID');
            }
            
            // Prevent circular references
            if (!empty($data['id']) && $data['id'] == $data['epic_id']) {
                throw new InvalidArgumentException('A milestone cannot be its own epic');
            }
            
            // Check if this milestone is an epic for the current epic (circular reference)
            if (!empty($data['id']) && $data['milestone_type'] === 'epic') {
                $childEpics = $this->getEpicMilestones($data['id']);
                foreach ($childEpics as $childEpic) {
                    if ($childEpic->id == $data['epic_id']) {
                        throw new InvalidArgumentException('Circular epic reference detected');
                    }
                }
            }
        }
    }
}