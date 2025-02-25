<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;
use RuntimeException;
use InvalidArgumentException;
use DateTime;

/**
 * User Model
 * 
 * Handles all user-related database operations
 */
class User extends BaseModel
{
    protected string $table = 'users';
    
    /**
     * User properties
     */
    public ?int $id = null;
    public ?int $company_id = null;
    public int $role_id;
    public string $first_name;
    public string $last_name;
    public string $email;
    public ?string $phone = null;
    public string $password_hash;
    public bool $is_active = false;
    public ?string $activation_token = null;
    public ?string $activation_token_expires_at = null;
    public ?string $reset_password_token = null;
    public ?string $reset_password_token_expires_at = null;
    public ?string $created_at = null;
    public ?string $updated_at = null;
    public bool $is_deleted = false;

    /**
     * Find user by email
     * 
     * @param string $email
     * @return object|null
     */
    public function findByEmail(string $email): ?object
    {
        $sql = "SELECT u.*, 
                       c.name as company_name,
                       r.name as role_name
                FROM users u
                LEFT JOIN companies c ON u.company_id = c.id
                LEFT JOIN roles r ON u.role_id = r.id
                WHERE u.email = :email 
                AND u.is_deleted = 0";

        $stmt = $this->db->executeQuery($sql, [':email' => $email]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    /**
     * Get user roles and permissions
     * 
     * @param int $userId
     * @return array
     */
    public function getRolesAndPermissions(int $userId): array
    {
        $sql = "SELECT r.name AS role_name, 
                       p.name AS permission_name
                FROM users u
                JOIN roles r ON u.role_id = r.id
                LEFT JOIN role_permissions rp ON r.id = rp.role_id
                LEFT JOIN permissions p ON rp.permission_id = p.id
                WHERE u.id = :user_id
                AND u.is_deleted = 0";

        $stmt = $this->db->executeQuery($sql, [':user_id' => $userId]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $this->organizeRolesAndPermissions($results);
    }

    /**
     * Find user with detailed information
     * 
     * @param int $id
     * @return object|null
     */
    public function findWithDetails(int $id): ?object
    {
        $sql = "SELECT 
                    u.*,
                    c.name AS company_name,
                    r.name AS role_name
                FROM users u
                LEFT JOIN companies c ON u.company_id = c.id
                LEFT JOIN roles r ON u.role_id = r.id
                WHERE u.id = :user_id 
                AND u.is_deleted = 0";

        $stmt = $this->db->executeQuery($sql, [':user_id' => $id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    /**
     * Find user by activation token
     * 
     * @param string $token
     * @return object|null
     */
    public function findByActivationToken(string $token): ?object
    {
        $sql = "SELECT * FROM users 
                WHERE activation_token = :token 
                AND activation_token_expires_at > NOW() 
                AND is_deleted = 0";

        $stmt = $this->db->executeQuery($sql, [':token' => $token]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    /**
     * Find user by reset password token
     * 
     * @param string $token
     * @return object|null
     */
    public function findByResetToken(string $token): ?object
    {
        $sql = "SELECT * FROM users 
                WHERE reset_password_token = :token 
                AND reset_password_token_expires_at > NOW() 
                AND is_deleted = 0";

        $stmt = $this->db->executeQuery($sql, [':token' => $token]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    /**
     * Generate password reset token
     * 
     * @param int $userId
     * @return string
     * @throws RuntimeException
     */
    public function generatePasswordResetToken(int $userId): string
    {
        try {
            $token = bin2hex(random_bytes(16));
            $expiresAt = (new DateTime())->modify('+1 hour')->format('Y-m-d H:i:s');

            $sql = "UPDATE users 
                    SET reset_password_token = :token,
                        reset_password_token_expires_at = :expires_at,
                        updated_at = NOW()
                    WHERE id = :id";

            $this->db->executeInsertUpdate($sql, [
                ':id' => $userId,
                ':token' => $token,
                ':expires_at' => $expiresAt
            ]);

            return $token;

        } catch (\Exception $e) {
            throw new RuntimeException('Failed to generate reset token: ' . $e->getMessage());
        }
    }

    /**
     * Generate activation token
     * 
     * @param int $userId
     * @return string
     * @throws RuntimeException
     */
    public function generateActivationToken(int $userId): string
    {
        try {
            $token = bin2hex(random_bytes(16));
            $expiresAt = (new DateTime())->modify('+24 hours')->format('Y-m-d H:i:s');

            $sql = "UPDATE users 
                    SET activation_token = :token,
                        activation_token_expires_at = :expires_at,
                        updated_at = NOW()
                    WHERE id = :id";

            $this->db->executeInsertUpdate($sql, [
                ':id' => $userId,
                ':token' => $token,
                ':expires_at' => $expiresAt
            ]);

            return $token;

        } catch (\Exception $e) {
            throw new RuntimeException('Failed to generate activation token: ' . $e->getMessage());
        }
    }

    /**
     * Clear password reset token
     * 
     * @param int $userId
     * @return bool
     */
    public function clearPasswordResetToken(int $userId): bool
    {
        $sql = "UPDATE users 
                SET reset_password_token = NULL,
                    reset_password_token_expires_at = NULL,
                    updated_at = NOW()
                WHERE id = :id";

        return $this->db->executeInsertUpdate($sql, [':id' => $userId]);
    }

    /**
     * Clear activation token
     * 
     * @param int $userId
     * @return bool
     */
    public function clearActivationToken(int $userId): bool
    {
        $sql = "UPDATE users 
                SET activation_token = NULL,
                    activation_token_expires_at = NULL,
                    updated_at = NOW()
                WHERE id = :id";

        return $this->db->executeInsertUpdate($sql, [':id' => $userId]);
    }

    /**
     * Organize roles and permissions array
     * 
     * @param array $results
     * @return array
     */
    private function organizeRolesAndPermissions(array $results): array
    {
        $roles = [];
        $permissions = [];

        foreach ($results as $row) {
            if (!in_array($row['role_name'], $roles)) {
                $roles[] = $row['role_name'];
            }
            if (!empty($row['permission_name']) && !in_array($row['permission_name'], $permissions)) {
                $permissions[] = $row['permission_name'];
            }
        }

        return [
            'roles' => $roles,
            'permissions' => $permissions
        ];
    }

    /**
     * Validate user data before save
     * 
     * @param array $data
     * @throws InvalidArgumentException
     */
    protected function beforeSave(array $data): void
    {
        parent::validate($data, $this->id);
        
        if (empty($data['first_name'])) {
            throw new InvalidArgumentException('First name is required');
        }

        if (empty($data['last_name'])) {
            throw new InvalidArgumentException('Last name is required');
        }

        if (empty($data['email'])) {
            throw new InvalidArgumentException('Email is required');
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email format');
        }

        if (empty($data['role_id'])) {
            throw new InvalidArgumentException('Role ID is required');
        }

        // Check for unique email
        $sql = "SELECT COUNT(*) FROM users 
                WHERE email = :email 
                AND id != :id 
                AND is_deleted = 0";

        $stmt = $this->db->executeQuery($sql, [
            ':email' => $data['email'],
            ':id' => $data['id'] ?? 0
        ]);

        if ($stmt->fetchColumn() > 0) {
            throw new InvalidArgumentException('Email address is already in use');
        }

        if (!empty($data['phone']) && !preg_match('/^[+]?[0-9()-\s]{10,}$/', $data['phone'])) {
            throw new InvalidArgumentException('Invalid phone number format');
        }
    }
}