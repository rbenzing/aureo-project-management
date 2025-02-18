<?php
namespace App\Models;

use PDO;
use App\Core\Database;

class Company {
    private PDO $db;

    public ?int $id = null;
    public string $name;
    public ?string $address = null;
    public ?string $phone = null;
    public string $email;
    public ?string $website = null;
    public bool $is_deleted = false;
    public ?string $created_at = null;
    public ?string $updated_at = null;

    public function __construct() {
        // Initialize the database connection
        $this->db = Database::getInstance();
    }

    /**
     * Hydrate the object with database row data.
     */
    private function hydrate(array $data): void {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    /**
     * Find a company by its ID.
     */
    public function find($id) {
        $stmt = $this->db->prepare("SELECT * FROM companies WHERE id = :id AND is_deleted = 0");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    /**
     * Fetch all companies (paginated).
     */
    public function getAllPaginated($limit = 10, $page = 1) {
        $offset = ($page - 1) * $limit;
        $stmt = $this->db->prepare("SELECT * FROM companies WHERE is_deleted = 0 LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Fetch all companies without pagination.
     */
    public function getAll() {
        $stmt = $this->db->prepare("SELECT * FROM companies WHERE is_deleted = 0");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Save a new company to the database.
     */
    public function save() {
        $stmt = $this->db->prepare("
            INSERT INTO companies (name, address, phone, email, created_at, updated_at)
            VALUES (:name, :address, :phone, :email, NOW(), NOW())
        ");
        $stmt->execute([
            'name' => $this->name,
            'address' => $this->address ?? null,
            'phone' => $this->phone ?? null,
            'email' => $this->email ?? null,
        ]);
        $this->id = $this->db->lastInsertId();
    }

    /**
     * Update an existing company in the database.
     */
    public function update() {
        $stmt = $this->db->prepare("
            UPDATE companies
            SET name = :name, address = :address, phone = :phone, email = :email, updated_at = NOW()
            WHERE id = :id
        ");
        $stmt->execute([
            'id' => $this->id,
            'name' => $this->name,
            'address' => $this->address ?? null,
            'phone' => $this->phone ?? null,
            'email' => $this->email ?? null,
        ]);
    }

    /**
     * Soft delete a company by marking it as deleted.
     */
    public function delete() {
        $stmt = $this->db->prepare("UPDATE companies SET is_deleted = 1, updated_at = NOW() WHERE id = :id");
        $stmt->execute(['id' => $this->id]);
    }

    /**
     * Fetch projects associated with this company.
     */
    public function getProjects() {
        $stmt = $this->db->prepare("SELECT * FROM projects WHERE company_id = :company_id AND is_deleted = 0");
        $stmt->execute(['company_id' => $this->id]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Check if a company with the given email already exists (for validation).
     */
    public static function emailExists($email, $excludeId = null) {
        $query = "SELECT COUNT(*) FROM companies WHERE email = :email AND is_deleted = 0";
        $params = ['email' => $email];

        if ($excludeId) {
            $query .= " AND id != :id";
            $params['id'] = $excludeId;
        }

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Get recent projects for a user.
     *
     * @param int $userId The user ID.
     * @return array An array of project objects.
     */
    public function getRecentProjectsByUser($userId) {
        $stmt = $this->db->prepare("
            SELECT * FROM projects 
            WHERE user_id = :user_id 
            ORDER BY created_at DESC 
            LIMIT 5
        ");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }
}