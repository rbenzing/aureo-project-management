<?php
// file: Controllers/SprintController.php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Config;
use App\Middleware\AuthMiddleware;
use App\Models\Sprint;
use App\Models\Project;
use App\Models\Task;
use App\Utils\Validator;
use RuntimeException;
use InvalidArgumentException;

class SprintController
{
    private AuthMiddleware $authMiddleware;
    private Sprint $sprintModel;
    private Project $projectModel;
    private Task $taskModel;

    public function __construct()
    {
        $this->authMiddleware = new AuthMiddleware();
        $this->authMiddleware->hasPermission('manage_sprints');
        $this->sprintModel = new Sprint();
        $this->projectModel = new Project();
        $this->taskModel = new Task();
    }

    /**
     * Display paginated list of sprints
     * @param string $requestMethod
     * @param array $data
     * @throws RuntimeException
     */
    public function index(string $requestMethod, array $data): void
    {
        try {
            $project_id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
            
            $page = isset($data['page']) ? max(1, intval($data['page'])) : 1;
            $limit = Config::get('max_pages', 10);

            if (!empty($project_id)) {
                $project = $this->projectModel->findWithDetails($project_id);
                
            } else {
                $projects = $this->projectModel->getAllWithDetails($limit, $page);
            }
            $sprints = $this->sprintModel->getAllWithTasks($limit, $page);

            include __DIR__ . '/../Views/Sprints/index.php';
        } catch (InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /sprints');
            exit;
        } catch (\Exception $e) {
            error_log("Exception in SprintController::index: " . $e->getMessage());
            $_SESSION['error'] = 'An error occurred while fetching sprints.';
            header('Location: /dashboard');
            exit;
        }
    }

    /**
     * View sprint details
     * @param string $requestMethod
     * @param array $data
     * @throws RuntimeException
     */
    public function view(string $requestMethod, array $data): void
    {
        try {
            $this->authMiddleware->hasPermission('view_sprints');

            $id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
            if (!$id) {
                throw new InvalidArgumentException('Invalid sprint ID');
            }

            $sprint = $this->sprintModel->find($id);
            if (!$sprint || $sprint->is_deleted) {
                throw new InvalidArgumentException('Sprint not found');
            }

            $tasks = $this->sprintModel->getSprintTasks($id);
            $project = $this->projectModel->find($sprint->project_id);
            
            include __DIR__ . '/../Views/Sprints/view.php';
        } catch (InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /sprints');
            exit;
        } catch (\Exception $e) {
            error_log("Exception in SprintController::view: " . $e->getMessage());
            $_SESSION['error'] = 'An error occurred while fetching sprint details.';
            header('Location: /dashboard');
            exit;
        }
    }

    /**
     * Display sprint creation form
     * @param string $requestMethod
     * @param array $data
     * @throws RuntimeException
     */
    public function createForm(string $requestMethod, array $data): void
    {
        try {
            $this->authMiddleware->hasPermission('create_sprints');
            
            $projectId = filter_var($data['project_id'] ?? null, FILTER_VALIDATE_INT);
            if (!$projectId) {
                throw new InvalidArgumentException('Project ID is required');
            }
            
            $project = $this->projectModel->find($projectId);
            if (!$project || $project->is_deleted) {
                throw new InvalidArgumentException('Project not found');
            }
            
            include __DIR__ . '/../Views/Sprints/create.php';
        } catch (InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /sprints');
            exit;
        } catch (\Exception $e) {
            error_log("Exception in SprintController::createForm: " . $e->getMessage());
            $_SESSION['error'] = 'An error occurred while loading the sprint creation form.';
            header('Location: /dashboard');
            exit;
        }
    }

    /**
     * Create new sprint
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
            $this->authMiddleware->hasPermission('create_sprints');

            $validator = new Validator($data, [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'project_id' => 'required|integer|exists:projects,id',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
                'status_id' => 'required|integer|exists:statuses_sprint,id'
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException(implode(', ', $validator->errors()));
            }

            $sprintData = [
                'name' => htmlspecialchars($data['name']),
                'description' => isset($data['description']) ? 
                    htmlspecialchars($data['description']) : null,
                'project_id' => filter_var($data['project_id'], FILTER_VALIDATE_INT),
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'status_id' => filter_var($data['status_id'], FILTER_VALIDATE_INT)
            ];

            $sprintId = $this->sprintModel->create($sprintData);

            // Add tasks to sprint if provided
            if (!empty($data['tasks'])) {
                $taskIds = array_map('intval', $data['tasks']);
                $this->sprintModel->addTasks($sprintId, $taskIds);
            }

            $_SESSION['success'] = 'Sprint created successfully.';
            header('Location: /sprints/view/' . $sprintId);
            exit;

        } catch (InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            $_SESSION['form_data'] = $data;
            header('Location: /sprints/create/' . $data['project_id']);
            exit;
        } catch (\Exception $e) {
            error_log("Exception in SprintController::create: " . $e->getMessage());
            $_SESSION['error'] = 'An error occurred while creating the sprint.';
            header('Location: /sprints/create/' . $data['project_id']);
            exit;
        }
    }
    
    /**
     * Display sprint edit form
     * @param string $requestMethod
     * @param array $data
     * @throws RuntimeException
     */
    public function editForm(string $requestMethod, array $data): void
    {
        try {
            $this->authMiddleware->hasPermission('edit_sprints');

            $id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
            if (!$id) {
                throw new InvalidArgumentException('Invalid sprint ID');
            }

            $sprint = $this->sprintModel->find($id);
            if (!$sprint || $sprint->is_deleted) {
                throw new InvalidArgumentException('Sprint not found');
            }

            $project = $this->projectModel->find($sprint->project_id);
            if (!$project || $project->is_deleted) {
                throw new InvalidArgumentException('Associated project not found');
            }

            // Get all tasks from the project that could be added to the sprint
            $projectTasks = $this->taskModel->getByProjectId($sprint->project_id);
            
            // Get tasks that are already in the sprint
            $sprintTasks = $this->sprintModel->getSprintTasks($id);
            $sprintTaskIds = array_map(function($task) {
                return $task->id;
            }, $sprintTasks);

            include __DIR__ . '/../Views/Sprints/edit.php';
        } catch (InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /sprints/view/' . $id);
            exit;
        } catch (\Exception $e) {
            error_log("Exception in SprintController::editForm: " . $e->getMessage());
            $_SESSION['error'] = 'An error occurred while loading the edit form.';
            header('Location: /dashboard');
            exit;
        }
    }

    /**
     * Update existing sprint
     * @param string $requestMethod
     * @param array $data
     * @throws RuntimeException
     */
    public function update(string $requestMethod, array $data): void
    {
        if ($requestMethod !== 'POST') {
            $this->editForm($requestMethod, $data);
            return;
        }

        try {
            $this->authMiddleware->hasPermission('edit_sprints');

            $id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
            if (!$id) {
                throw new InvalidArgumentException('Invalid sprint ID');
            }

            $validator = new Validator($data, [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
                'status_id' => 'required|integer|exists:statuses_sprint,id'
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException(implode(', ', $validator->errors()));
            }

            // Get the existing sprint to preserve project_id
            $sprint = $this->sprintModel->find($id);
            if (!$sprint || $sprint->is_deleted) {
                throw new InvalidArgumentException('Sprint not found');
            }

            $sprintData = [
                'name' => htmlspecialchars($data['name']),
                'description' => isset($data['description']) ? 
                    htmlspecialchars($data['description']) : null,
                'project_id' => $sprint->project_id, // Preserve project_id
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'status_id' => filter_var($data['status_id'], FILTER_VALIDATE_INT)
            ];

            $this->sprintModel->update($id, $sprintData);

            // Update tasks in sprint if provided
            if (isset($data['tasks'])) {
                $taskIds = !empty($data['tasks']) ? array_map('intval', $data['tasks']) : [];
                $this->sprintModel->addTasks($id, $taskIds);
            }

            $_SESSION['success'] = 'Sprint updated successfully.';
            header('Location: /sprints/view/' . $id);
            exit;

        } catch (InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            $_SESSION['form_data'] = $data;
            header("Location: /sprints/edit/{$id}");
            exit;
        } catch (\Exception $e) {
            error_log("Exception in SprintController::update: " . $e->getMessage());
            $_SESSION['error'] = 'An error occurred while updating the sprint.';
            header("Location: /sprints/edit/{$id}");
            exit;
        }
    }

    /**
     * Delete sprint (soft delete)
     * @param string $requestMethod
     * @param array $data
     * @throws RuntimeException
     */
    public function delete(string $requestMethod, array $data): void
    {
        if ($requestMethod !== 'POST') {
            $_SESSION['error'] = 'Invalid request method.';
            header('Location: /sprints');
            exit;
        }

        try {
            $this->authMiddleware->hasPermission('delete_sprints');

            $id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
            if (!$id) {
                throw new InvalidArgumentException('Invalid sprint ID');
            }

            $sprint = $this->sprintModel->find($id);
            if (!$sprint || $sprint->is_deleted) {
                throw new InvalidArgumentException('Sprint not found');
            }

            // Store project_id for redirection after deletion
            $projectId = $sprint->project_id;

            // Get sprint tasks to check for active tasks
            $sprintTasks = $this->sprintModel->getSprintTasks($id);
            $activeTasks = array_filter($sprintTasks, function($task) {
                return $task->status_id != 6 && $task->status_id != 7; // Not completed or cancelled
            });

            // Only allow deletion if sprint has no active tasks or is not active
            if ($sprint->status_id == 2 && !empty($activeTasks)) { // 2 = active status
                throw new InvalidArgumentException('Cannot delete active sprint with incomplete tasks');
            }

            $this->sprintModel->update($id, ['is_deleted' => true]);

            $_SESSION['success'] = 'Sprint deleted successfully.';
            header('Location: /sprints/view/' . $projectId);
            exit;

        } catch (InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /sprints/view/' . ($id ?? ''));
            exit;
        } catch (\Exception $e) {
            error_log("Error in SprintController::delete: " . $e->getMessage());
            $_SESSION['error'] = 'An error occurred while deleting the sprint.';
            header('Location: /sprints/view/' . ($id ?? ''));
            exit;
        }
    }

    /**
     * Add tasks to sprint
     * @param string $requestMethod
     * @param array $data
     * @throws RuntimeException
     */
    public function addTasks(string $requestMethod, array $data): void
    {
        if ($requestMethod !== 'POST') {
            $_SESSION['error'] = 'Invalid request method.';
            header('Location: /sprints');
            exit;
        }

        try {
            $this->authMiddleware->hasPermission('edit_sprints');

            $sprintId = filter_var($data['sprint_id'] ?? null, FILTER_VALIDATE_INT);
            if (!$sprintId) {
                throw new InvalidArgumentException('Invalid sprint ID');
            }

            if (empty($data['task_ids']) || !is_array($data['task_ids'])) {
                throw new InvalidArgumentException('No tasks selected');
            }

            $taskIds = array_map('intval', $data['task_ids']);
            $this->sprintModel->addTasks($sprintId, $taskIds);

            $_SESSION['success'] = 'Tasks added to sprint successfully.';
            header('Location: /sprints/view/' . $sprintId);
            exit;

        } catch (InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /sprints/view/' . ($sprintId ?? ''));
            exit;
        } catch (\Exception $e) {
            error_log("Error in SprintController::addTasks: " . $e->getMessage());
            $_SESSION['error'] = 'An error occurred while adding tasks to the sprint.';
            header('Location: /sprints/view/' . ($sprintId ?? ''));
            exit;
        }
    }
}