<?php
namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Models\Project;
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
            header('Location: /projects/index.php');
            exit;
        }

        $tasks = (new \App\Models\Task())->getByProjectId($id);
        include __DIR__ . '/../views/projects/view.php';
    }

    public function createForm() {
        include __DIR__ . '/../views/projects/create.php';
    }

    public function create($data) {
        $middleware = new AuthMiddleware();
        $middleware->hasPermission('create_projects');

        if (!isset($data['csrf_token']) || $data['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['error'] = 'Invalid CSRF token.';
            header('Location: /projects/create.php');
            exit;
        }

        $validator = new Validator($data, [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'status_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            $_SESSION['error'] = 'Validation failed: ' . implode(', ', $validator->errors());
            header('Location: /projects/create.php');
            exit;
        }

        $project = new Project();
        $project->name = htmlspecialchars($data['name']);
        $project->description = htmlspecialchars($data['description'] ?? null);
        $project->status_id = $data['status_id'];
        $project->company_id = $_SESSION['user']['company_id'];
        $project->save();

        $_SESSION['success'] = 'Project created successfully.';
        header('Location: /projects/index.php');
        exit;
    }

    public function editForm($id) {
        $project = (new Project())->find($id);
        if (!$project) {
            $_SESSION['error'] = 'Project not found.';
            header('Location: /projects/index.php');
            exit;
        }
        include __DIR__ . '/../views/projects/edit.php';
    }

    public function update($data, $id) {
        $middleware = new AuthMiddleware();
        $middleware->hasPermission('edit_projects');

        if (!isset($data['csrf_token']) || $data['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['error'] = 'Invalid CSRF token.';
            header("Location: /projects/edit.php?id=$id");
            exit;
        }

        $validator = new Validator($data, [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'status_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            $_SESSION['error'] = 'Validation failed: ' . implode(', ', $validator->errors());
            header("Location: /projects/edit.php?id=$id");
            exit;
        }

        $project = (new Project())->find($id);
        if (!$project) {
            $_SESSION['error'] = 'Project not found.';
            header('Location: /projects/index.php');
            exit;
        }

        $project->name = htmlspecialchars($data['name']);
        $project->description = htmlspecialchars($data['description'] ?? null);
        $project->status_id = $data['status_id'];
        $project->save();

        $_SESSION['success'] = 'Project updated successfully.';
        header('Location: /projects/index.php');
        exit;
    }

    public function delete($id) {
        $middleware = new AuthMiddleware();
        $middleware->hasPermission('delete_projects');

        $project = (new Project())->find($id);
        if (!$project) {
            $_SESSION['error'] = 'Project not found.';
            header('Location: /projects/index.php');
            exit;
        }

        $project->is_deleted = true;
        $project->save();

        $_SESSION['success'] = 'Project deleted successfully.';
        header('Location: /projects/index.php');
        exit;
    }
}