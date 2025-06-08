<?php
//file: Services/SecurityService.php
declare(strict_types=1);

namespace App\Services;

// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}

/**
 * Security Service
 * 
 * Centralized service for managing security features and validations
 */
class SecurityService
{
    private SettingsService $settingsService;
    private static ?SecurityService $instance = null;

    public function __construct()
    {
        $this->settingsService = SettingsService::getInstance();
    }

    /**
     * Get singleton instance
     */
    public static function getInstance(): SecurityService
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Validate redirect URL against security settings
     */
    public function validateRedirectUrl(string $url): bool
    {
        if (!$this->settingsService->isSecurityFeatureEnabled('validate_redirects')) {
            return true; // Validation disabled
        }

        // Parse the URL
        $parsedUrl = parse_url($url);
        if (!$parsedUrl) {
            return false;
        }

        // Allow relative URLs (same domain)
        if (!isset($parsedUrl['host'])) {
            return true;
        }

        // Get allowed domains
        $allowedDomains = $this->settingsService->getAllowedRedirectDomains();
        
        // If no allowed domains specified, only allow same domain
        if (empty($allowedDomains)) {
            $currentHost = $_SERVER['HTTP_HOST'] ?? '';
            return $parsedUrl['host'] === $currentHost;
        }

        // Check against allowed domains
        return in_array($parsedUrl['host'], $allowedDomains, true);
    }

    /**
     * Get safe redirect URL
     */
    public function getSafeRedirectUrl(string $url, string $fallback = '/dashboard'): string
    {
        if ($this->validateRedirectUrl($url)) {
            return $url;
        }
        return $fallback;
    }

    /**
     * Validate input size against security settings
     */
    public function validateInputSize(string $input): bool
    {
        $maxSize = $this->settingsService->getSecuritySetting('max_input_size', 1048576);
        return strlen($input) <= $maxSize;
    }

    /**
     * Sanitize HTML content based on security settings
     */
    public function sanitizeHtml(string $content): string
    {
        if (!$this->settingsService->isSecurityFeatureEnabled('html_sanitization')) {
            return $content;
        }

        // Basic HTML sanitization
        return htmlspecialchars($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Enhanced HTML sanitization for rich content
     */
    public function sanitizeRichContent(string $content): string
    {
        if (!$this->settingsService->isSecurityFeatureEnabled('html_sanitization')) {
            return $content;
        }

        // Allow basic formatting but remove dangerous elements
        $allowedTags = '<p><br><strong><em><ul><ol><li><h1><h2><h3><h4><h5><h6><blockquote><code>';
        return strip_tags($content, $allowedTags);
    }

    /**
     * Validate session domain
     */
    public function validateSessionDomain(string $domain): bool
    {
        if (!$this->settingsService->isSecurityFeatureEnabled('validate_session_domain')) {
            return true;
        }

        $currentHost = $_SERVER['HTTP_HOST'] ?? '';
        
        // Basic domain validation
        if ($domain === $currentHost) {
            return true;
        }

        // Allow subdomains of current host
        if (str_ends_with($domain, '.' . $currentHost)) {
            return true;
        }

        return false;
    }

    /**
     * Get security headers based on settings
     */
    public function getSecurityHeaders(): array
    {
        $headers = [];

        // Content Security Policy
        if ($this->settingsService->isSecurityFeatureEnabled('enable_csp')) {
            $csp = $this->settingsService->getContentSecurityPolicy();
            if (!empty($csp)) {
                $headers['Content-Security-Policy'] = $csp;
            }
        }

        // Additional security headers
        if ($this->settingsService->isSecurityFeatureEnabled('additional_headers')) {
            $headers['X-Content-Type-Options'] = 'nosniff';
            $headers['X-Frame-Options'] = 'DENY';
            $headers['X-XSS-Protection'] = '1; mode=block';
            $headers['Referrer-Policy'] = 'strict-origin-when-cross-origin';
            $headers['Permissions-Policy'] = 'geolocation=(), microphone=(), camera=()';
            
            // HSTS for HTTPS
            if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
                $headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains';
            }
        }

        return $headers;
    }

    /**
     * Apply security headers
     */
    public function applySecurityHeaders(): void
    {
        $headers = $this->getSecurityHeaders();
        
        foreach ($headers as $name => $value) {
            header("$name: $value");
        }
    }

    /**
     * Log security event
     */
    public function logSecurityEvent(string $event, array $context = []): void
    {
        if (!$this->settingsService->isSecurityFeatureEnabled('log_security_events')) {
            return;
        }

        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'event' => $event,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'user_id' => $_SESSION['user']['id'] ?? null,
            'context' => $context
        ];

        error_log('SECURITY_EVENT: ' . json_encode($logData));
    }

    /**
     * Check rate limiting
     */
    public function checkRateLimit(string $identifier = null): bool
    {
        $maxAttempts = $this->settingsService->getSecuritySetting('rate_limit_attempts', 60);
        
        if ($maxAttempts <= 0) {
            return true; // Rate limiting disabled
        }

        $identifier = $identifier ?? ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
        
        // Simple in-memory rate limiting (for basic protection)
        // In production, you might want to use Redis or database
        $key = 'rate_limit_' . md5($identifier);
        $current = $_SESSION[$key] ?? ['count' => 0, 'reset' => time() + 60];
        
        // Reset counter if time window passed
        if (time() > $current['reset']) {
            $current = ['count' => 0, 'reset' => time() + 60];
        }
        
        $current['count']++;
        $_SESSION[$key] = $current;
        
        if ($current['count'] > $maxAttempts) {
            $this->logSecurityEvent('rate_limit_exceeded', [
                'identifier' => $identifier,
                'attempts' => $current['count'],
                'limit' => $maxAttempts
            ]);
            return false;
        }
        
        return true;
    }

    /**
     * Get session configuration based on security settings
     */
    public function getSessionConfig(): array
    {
        return [
            'cookie_httponly' => true,
            'use_only_cookies' => true,
            'cookie_secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
            'cookie_samesite' => $this->settingsService->getSecuritySetting('session_samesite', 'Lax')
        ];
    }

    /**
     * Should hide error details based on security settings
     */
    public function shouldHideErrorDetails(): bool
    {
        return $this->settingsService->isSecurityFeatureEnabled('hide_error_details');
    }

    /**
     * Get safe error message for display to users
     */
    public function getSafeErrorMessage(string $originalMessage, string $fallbackMessage = 'An error occurred. Please try again later.'): string
    {
        if ($this->shouldHideErrorDetails()) {
            return $fallbackMessage;
        }
        return $originalMessage;
    }

    /**
     * Log error and return safe message for display
     */
    public function handleError(\Exception $e, string $context = '', string $fallbackMessage = 'An error occurred. Please try again later.'): string
    {
        // Always log the full error details
        error_log("Error in {$context}: " . $e->getMessage());

        // Log as security event if enabled
        $this->logSecurityEvent('application_error', [
            'context' => $context,
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);

        // Return safe message based on settings
        return $this->getSafeErrorMessage($e->getMessage(), $fallbackMessage);
    }
}
