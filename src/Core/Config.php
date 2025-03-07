<?php
// file: Core/Config.php
declare(strict_types=1);

namespace App\Core;

// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}

use Dotenv\Dotenv;
use RuntimeException;

class Config
{
    /**
     * Default configuration constants
     */
    private const DEFAULTS = [
        'PAGE_LIMIT' => 10,
        'TIMEZONE' => 'UTC',
        'COMPANY' => 'Slimbooks',
        'DOMAIN' => 'slimbooks',
        'SCHEME' => 'http',
        'LOCALE' => 'en_US',
        'CURRENCY_FORMAT' => '%i'
    ];

    /**
     * Required environment variables
     */
    private const REQUIRED_ENV = [
        'APP_DEBUG',
        'DB_HOST',
        'DB_NAME',
        'DB_USERNAME',
        'DB_PASSWORD'
    ];

    /**
     * Application configuration storage
     * @var array
     */
    private static array $config = [];

    /**
     * Track if environment is loaded
     * @var bool
     */
    private static bool $isInitialized = false;

    /**
     * Initialize configuration with environment-specific settings
     * @throws RuntimeException
     */
    public static function init(): void
    {
        if (self::$isInitialized) {
            return;
        }

        self::loadEnvironment();
        self::validateEnvironment();
        self::initializeSettings();
        
        self::$isInitialized = true;
    }

    /**
     * Load environment variables
     * @throws RuntimeException
     */
    private static function loadEnvironment(): void
    {
        $envPath = dirname(__DIR__, 2);
        if (!file_exists($envPath . '/.env')) {
            throw new RuntimeException('.env file not found');
        }

        $dotenv = Dotenv::createImmutable($envPath);
        $dotenv->load();
    }

    /**
     * Validate required environment variables
     * @throws RuntimeException
     */
    private static function validateEnvironment(): void
    {
        $missing = [];
        foreach (self::REQUIRED_ENV as $var) {
            if (!isset($_ENV[$var])) {
                $missing[] = $var;
            }
        }

        if (!empty($missing)) {
            throw new RuntimeException(
                'Missing required environment variables: ' . implode(', ', $missing)
            );
        }
    }

    /**
     * Initialize application settings
     */
    private static function initializeSettings(): void
    {
        // Set timezone
        date_default_timezone_set(self::get('TIMEZONE', self::DEFAULTS['TIMEZONE']));

        // Set locale
        $locale = self::get('LOCALE', self::DEFAULTS['LOCALE']);
        setlocale(LC_ALL, $locale . '.UTF-8');

        // Initialize configuration
        self::$config = [
            'debug' => self::getEnvBoolean('APP_DEBUG', false),
            'max_pages' => (int) self::get('PAGE_LIMIT', self::DEFAULTS['PAGE_LIMIT']),
            'timezone' => self::get('TIMEZONE', self::DEFAULTS['TIMEZONE']),
            'domain' => self::get('DOMAIN', self::DEFAULTS['DOMAIN']),
            'company_name' => self::get('COMPANY', self::DEFAULTS['COMPANY']),
            'scheme' => self::get('SCHEME', self::DEFAULTS['SCHEME']),
            'locale' => $locale,
            'currency_format' => self::get('CURRENCY_FORMAT', self::DEFAULTS['CURRENCY_FORMAT']),
            'base_url' => sprintf(
                '%s://%s',
                self::get('SCHEME', self::DEFAULTS['SCHEME']),
                self::get('DOMAIN', self::DEFAULTS['DOMAIN'])
            )
        ];
    }

    /**
     * Get a configuration value
     * @param string $key Configuration key
     * @param mixed $default Default value if key not found
     * @return mixed
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return $_ENV[$key] ?? self::$config[strtolower($key)] ?? $default;
    }

    /**
     * Set a configuration value
     * @param string $key Configuration key
     * @param mixed $value Configuration value
     */
    public static function set(string $key, mixed $value): void
    {
        self::$config[strtolower($key)] = $value;
    }

    /**
     * Check if a configuration key exists
     * @param string $key Configuration key
     * @return bool
     */
    public static function has(string $key): bool
    {
        return isset(self::$config[strtolower($key)]) || isset($_ENV[$key]);
    }

    /**
     * Get boolean value from environment variable
     * @param string $key Environment variable key
     * @param bool $default Default value
     * @return bool
     */
    private static function getEnvBoolean(string $key, bool $default = false): bool
    {
        $value = $_ENV[$key] ?? $default;
        if (is_string($value)) {
            return in_array(strtolower($value), ['true', '1', 'yes', 'on'], true);
        }
        return (bool) $value;
    }

    /**
     * Get all configuration values
     * @return array
     */
    public static function all(): array
    {
        return self::$config;
    }
}