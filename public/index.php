<?php
// Start the session
session_start();

// Include Composer's autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Define base path
define('BASE_PATH', __DIR__);

// Load configuration
require_once __DIR__ . '/../src/config/Config.php';

// Initialize CSRF token if not set
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Parse the request URI
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$baseDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'); // Get the base directory
$requestUri = substr($requestUri, strlen($baseDir)); // Remove base directory from URI

// Default route
$route = str_replace('.php','',trim($requestUri, '/')) ?: 'login'; // Default to 'login' if no route is provided

// Route mapping (you can expand this as needed)
$routes = [
    '' => ['controller' => 'AuthController', 'method' => 'login'], // Default route
    'login' => ['controller' => 'AuthController', 'method' => 'login'],
    'register' => ['controller' => 'AuthController', 'method' => 'register'],
    'activate' => ['controller' => 'AuthController', 'method' => 'activate'],
    'dashboard' => ['controller' => 'DashboardController', 'method' => 'index'],
    'projects' => ['controller' => 'ProjectController', 'method' => 'index'],
    'tasks' => ['controller' => 'TaskController', 'method' => 'index'],
    'subtasks' => ['controller' => 'SubtaskController', 'method' => 'index'], // Subtasks route
    'users' => ['controller' => 'UsersController', 'method' => 'index'],
    'roles' => ['controller' => 'RoleController', 'method' => 'index'], // Roles route
    'companies' => ['controller' => 'CompaniesController', 'method' => 'index'],
    'reset-password' => ['controller' => 'AuthController', 'method' => 'resetPassword'],
    'forgot-password' => ['controller' => 'AuthController', 'method' => 'forgotPassword']
];

// Check if the route exists
if (array_key_exists($route, $routes)) {
    $controllerName = '\\App\\Controllers\\' . $routes[$route]['controller'];
    $methodName = $routes[$route]['method'];

    // Ensure the controller and method exist
    if (class_exists($controllerName) && method_exists($controllerName, $methodName)) {
        // Instantiate the controller and call the method
        $controller = new $controllerName();
        $controller->$methodName();
    } else {
        // Handle invalid controller or method
        http_response_code(500);
        echo 'Internal Server Error: Invalid controller or method.';
    }
} else {
    // Handle 404 Not Found
    http_response_code(404);
    echo 'Page not found.';
}