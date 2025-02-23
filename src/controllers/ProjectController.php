<?php
namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Models\Project;
use App\Models\Task;
use App\Models\Company;
use App\Utils\Validator;

class ProjectController
{
    private $authMiddleware;
    private $csrfMiddleware;

    public function __construct()
    {
        // Ensure the user has the required permission
        $this->authMiddleware = new AuthMiddleware();
        $this->csrfMiddleware = new CsrfMiddleware();
        $this->authMiddleware->hasPermission('view_projects'); // Default permission for all actions
    }

    /**
     * Display a list of projects (paginated).
     */
    public function index($requestMethod, $data)
    {
        // Fetch all projects from the database (paginated)
        $limit = 10; // Number of projects per page
        $page = isset($data['page']) ? intval($data['page']) : 1;
        $projects = (new Project())->getAllPaginated($limit, $page);

        // Prepare pagination data
        $totalProjects = (new Project())->countAll();
        $totalPages = ceil($totalProjects / $limit);
        $prevPage = $page > 1 ? $page - 1 : null;
        $nextPage = $page < $totalPages ? $page + 1 : null;

        $pagination = [
            'prev_page' => $prevPage,
            'next_page' => $nextPage,
        ];

        include __DIR__ . '/../Views/Projects/index.php';
    }

    /**
     * View details of a specific project.
     */
    public function view($requestMethod, $data)
    {
        $id = $data['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = 'Invalid project ID.';
            header('Location: /projects');
            exit;
        }

        // Fetch a single project by ID
        $project = (new Project())->find($id);
        if (!$project) {
            $_SESSION['error'] = 'Project not found.';
            header('Location: /projects');
            exit;
        }

        // Fetch tasks associated with the project
        $tasksByStatus = (new Task())->getByProjectId($id);

        include __DIR__ . '/../Views/Projects/view.php';
    }

    /**
     * Show the form to create a new project.
     */
    public function createForm($requestMethod, $data)
    {
        $this->authMiddleware->hasPermission('create_projects');

        // Fetch all projects and statuses for the form
        $statuses = (new Project())->getAllStatuses();
        $companies = (new Company())->getAll();

        include __DIR__ . '/../Views/Projects/create.php';
    }

    /**
     * Create a new project.
     */
    public function create($requestMethod, $data)
    {
        if ($requestMethod === 'POST') {
            $this->authMiddleware->hasPermission('create_projects');

            // Validate input data
            $validator = new Validator($data, [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string|max:500',
                'status_id' => 'required|integer',
                'company_id' => 'required|integer',
            ]);
            if ($validator->fails()) {
                $_SESSION['error'] = 'Validation failed: ' . implode(', ', $validator->errors());
                header('Location: /projects/create');
                exit;
            }

            // Create the project
            $project = new Project();
            $project->name = htmlspecialchars($data['name']);
            $project->description = htmlspecialchars($data['description'] ?? null);
            $project->status_id = intval($data['status_id']);
            $project->company_id = intval($data['company_id']);
            $project->save();

            $_SESSION['success'] = 'Project created successfully.';
            header('Location: /projects');
            exit;
        }

        // Render the create form
        $this->createForm($requestMethod, $data);
    }

    /**
     * Show the form to edit an existing project.
     */
    public function editForm($requestMethod, $data)
    {
        $id = $data['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = 'Invalid project ID.';
            header('Location: /projects');
            exit;
        }

        $this->authMiddleware->hasPermission('edit_projects');

        // Fetch the project
        $project = (new Project())->find($id);
        if (!$project) {
            $_SESSION['error'] = 'Project not found.';
            header('Location: /projects');
            exit;
        }

        // Fetch all projects and statuses for the form
        $statuses = (new Project())->getAllStatuses();
        $companies = (new Company())->getAll();

        include __DIR__ . '/../Views/Projects/edit.php';
    }

    /**
     * Update an existing project.
     */
    public function update($requestMethod, $data)
    {
        if ($requestMethod === 'POST') {
            $id = $data['id'] ?? null;
            if (!$id) {
                $_SESSION['error'] = 'Invalid project ID.';
                header('Location: /projects');
                exit;
            }

            $this->authMiddleware->hasPermission('edit_projects');

            // Validate input data
            $validator = new Validator($data, [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string|max:500',
                'status_id' => 'required|integer',
                'company_id' => 'required|integer',
            ]);
            if ($validator->fails()) {
                $_SESSION['error'] = 'Validation failed: ' . implode(', ', $validator->errors());
                header("Location: /projects/edit/$id");
                exit;
            }

            // Update the project
            $project = (new Project())->find($id);
            if (!$project) {
                $_SESSION['error'] = 'Project not found.';
                header('Location: /projects');
                exit;
            }

            $project->name = htmlspecialchars($data['name']);
            $project->description = htmlspecialchars($data['description'] ?? null);
            $project->status_id = intval($data['status_id']);
            $project->company_id = intval($data['company_id']);
            $project->save();

            $_SESSION['success'] = 'Project updated successfully.';
            header('Location: /projects');
            exit;
        }

        // Fetch the project for the edit form
        $this->editForm($requestMethod, $data);
    }

    /**
     * Delete a project (soft delete).
     */
    public function delete($requestMethod, $data)
    {
        if ($requestMethod === 'POST') {
            $id = $data['id'] ?? null;
            if (!$id) {
                $_SESSION['error'] = 'Invalid project ID.';
                header('Location: /projects');
                exit;
            }

            $this->authMiddleware->hasPermission('delete_projects');

            // Soft delete the project
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

        // Fetch the project for the delete confirmation form
        $id = $data['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = 'Invalid project ID.';
            header('Location: /projects');
            exit;
        }

        $project = (new Project())->find($id);
        if (!$project) {
            $_SESSION['error'] = 'Project not found.';
            header('Location: /projects');
            exit;
        }

        include __DIR__ . '/../Views/Projects/delete.php';
    }
}