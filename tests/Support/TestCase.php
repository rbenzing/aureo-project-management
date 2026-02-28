<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Core\Database;
use PHPUnit\Framework\TestCase as BaseTestCase;

/**
 * Base test case with common testing utilities
 */
abstract class TestCase extends BaseTestCase
{
    protected Database $db;
    protected static bool $dbInitialized = false;

    protected function setUp(): void
    {
        parent::setUp();

        // Initialize database connection for integration tests
        if (!self::$dbInitialized) {
            $this->initializeTestDatabase();
            self::$dbInitialized = true;
        }
    }

    /**
     * Initialize test database connection
     */
    protected function initializeTestDatabase(): void
    {
        // Set test environment variables
        $_ENV['APP_ENV'] = 'testing';
        $_ENV['DB_HOST'] = getenv('DB_HOST') ?: 'localhost';
        $_ENV['DB_NAME'] = getenv('DB_NAME') ?: 'pms_test';
        $_ENV['DB_USERNAME'] = getenv('DB_USERNAME') ?: 'root';
        $_ENV['DB_PASSWORD'] = getenv('DB_PASSWORD') ?: '';

        $this->db = Database::getInstance();
    }

    /**
     * Create a mock object with expectations
     */
    protected function mockWithExpectations(string $class, array $methods = []): object
    {
        $mock = $this->createMock($class);

        foreach ($methods as $method => $returnValue) {
            $mock->expects($this->any())
                ->method($method)
                ->willReturn($returnValue);
        }

        return $mock;
    }

    /**
     * Assert array has keys
     */
    protected function assertArrayHasKeys(array $keys, array $array, string $message = ''): void
    {
        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $array, $message ?: "Array missing key: {$key}");
        }
    }

    /**
     * Assert object has properties
     */
    protected function assertObjectHasProperties(array $properties, object $object, string $message = ''): void
    {
        foreach ($properties as $property) {
            $this->assertObjectHasProperty($property, $object, $message ?: "Object missing property: {$property}");
        }
    }

    /**
     * Simulate authenticated user session
     */
    protected function actingAs(array $userData): void
    {
        $_SESSION['user'] = $userData;
        $_SESSION['authenticated'] = true;
    }

    /**
     * Clear session data
     */
    protected function clearSession(): void
    {
        $_SESSION = [];
    }

    /**
     * Create test request data
     */
    protected function createRequestData(array $data, string $method = 'POST'): array
    {
        return array_merge([
            '_method' => $method,
            'csrf_token' => $_SESSION['csrf_token'] ?? 'test-token',
        ], $data);
    }
}
