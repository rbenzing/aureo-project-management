<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class User
{
    private Database $db;

    public ?int $id = null;
    public ?int $company_id = null;
    public int $role_id;
    public string $first_name;
    public string $last_name;
    public string $email;
    public ?string $phone = null;
    public string $password_hash;
    public bool $is_active;
    public ?string $activation_token = null;
    public ?string $activation_token_expires_at = null;
    public ?string $reset_password_token = null;
    public ?string $reset_password_token_expires_at = null;
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
     * Get the total of all companies
     */
    public function countAll(): int
    {
        $stmt = $this->db->executeQuery(
            "SELECT COUNT(*) as total FROM users WHERE is_deleted = 0"
        );
        return (int)$stmt->fetchColumn();
    }

    /**
     * Fetch all users (paginated).
     */
    public function getAllPaginated(int $limit = 10, int $page = 1): array
    {
        $offset = ($page - 1) * $limit;
        $stmt = $this->db->executeQuery(
            "SELECT * FROM users WHERE is_deleted = 0 LIMIT :limit OFFSET :offset",
            [
                ':limit' => $limit,
                ':offset' => $offset,
            ]
        );
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Find a user by their ID.
     */
    public function find(int $id): ?self
    {
        $stmt = $this->db->executeQuery(
            "SELECT * FROM users WHERE id = :id AND is_deleted = 0 LIMIT 1",
            [':id' => $id]
        );
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$userData) {
            return null;
        }

        $this->hydrate($userData);
        return $this;
    }

    /**
     * Find a user by their email address.
     */
    public function findByEmail(string $email): ?self
    {
        $stmt = $this->db->executeQuery(
            "SELECT * FROM users WHERE email = :email AND is_deleted = 0 LIMIT 1",
            [':email' => $email]
        );
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$userData) {
            return null;
        }

        $this->hydrate($userData);
        return $this;
    }

    /**
     * Find a user by ID.
     *
     * @param int $id The user ID.
     * @return object|null The user object or null if not found.
     */
    public function findById(int $id): ?object
    {
        $stmt = $this->db->executeQuery(
            "SELECT * FROM users WHERE id = :id LIMIT 1",
            [':id' => $id]
        );
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    /**
     * Save or update a user.
     */
    public function save(): bool
    {
        if ($this->id) {
            $stmt = $this->db->executeQuery(
                "UPDATE users
                 SET company_id = :company_id, role_id = :role_id, first_name = :first_name, last_name = :last_name, email = :email, phone = :phone,
                     password_hash = :password_hash, activation_token = :activation_token, activation_token_expires_at = :activation_token_expires_at,
                     is_active = :is_active, is_deleted = :is_deleted, updated_at = NOW()
                 WHERE id = :id",
                [
                    ':id' => $this->id,
                    ':company_id' => $this->company_id,
                    ':role_id' => $this->role_id,
                    ':first_name' => $this->first_name,
                    ':last_name' => $this->last_name,
                    ':email' => $this->email,
                    ':phone' => $this->phone,
                    ':password_hash' => $this->password_hash,
                    ':activation_token' => $this->activation_token,
                    ':activation_token_expires_at' => $this->activation_token_expires_at,
                    ':is_active' => $this->is_active ? 1 : 0,
                    ':is_deleted' => $this->is_deleted ? 1 : 0,
                ]
            );
        } else {
            $stmt = $this->db->executeQuery(
                "INSERT INTO users (company_id, role_id, first_name, last_name, email, phone, password_hash, activation_token,
                                  activation_token_expires_at, is_active, created_at, updated_at, is_deleted)
                 VALUES (:company_id, :role_id, :first_name, :last_name, :email, :phone, :password_hash, :activation_token,
                         :activation_token_expires_at, :is_active, NOW(), NOW(), :is_deleted)",
                [
                    ':company_id' => $this->company_id,
                    ':role_id' => $this->role_id,
                    ':first_name' => $this->first_name,
                    ':last_name' => $this->last_name,
                    ':email' => $this->email,
                    ':phone' => $this->phone,
                    ':password_hash' => $this->password_hash,
                    ':activation_token' => $this->activation_token,
                    ':activation_token_expires_at' => $this->activation_token_expires_at,
                    ':is_active' => $this->is_active ? 1 : 0,
                    ':is_deleted' => $this->is_deleted ? 1 : 0,
                ]
            );

            if (!$this->id) {
                $this->id = $this->db->lastInsertId();
            }
        }

        return true;
    }

    /**
     * Soft delete a user.
     */
    public function delete(): bool
    {
        if (!$this->id) {
            throw new Exception("User ID is not set.");
        }

        $stmt = $this->db->executeQuery(
            "UPDATE users SET is_deleted = 1, updated_at = NOW() WHERE id = :id",
            [':id' => $this->id]
        );

        return true;
    }

    /**
     * Get the roles and permissions for a user.
     *
     * @param int $userId The user ID.
     * @return array An array of roles and permissions.
     */
    public function getRolesAndPermissions(int $userId): array
    {
        $stmt = $this->db->executeQuery(
            "SELECT r.name AS role_name, p.name AS permission_name
             FROM users u
             JOIN roles r ON u.role_id = r.id
             LEFT JOIN role_permissions pr ON r.id = pr.role_id
             LEFT JOIN permissions p ON pr.permission_id = p.id
             WHERE u.id = :user_id",
            [':user_id' => $userId]
        );

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Organize roles and permissions
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
            'permissions' => $permissions,
        ];
    }

    /**
     * Find a user account by activation token.
     */
    public function findByActivationToken(string $token): ?self
    {
        $stmt = $this->db->executeQuery(
            "SELECT * FROM users 
             WHERE activation_token = :activation_token 
             AND activation_token_expires_at > NOW() 
             AND is_deleted = 0",
            [':activation_token' => $token]
        );
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$userData) {
            return null;
        }

        $this->hydrate($userData);
        return $this;
    }

    /**
     * Generate a password reset token.
     */
    public function generatePasswordResetToken(): string
    {
        $this->reset_password_token = bin2hex(random_bytes(16));
        $this->reset_password_token_expires_at = (new \DateTime())->modify('+1 hour')->format('Y-m-d H:i:s');

        $stmt = $this->db->executeQuery(
            "UPDATE users
             SET reset_password_token = :token, reset_password_token_expires_at = :expires_at, updated_at = NOW()
             WHERE id = :id",
            [
                ':id' => $this->id,
                ':token' => $this->reset_password_token,
                ':expires_at' => $this->reset_password_token_expires_at,
            ]
        );

        return $this->reset_password_token;
    }

    /**
     * Generate an account activation token.
     */
    public function generateActivationToken(): string
    {
        $this->activation_token = bin2hex(random_bytes(16));
        $this->activation_token_expires_at = (new \DateTime())->modify('+24 hours')->format('Y-m-d H:i:s');

        $stmt = $this->db->executeQuery(
            "UPDATE users
             SET activation_token = :token, activation_token_expires_at = :expires_at, updated_at = NOW()
             WHERE id = :id",
            [
                ':id' => $this->id,
                ':token' => $this->activation_token,
                ':expires_at' => $this->activation_token_expires_at,
            ]
        );

        return $this->activation_token;
    }

    /**
     * Clear the password reset token after use.
     */
    public function clearPasswordResetToken(): bool
    {
        $stmt = $this->db->executeQuery(
            "UPDATE users
             SET reset_password_token = NULL, updated_at = NOW()
             WHERE id = :id",
            [':id' => $this->id]
        );

        return true;
    }

    /**
     * Clear the activation token after use.
     */
    public function clearActivationToken(): bool
    {
        $stmt = $this->db->executeQuery(
            "UPDATE users
             SET activation_token = NULL, updated_at = NOW()
             WHERE id = :id",
            [':id' => $this->id]
        );

        return true;
    }

    /**
     * Check if an email exists in the database (for validation).
     */
    public static function emailExists(string $email, ?int $excludeId = null): bool
    {
        $query = "SELECT COUNT(*) FROM users WHERE email = :email AND is_deleted = 0";
        $params = [':email' => $email];

        if ($excludeId) {
            $query .= " AND id != :id";
            $params[':id'] = $excludeId;
        }

        $stmt = Database::getInstance()->executeQuery($query, $params);
        return $stmt->fetchColumn() > 0;
    }
}