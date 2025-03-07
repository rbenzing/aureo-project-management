<?php
// file: Middleware/ActivityMiddleware.php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Database;
use Exception;

class ActivityMiddleware
{
    private $db;
    private array $ignoredPaths = [
        '/assets/',
        '/favicon.ico',
        '/ping',
        '/health'
    ];

    private array $sensitiveFields = [
        'password',
        'password_confirmation',
        'current_password',
        'token',
        'csrf_token',
        'credit_card',
        'card_number',
        'cvv',
        'secret'
    ];

    /**
     * Initialize the middleware with database connection
     */
    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Main middleware handler
     * 
     * @return void
     */
    public function handle(): void
    {
        try {
            if (!$this->shouldIgnorePath($_SERVER['REQUEST_URI'])) {
                $this->logActivity();
            }
        } catch (Exception $e) {
            // Log error but don't interrupt the application flow
            error_log("Activity logging failed: " . $e->getMessage());
        }
    }

    /**
     * Log the current activity
     * 
     * @return void 
     */
    private function logActivity(): void
    {
        $userId = $_SESSION['user']['id'] ?? null;
        $sessionId = session_id();
        
        $requestData = $this->collectRequestData();
        $eventType = $this->determineEventType($requestData['path'], $requestData['method']);

        $this->db->executeQuery(
            "INSERT INTO activity_logs (
                user_id, 
                session_id,
                event_type,
                method,
                path,
                query_string,
                referer,
                user_agent,
                ip_address,
                request_data,
                created_at
            ) VALUES (
                :user_id,
                :session_id,
                :event_type,
                :method,
                :path,
                :query_string,
                :referer,
                :user_agent,
                :ip_address,
                :request_data,
                NOW()
            )",
            [
                ':user_id' => $userId,
                ':session_id' => $sessionId,
                ':event_type' => $eventType,
                ':method' => $requestData['method'],
                ':path' => $requestData['path'],
                ':query_string' => $requestData['query'],
                ':referer' => $requestData['referer'],
                ':user_agent' => $requestData['user_agent'],
                ':ip_address' => $requestData['ip_address'],
                ':request_data' => json_encode($requestData['post_data'])
            ]
        );
    }

    /**
     * Collect all relevant request data
     * 
     * @return array
     */
    private function collectRequestData(): array
    {
        return [
            'method' => $_SERVER['REQUEST_METHOD'],
            'path' => $_SERVER['REQUEST_URI'],
            'query' => $_SERVER['QUERY_STRING'] ?? null,
            'referer' => $_SERVER['HTTP_REFERER'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'ip_address' => $this->getClientIp(),
            'post_data' => $this->sanitizePostData($_POST),
        ];
    }

    /**
     * Check if the path should be ignored
     * 
     * @param string $path
     * @return bool
     */
    private function shouldIgnorePath(string $path): bool
    {
        foreach ($this->ignoredPaths as $ignoredPath) {
            if (str_starts_with($path, $ignoredPath)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get the client's IP address
     * 
     * @return string
     */
    private function getClientIp(): string
    {
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '::1';
        
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ipAddress = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }

        return $ipAddress;
    }

    /**
     * Sanitize POST data for logging
     * 
     * @param array $postData
     * @return array
     */
    private function sanitizePostData(array $postData): array
    {
        $sanitized = [];
        foreach ($postData as $key => $value) {
            if (in_array(strtolower($key), $this->sensitiveFields, true)) {
                $sanitized[$key] = '[REDACTED]';
            } else if (is_array($value)) {
                $sanitized[$key] = $this->sanitizePostData($value);
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }

    /**
     * Determine the event type based on the request
     * 
     * @param string $path
     * @param string $method
     * @return string
     */
    private function determineEventType(string $path, string $method): string
    {
        // Extract the controller and action from the path
        $pathParts = explode('/', trim($path, '/'));
        $controller = $pathParts[0] ?? 'home';
        $action = $pathParts[1] ?? 'index';

        // Handle different HTTP methods
        return match ($method) {
            'GET' => $this->determineGetEventType($action),
            'POST' => $this->determinePostEventType($action),
            'PUT', 'PATCH' => 'update',
            'DELETE' => 'delete',
            default => strtolower($method) . '_request'
        };
    }

    /**
     * Determine event type for GET requests
     * 
     * @param string $action
     * @return string
     */
    private function determineGetEventType(string $action): string
    {
        return match ($action) {
            'index' => 'list_view',
            'show', 'view', 'details' => 'detail_view',
            'create', 'new', 'edit' => 'form_view',
            'login' => 'login_form_view',
            'logout' => 'logout',
            default => 'page_view'
        };
    }

    /**
     * Determine event type for POST requests
     * 
     * @param string $action
     * @return string
     */
    private function determinePostEventType(string $action): string
    {
        return match ($action) {
            'create', 'store' => 'create',
            'update', 'edit' => 'update',
            'delete', 'destroy' => 'delete',
            'login' => 'login_attempt',
            'logout' => 'logout',
            default => 'form_submission'
        };
    }

    /**
     * Add custom paths to ignore
     * 
     * @param array $paths
     * @return self
     */
    public function addIgnoredPaths(array $paths): self
    {
        $this->ignoredPaths = array_merge($this->ignoredPaths, $paths);
        return $this;
    }

    /**
     * Add custom sensitive fields
     * 
     * @param array $fields
     * @return self
     */
    public function addSensitiveFields(array $fields): self
    {
        $this->sensitiveFields = array_merge($this->sensitiveFields, $fields);
        return $this;
    }
}