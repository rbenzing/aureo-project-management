<?php
namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Models\User;
use App\Models\Project;
use App\Models\Task;

class DashboardController
{
    private $authMiddleware;

    public function __construct()
    {
        // Ensure the user has the required permission
        $this->authMiddleware = new AuthMiddleware();
        $this->authMiddleware->isAuthenticated();
        $this->authMiddleware->hasPermission('view_dashboard'); // Default permission for all actions
    }

    /**
     * Display the dashboard page.
     */
    public function index($requestMethod, $data)
    {
        $userId = $_SESSION['user']['profile']['id'];
        $user = (new User())->findById($userId);
        if (!$user) {
            $_SESSION['error'] = 'User not found.';
            header('Location: /login');
            exit;
        }

        // Fetch recent projects for the user
        $projects = (new Project())->getRecentProjectsByUserId($userId);

        // Fetch recent tasks for the user
        $tasks = (new Task())->getRecentTasksByUserId($userId);

        // Include the dashboard view
        include __DIR__ . '/../Views/Dashboard/index.php';
    }
}