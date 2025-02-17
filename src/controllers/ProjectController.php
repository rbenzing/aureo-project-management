<?php
namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Models\Project;
use App\Models\Company;
use App\Utils\Validator;

class ProjectController {
    public function __construct() {
        $middleware = new AuthMiddleware();
        $middleware->hasPermission('view_projects'); // Default permission for all actions
    }

    public function index() {
        $projects = (new Project())->getAllPaginated(10); // Paginate results

        include __DIR__ . '/../views/projects/index.php';
    }

    public function view($id) {
        $project = (new Project())->find($id);
        if (!$project) {
            $_SESSION['error'] = 'Project not found.';
            header('Location: /projects');
            exit;
        }

        $tasks = (new \App\Models\Project())->getProjectTasks($id);

        include __DIR__ . '/../views/projects/view.php';
    }

    public function create() {
        $middleware = new AuthMiddleware();
        $middleware->hasPermission('create_projects');

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_createproject'])) {
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                $_SESSION['error'] = 'Invalid CSRF token.';
                header('Location: /create_project');
                exit;
            }

            $validator = new Validator($_POST, [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string|max:500',
                'status_id' => 'required|integer',
            ]);

            if ($validator->fails()) {
                $_SESSION['error'] = 'Validation failed: ' . implode(', ', $validator->errors());
                header('Location: /create_project');
                exit;
            }

            $project = new Project();
            $project->name = htmlspecialchars($_POST['name']);
            $project->description = htmlspecialchars($_POST['description'] ?? null);
            $project->status_id = $_POST['status_id'];
            $project->company_id = $_POST['company_id'];
            $project->save();

            $_SESSION['success'] = 'Project created successfully.';
            header('Location: /projects');
            exit;
        }

        $statuses = (new Project())->getAllStatuses(); // Get project statuses
        $companies = (new Company())->getAll(); // Get companies

        include __DIR__ . '/../views/projects/create.php';
    }

    public function edit() {
        $id = $_GET['id'];
        $project = (new Project())->find($id);
        if (!$project) {
            $_SESSION['error'] = 'Project not found.';
            header('Location: /projects');
            exit;
        }
        include __DIR__ . '/../views/projects/edit.php';
    }

    public function update($id) {
        $middleware = new AuthMiddleware();
        $middleware->hasPermission('edit_projects');

        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['error'] = 'Invalid CSRF token.';
            header("Location: /edit_project?id=$id");
            exit;
        }

        $validator = new Validator($_POST, [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'status_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            $_SESSION['error'] = 'Validation failed: ' . implode(', ', $validator->errors());
            header("Location: /edit_project?id=$id");
            exit;
        }

        $project = (new Project())->find($id);
        if (!$project) {
            $_SESSION['error'] = 'Project not found.';
            header('Location: /projects');
            exit;
        }

        $project->name = htmlspecialchars($_POST['name']);
        $project->description = htmlspecialchars($_POST['description'] ?? null);
        $project->status_id = $_POST['status_id'];
        $project->save();

        $_SESSION['success'] = 'Project updated successfully.';
        header('Location: /projects');
        exit;
    }

    public function delete($id) {
        $middleware = new AuthMiddleware();
        $middleware->hasPermission('delete_projects');

        $project = (new Project())->find($id);
        if (!$project) {
            $_SESSION['error'] = 'Project not found.';
            header('Location: /projects');
            exit;
        }

        $project->is_deleted = true;
        $project->save();

        $_SESSION['success'] = 'Project deleted successfully.';
        header('Location: /projects');
        exit;
    }
}