<?php
namespace App\Middleware;

use App\Core\Database;

class SessionMiddleware {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Handle session management.
     */
    public function handle() {
        // Start the session
        session_start();

        // Get the session ID from the cookie
        $sessionId = session_id();

        // Fetch the session from the database
        $stmt = $this->db->prepare("
            SELECT id, user_id, data, expires_at 
            FROM sessions 
            WHERE id = :id AND expires_at > NOW()
        ");
        $stmt->execute(['id' => $sessionId]);
        $session = $stmt->fetch(\PDO::FETCH_OBJ);

        if ($session) {
            // Deserialize session data
            $_SESSION = json_decode($session->data, true);

            // Extend session expiration time and update last_accessed_at
            $newExpiresAt = date('Y-m-d H:i:s', strtotime('+1 hour')); // Extend by 1 hour
            $stmt = $this->db->prepare("
                UPDATE sessions 
                SET expires_at = :expires_at, last_accessed_at = NOW()
                WHERE id = :id
            ");
            $stmt->execute([
                'expires_at' => $newExpiresAt,
                'id' => $sessionId,
            ]);
        } else {
            // Clear the session if invalid or expired
            session_destroy();
            $_SESSION = [];
        }
    }

    /**
     * Save session data to the database.
     */
    public static function saveSession($userId = null, $data = []) {
        $sessionId = session_id();
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour')); // Default expiration: 1 hour

        // Serialize session data
        $serializedData = json_encode($data);

        // Insert or update the session in the database
        $stmt = Database::getInstance()->prepare("
            INSERT INTO sessions (id, user_id, data, expires_at, last_accessed_at)
            VALUES (:id, :user_id, :data, :expires_at, NOW())
            ON DUPLICATE KEY UPDATE 
                user_id = VALUES(user_id), 
                data = VALUES(data), 
                expires_at = VALUES(expires_at),
                last_accessed_at = NOW()
        ");
        $stmt->execute([
            'id' => $sessionId,
            'user_id' => $userId,
            'data' => $serializedData,
            'expires_at' => $expiresAt,
        ]);
    }

    /**
     * Destroy the session.
     */
    public static function destroySession() {
        $sessionId = session_id();

        // Delete the session from the database
        $stmt = Database::getInstance()->prepare("
            DELETE FROM sessions 
            WHERE id = :id
        ");
        $stmt->execute(['id' => $sessionId]);

        // Destroy the PHP session
        session_destroy();
        $_SESSION = [];
    }
}