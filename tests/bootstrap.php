<?php

declare(strict_types=1);

// Define BASE_PATH for tests
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__) . '/public');
}

// Load Composer autoloader
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Set test environment variables (before Config::init())
$_ENV['APP_DEBUG'] = 'true';
$_ENV['APP_ENV'] = 'testing';

// Initialize session for tests
if (session_status() === PHP_SESSION_NONE) {
    $_SESSION = [];
}
