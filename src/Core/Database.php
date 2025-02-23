<?php
namespace App\Core;

use Dotenv\Dotenv;
use PDO;
use PDOException;
use Exception;

class Database
{
    private static $instance = null;
    private $pdo;

    private function __construct()
    {
        // Load environment variables
        $dotenv = Dotenv::createImmutable(BASE_PATH . '/../');
        $dotenv->load();

        // Retrieve database credentials from environment variables
        $host = $_ENV['DB_HOST'] ?? 'localhost';
        $dbname = $_ENV['DB_NAME'] ?? 'database_name';
        $username = $_ENV['DB_USERNAME'] ?? 'root';
        $password = $_ENV['DB_PASSWORD'] ?? '';
        $charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';

        if (!isset($host) || !isset($dbname) || !isset($username) || !isset($password)) {
            throw new Exception('Invalid database credentials.');
        }

        try {
            $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $this->pdo = new PDO($dsn, $username, $password, $options);
        } catch (PDOException $e) {
            throw new Exception('Database connection failed: ' . $e->getMessage());
        }
    }

    public function __clone() {}
    public function __wakeup() {}

    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Execute a prepared statement with optional parameters.
     *
     * @param string $sql
     * @param array $params
     * @return PDOStatement
     */
    public function executeQuery(string $sql, array $params = [])
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Get the PDO instance.
     *
     * @return PDO
     */
    public function getPdo(): PDO
    {
        return $this->pdo;
    }
}