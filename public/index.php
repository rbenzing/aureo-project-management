<?php
// file: public/index.php
declare(strict_types=1);

// Start the session
session_start();

// Define base path
define('BASE_PATH', __DIR__);

// CSP Headers
$cspHeader = "Content-Security-Policy: ".
    "default-src 'self'; ".
    "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; ".
    "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; ".
    "img-src 'self' data: https://cdn.jsdelivr.net; ".
    "font-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; ".
    "connect-src 'self'; ".
    "frame-src 'self'; ".
    "object-src 'none'; ".
    "base-uri 'self';";
header($cspHeader);

// additional headers
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Permissions-Policy: geolocation=(), microphone=()");

// Include Composer's autoloader
require_once BASE_PATH . '/../vendor/autoload.php';

// Error handling
try {

    // Load configuration
    \App\Core\Config::init();

    // Initialize middleware stack
    (new \App\Middleware\CsrfMiddleware())->handleToken();
    (new \App\Middleware\ActivityMiddleware())->handle();

    // Create Router Instance
    $router = new \App\Core\Router();

    // Define Routes
    // Auth Routes
    $router->get('', ['controller' => 'Dashboard', 'action' => 'index']);
    $router->get('login', ['controller' => 'Auth', 'action' => 'loginForm']);
    $router->post('login', ['controller' => 'Auth', 'action' => 'login']);
    $router->get('logout', ['controller' => 'Auth', 'action' => 'logout']);
    $router->get('register', ['controller' => 'Auth', 'action' => 'registerForm']);
    $router->post('register', ['controller' => 'Auth', 'action' => 'register']);
    $router->get('activate/:token', ['controller' => 'Auth', 'action' => 'activate', 'params' => ['token']]);
    $router->get('reset-password/:token', ['controller' => 'Auth', 'action' => 'resetPassword', 'params' => ['token']]);
    $router->post('reset-password/:token', ['controller' => 'Auth', 'action' => 'resetPassword', 'params' => ['token']]);
    $router->get('forgot-password', ['controller' => 'Auth', 'action' => 'forgotPassword']);
    $router->post('forgot-password', ['controller' => 'Auth', 'action' => 'forgotPassword']);

    // Dashboard Routes
    $router->get('dashboard', ['controller' => 'Dashboard', 'action' => 'index']);

    // Project Routes
    $router->get('projects', ['controller' => 'Project', 'action' => 'index']);
    $router->get('projects/page/:page', ['controller' => 'Project', 'action' => 'index', 'params' => ['page']]);
    $router->get('projects/view/:id', ['controller' => 'Project', 'action' => 'view', 'params' => ['id']]);
    $router->get('projects/create', ['controller' => 'Project', 'action' => 'createForm']);
    $router->post('projects/create', ['controller' => 'Project', 'action' => 'create']);
    $router->get('projects/edit/:id', ['controller' => 'Project', 'action' => 'editForm', 'params' => ['id']]);
    $router->post('projects/update', ['controller' => 'Project', 'action' => 'update']);
    $router->post('projects/delete/:id', ['controller' => 'Project', 'action' => 'delete', 'params' => ['id']]);

    // Task Routes
    $router->get('tasks', ['controller' => 'Task', 'action' => 'index']);
    $router->get('tasks/page/:page', ['controller' => 'Task', 'action' => 'index', 'params' => ['page']]);
    $router->get('tasks/assigned/:id', ['controller' => 'Task', 'action' => 'index', 'params' => ['id']]);
    $router->get('tasks/assigned/:id/page/:page', ['controller' => 'Task', 'action' => 'index', 'params' => ['id', 'page']]);
    $router->get('tasks/project/:id', ['controller' => 'Task', 'action' => 'index', 'params' => ['id']]);
    $router->get('tasks/view/:id', ['controller' => 'Task', 'action' => 'view', 'params' => ['id']]);
    $router->get('tasks/create', ['controller' => 'Task', 'action' => 'createForm']);
    $router->post('tasks/create', ['controller' => 'Task', 'action' => 'create']);
    $router->get('tasks/edit/:id', ['controller' => 'Task', 'action' => 'editForm', 'params' => ['id']]);
    $router->post('tasks/update', ['controller' => 'Task', 'action' => 'update']);
    $router->post('tasks/delete/:id', ['controller' => 'Task', 'action' => 'delete', 'params' => ['id']]);
    $router->post('tasks/start-timer/:task_id', ['controller' => 'Task', 'action' => 'startTimer', 'params' => ['task_id']]);
    $router->post('tasks/stop-timer/:time_entry_id', ['controller' => 'Task', 'action' => 'stopTimer', 'params' => ['time_entry_id']]);

    // User Routes
    $router->get('users', ['controller' => 'User', 'action' => 'index']);
    $router->get('users/page/:page', ['controller' => 'User', 'action' => 'index', 'params' => ['page']]);
    $router->get('users/view/:id', ['controller' => 'User', 'action' => 'view', 'params' => ['id']]);
    $router->get('users/create', ['controller' => 'User', 'action' => 'createForm']);
    $router->post('users/create', ['controller' => 'User', 'action' => 'create']);
    $router->get('users/edit/:id', ['controller' => 'User', 'action' => 'editForm', 'params' => ['id']]);
    $router->post('users/update', ['controller' => 'User', 'action' => 'update']);
    $router->post('users/delete/:id', ['controller' => 'User', 'action' => 'delete', 'params' => ['id']]);

    // Role Routes
    $router->get('roles', ['controller' => 'Role', 'action' => 'index']);
    $router->get('roles/page/:page', ['controller' => 'Role', 'action' => 'index', 'params' => ['page']]);
    $router->get('roles/view/:id', ['controller' => 'Role', 'action' => 'view', 'params' => ['id']]);
    $router->get('roles/create', ['controller' => 'Role', 'action' => 'createForm']);
    $router->post('roles/create', ['controller' => 'Role', 'action' => 'create']);
    $router->get('roles/edit/:id', ['controller' => 'Role', 'action' => 'editForm', 'params' => ['id']]);
    $router->post('roles/update', ['controller' => 'Role', 'action' => 'update']);
    $router->post('roles/delete/:id', ['controller' => 'Role', 'action' => 'delete', 'params' => ['id']]);

    // Company Routes
    $router->get('companies', ['controller' => 'Company', 'action' => 'index']);
    $router->get('companies/page/:page', ['controller' => 'Company', 'action' => 'index', 'params' => ['page']]);
    $router->get('companies/view/:id', ['controller' => 'Company', 'action' => 'view', 'params' => ['id']]);
    $router->get('companies/create', ['controller' => 'Company', 'action' => 'createForm']);
    $router->post('companies/create', ['controller' => 'Company', 'action' => 'create']);
    $router->get('companies/edit/:id', ['controller' => 'Company', 'action' => 'editForm', 'params' => ['id']]);
    $router->post('companies/update', ['controller' => 'Company', 'action' => 'update']);
    $router->post('companies/delete/:id', ['controller' => 'Company', 'action' => 'delete', 'params' => ['id']]);

    // Milestone Routes
    $router->get('milestones', ['controller' => 'Milestone', 'action' => 'index']);
    $router->get('milestones/page/:page', ['controller' => 'Milestone', 'action' => 'index', 'params' => ['page']]);
    $router->get('milestones/view/:id', ['controller' => 'Milestone', 'action' => 'view', 'params' => ['id']]);
    $router->get('milestones/create', ['controller' => 'Milestone', 'action' => 'createForm']);
    $router->post('milestones/create', ['controller' => 'Milestone', 'action' => 'create']);
    $router->get('milestones/edit/:id', ['controller' => 'Milestone', 'action' => 'editForm', 'params' => ['id']]);
    $router->post('milestones/update', ['controller' => 'Milestone', 'action' => 'update']);
    $router->post('milestones/delete/:id', ['controller' => 'Milestone', 'action' => 'delete', 'params' => ['id']]);

    // Sprint Routes
    $router->get('sprints', ['controller' => 'Sprint', 'action' => 'index']);
    $router->get('sprints/page/:page', ['controller' => 'Sprint', 'action' => 'index', 'params' => ['page']]);
    $router->get('sprints/project/:id', ['controller' => 'Sprint', 'action' => 'index', 'params' => ['id']]);
    $router->get('sprints/project/:id/page/:page', ['controller' => 'Sprint', 'action' => 'index', 'params' => ['id', 'page']]);
    $router->get('sprints/view/:id', ['controller' => 'Sprint', 'action' => 'view', 'params' => ['id']]);
    $router->get('sprints/create', ['controller' => 'Sprint', 'action' => 'createForm']);
    $router->post('sprints/create', ['controller' => 'Sprint', 'action' => 'create']);
    $router->get('sprints/edit/:id', ['controller' => 'Sprint', 'action' => 'editForm', 'params' => ['id']]);
    $router->post('sprints/update/:id', ['controller' => 'Sprint', 'action' => 'update', 'params' => ['id']]);
    $router->post('sprints/delete/:id', ['controller' => 'Sprint', 'action' => 'delete', 'params' => ['id']]);
    $router->post('sprints/start/:id', ['controller' => 'Sprint', 'action' => 'startSprint', 'params' => ['id']]);
    $router->post('sprints/complete/:id', ['controller' => 'Sprint', 'action' => 'completeSprint', 'params' => ['id']]);
    $router->post('sprints/delay/:id', ['controller' => 'Sprint', 'action' => 'delaySprint', 'params' => ['id']]);
    $router->post('sprints/cancel/:id', ['controller' => 'Sprint', 'action' => 'cancelSprint', 'params' => ['id']]);
    $router->post('sprints/add-tasks/:id', ['controller' => 'Sprint', 'action' => 'addTasks', 'params' => ['id']]);

    // Project Template Routes
    $router->get('project-templates', ['controller' => 'ProjectTemplate', 'action' => 'index']);
    $router->get('project-templates/page/:page', ['controller' => 'ProjectTemplate', 'action' => 'index', 'params' => ['page']]);
    $router->get('project-templates/view/:id', ['controller' => 'ProjectTemplate', 'action' => 'view', 'params' => ['id']]);
    $router->get('project-templates/create', ['controller' => 'ProjectTemplate', 'action' => 'createForm']);
    $router->post('project-templates/create', ['controller' => 'ProjectTemplate', 'action' => 'create']);
    $router->get('project-templates/edit/:id', ['controller' => 'ProjectTemplate', 'action' => 'editForm', 'params' => ['id']]);
    $router->post('project-templates/update', ['controller' => 'ProjectTemplate', 'action' => 'update']);
    $router->post('project-templates/delete/:id', ['controller' => 'ProjectTemplate', 'action' => 'delete', 'params' => ['id']]);
    $router->get('project-templates/get/:id', ['controller' => 'ProjectTemplate', 'action' => 'getTemplate', 'params' => ['id']]);

    // Get request URI and method
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $segments = explode('/', ltrim($uri, '/'));

    // Dispatch Request with proper error handling
    $router->dispatch(
        $_SERVER['REQUEST_METHOD'],
        $segments
    );
} catch (\PDOException $e) {
    // Database errors
    error_log($e->getMessage());
    http_response_code(500);
    echo "Database error occurred";
} catch (\Exception $e) {
    // Other errors
    error_log($e->getMessage());
    $code = $e->getCode() ?: 500;
    http_response_code($code);
    echo $e->getMessage();
}
