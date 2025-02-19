<?php
namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Models\User;
use App\Models\Project;
use App\Models\Task;

class DashboardController {
    /**
     * Display the dashboard page.
     */
    public function index() {
        $middleware = new AuthMiddleware();
        $middleware->hasPermission('view_dashboard');

        // Ensure the user is logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        // Fetch the logged-in user's details
        $userId = $_SESSION['user_id'];
        $user = (new User())->findById($userId);
        
        // Fetch recent projects for the user
        $projects = (new Project())->getRecentProjectsByUserId($userId);

        // Fetch recent tasks for the user
        $tasks = (new Task())->getRecentTasksByUserId($userId);

        // Include the dashboard view
        include __DIR__ . '/../views/dashboard/index.php';
    }
}