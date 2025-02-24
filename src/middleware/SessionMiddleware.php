<?php
namespace App\Middleware;

use App\Core\Database;
use Exception;

class SessionMiddleware
{
    private static $db;

    private function __construct()
    {
        // Use the singleton instance of Database
        self::$db = Database::getInstance();
    }

    /**
     * Handle session management.
     */
    public static function handle()
    {
        // Start the session
        session_start();

        // Ensure the database instance is initialized
        if (self::$db === null) {
            self::$db = Database::getInstance();
        }

        // Get the session ID from the cookie
        $sessionId = session_id();

        // Fetch the session from the database
        $stmt = self::$db->executeQuery(
            "SELECT id, user_id, data, expires_at FROM sessions WHERE id = :id AND expires_at > NOW()",
            [':id' => $sessionId]
        );
        $session = $stmt->fetch(\PDO::FETCH_OBJ);

        if ($session) {
            // Deserialize session data
            $_SESSION = json_decode($session->data, true);

            // Extend session expiration time and update last_accessed_at
            $newExpiresAt = date('Y-m-d H:i:s', strtotime('+1 hour')); // Extend by 1 hour
            self::$db->executeQuery(
                "UPDATE sessions SET expires_at = :expires_at, last_accessed_at = NOW() WHERE id = :id",
                [
                    ':expires_at' => $newExpiresAt,
                    ':id' => $sessionId,
                ]
            );
        } else {
            // Clear the session if invalid or expired
            self::destroySession();
        }
    }

    /**
     * Save session data to the database.
     */
    public static function saveSession($userId = null, $data = [])
    {
        // Ensure the database instance is initialized
        if (self::$db === null) {
            self::$db = Database::getInstance();
        }

        $ipAddress = "::1";
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ipAddress = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ipAddress = $_SERVER['REMOTE_ADDR'];
        }
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        $sessionId = session_id();
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour')); // Default expiration: 1 hour
        
        // Serialize session data
        $serializedData = json_encode($data);
        
        $_SESSION['user'] = $data;
        $_SESSION['last_activity'] = time();

        // Insert or update the session in the database
        self::$db->executeQuery(
            "INSERT INTO sessions (id, user_id, data, expires_at, last_accessed_at)
             VALUES (:id, :user_id, :data, :expires_at, NOW())
             ON DUPLICATE KEY UPDATE 
                 user_id = VALUES(user_id), 
                 data = VALUES(data), 
                 ip_address = :ip_address,
                 user_agent = :user_agent,
                 expires_at = VALUES(expires_at),
                 last_accessed_at = NOW()",
            [
                ':id' => $sessionId,
                ':user_id' => $userId,
                ':data' => $serializedData,
                ':ip_address' => $ipAddress,
                ':user_agent' => $userAgent,
                ':expires_at' => $expiresAt,
            ]
        );
    }

    /**
     * Destroy the session.
     */
    public static function destroySession()
    {
        // Ensure the database instance is initialized
        if (self::$db === null) {
            self::$db = Database::getInstance();
        }

        $sessionId = session_id();

        // Delete the session from the csrf_tokens database
        self::$db->executeQuery(
            "DELETE FROM csrf_tokens WHERE session_id = :id",
            [':id' => $sessionId]
        );

        // Delete the session from the session database
        self::$db->executeQuery(
            "DELETE FROM sessions WHERE id = :id",
            [':id' => $sessionId]
        );

        // Destroy the PHP session
        session_destroy();
        $_SESSION = [];
    }

    /**
     * Regenerate the session ID.
     */
    public static function regenerateSessionId()
    {
        // Ensure the database instance is initialized
        if (self::$db === null) {
            self::$db = Database::getInstance();
        }

        $oldSessionId = session_id();
        session_regenerate_id(true);
        $newSessionId = session_id();

        // Fetch the old session data
        $stmt = self::$db->executeQuery(
            "SELECT id, user_id, data, expires_at FROM sessions WHERE id = :id AND expires_at > NOW()",
            [':id' => $oldSessionId]
        );
        $session = $stmt->fetch(\PDO::FETCH_OBJ);

        if ($session) {
            // Deserialize session data
            $_SESSION = json_decode($session->data, true);

            // Extend session expiration time and update last_accessed_at
            $newExpiresAt = date('Y-m-d H:i:s', strtotime('+1 hour')); // Extend by 1 hour
            self::$db->executeQuery(
                "UPDATE sessions SET expires_at = :expires_at, last_accessed_at = NOW() WHERE id = :id",
                [
                    ':expires_at' => $newExpiresAt,
                    ':id' => $newSessionId,
                ]
            );

            // Delete the old session
            self::$db->executeQuery(
                "DELETE FROM sessions WHERE id = :id",
                [':id' => $oldSessionId]
            );
        } else {
            // Clear the session if invalid or expired
            session_destroy();
            $_SESSION = [];
        }
    }
}