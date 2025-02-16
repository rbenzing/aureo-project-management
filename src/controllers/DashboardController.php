<?php
namespace App\Controllers;

use App\Models\Project;
use App\Models\Task;

class DashboardController {
    /**
     * Display the dashboard page.
     */
    public function index() {
        // Ensure the user is logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        // Fetch the logged-in user's details
        $userId = $_SESSION['user_id'];

        // Fetch recent projects for the user
        $projects = (new Project())->getRecentProjectsByUser($userId);

        // Fetch recent tasks for the user
        $tasks = (new Task())->getRecentTasksByUser($userId);

        // Include the dashboard view
        include __DIR__ . '/../views/dashboard/index.php';
    }
}