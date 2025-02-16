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
     * Activate a user account.
     */
    public function activate(): bool {
        $stmt = $this->db->prepare("
            UPDATE users 
            SET is_active = 1, activation_token = NULL, activation_token_expires_at = NULL, updated_at = NOW() 
            WHERE id = :id
        ");
        return $stmt->execute(['id' => $this->id]);
    }

    /**
     * Generate a password reset token.
     */
    public function generatePasswordResetToken(): string {
        $this->reset_password_token = bin2hex(random_bytes(16));
        $this->reset_password_token_expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

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
     * Clear the password reset token after use.
     */
    public function clearPasswordResetToken(): bool {
        $stmt = $this->db->prepare("
            UPDATE users
            SET reset_password_token = NULL, reset_password_token_expires_at = NULL, updated_at = NOW()
            WHERE id = :id
        ");
        return $stmt->execute(['id' => $this->id]);
    }

    /**
     * Check if an email exists in the database (for validation).
     */
    public static function emailExists(string $email, ?int $excludeId = null): bool {
        $db = Database::getInstance();
        $query = "SELECT COUNT(*) FROM users WHERE email = :email AND is_deleted = 0";
        $params = ['email' => $email];

        if ($excludeId) {
            $query .= " AND id != :id";
            $params['id'] = $excludeId;
        }

        $stmt = $db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }
}
