<?php
// file: Core/Database.php
declare(strict_types=1);

namespace App\Core;

// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}

use PDO;
use PDOException;
use RuntimeException;
use InvalidArgumentException;

class Database
{
    private static ?Database $instance = null;
    private ?PDO $pdo = null;
    private array $options;
    private array $credentials;
    private static array $queryLog = [];
    private static bool $logQueries = false;

    /**
     * Private constructor to prevent direct instantiation
     * @throws RuntimeException
     */
    private function __construct()
    {
        $this->loadConfiguration();
        $this->setDefaultOptions();
    }

    /**
     * Prevent cloning of the instance
     */
    private function __clone() {}

    /**
     * Prevent unserializing of the instance
     */
    public function __wakeup()
    {
        throw new RuntimeException('Cannot unserialize singleton');
    }

    /**
     * Get Database instance
     * @return self
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Load database configuration from environment
     * @throws RuntimeException
     */
    private function loadConfiguration(): void
    {
        try {
            $this->credentials = [
                'host' => $_ENV['DB_HOST'] ?? 'localhost',
                'dbname' => $_ENV['DB_NAME'] ?? null,
                'username' => $_ENV['DB_USERNAME'] ?? null,
                'password' => $_ENV['DB_PASSWORD'] ?? null,
                'charset' => $_ENV['DB_CHARSET'] ?? 'utf8mb4'
            ];

            $this->validateCredentials();
        } catch (\Exception $e) {
            throw new RuntimeException('Failed to load database configuration: ' . $e->getMessage());
        }
    }

    /**
     * Set default PDO options
     */
    private function setDefaultOptions(): void
    {
        $this->options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_PERSISTENT         => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ];
    }

    /**
     * Validate database credentials
     * @throws RuntimeException
     */
    private function validateCredentials(): void
    {
        $required = ['dbname', 'username']; // todo: when prod add 'password'
        foreach ($required as $field) {
            if (empty($this->credentials[$field])) {
                throw new RuntimeException("Missing required database credential: {$field}");
            }
        }
    }

    /**
     * Get PDO connection
     * @return PDO
     * @throws RuntimeException
     */
    public function getConnection(): PDO
    {
        if ($this->pdo === null) {
            $this->connect();
        }
        return $this->pdo;
    }

    /**
     * Establish database connection
     * @throws RuntimeException
     */
    private function connect(): void
    {
        try {
            $dsn = sprintf(
                "mysql:host=%s;dbname=%s;charset=%s",
                $this->credentials['host'],
                $this->credentials['dbname'],
                $this->credentials['charset']
            );

            $this->pdo = new PDO(
                $dsn,
                $this->credentials['username'],
                $this->credentials['password'],
                $this->options
            );
        } catch (PDOException $e) {
            throw new RuntimeException('Database connection failed: ' . $e->getMessage());
        }
    }

    /**
     * Execute a prepared statement
     * @param string $sql SQL query
     * @param array $params Query parameters
     * @param int $fetchMode PDO fetch mode
     * @return \PDOStatement
     * @throws RuntimeException
     */
    public function executeQuery(string $sql, array $params = [], int $fetchMode = PDO::FETCH_ASSOC): \PDOStatement
    {
        try {
            $startTime = microtime(true);
            
            $stmt = $this->getConnection()->prepare($sql);
            
            // Ensure numeric indexing for parameters
            $sanitizedParams = [];
            foreach ($params as $key => $value) {
                // Remove ':' prefix if present
                $cleanKey = ltrim($key, ':');
                $stmt->bindValue(':' . $cleanKey, $value);
            }

            $stmt->setFetchMode($fetchMode);
            $success = $stmt->execute();

            if (self::$logQueries) {
                $this->logQuery($sql, $params, microtime(true) - $startTime);
            }

            if (!$success) {
                throw new RuntimeException('Query execution failed');
            }

            return $stmt;
        } catch (PDOException $e) {
            // Log the full error details
            error_log("Query Execution Error: " . $e->getMessage());
            error_log("SQL: " . $sql);
            error_log("Params: " . json_encode($params));
            
            throw new RuntimeException('Query execution failed: ' . $e->getMessage());
        }
    }

    /**
     * Execute a prepared statement
     * @param string $sql SQL query
     * @param array $params Query parameters
     * @return bool
     * @throws RuntimeException
     */
    public function executeInsertUpdate(string $sql, array $params = []): bool
    {
        try {
            $startTime = microtime(true);
            
            $stmt = $this->getConnection()->prepare($sql);
            $success = $stmt->execute($params);

            if (self::$logQueries) {
                $this->logQuery($sql, $params, microtime(true) - $startTime);
            }

            return $success;
        } catch (PDOException $e) {
            throw new RuntimeException('Insert/Update execution failed: ' . $e->getMessage());
        }
    }

    public function lastInsertId(): int
    {
        return (int)$this->getConnection()->lastInsertId();
    }

    /**
     * Begin a transaction
     * @return bool
     */
    public function beginTransaction(): bool
    {
        return $this->getConnection()->beginTransaction();
    }

    /**
     * Commit a transaction
     * @return bool
     */
    public function commit(): bool
    {
        return $this->getConnection()->commit();
    }

    /**
     * Rollback a transaction
     * @return bool
     */
    public function rollBack(): bool
    {
        return $this->getConnection()->rollBack();
    }

    /**
     * Enable or disable query logging
     * @param bool $enable
     */
    public static function enableQueryLog(bool $enable = true): void
    {
        self::$logQueries = $enable;
    }

    /**
     * Get query log
     * @return array
     */
    public static function getQueryLog(): array
    {
        return self::$queryLog;
    }

    /**
     * Log a query
     * @param string $sql
     * @param array $params
     * @param float $executionTime
     */
    private function logQuery(string $sql, array $params, float $executionTime): void
    {
        self::$queryLog[] = [
            'query' => $sql,
            'params' => $params,
            'execution_time' => round($executionTime * 1000, 2) . 'ms',
            'time' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Close the database connection
     */
    public function close(): void
    {
        $this->pdo = null;
    }

    /**
     * Destructor to ensure connection is closed
     */
    public function __destruct()
    {
        $this->close();
    }
}