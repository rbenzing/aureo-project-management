<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Milestone
{
    private $db;

    public ?int $id = null;
    public string $title;
    public ?string $description = null;
    public ?string $due_date = null;
    public ?string $complete_date = null;
    public int $status_id;
    public int $project_id;
    public ?string $created_at = null;
    public ?string $updated_at = null;
    public bool $is_deleted = false;

    public function __construct()
    {
        // Initialize the database connection
        $this->db = Database::getInstance();
    }

    /**
     * Hydrate the object with database row data.
     */
    private function hydrate(array $data): void
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    /**
     * Fetch all milestones from the database (paginated).
     */
    public function getAllPaginated($limit = 10, $page = 1): array
    {
        $offset = ($page - 1) * $limit;
        $stmt = $this->db->executeQuery("SELECT * FROM milestones WHERE is_deleted = 0 LIMIT :limit OFFSET :offset", [
            ':limit' => $limit,
            ':offset' => $offset,
        ]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Count all active milestones.
     */
    public function countAll(): int
    {
        $stmt = $this->db->executeQuery("SELECT COUNT(*) FROM milestones WHERE is_deleted = 0");
        return (int)$stmt->fetchColumn();
    }

    /**
     * Find a milestone by its ID.
     */
    public function find(int $id): ?self
    {
        $stmt = $this->db->executeQuery("SELECT * FROM milestones WHERE id = :id AND is_deleted = 0 LIMIT 1", [
            ':id' => $id,
        ]);
        $milestoneData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$milestoneData) {
            return null;
        }

        $this->hydrate($milestoneData);
        return $this;
    }

    /**
     * Save or update a milestone.
     */
    public function save(): bool
    {
        if ($this->id) {
            $stmt = $this->db->executeQuery("
                UPDATE milestones
                SET title = :title, description = :description, due_date = :due_date, complete_date = :complete_date,
                    status_id = :status_id, project_id = :project_id, is_deleted = :is_deleted, updated_at = NOW()
                WHERE id = :id
            ", [
                ':id' => $this->id,
                ':title' => $this->title,
                ':description' => $this->description,
                ':due_date' => $this->due_date,
                ':complete_date' => $this->complete_date,
                ':status_id' => $this->status_id,
                ':project_id' => $this->project_id,
                ':is_deleted' => $this->is_deleted ? 1 : 0,
            ]);
        } else {
            $stmt = $this->db->executeQuery("
                INSERT INTO milestones (title, description, due_date, complete_date, status_id, project_id, is_deleted, created_at, updated_at)
                VALUES (:title, :description, :due_date, :complete_date, :status_id, :project_id, :is_deleted, NOW(), NOW())
            ", [
                ':title' => $this->title,
                ':description' => $this->description,
                ':due_date' => $this->due_date,
                ':complete_date' => $this->complete_date,
                ':status_id' => $this->status_id,
                ':project_id' => $this->project_id,
                ':is_deleted' => $this->is_deleted ? 1 : 0,
            ]);

            if (!$this->id) {
                $this->id = $this->db->getPdo()->lastInsertId();
            }
        }

        return true;
    }

    /**
     * Soft delete a milestone.
     */
    public function delete(): bool
    {
        $stmt = $this->db->executeQuery("UPDATE milestones SET is_deleted = 1, updated_at = NOW() WHERE id = :id", [
            ':id' => $this->id,
        ]);
        return $stmt->execute();
    }

    /**
     * Get all milestone statuses.
     */
    public function getMilestoneStatuses(): array
    {
        $stmt = $this->db->executeQuery("SELECT * FROM milestone_statuses WHERE is_deleted = 0");
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}