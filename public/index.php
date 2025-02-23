<?php
declare(strict_types=1);

// Start the session
session_start();

// Define base path
define('BASE_PATH', __DIR__);

// Include Composer's autoloader
require_once BASE_PATH . '/../vendor/autoload.php';

// Load configuration
require_once BASE_PATH . '/../src/Core/Config.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH . '/../');
$dotenv->load();

// handle CSRF token
(new \App\Middleware\CsrfMiddleware())->handleToken();

// handle activity log
(new \App\Middleware\ActivityMiddleware())->handle();

// Create Router Instance
$router = new \App\Core\Router();

// Define Routes
$router->add('', ['controller' => 'Dashboard', 'action' => 'index']);
$router->add('login', ['controller' => 'Auth', 'action' => 'login']);
$router->add('logout', ['controller' => 'Auth', 'action' => 'logout']);
$router->add('register', ['controller' => 'Auth', 'action' => 'register']);
$router->add('activate/:token', ['controller' => 'Auth', 'action' => 'activate']);
$router->add('reset-password/:token', ['controller' => 'Auth', 'action' => 'resetPassword']);
$router->add('forgot-password', ['controller' => 'Auth', 'action' => 'forgotPassword']);

$router->add('dashboard', ['controller' => 'Dashboard', 'action' => 'index']);

$router->add('projects/page/:page', ['controller' => 'Project', 'action' => 'index']);
$router->add('projects/view/:id', ['controller' => 'Project', 'action' => 'view']);
$router->add('projects/create', ['controller' => 'Project', 'action' => 'createForm']);
$router->add('projects/edit/:id', ['controller' => 'Project', 'action' => 'editForm']);
$router->add('projects/update', ['controller' => 'Project', 'action' => 'update']);
$router->add('projects/delete/:id', ['controller' => 'Project', 'action' => 'delete']);
$router->add('projects', ['controller' => 'Project', 'action' => 'index']);

$router->add('tasks/page/:page', ['controller' => 'Task', 'action' => 'index']);
$router->add('tasks/view/:id', ['controller' => 'Task', 'action' => 'view']);
$router->add('tasks/create', ['controller' => 'Task', 'action' => 'createForm']);
$router->add('tasks/edit/:id', ['controller' => 'Task', 'action' => 'editForm']);
$router->add('tasks/update', ['controller' => 'Task', 'action' => 'update']);
$router->add('tasks/delete/:id', ['controller' => 'Task', 'action' => 'delete']);
$router->add('tasks/start-timer/:task_id', ['controller' => 'Task', 'action' => 'startTimer']);
$router->add('tasks/stop-timer/:time_entry_id', ['controller' => 'Task', 'action' => 'stopTimer']);
$router->add('tasks', ['controller' => 'Task', 'action' => 'index']);

$router->add('users/page/:page', ['controller' => 'User', 'action' => 'index']);
$router->add('users/view/:id', ['controller' => 'User', 'action' => 'view']);
$router->add('users/create', ['controller' => 'User', 'action' => 'createForm']);
$router->add('users/edit/:id', ['controller' => 'User', 'action' => 'editForm']);
$router->add('users/update', ['controller' => 'User', 'action' => 'update']);
$router->add('users/delete/:id', ['controller' => 'User', 'action' => 'delete']);
$router->add('users', ['controller' => 'User', 'action' => 'index']);

$router->add('roles/page/:page', ['controller' => 'Role', 'action' => 'index']);
$router->add('roles/view/:id', ['controller' => 'Role', 'action' => 'view']);
$router->add('roles/create', ['controller' => 'Role', 'action' => 'createForm']);
$router->add('roles/edit/:id', ['controller' => 'Role', 'action' => 'editForm']);
$router->add('roles/update', ['controller' => 'Role', 'action' => 'update']);
$router->add('roles/delete/:id', ['controller' => 'Role', 'action' => 'delete']);
$router->add('roles', ['controller' => 'Role', 'action' => 'index']);

$router->add('companies/page/:page', ['controller' => 'Company', 'action' => 'index']);
$router->add('companies/view/:id', ['controller' => 'Company', 'action' => 'view']);
$router->add('companies/create', ['controller' => 'Company', 'action' => 'createForm']);
$router->add('companies/edit/:id', ['controller' => 'Company', 'action' => 'editForm']);
$router->add('companies/update', ['controller' => 'Company', 'action' => 'update']);
$router->add('companies/delete/:id', ['controller' => 'Company', 'action' => 'delete']);
$router->add('companies', ['controller' => 'Company', 'action' => 'index']);

$router->add('milestones/page/:page', ['controller' => 'Milestone', 'action' => 'index']);
$router->add('milestones/view/:id', ['controller' => 'Milestone', 'action' => 'view']);
$router->add('milestones/create', ['controller' => 'Milestone', 'action' => 'createForm']);
$router->add('milestones/edit/:id', ['controller' => 'Milestone', 'action' => 'editForm']);
$router->add('milestones/update', ['controller' => 'Milestone', 'action' => 'update']);
$router->add('milestones/delete/:id', ['controller' => 'Milestone', 'action' => 'delete']);
$router->add('milestones', ['controller' => 'Milestone', 'action' => 'index']);

try {
    // Dispatch Request
    $router->dispatch($_SERVER['REQUEST_METHOD'], explode('/', ltrim($_SERVER['REQUEST_URI'], '/')));
} catch(\PDOException $e) {
    http_response_code(400);
    echo $e->getMessage();
} catch (\Exception $e) {
    http_response_code($e->getCode());
    echo $e->getMessage();
}