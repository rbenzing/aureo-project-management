<?php
namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Models\Milestone;
use App\Models\Project;
use App\Utils\Validator;

class MilestoneController
{
    private $authMiddleware;
    private $csrfMiddleware;

    public function __construct()
    {
        // Ensure the user has the required permission
        $this->authMiddleware = new AuthMiddleware();
        $this->csrfMiddleware = new CsrfMiddleware();
        $this->authMiddleware->hasPermission('view_milestones'); // Default permission for all actions
    }

    /**
     * Display a list of milestones (paginated).
     */
    public function index($requestMethod, $data)
    {
        // Fetch all milestones from the database (paginated)
        $limit = 10; // Number of milestones per page
        $page = isset($data['page']) ? intval($data['page']) : 1;
        $milestones = (new Milestone())->getAllPaginated($limit, $page);

        // Prepare pagination data
        $totalMilestones = (new Milestone())->countAll();
        $totalPages = ceil($totalMilestones / $limit);
        $prevPage = $page > 1 ? $page - 1 : null;
        $nextPage = $page < $totalPages ? $page + 1 : null;

        $pagination = [
            'prev_page' => $prevPage,
            'next_page' => $nextPage,
        ];

        include __DIR__ . '/../Views/Milestones/index.php';
    }

    /**
     * View details of a specific milestone.
     */
    public function view($requestMethod, $data)
    {
        $id = $data['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = 'Invalid milestone ID.';
            header('Location: /milestones');
            exit;
        }

        // Fetch a single milestone by ID
        $milestone = (new Milestone())->find($id);
        if (!$milestone) {
            $_SESSION['error'] = 'Milestone not found.';
            header('Location: /milestones');
            exit;
        }

        $project = (new Project())->find($milestone->project_id);

        // Render the view
        include __DIR__ . '/../Views/Milestones/view.php';
    }

    /**
     * Show the form to create a new milestone.
     */
    public function createForm($requestMethod, $data)
    {
        $this->authMiddleware->hasPermission('create_milestones');

        // Fetch all projects and statuses for the form
        $projects = (new Project())->getAll();
        $statuses = (new Milestone())->getMilestoneStatuses();

        include __DIR__ . '/../Views/Milestones/create.php';
    }

    /**
     * Create a new milestone.
     */
    public function create($requestMethod, $data)
    {
        if ($requestMethod === 'POST') {
            $this->authMiddleware->hasPermission('create_milestones');

            // Validate input data
            $validator = new Validator($data, [
                'title' => 'required|string|max:255',
                'description' => 'nullable|string|max:500',
                'due_date' => 'nullable|date',
                'status_id' => 'required|integer',
                'project_id' => 'required|integer',
            ]);
            if ($validator->fails()) {
                $_SESSION['error'] = 'Validation failed: ' . implode(', ', $validator->errors());
                header('Location: /milestones/create');
                exit;
            }

            // Create the milestone
            $milestone = new Milestone();
            $milestone->title = htmlspecialchars($data['title']);
            $milestone->description = htmlspecialchars($data['description'] ?? null);
            $milestone->due_date = $data['due_date'] ?? null;
            $milestone->status_id = intval($data['status_id']);
            $milestone->project_id = intval($data['project_id']);
            $milestone->save();

            $_SESSION['success'] = 'Milestone created successfully.';
            header('Location: /milestones');
            exit;
        }

        // Render the create form
        $this->createForm($requestMethod, $data);
    }

    /**
     * Show the form to edit an existing milestone.
     */
    public function editForm($requestMethod, $data)
    {
        $id = $data['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = 'Invalid milestone ID.';
            header('Location: /milestones');
            exit;
        }

        $this->authMiddleware->hasPermission('edit_milestones');

        // Fetch the milestone
        $milestone = (new Milestone())->find($id);
        if (!$milestone) {
            $_SESSION['error'] = 'Milestone not found.';
            header('Location: /milestones');
            exit;
        }

        // Fetch all projects and statuses for the form
        $projects = (new Project())->getAll();
        $statuses = (new Milestone())->getMilestoneStatuses();

        include __DIR__ . '/../Views/Milestones/edit.php';
    }

    /**
     * Update an existing milestone.
     */
    public function update($requestMethod, $data)
    {
        if ($requestMethod === 'POST') {
            $id = $data['id'] ?? null;
            if (!$id) {
                $_SESSION['error'] = 'Invalid milestone ID.';
                header('Location: /milestones');
                exit;
            }

            $this->authMiddleware->hasPermission('edit_milestones');

            // Validate input data
            $validator = new Validator($data, [
                'title' => 'required|string|max:255',
                'description' => 'nullable|string|max:500',
                'due_date' => 'nullable|date',
                'status_id' => 'required|integer',
                'project_id' => 'required|integer',
            ]);
            if ($validator->fails()) {
                $_SESSION['error'] = 'Validation failed: ' . implode(', ', $validator->errors());
                header("Location: /milestones/edit/$id");
                exit;
            }

            // Update the milestone
            $milestone = (new Milestone())->find($id);
            if (!$milestone) {
                $_SESSION['error'] = 'Milestone not found.';
                header('Location: /milestones');
                exit;
            }

            $milestone->title = htmlspecialchars($data['title']);
            $milestone->description = htmlspecialchars($data['description'] ?? null);
            $milestone->due_date = $data['due_date'] ?? null;
            $milestone->status_id = intval($data['status_id']);
            $milestone->project_id = intval($data['project_id']);
            $milestone->save();

            $_SESSION['success'] = 'Milestone updated successfully.';
            header('Location: /milestones');
            exit;
        }

        // Fetch the milestone for the edit form
        $this->editForm($requestMethod, $data);
    }

    /**
     * Delete a milestone (soft delete).
     */
    public function delete($requestMethod, $data)
    {
        if ($requestMethod === 'POST') {
            $id = $data['id'] ?? null;
            if (!$id) {
                $_SESSION['error'] = 'Invalid milestone ID.';
                header('Location: /milestones');
                exit;
            }

            $this->authMiddleware->hasPermission('delete_milestones');

            // Soft delete the milestone
            $milestone = (new Milestone())->find($id);
            if (!$milestone) {
                $_SESSION['error'] = 'Milestone not found.';
                header('Location: /milestones');
                exit;
            }

            $milestone->is_deleted = true;
            $milestone->save();

            $_SESSION['success'] = 'Milestone deleted successfully.';
            header('Location: /milestones');
            exit;
        }

        // Fetch the milestone for the delete confirmation form
        $id = $data['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = 'Invalid milestone ID.';
            header('Location: /milestones');
            exit;
        }

        $milestone = (new Milestone())->find($id);
        if (!$milestone) {
            $_SESSION['error'] = 'Milestone not found.';
            header('Location: /milestones');
            exit;
        }

        // Render the delete confirmation form
        include __DIR__ . '/../Views/Milestones/delete.php';
    }
}