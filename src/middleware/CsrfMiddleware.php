<?php
namespace App\Middleware;

use App\Core\Database;
use Exception;

class CsrfMiddleware
{
    private Database $db;
    private const TOKEN_LENGTH = 32;
    private const TOKEN_EXPIRY = '1 hour';
    private const CLEANUP_PROBABILITY = 0.01; // 1% chance to run cleanup

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Generate and store a new CSRF token
     * 
     * @return string Generated token
     * @throws Exception If token generation fails
     */
    public function generateToken(): string
    {
        try {
            $token = bin2hex(random_bytes(self::TOKEN_LENGTH));
            $expiresAt = date('Y-m-d H:i:s', strtotime('+' . self::TOKEN_EXPIRY));

            // Get user ID from session if available
            $userId = $_SESSION['user']['id'] ?? null;
                         
            $success = $this->db->executeInsertUpdate(
                "INSERT INTO csrf_tokens (token, session_id, user_id, expires_at) 
                    VALUES (:token, :session_id, :user_id, :expires_at)", 
                [
                    ':token' => $token,
                    ':session_id' => session_id(),
                    ':user_id' => $userId,
                    ':expires_at' => $expiresAt
                ]        
            );
            // set token session value
            $_SESSION['csrf_token'] = $token;
            
            return $token;
        } catch (Exception $e) {
            error_log("CSRF token generation failed: " . $e->getMessage());
            throw new Exception('Failed to generate security token');
        }
    }

    /**
     * Validate a CSRF token
     * 
     * @param string $requestToken Token to validate
     * @return bool True if token is valid
     * @throws Exception If validation fails
     */
    public function validateToken(string $requestToken): bool
    {
        if (empty($requestToken) || !isset($_SESSION['csrf_token'])) {
            throw new Exception('CSRF token is missing');
        }

        if (!$this->isValidTokenFormat($requestToken)) {
            throw new Exception('Invalid token format');
        }

        $stmt = $this->db->executeQuery(
            "SELECT token, session_id FROM csrf_tokens 
             WHERE token = :token AND expires_at > NOW() 
             AND session_id = :session_id",
            [
                ':token' => $requestToken,
                ':session_id' => session_id()
            ]
        );
        $storedToken = $stmt->fetch();
        
        if(empty($storedToken)) {
            $this->cleanupExpiredTokens();
            throw new Exception('Missing or expired CSRF token');
        }

        if (!hash_equals($_SESSION['csrf_token'], $requestToken)) {
            throw new Exception('Invalid CSRF token');
        }

        return true;
    }

    /**
     * Handle CSRF protection for requests
     * 
     * @throws Exception If CSRF validation fails
     */
    public function handleToken(): void
    {
        // Random cleanup of expired tokens
        if (mt_rand() / mt_getrandmax() < self::CLEANUP_PROBABILITY) {
            $this->cleanupExpiredTokens();
        }

        // Generate token if it doesn't exist
        if (!isset($_SESSION['csrf_token'])) {
            $this->generateToken();
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validatePostRequest();
        }
    }

    /**
     * Remove expired tokens from the database
     */
    public function cleanupExpiredTokens(): void
    {
        try {
            unset($_SESSION['csrf_token']);
            $this->db->executeQuery(
                "DELETE FROM csrf_tokens WHERE expires_at < NOW()"
            );
        } catch (Exception $e) {
            error_log("Failed to cleanup expired CSRF tokens: " . $e->getMessage());
        }
    }

    /**
     * Validate POST request CSRF token
     * 
     * @throws Exception If validation fails
     */
    private function validatePostRequest(): void
    {
        try {
            if (!isset($_POST['csrf_token'])) {
                throw new Exception('CSRF token is missing from the request');
            }
             
            $this->validateToken($_POST['csrf_token']);
        } catch (Exception $e) {
            error_log("CSRF validation failed: " . $e->getMessage());
            $_SESSION['error'] = 'Security validation failed. Please try again.';
            
            header('Location: ' . $this->getSafeRedirectUrl());
            exit;
        }
    }

    /**
     * Validate token format
     */
    private function isValidTokenFormat(string $token): bool
    {
        return (bool) preg_match('/^[a-f0-9]{' . (self::TOKEN_LENGTH * 2) . '}$/', $token);
    }

    /**
     * Get safe redirect URL after validation failure
     */
    private function getSafeRedirectUrl(): string
    {
        $safeUrls = ['/login', '/dashboard', '/'];
        $referrer = $_SERVER['HTTP_REFERER'] ?? '';
        
        // Check if referrer is a local URL and is safe
        if (!empty($referrer)) {
            $parsedUrl = parse_url($referrer);
            if (isset($parsedUrl['path']) && in_array($parsedUrl['path'], $safeUrls)) {
                return $parsedUrl['path'];
            }
        }
        
        return '/login';
    }
}