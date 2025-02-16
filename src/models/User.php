<?php
namespace App\Models;

use PDO;
use App\Core\Database;

class User {
    private PDO $db;

    public ?int $id = null;
    public ?string $company_id = null;
    public string $first_name;
    public string $last_name;
    public string $email;
    public string $password_hash;
    public ?int $role_id = null;
    public ?string $activation_token = null;
    public ?string $activation_token_expires_at = null;
    public bool $is_active = false;
    public ?string $reset_password_token = null;
    public ?string $reset_password_token_expires_at = null;
    public bool $is_deleted = false;
    public ?string $created_at = null;
    public ?string $updated_at = null;

    public function __construct() {
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
     * Fetch all users (paginated).
     */
    public function getAllPaginated($limit = 10, $page = 1) {
        $offset = ($page - 1) * $limit;
        $stmt = $this->db->prepare("SELECT * FROM users WHERE is_deleted = 0 LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Find a user by their ID.
     */
    public function find(int $id): ?self {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id AND is_deleted = 0 LIMIT 1");
        $stmt->execute(['id' => $id]);
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
    public function findByEmail(string $email): ?self {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email AND is_deleted = 0 LIMIT 1");
        $stmt->execute(['email' => $email]);
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
    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(\PDO::FETCH_OBJ);
    }

    /**
     * Save or update a user.
     */
    public function save(): bool {
        if ($this->id) {
            $stmt = $this->db->prepare("
                UPDATE users
                SET company_id = :company_id, role_id = :role_id, first_name = :first_name, last_name = :last_name, email = :email, 
                password_hash = :password_hash, activation_token = :activation_token, activation_token_expires_at = :activation_token_expires_at,
                is_active = :is_active, is_deleted = :is_deleted, updated_at = NOW()
                WHERE id = :id
            ");

            $stmt->execute([
                'id' => $this->id ?? null,
                'company_id' => $this->company_id ?? null,
                'role_id' => $this->role_id,
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'email' => $this->email,
                'password_hash' => $this->password_hash,
                'activation_token' => $this->activation_token,
                'activation_token_expires_at' => $this->activation_token_expires_at,
                'is_active' => $this->is_active ? 1 : 0,
                'is_deleted' => $this->is_deleted ? 1 : 0,
            ]);
        } else {
            $stmt = $this->db->prepare("
                INSERT INTO users (company_id, role_id, first_name, last_name, email, password_hash, activation_token,
                                  activation_token_expires_at, is_active, created_at, updated_at, is_deleted)
                VALUES (:company_id, :role_id, :first_name, :last_name, :email, :password_hash, :activation_token,
                        :activation_token_expires_at, :is_active, NOW(), NOW(), :is_deleted)
            ");

            $stmt->execute([
                'company_id' => $this->company_id ?? null,
                'role_id' => $this->role_id ?? 1,
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'email' => $this->email,
                'password_hash' => $this->password_hash,
                'activation_token' => $this->activation_token,
                'activation_token_expires_at' => $this->activation_token_expires_at,
                'is_active' => $this->is_active ? 1 : 0,
                'is_deleted' => $this->is_deleted ? 1 : 0,
            ]);
        }

        if (!$this->id) {
            $this->id = $this->db->lastInsertId();
        }

        return true;
    }

    /**
     * Soft delete a user.
     */
    public function delete(): bool {
        $stmt = $this->db->prepare("UPDATE users SET is_deleted = 1, updated_at = NOW() WHERE id = :id");
        return $stmt->execute(['id' => $this->id]);
    }

    /**
     * Get the roles and permissions for a user.
     *
     * @param int $userId The user ID.
     * @return array An array of roles and permissions.
     */
    public function getRolesAndPermissions($userId) {
        $stmt = $this->db->prepare("
            SELECT r.name AS role_name, p.name AS permission_name
            FROM users u
            JOIN roles r ON u.role_id = r.id
            LEFT JOIN role_permissions pr ON r.id = pr.role_id
            LEFT JOIN permissions p ON pr.permission_id = p.id
            WHERE u.id = :user_id
        ");
        $stmt->execute(['user_id' => $userId]);

        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
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
     * Find a user account by token.
     */
    public function findByActivationToken(string $token): ?self {
        $stmt = $this->db->prepare("
            SELECT * FROM users 
            WHERE activation_token = :activation_token 
            AND activation_token_expires_at > NOW() 
            AND is_deleted = 0
        ");
        $stmt->execute(['activation_token' => $token]);
        $userData = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$userData) {
            return null;
        }

        $this->hydrate($userData);
        return $this;
    }

    /**
     * Generate a password reset token.
     */
    public function generatePasswordResetToken(): string {
        $this->reset_password_token = bin2hex(random_bytes(16));
        $this->reset_password_token_expires_at = (new \DateTime())->modify('+1 hours')->format('Y-m-d H:i:s');

        $stmt = $this->db->prepare("
            UPDATE users
            SET reset_password_token = :token, reset_password_token_expires_at = :expires_at, updated_at = NOW()
            WHERE id = :id
        ");
        $stmt->execute([
            'id' => $this->id,
            'token' => $this->reset_password_token,
            'expires_at' => $this->reset_password_token_expires_at,
        ]);

        return $this->reset_password_token;
    }

    /**
     * Generate a account activation token.
     */
    public function generateActivationToken(): string {
        $this->reset_password_token = bin2hex(random_bytes(16));
        $this->reset_password_token_expires_at = (new \DateTime())->modify('+24 hours')->format('Y-m-d H:i:s');

        $stmt = $this->db->prepare("
            UPDATE users
            SET activation_token = :token, activation_token_expires_at = :expires_at, updated_at = NOW()
            WHERE id = :id
        ");
        $stmt->execute([
            'id' => $this->id,
            'token' => $this->activation_token,
            'expires_at' => $this->activation_token_expires_at,
        ]);

        return $this->activation_token;
    }

    /**
     * Clear the password reset token after use.
     */
    public function clearPasswordResetToken(): bool {
        $stmt = $this->db->prepare("
            UPDATE users
            SET reset_password_token = NULL, updated_at = NOW()
            WHERE id = :id
        ");
        return $stmt->execute(['id' => $this->id]);
    }

    /**
     * Clear the activation token after use.
     */
    public function clearActivationToken(): bool {
        $stmt = $this->db->prepare("
            UPDATE users
            SET activation_token = NULL, updated_at = NOW()
            WHERE id = :id
        ");
        return $stmt->execute(['id' => $this->id]);
    }

    /**
     * Check if an email exists in the database (for validation).
     */
    public static function emailExists(string $email, ?int $excludeId = null): bool {
        $query = "SELECT COUNT(*) FROM users WHERE email = :email AND is_deleted = 0";
        $params = ['email' => $email];

        if ($excludeId) {
            $query .= " AND id != :id";
            $params['id'] = $excludeId;
        }

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }
}
