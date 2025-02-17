<?php
declare(strict_types=1);

// Start the session
session_start();

// Define base path
define('BASE_PATH', __DIR__);

// Include Composer's autoloader
require_once BASE_PATH . '/../vendor/autoload.php';

// Load configuration
require_once BASE_PATH . '/../src/config/Config.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH . '/../');
$dotenv->load();

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
    'logout' => ['controller' => 'AuthController', 'method' => 'logout'],
    'register' => ['controller' => 'AuthController', 'method' => 'register'],
    'activate' => ['controller' => 'AuthController', 'method' => 'activate'],
    'reset-password' => ['controller' => 'AuthController', 'method' => 'resetPassword'],
    'forgot-password' => ['controller' => 'AuthController', 'method' => 'forgotPassword'],
    
    'dashboard' => ['controller' => 'DashboardController', 'method' => 'index'],
    
    'projects' => ['controller' => 'ProjectController', 'method' => 'index'],
    'create_project' => ['controller' => 'ProjectController', 'method' => 'create'],
    'view_project' => ['controller' => 'ProjectController', 'method' => 'view'],
    'edit_project' => ['controller' => 'ProjectController', 'method' => 'edit'],
    
    'tasks' => ['controller' => 'TaskController', 'method' => 'index'],
    'create_task' => ['controller' => 'TaskController', 'method' => 'create'],
    'view_task' => ['controller' => 'TaskController', 'method' => 'view'],
    'edit_task' => ['controller' => 'TaskController', 'method' => 'edit'],
    
    'subtasks' => ['controller' => 'SubtaskController', 'method' => 'index'],
    'create_task' => ['controller' => 'SubtaskController', 'method' => 'create'],
    'view_task' => ['controller' => 'SubtaskController', 'method' => 'view'],
    'edit_task' => ['controller' => 'SubtaskController', 'method' => 'edit'],
    
    'users' => ['controller' => 'UserController', 'method' => 'index'],
    'create_user' => ['controller' => 'UserController', 'method' => 'create'],
    'view_user' => ['controller' => 'UserController', 'method' => 'view'],
    'edit_user' => ['controller' => 'UserController', 'method' => 'edit'],
    
    'roles' => ['controller' => 'RoleController', 'method' => 'index'],
    'create_role' => ['controller' => 'RoleController', 'method' => 'create'],
    'view_role' => ['controller' => 'RoleController', 'method' => 'view'],
    'edit_role' => ['controller' => 'RoleController', 'method' => 'edit'],
    
    'companies' => ['controller' => 'CompanyController', 'method' => 'index'],
    'create_company' => ['controller' => 'CompanyController', 'method' => 'create'],
    'view_company' => ['controller' => 'CompanyController', 'method' => 'view'],
    'edit_company' => ['controller' => 'CompanyController', 'method' => 'edit'],
];

// Check if the route exists
if (array_key_exists($route, $routes)) {
    $controllerName = '\\App\\Controllers\\' . $routes[$route]['controller'];
    $methodName = $routes[$route]['method'];

    // Ensure the controller and method exist
    if (class_exists($controllerName) && method_exists($controllerName, $methodName)) {
        // Instantiate the controller and call the method
        $controller = new $controllerName();
        $controller->$methodName(isset($_GET['id']) ? htmlspecialchars(trim($_GET['id'])) : null);
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