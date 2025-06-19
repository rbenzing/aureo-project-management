<?php
// file: Controllers/ProjectController.php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Config;
use App\Middleware\AuthMiddleware;
use App\Models\Project;
use App\Models\Task;
use App\Models\Company;
use App\Models\Template;
use App\Models\User;
use App\Utils\Validator;
use App\Utils\Sort;
use RuntimeException;
use InvalidArgumentException;
use App\Services\SecurityService;

class ProjectController
{
    private AuthMiddleware $authMiddleware;
    private Project $projectModel;
    private Task $taskModel;
    private Company $companyModel;
    private User $userModel;
    private Template $templateModel;

    public function __construct()
    {
        $this->authMiddleware = new AuthMiddleware();
        $this->projectModel = new Project();
        $this->taskModel = new Task();
        $this->userModel = new User();
        $this->companyModel = new Company();
        $this->templateModel = new Template();
    }

    /**
     * Display paginated list of projects
     * @param string $requestMethod
     * @param array $data
     * @throws RuntimeException
     */
    public function index(string $requestMethod, array $data): void
    {
        try {
            $this->authMiddleware->hasPermission('view_projects');

            $page = isset($data['page']) ? max(1, intval($data['page'])) : 1;
            $settingsService = \App\Services\SettingsService::getInstance();
            $limit = $settingsService->getResultsPerPage();

            // Extract filter parameters from request
            $searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
            $statusFilter = isset($_GET['status_id']) ? (int)$_GET['status_id'] : null;
            $companyFilter = isset($_GET['company_id']) ? (int)$_GET['company_id'] : null;
            $sortField = isset($_GET['sort']) ? $_GET['sort'] : 'updated_at';
            $sortDirection = isset($_GET['dir']) && $_GET['dir'] === 'asc' ? 'asc' : 'desc';
            $viewBy = isset($_GET['by']) ? $_GET['by'] : 'tasks';

            // For task sorting within projects
            $taskSortField = isset($_GET['task_sort']) ? $_GET['task_sort'] : 'priority';
            $taskSortDir = isset($_GET['task_dir']) && $_GET['task_dir'] === 'asc' ? 'asc' : 'desc';

            // Build filter array for BaseModel::getAll method
            $filters = ['is_deleted' => 0];

            if (!empty($searchQuery)) {
                $filters['search'] = $searchQuery;
            }

            if (!empty($statusFilter)) {
                $filters['status_id'] = $statusFilter;
            }

            if (!empty($companyFilter)) {
                $filters['company_id'] = $companyFilter;
            }

            // Get paginated projects with filters and sorting
            $results = $this->projectModel->getAll($filters, $page, $limit, $sortField, $sortDirection);
            $totalProjects = $results['total'];

            // Replace with projects with details for better display
            $projectIds = array_map(function ($project) {
                return $project->id;
            }, $results['records']);

            // Get detailed projects for the IDs we found
            $projects = [];
            foreach ($projectIds as $projectId) {
                $project = $this->projectModel->findWithDetails($projectId);
                
                // Sort tasks if in tasks view
                if ($viewBy === 'tasks' && isset($project->tasks) && is_array($project->tasks)) {
                    $project->tasks = Sort::sortObjects($project->tasks, $taskSortField, $taskSortDir);
                }
                
                $projects[] = $project;
            }

            // Load companies for the filter dropdown
            $companies = $this->companyModel->getAllCompanies();

            // Calculate project statistics for quick stats bar
            $projectStats = [
                'total' => $totalProjects,
                'in_progress' => $this->projectModel->count(['status_id' => 2, 'is_deleted' => 0]),
                'completed' => $this->projectModel->count(['status_id' => 3, 'is_deleted' => 0]),
                'on_hold' => $this->projectModel->count(['status_id' => 4, 'is_deleted' => 0]),
                'delayed' => $this->projectModel->count(['status_id' => 6, 'is_deleted' => 0])
            ];

            $totalPages = ceil($totalProjects / $limit);

            include __DIR__ . '/../Views/Projects/index.php';
        } catch (\Exception $e) {
            error_log("Exception in ProjectController::index: " . $e->getMessage());
            $_SESSION['error'] = 'An error occurred while fetching projects.';
            header('Location: /dashboard');
            exit;
        }
    }

    /**
     * View project details
     * @param string $requestMethod
     * @param array $data
     * @throws RuntimeException
     */
    public function view(string $requestMethod, array $data): void
    {
        try {
            $this->authMiddleware->hasPermission('view_projects');

            $id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
            if (!$id) {
                throw new InvalidArgumentException('Invalid project ID');
            }

            $project = $this->projectModel->findWithDetails($id);
            if (!$project || $project->is_deleted) {
                throw new InvalidArgumentException('Project not found');
            }

            // For task sorting within project view
            $taskSortField = isset($_GET['task_sort']) ? $_GET['task_sort'] : 'priority';
            $taskSortDir = isset($_GET['task_dir']) && $_GET['task_dir'] === 'asc' ? 'asc' : 'desc';
            
            // Sort tasks if needed
            if (isset($project->tasks) && is_array($project->tasks)) {
                $project->tasks = Sort::sortObjects($project->tasks, $taskSortField, $taskSortDir);
            }

            $tasksByStatus = $this->taskModel->getByProjectId($id);

            include __DIR__ . '/../Views/Projects/view.php';
        } catch (InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /projects');
            exit;
        } catch (\Exception $e) {
            error_log("Exception in ProjectController::view: " . $e->getMessage());
            $_SESSION['error'] = 'An error occurred while fetching project details.';
            header('Location: /projects');
            exit;
        }
    }

    /**
     * Display project creation form
     * @param string $requestMethod
     * @param array $data
     * @throws RuntimeException
     */
    public function createForm(string $requestMethod, array $data): void
    {
        try {
            $this->authMiddleware->hasPermission('create_projects');

            $users = $this->userModel->getAllUsers();
            $companies = $this->companyModel->getAllCompanies();
            $statuses = $this->projectModel->getAllStatuses();

            // Get company ID from user session if available
            $companyId = $_SESSION['user']['profile']['company_id'] ?? null;
            // Load templates available for this company or global templates
            $templates = $this->templateModel->getAvailableTemplates('project', $companyId);

            include __DIR__ . '/../Views/Projects/create.php';
        } catch (\Exception $e) {
            error_log("Exception in ProjectController::createForm: " . $e->getMessage());
            $_SESSION['error'] = 'An error occurred while loading the creation form.';
            header('Location: /projects');
            exit;
        }
    }

    /**
     * Create new project
     * @param string $requestMethod
     * @param array $data
     * @throws RuntimeException
     */
    public function create(string $requestMethod, array $data): void
    {
        if ($requestMethod !== 'POST') {
            $this->createForm($requestMethod, $data);
            return;
        }

        try {
            $this->authMiddleware->hasPermission('create_projects');

            $validator = new Validator($data, [
                'name' => 'required|string|max:255|min:5',
                'description' => 'nullable|string|max:500',
                'status_id' => 'required|integer|exists:statuses_project,id',
                'owner_id' => 'required|integer|exists:users,id',
                'company_id' => 'required|integer|exists:companies,id',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after:start_date',
                'template_id' => 'nullable|integer|exists:templates,id'
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException(implode(', ', $validator->errors()));
            }

            // If a template was selected but no description provided, load the template content
            if (!empty($data['template_id']) && empty($data['description'])) {
                $templateId = filter_var($data['template_id'], FILTER_VALIDATE_INT);
                if ($templateId) {
                    $template = $this->templateModel->find($templateId);
                    if ($template && !$template->is_deleted) {
                        $data['description'] = $template->description;
                    }
                }
            }

            $projectData = [
                'name' => htmlspecialchars($data['name']),
                'description' => isset($data['description']) ? htmlspecialchars($data['description']) : null,
                'owner_id' => $data['owner_id'] ? filter_var($data['owner_id'], FILTER_VALIDATE_INT) : null,
                'status_id' => $data['status_id'] ? filter_var($data['status_id'], FILTER_VALIDATE_INT) : null,
                'company_id' => $data['company_id'] ? filter_var($data['company_id'], FILTER_VALIDATE_INT) : null,
                'start_date' => $data['start_date'] ?? null,
                'end_date' => $data['end_date'] ?? null,
                'key_code' => $this->projectModel->transformKeyCodeFormat($data['name'])
            ];

            $projectId = $this->projectModel->create($projectData);

            $_SESSION['success'] = 'Project created successfully.';
            header('Location: /projects/view/' . $projectId);
            exit;
        } catch (InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            $_SESSION['form_data'] = $data;
            header('Location: /projects/create');
            exit;
        } catch (\Exception $e) {
            $securityService = SecurityService::getInstance();
            $_SESSION['error'] = $securityService->handleError($e, 'ProjectController::create', 'An error occurred while creating the project.');
            header('Location: /projects/create');
            exit;
        }
    }

    /**
     * Display project edit form
     * @param string $requestMethod
     * @param array $data
     * @throws RuntimeException
     */
    public function editForm(string $requestMethod, array $data): void
    {
        try {
            $this->authMiddleware->hasPermission('edit_projects');

            $id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
            if (!$id) {
                throw new InvalidArgumentException('Invalid project ID');
            }

            $project = $this->projectModel->findWithDetails($id);
            if (!$project || $project->is_deleted) {
                throw new InvalidArgumentException('Project not found');
            }

            $companies = $this->companyModel->getAll(['is_deleted' => 0]);
            $statuses = $this->projectModel->getAllStatuses();

            // Get company ID from project
            $companyId = $project->company_id ?? null;
            // Load templates available for this company or global templates
            $templates = $this->templateModel->getAvailableTemplates('project', $companyId);

            include __DIR__ . '/../Views/Projects/edit.php';
        } catch (InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /projects');
            exit;
        } catch (\Exception $e) {
            error_log("Exception in ProjectController::editForm: " . $e->getMessage());
            $_SESSION['error'] = 'An error occurred while loading the edit form.';
            header('Location: /projects');
            exit;
        }
    }

    /**
     * Update existing project
     * @param string $requestMethod
     * @param array $data
     * @throws RuntimeException
     */
    public function update(string $requestMethod, array $data): void
    {
        try {
            $this->authMiddleware->hasPermission('edit_projects');

            $id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
            if (!$id) {
                throw new InvalidArgumentException('Invalid project ID');
            }

            $validator = new Validator($data, [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string|max:500',
                'status_id' => 'required|integer|exists:statuses_project,id',
                'company_id' => 'nullable|integer|exists:companies,id',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after:start_date',
                'template_id' => 'nullable|integer|exists:templates,id'
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException(implode(', ', $validator->errors()));
            }

            // If a template was selected but no description provided, load the template content
            if (!empty($data['template_id']) && empty($data['description'])) {
                $templateId = filter_var($data['template_id'], FILTER_VALIDATE_INT);
                if ($templateId) {
                    $template = $this->templateModel->find($templateId);
                    if ($template && !$template->is_deleted) {
                        $data['description'] = $template->description;
                    }
                }
            }

            $projectData = [
                'name' => htmlspecialchars($data['name']),
                'description' => isset($data['description']) ?
                    htmlspecialchars($data['description']) : null,
                'status_id' => filter_var($data['status_id'], FILTER_VALIDATE_INT),
                'company_id' => filter_var($data['company_id'], FILTER_VALIDATE_INT),
                'start_date' => $data['start_date'] ?? null,
                'end_date' => $data['end_date'] ?? null
            ];

            $this->projectModel->update($id, $projectData);

            $_SESSION['success'] = 'Project updated successfully.';
            header('Location: /projects/view/' . $id);
            exit;
        } catch (InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            $_SESSION['form_data'] = $data;
            header("Location: /projects/edit/{$id}");
            exit;
        } catch (\Exception $e) {
            error_log("Exception in ProjectController::update: " . $e->getMessage());
            $_SESSION['error'] = 'An error occurred while updating the project.';
            header("Location: /projects/edit/{$id}");
            exit;
        }
    }

    /**
     * Delete project (soft delete)
     * @param string $requestMethod
     * @param array $data
     * @throws RuntimeException
     */
    public function delete(string $requestMethod, array $data): void
    {
        if ($requestMethod !== 'POST') {
            $_SESSION['error'] = 'Invalid request method.';
            header('Location: /projects');
            exit;
        }

        try {
            $this->authMiddleware->hasPermission('delete_projects');

            $id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
            if (!$id) {
                throw new InvalidArgumentException('Invalid project ID');
            }

            $project = $this->projectModel->find($id);
            if (!$project || $project->is_deleted) {
                throw new InvalidArgumentException('Project not found');
            }

            // Check if project has active tasks
            if ($this->taskModel->count(['project_id' => $id, 'is_deleted' => 0]) > 0) {
                throw new InvalidArgumentException('Cannot delete project with active tasks');
            }

            $this->projectModel->update($id, ['is_deleted' => true]);

            $_SESSION['success'] = 'Project deleted successfully.';
            header('Location: /projects');
            exit;
        } catch (InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /projects');
            exit;
        } catch (\Exception $e) {
            error_log("Exception in ProjectController::delete: " . $e->getMessage());
            $_SESSION['error'] = 'An error occurred while deleting the project.';
            header('Location: /projects');
            exit;
        }
    }
}
