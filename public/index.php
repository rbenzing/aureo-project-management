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
    'update_project' => ['controller' => 'ProjectController', 'method' => 'update'],
    'delete_project' => ['controller' => 'ProjectController', 'method' => 'delete'],
    
    'tasks' => ['controller' => 'TaskController', 'method' => 'index'],
    'create_task' => ['controller' => 'TaskController', 'method' => 'create'],
    'view_task' => ['controller' => 'TaskController', 'method' => 'view'],
    'edit_task' => ['controller' => 'TaskController', 'method' => 'edit'],
    'update_task' => ['controller' => 'TaskController', 'method' => 'update'],
    'delete_task' => ['controller' => 'TaskController', 'method' => 'delete'],
    
    'subtasks' => ['controller' => 'SubtaskController', 'method' => 'index'],
    'create_subtask' => ['controller' => 'SubtaskController', 'method' => 'create'],
    'view_subtask' => ['controller' => 'SubtaskController', 'method' => 'view'],
    'edit_subtask' => ['controller' => 'SubtaskController', 'method' => 'edit'],
    'update_subtask' => ['controller' => 'SubtaskController', 'method' => 'update'],
    'delete_subtask' => ['controller' => 'SubtaskController', 'method' => 'delete'],
    
    'users' => ['controller' => 'UserController', 'method' => 'index'],
    'create_user' => ['controller' => 'UserController', 'method' => 'create'],
    'view_user' => ['controller' => 'UserController', 'method' => 'view'],
    'edit_user' => ['controller' => 'UserController', 'method' => 'edit'],
    'update_user' => ['controller' => 'UserController', 'method' => 'update'],
    'delete_user' => ['controller' => 'UserController', 'method' => 'delete'],
    
    'roles' => ['controller' => 'RoleController', 'method' => 'index'],
    'create_role' => ['controller' => 'RoleController', 'method' => 'create'],
    'view_role' => ['controller' => 'RoleController', 'method' => 'view'],
    'edit_role' => ['controller' => 'RoleController', 'method' => 'edit'],
    'update_role' => ['controller' => 'RoleController', 'method' => 'update'],
    'delete_role' => ['controller' => 'RoleController', 'method' => 'delete'],
    
    'companies' => ['controller' => 'CompanyController', 'method' => 'index'],
    'create_company' => ['controller' => 'CompanyController', 'method' => 'create'],
    'view_company' => ['controller' => 'CompanyController', 'method' => 'view'],
    'edit_company' => ['controller' => 'CompanyController', 'method' => 'edit'],
    'update_company' => ['controller' => 'CompanyController', 'method' => 'update'],
    'delete_company' => ['controller' => 'CompanyController', 'method' => 'delete'],
];

// Check if the route exists
if (array_key_exists($route, $routes)) {
    $controllerName = '\\App\\Controllers\\' . $routes[$route]['controller'];
    $methodName = $routes[$route]['method'];

    // Ensure the controller and method exist
    if (class_exists($controllerName) && method_exists($controllerName, $methodName)) {
        $params = null;
        if ($_SERVER["REQUEST_METHOD"] == "GET") {
            $params = $_GET;
        } 
        else if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $params = $_POST;
        }
        // Instantiate the controller and call the method
        $controller = new $controllerName();
        $controller->$methodName($params);
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