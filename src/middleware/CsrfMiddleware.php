<?php
namespace App\Middleware;

use App\Core\Database;
use Exception;

class CsrfMiddleware
{
    private $db;

    public function __construct()
    {
        // Use the singleton instance of Database
        $this->db = Database::getInstance();
    }

    /**
     * Generate a CSRF token and store it in the database.
     */
    public function generateToken($user_id = null)
    {
        // Generate a secure random token
        $token = bin2hex(random_bytes(32));
        // Set expiration time (e.g., 1 hour from now)
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Store the token in the csrf_tokens table
        $this->db->executeQuery(
            "INSERT INTO csrf_tokens (token, session_id, expires_at) VALUES (:token, :session_id, :expires_at)",
            [
                ':token' => $token,
                ':session_id' => session_id(),
                ':expires_at' => $expiresAt
            ]
        );

        // Store the token in the session for easy access
        $_SESSION['csrf_token'] = $token;
    }

    /**
     * Validate the CSRF token from the request.
     */
    public function validateToken($requestToken)
    {
        if (!isset($_SESSION['csrf_token'])) {
            throw new Exception('CSRF token is missing.');
        }

        // Fetch the stored token from the database
        $stmt = $this->db->executeQuery(
            "SELECT * FROM csrf_tokens WHERE token = :token AND expires_at > NOW()",
            [':token' => $requestToken]
        );

        $storedToken = $stmt->fetch();

        if (!$storedToken) {
            throw new Exception('Invalid or expired CSRF token.');
        }

        // Compare the request token with the session token
        if (!hash_equals($_SESSION['csrf_token'], $requestToken)) {
            throw new Exception('CSRF token validation failed.');
        }
    }

    /**
     * Middleware to handle CSRF protection for POST requests.
     */
    public function handleToken()
    {
        // Generate a CSRF token if it doesn't already exist
        if (!isset($_SESSION['csrf_token'])) {
            $this->generateToken();
        }

        // Check if the request method is POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Ensure the CSRF token is included in the request
            if (!isset($_POST['csrf_token'])) {
                throw new Exception('CSRF token is missing from the request.');
            }

            // Validate the CSRF token
            try {
                $this->validateToken($_POST['csrf_token']);
            } catch (Exception $e) {
                // Log the error and redirect to an error page
                error_log($e->getMessage());
                $_SESSION['error'] = 'CSRF validation failed. Please try again.';
                header('Location: /login');
                exit;
            }
        }
    }

    /**
     * Clear expired CSRF tokens from the database.
     */
    public function cleanupExpiredTokens()
    {
        $this->db->executeQuery("DELETE FROM csrf_tokens WHERE expires_at < NOW()");
    }
}