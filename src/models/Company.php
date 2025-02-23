<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Company
{
    private Database $db;

    public ?int $id = null;
    public string $name;
    public ?string $address = null;
    public ?string $phone = null;
    public string $email;
    public ?string $website = null;
    public bool $is_deleted = false;
    public ?string $created_at = null;
    public ?string $updated_at = null;

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
     * Find a company by its ID.
     */
    public function find(int $id): ?object
    {
        $stmt = $this->db->executeQuery(
            "SELECT * FROM companies WHERE id = :id AND is_deleted = 0",
            [':id' => $id]
        );
        $companyData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$companyData) {
            return null;
        }

        $this->hydrate($companyData);
        return $this;
    }

    /**
     * Fetch all companies (paginated).
     */
    public function getAllPaginated(int $limit = 10, int $page = 1): array
    {
        $offset = ($page - 1) * $limit;
        $stmt = $this->db->executeQuery(
            "SELECT * FROM companies WHERE is_deleted = 0 LIMIT :limit OFFSET :offset",
            [
                ':limit' => $limit,
                ':offset' => $offset,
            ]
        );
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Fetch all companies without pagination.
     */
    public function getAll(): array
    {
        $stmt = $this->db->executeQuery(
            "SELECT * FROM companies WHERE is_deleted = 0"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get the total of all companies
     */
    public function countAll(): int
    {
        $stmt = $this->db->executeQuery(
            "SELECT COUNT(*) as total FROM companies WHERE is_deleted = 0"
        );
        return (int)$stmt->fetchColumn();
    }

    /**
     * Save a new company to the database.
     */
    public function save(): bool
    {
        $stmt = $this->db->executeQuery(
            "INSERT INTO companies (name, address, phone, email, website, created_at, updated_at)
             VALUES (:name, :address, :phone, :email, :website, NOW(), NOW())",
            [
                ':name' => $this->name,
                ':address' => $this->address,
                ':phone' => $this->phone,
                ':email' => $this->email,
                ':website' => $this->website,
            ]
        );

        $this->id = $this->db->lastInsertId();
        return true;
    }

    /**
     * Update an existing company in the database.
     */
    public function update(): bool
    {
        if (!$this->id) {
            throw new Exception("Company ID is not set.");
        }

        $stmt = $this->db->executeQuery(
            "UPDATE companies
             SET name = :name, address = :address, phone = :phone, email = :email, website = :website, updated_at = NOW()
             WHERE id = :id",
            [
                ':id' => $this->id,
                ':name' => $this->name,
                ':address' => $this->address,
                ':phone' => $this->phone,
                ':email' => $this->email,
                ':website' => $this->website,
            ]
        );

        return true;
    }

    /**
     * Soft delete a company by marking it as deleted.
     */
    public function delete(): bool
    {
        if (!$this->id) {
            throw new Exception("Company ID is not set.");
        }

        $stmt = $this->db->executeQuery(
            "UPDATE companies SET is_deleted = 1, updated_at = NOW() WHERE id = :id",
            [':id' => $this->id]
        );

        return true;
    }

    /**
     * Fetch projects associated with this company.
     */
    public function getProjects(): array
    {
        if (!$this->id) {
            throw new Exception("Company ID is not set.");
        }

        $stmt = $this->db->executeQuery(
            "SELECT * FROM projects WHERE company_id = :company_id AND is_deleted = 0",
            [':company_id' => $this->id]
        );

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Check if a company with the given email already exists (for validation).
     */
    public static function emailExists(string $email, ?int $excludeId = null): bool
    {
        $query = "SELECT COUNT(*) FROM companies WHERE email = :email AND is_deleted = 0";
        $params = [':email' => $email];

        if ($excludeId) {
            $query .= " AND id != :id";
            $params[':id'] = $excludeId;
        }

        $stmt = $this->db->executeQuery($query, $params);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Get recent projects for a user.
     *
     * @param int $userId The user ID.
     * @return array An array of project objects.
     */
    public function getRecentProjectsByUser(int $userId): array
    {
        $stmt = $this->db->executeQuery(
            "SELECT * FROM projects 
             WHERE user_id = :user_id 
             ORDER BY created_at DESC 
             LIMIT 5",
            [':user_id' => $userId]
        );

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}