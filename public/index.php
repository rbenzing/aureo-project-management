<?php
// Start the session
session_start();

// Include autoloader for classes
require_once __DIR__ . '/../vendor/autoload.php';

// Define base path
define('BASE_PATH', __DIR__);

// Load configuration
require_once __DIR__ . '/../src/config/Config.php';

// Load helpers
require_once __DIR__ . '/../src/utils/Helpers.php';

// Initialize CSRF token if not set
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Parse the request URI
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$baseDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'); // Get the base directory
$requestUri = substr($requestUri, strlen($baseDir)); // Remove base directory from URI

// Default route
$route = trim($requestUri, '/') ?: 'login'; // Default to 'login' if no route is provided

// Route mapping (you can expand this as needed)
$routes = [
    'login' => ['controller' => 'AuthController', 'method' => 'login'],
    'register' => ['controller' => 'AuthController', 'method' => 'register'],
    'dashboard' => ['controller' => 'DashboardController', 'method' => 'index'],
    'projects' => ['controller' => 'ProjectController', 'method' => 'index'],
    'tasks' => ['controller' => 'TaskController', 'method' => 'index'],
    'users' => ['controller' => 'UsersController', 'method' => 'index'],
    'companies' => ['controller' => 'CompaniesController', 'method' => 'index'],
    'reset-password' => ['controller' => 'AuthController', 'method' => 'resetPassword'],
];

// Check if the route exists
if (array_key_exists($route, $routes)) {
    $controllerName = '\\App\\Controllers\\' . $routes[$route]['controller'];
    $methodName = $routes[$route]['method'];

    // Instantiate the controller and call the method
    $controller = new $controllerName();
    $controller->$methodName();
} else {
    // Handle 404 Not Found
    http_response_code(404);
    echo 'Page not found.';
}