<?php
namespace App\Middleware;

use Exception;

class CsrfMiddleware {
    private $db;

    public function __construct($db) {
        $this->db = $db; // Database connection passed from the application
    }

    /**
     * Generate a CSRF token and store it in the database.
     */
    public function generateToken() {
        // Generate a secure random token
        $token = bin2hex(random_bytes(32));

        // Set expiration time (e.g., 1 hour from now)
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Store the token in the csrf_tokens table
        $this->db->query(
            "INSERT INTO csrf_tokens (token, user_id, expires_at) VALUES (?, ?, ?)",
            [$token, $_SESSION['user_id'], $expiresAt]
        );

        // Store the token in the session for easy access
        $_SESSION['csrf_token'] = $token;
    }

    /**
     * Validate the CSRF token from the request.
     */
    public function validateToken($requestToken) {
        if (!isset($_SESSION['csrf_token'])) {
            throw new Exception('CSRF token is missing.');
        }

        // Fetch the stored token from the database
        $storedToken = $this->db->query(
            "SELECT * FROM csrf_tokens WHERE token = ? AND expires_at > NOW()",
            [$requestToken]
        )->fetch();

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
    public function handle() {
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
                header('Location: /error');
                exit;
            }
        }
    }

    /**
     * Clear expired CSRF tokens from the database.
     */
    public function cleanupExpiredTokens() {
        $this->db->query("DELETE FROM csrf_tokens WHERE expires_at < NOW()");
    }
}