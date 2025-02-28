<?php

declare(strict_types=1);

namespace App\Models;

use PDO;
use RuntimeException;
use InvalidArgumentException;

/**
 * Company Model
 * 
 * Handles all company-related database operations
 */
class Company extends BaseModel
{
    protected string $table = 'companies';
    
    /**
     * Company properties
     */
    public ?int $id = null;
    public string $name;
    public ?int $user_id = null;
    public ?string $address = null;
    public ?string $phone = null;
    public string $email;
    public ?string $website = null;
    public bool $is_deleted = false;
    public ?string $created_at = null;
    public ?string $updated_at = null;

    /**
     * Get company users
     * 
     * @param int $companyId
     * @return array
     * @throws RuntimeException
     */
    public function getUsers(int $companyId): array
    {
        $sql = "SELECT * FROM users WHERE company_id = :company_id AND is_deleted = 0";
        $stmt = $this->db->executeQuery($sql, [':company_id' => $companyId]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Get company projects
     * 
     * @return array
     * @throws RuntimeException
     */
    public function getProjects(): array
    {
        if (!$this->id) {
            throw new RuntimeException("Company ID is not set");
        }

        $sql = "SELECT * FROM projects WHERE company_id = :company_id AND is_deleted = 0";
        $stmt = $this->db->executeQuery($sql, [':company_id' => $this->id]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Get all companies without pagination
     * 
     * @return array
     */
    public function getAllCompanies(): array
    {
        $sql = "SELECT * FROM companies WHERE is_deleted = 0";
        $stmt = $this->db->executeQuery($sql);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Get recent projects for a user
     * 
     * @param int $userId
     * @return array
     */
    public function getRecentProjectsByUser(int $userId): array
    {
        $sql = "SELECT p.* 
                FROM projects p 
                JOIN companies c ON p.company_id = c.id 
                WHERE c.user_id = :user_id AND c.is_deleted = 0 
                ORDER BY p.created_at DESC 
                LIMIT 5";

        $stmt = $this->db->executeQuery($sql, [':user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Validate company data before save
     * 
     * @param array $data
     * @throws InvalidArgumentException
     */
    protected function beforeSave(array $data): void
    {
        parent::validate($data, $this->id);
        
        if (empty($data['name'])) {
            throw new InvalidArgumentException('Company name is required');
        }

        if (empty($data['email'])) {
            throw new InvalidArgumentException('Company email is required');
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email format');
        }

        if (!empty($data['website']) && !filter_var($data['website'], FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('Invalid website URL format');
        }

        if (!empty($data['phone']) && !preg_match('/^[+]?[0-9()-\s]{10,}$/', $data['phone'])) {
            throw new InvalidArgumentException('Invalid phone number format');
        }
    }
}