<?php
namespace App\Models;

use PDO;

class User {
    private $db;

    public function __construct() {
        // Initialize the database connection
        $this->db = \App\Core\Database::getInstance();
    }

    /**
     * Find a user by their ID.
     */
    public function find($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id AND is_deleted = 0");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    /**
     * Find a user by their email address.
     */
    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email AND is_deleted = 0");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    /**
     * Find a user by their activation token.
     */
    public function findByActivationToken($token) {
        $stmt = $this->db->prepare("
            SELECT * FROM users 
            WHERE activation_token = :token 
              AND activation_token_expires_at > NOW() 
              AND is_active = 0 
              AND is_deleted = 0
        ");
        $stmt->execute(['token' => $token]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    /**
     * Find a user by their password reset token.
     */
    public function findByResetToken($token) {
        $stmt = $this->db->prepare("
            SELECT * FROM users 
            WHERE reset_password_token = :token 
              AND reset_password_token_expires_at > NOW() 
              AND is_deleted = 0
        ");
        $stmt->execute(['token' => $token]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    /**
     * Save a new user to the database.
     */
    public function save() {
        $stmt = $this->db->prepare("
            INSERT INTO users (
                first_name, last_name, email, password_hash, role_id, activation_token, 
                activation_token_expires_at, is_active, created_at, updated_at
            ) VALUES (
                :first_name, :last_name, :email, :password_hash, :role_id, :activation_token, 
                :activation_token_expires_at, :is_active, NOW(), NOW()
            )
        ");
        $stmt->execute([
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'password_hash' => $this->password_hash,
            'role_id' => $this->role_id ?? null,
            'activation_token' => $this->activation_token,
            'activation_token_expires_at' => $this->activation_token_expires_at,
            'is_active' => $this->is_active ? 1 : 0,
        ]);
        $this->id = $this->db->lastInsertId();
    }

    /**
     * Update an existing user in the database.
     */
    public function update() {
        $stmt = $this->db->prepare("
            UPDATE users
            SET first_name = :first_name, last_name = :last_name, email = :email, role_id = :role_id, updated_at = NOW()
            WHERE id = :id
        ");
        $stmt->execute([
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'role_id' => $this->role_id ?? null,
        ]);
    }

    /**
     * Soft delete a user by marking them as deleted.
     */
    public function delete() {
        $stmt = $this->db->prepare("UPDATE users SET is_deleted = 1, updated_at = NOW() WHERE id = :id");
        $stmt->execute(['id' => $this->id]);
    }

    /**
     * Activate a user account.
     */
    public function activate() {
        $stmt = $this->db->prepare("
            UPDATE users 
            SET is_active = 1, activation_token = NULL, activation_token_expires_at = NULL, updated_at = NOW() 
            WHERE id = :id
        ");
        $stmt->execute(['id' => $this->id]);
    }

    /**
     * Generate and save a password reset token for the user.
     */
    public function generatePasswordResetToken() {
        $token = bin2hex(random_bytes(16));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $stmt = $this->db->prepare("
            UPDATE users
            SET reset_password_token = :token, reset_password_token_expires_at = :expires_at, updated_at = NOW()
            WHERE id = :id
        ");
        $stmt->execute([
            'id' => $this->id,
            'token' => $token,
            'expires_at' => $expiresAt,
        ]);

        return $token;
    }

    /**
     * Clear the password reset token after it has been used.
     */
    public function clearPasswordResetToken() {
        $stmt = $this->db->prepare("
            UPDATE users
            SET reset_password_token = NULL, reset_password_token_expires_at = NULL, updated_at = NOW()
            WHERE id = :id
        ");
        $stmt->execute(['id' => $this->id]);
    }

    /**
     * Check if a user with the given email already exists (for validation).
     */
    public static function emailExists($email, $excludeId = null) {
        $db = \App\Core\Database::getInstance();
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