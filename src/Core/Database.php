<?php
namespace App\Core;

use Dotenv\Dotenv;

class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        // Load environment variables if not already loaded
        if (!isset($_ENV['DB_HOST'])) {
            throw new Exception('Missing database configuration.');
        }

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
            $this->pdo = new \PDO("mysql:host=$host;dbname=$dbname;charset=$charset", $username, $password);
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            throw new Exception('Database connection failed: ' . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->pdo;
    }
}