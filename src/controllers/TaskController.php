<?php
// file: Controllers/TaskController.php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Config;
use App\Middleware\AuthMiddleware;
use App\Models\Task;
use App\Models\Project;
use App\Models\User;
use App\Models\Sprint;
use App\Models\Template;
use App\Utils\Validator;
use App\Utils\Time;
use App\Services\SettingsService;
use App\Services\SecurityService;
use RuntimeException;
use InvalidArgumentException;

class TaskController
{
    private AuthMiddleware $authMiddleware;
    private Task $taskModel;
    private Project $projectModel;
    private User $userModel;
    private Sprint $sprintModel;
    private Template $templateModel;

    public function __construct()
    {
        $this->authMiddleware = new AuthMiddleware();
        $this->authMiddleware->hasPermission('manage_tasks');

        $this->taskModel = new Task();
        $this->projectModel = new Project();
        $this->userModel = new User();
        $this->sprintModel = new Sprint();
        $this->templateModel = new Template();
    }

    /**
     * Display paginated list of tasks
     * @param string $requestMethod
     * @param array $data
     * @throws RuntimeException
     */
    public function index(string $requestMethod, array $data): void
    {
        try {
            $this->authMiddleware->hasPermission('view_tasks');

            $page = isset($data['page']) ? max(1, intval($data['page'])) : 1;
            $settingsService = \App\Services\SettingsService::getInstance();
            $limit = $settingsService->getResultsPerPage();
            $id = $data['id'] ?? null;

            // Get sorting parameters
            $sortField = isset($_GET['task_sort']) ? $_GET['task_sort'] : 'due_date';
            $sortDirection = isset($_GET['task_dir']) && $_GET['task_dir'] === 'desc' ? 'desc' : 'asc';

            // Check for no subtasks filter
            $noSubtasks = isset($_GET['no_subtasks']) && $_GET['no_subtasks'] == '1';

            // Determine the context based on the current route
            $currentPath = $_SERVER['REQUEST_URI'] ?? '';
            $isProjectContext = strpos($currentPath, '/tasks/project/') !== false;
            $isUserContext = strpos($currentPath, '/tasks/assigned/') !== false;
            $isUnassignedContext = strpos($currentPath, '/tasks/unassigned') !== false;

            if (!empty($id) && $isProjectContext) {
                // Project-specific tasks
                $project = $this->projectModel->findWithDetails(intval($id));
                if (!$project || $project->is_deleted) {
                    throw new InvalidArgumentException('Project not found');
                }
                $tasks = $this->taskModel->getByProjectId(intval($id), $sortField, $sortDirection);
                $totalTasks = $this->taskModel->count(['project_id' => intval($id), 'is_deleted' => 0]);
                $viewType = 'project_tasks';
            } elseif (!empty($id) && $isUserContext) {
                // User-specific tasks
                $tasks = $this->taskModel->getByUserId(intval($id), $limit, $page, $sortField, $sortDirection);
                $totalTasks = $this->taskModel->count(['assigned_to' => intval($id), 'is_deleted' => 0]);
                $viewType = 'user_tasks';
                $userId = intval($id);
            } elseif ($isUnassignedContext) {
                // Unassigned tasks
                $tasks = $this->taskModel->getUnassignedTasks($limit, $page, $sortField, $sortDirection);
                $totalTasks = $this->taskModel->countUnassignedTasks();
                $viewType = 'unassigned_tasks';
            } else {
                // All tasks
                if ($noSubtasks) {
                    $tasks = $this->taskModel->getAllWithDetailsNoSubtasks($limit, $page, $sortField, $sortDirection);
                    $totalTasks = $this->taskModel->count(['is_deleted' => 0, 'is_subtask' => 0]);
                } else {
                    $tasks = $this->taskModel->getAllWithDetails($limit, $page, $sortField, $sortDirection);
                    $totalTasks = $this->taskModel->count(['is_deleted' => 0]);
                }
                $viewType = 'all_tasks';
            }

            $totalPages = ceil($totalTasks / $limit);

            // Make sorting parameters available to the view
            $currentSortField = $sortField;
            $currentSortDirection = $sortDirection;

            include __DIR__ . '/../views/tasks/index.php';
        } catch (InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /tasks');
            exit;
        } catch (\Exception $e) {
            $securityService = SecurityService::getInstance();
            $_SESSION['error'] = $securityService->handleError($e, 'TaskController::index', 'An error occurred while fetching tasks.');
            header('Location: /dashboard');
            exit;
        }
    }

    /**
     * Display product backlog (tasks not assigned to sprints)
     * @param string $requestMethod
     * @param array $data
     * @throws RuntimeException
     */
    public function backlog(string $requestMethod, array $data): void
    {
        try {
            $this->authMiddleware->hasPermission('view_tasks');

            $page = isset($data['page']) ? max(1, intval($data['page'])) : 1;
            $settingsService = SettingsService::getInstance();
            $limit = $settingsService->getResultsPerPage();
            $projectId = isset($_GET['project_id']) ? intval($_GET['project_id']) : null;

            // Get sorting parameters (default to backlog_priority for backlog view)
            $sortField = isset($_GET['task_sort']) ? $_GET['task_sort'] : 'backlog_priority';
            $sortDirection = isset($_GET['task_dir']) && $_GET['task_dir'] === 'desc' ? 'desc' : 'asc';

            $tasks = $this->taskModel->getProductBacklog($limit, $page, $projectId, $sortField, $sortDirection);
            $totalTasks = $this->taskModel->countProductBacklog($projectId);
            $totalPages = ceil($totalTasks / $limit);

            // Get projects for filtering
            $projects = $this->projectModel->getAllWithDetails(100, 1); // Get all projects for filter

            // Set view data
            $viewType = 'backlog';
            $selectedProjectId = $projectId;

            include __DIR__ . '/../views/tasks/backlog.php';
        } catch (\Exception $e) {
            error_log("Error in TaskController::backlog: " . $e->getMessage());
            $_SESSION['error'] = 'An error occurred while loading the product backlog.';
            header('Location: /dashboard');
            exit;
        }
    }

    /**
     * Display sprint planning interface
     * @param string $requestMethod
     * @param array $data
     * @throws RuntimeException
     */
    public function sprintPlanning(string $requestMethod, array $data): void
    {
        try {
            $this->authMiddleware->hasPermission('view_tasks');

            $projectId = isset($_GET['project_id']) ? intval($_GET['project_id']) : null;

            if (!$projectId) {
                // Get all projects for selection
                $projects = $this->projectModel->getAllWithDetails(100, 1);
                $viewType = 'sprint_planning_selection';

                include __DIR__ . '/../views/tasks/sprint-planning.php';
            } else {
                // Get project details
                $project = $this->projectModel->findWithDetails($projectId);
                if (!$project) {
                    throw new RuntimeException('Project not found');
                }

                // Get available tasks for sprint planning
                $availableTasks = $this->taskModel->getAvailableForSprint($projectId);

                // Get active/planning sprints for this project
                $sprints = $this->sprintModel->getByProjectId($projectId);
                $activeSprints = array_filter($sprints, function($sprint) {
                    return in_array($sprint->status_id, [1, 2]); // Planning or Active
                });

                $viewType = 'sprint_planning';

                include __DIR__ . '/../views/tasks/sprint-planning.php';
            }
        } catch (\Exception $e) {
            error_log("Error in TaskController::sprintPlanning: " . $e->getMessage());
            $_SESSION['error'] = 'An error occurred while loading sprint planning.';
            header('Location: /dashboard');
            exit;
        }
    }

    /**
     * Update backlog priorities via AJAX
     * @param string $requestMethod
     * @param array $data
     */
    public function updateBacklogPriorities(string $requestMethod, array $data): void
    {
        if ($requestMethod !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        try {
            $this->authMiddleware->hasPermission('edit_tasks');

            // Get JSON input
            $input = json_decode(file_get_contents('php://input'), true);

            if (!isset($input['tasks']) || !is_array($input['tasks'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid input data']);
                return;
            }

            $success = true;
            foreach ($input['tasks'] as $taskData) {
                if (!isset($taskData['id']) || !isset($taskData['priority'])) {
                    continue;
                }

                $taskId = intval($taskData['id']);
                $priority = intval($taskData['priority']);

                $result = $this->taskModel->update($taskId, ['backlog_priority' => $priority]);
                if (!$result) {
                    $success = false;
                }
            }

            echo json_encode([
                'success' => $success,
                'message' => $success ? 'Backlog priorities updated successfully' : 'Some priorities failed to update'
            ]);

        } catch (\Exception $e) {
            error_log("Error in TaskController::updateBacklogPriorities: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Internal server error']);
        }
    }

    /**
     * View task details
     * @param string $requestMethod
     * @param array $data
     * @throws RuntimeException
     */
    public function view(string $requestMethod, array $data): void
    {
        try {
            $this->authMiddleware->hasPermission('view_tasks');

            $id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
            if (!$id) {
                throw new InvalidArgumentException('Invalid task ID');
            }

            $task = $this->taskModel->findWithDetails($id);
            if (!$task || $task->is_deleted) {
                throw new InvalidArgumentException('Task not found');
            }

            // Check permissions
            $userId = $_SESSION['user']['id'] ?? null;
            if ($task->assigned_to !== $userId && !$this->authMiddleware->hasPermission('manage_tasks')) {
                throw new InvalidArgumentException('You do not have permission to view this task');
            }

            $project = $this->projectModel->findWithDetails($task->project_id);
            $subtasks = $this->taskModel->getSubtasks($id);
            $activeTimer = $_SESSION['active_timer'] ?? null;

            include __DIR__ . '/../views/tasks/view.php';
        } catch (InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /tasks');
            exit;
        } catch (\Exception $e) {
            error_log("Exception in TaskController::view: " . $e->getMessage());
            $_SESSION['error'] = 'An error occurred while fetching task details.';
            header('Location: /tasks');
            exit;
        }
    }

    /**
     * Display task creation form
     * @param string $requestMethod
     * @param array $data
     * @throws RuntimeException
     */
    public function createForm(string $requestMethod, array $data): void
    {
        try {
            $this->authMiddleware->hasPermission('create_tasks');

            $projects = $this->projectModel->getAll(['is_deleted' => 0]);
            $usersResult = $this->userModel->getAll(['is_deleted' => 0], 1, 1000); // Get up to 1000 users
            $users = $usersResult['records'];
            $statuses = $this->taskModel->getTaskStatuses();

            // Get settings for default values
            $settingsService = SettingsService::getInstance();
            $taskSettings = $settingsService->getTaskSettings();
            $projectSettings = $settingsService->getProjectSettings();
            $timeSettings = $settingsService->getTimeIntervalSettings();

            // Get company ID from user session if available
            $companyId = $_SESSION['user']['profile']['company_id'] ?? null;
            // Load templates available for this company or global templates
            $templates = $this->templateModel->getAvailableTemplates('task', $companyId);

            // Initialize errors array for form validation display
            $errors = $_SESSION['errors'] ?? [];
            unset($_SESSION['errors']);

            include __DIR__ . '/../views/tasks/create.php';
        } catch (\Exception $e) {
            error_log("Exception in TaskController::createForm: " . $e->getMessage());
            $_SESSION['error'] = 'An error occurred while loading the creation form.';
            header('Location: /tasks');
            exit;
        }
    }

    /**
     * Create new task
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
            $this->authMiddleware->hasPermission('create_tasks');

            // Get settings for default values
            $settingsService = SettingsService::getInstance();
            $taskSettings = $settingsService->getTaskSettings();
            $projectSettings = $settingsService->getProjectSettings();

            // Apply default values from settings if not provided
            if (empty($data['priority'])) {
                $data['priority'] = $taskSettings['default_priority'];
            }
            if (empty($data['task_type'])) {
                $data['task_type'] = $projectSettings['default_task_type'];
            }

            // Parse time fields from minutes to seconds if provided
            if (isset($data['estimated_time']) && $data['estimated_time'] !== null && $data['estimated_time'] !== '') {
                $data['estimated_time'] = Time::parseTimeToSeconds($data['estimated_time']);
            } else {
                $data['estimated_time'] = null;
            }

            if (isset($data['time_spent']) && $data['time_spent'] !== null && $data['time_spent'] !== '') {
                $data['time_spent'] = Time::parseTimeToSeconds($data['time_spent']);
            } else {
                $data['time_spent'] = null;
            }

            if (isset($data['billable_time']) && $data['billable_time'] !== null && $data['billable_time'] !== '') {
                $data['billable_time'] = Time::parseTimeToSeconds($data['billable_time']);
            } else {
                $data['billable_time'] = null;
            }

            $validator = new Validator($data, [
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'priority' => 'required|in:none,low,medium,high',
                'status_id' => 'required|integer|exists:statuses_task,id',
                'project_id' => 'nullable|integer|exists:projects,id',
                'start_date' => 'nullable|date',
                'due_date' => 'nullable|date',
                'estimated_time' => 'nullable|integer|min:0',
                'time_spent' => 'nullable|integer|min:0',
                'billable_time' => 'nullable|integer|min:0',
                'hourly_rate' => 'nullable|integer|min:0',
                'is_hourly' => 'boolean',
                'parent_task_id' => 'nullable|integer|exists:tasks,id',
                'story_points' => 'nullable|integer|min:1|max:13',
                'acceptance_criteria' => 'nullable|string',
                'task_type' => 'required|in:story,bug,task,epic,subtask',
                'backlog_priority' => 'nullable|integer|min:1',
                'is_ready_for_sprint' => 'boolean'
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException(implode(', ', $validator->errors()));
            }

            // Determine assigned_to based on settings
            $assignedTo = $_SESSION['user']['id'];
            if (!$projectSettings['auto_assign_creator'] && !empty($data['assigned_to'])) {
                $assignedTo = filter_var($data['assigned_to'], FILTER_VALIDATE_INT);
            }

            // Handle subtask logic - if task_type is 'subtask', set is_subtask=1 and task_type='task'
            $taskType = $data['task_type'] ?? 'task';
            $isSubtask = false;

            if ($taskType === 'subtask') {
                $isSubtask = true;
                $taskType = 'task'; // Store as 'task' in database
            } elseif (isset($data['parent_task_id']) && !empty($data['parent_task_id'])) {
                $isSubtask = true; // Also set as subtask if parent task is selected
            }

            $taskData = [
                'title' => htmlspecialchars($data['title']),
                'description' => isset($data['description']) ?
                    htmlspecialchars($data['description']) : null,
                'priority' => $data['priority'],
                'status_id' => filter_var($data['status_id'], FILTER_VALIDATE_INT),
                'project_id' => filter_var($data['project_id'], FILTER_VALIDATE_INT),
                'assigned_to' => $assignedTo,
                'start_date' => $data['start_date'] ?? null,
                'due_date' => $data['due_date'] ?? null,
                'estimated_time' => isset($data['estimated_time']) && $data['estimated_time'] !== '' ?
                    $data['estimated_time'] : null,
                'time_spent' => isset($data['time_spent']) && $data['time_spent'] !== '' ?
                    $data['time_spent'] : null,
                'billable_time' => isset($data['billable_time']) && $data['billable_time'] !== '' ?
                    $data['billable_time'] : null,
                'hourly_rate' => isset($data['hourly_rate']) && $data['hourly_rate'] !== '' ?
                    filter_var($data['hourly_rate'], FILTER_VALIDATE_INT) : null,
                'is_hourly' => isset($data['is_hourly']) && $data['is_hourly'] !== '' ?
                    filter_var($data['is_hourly'], FILTER_VALIDATE_BOOLEAN) : false,
                'parent_task_id' => isset($data['parent_task_id']) && $data['parent_task_id'] !== '' ?
                    filter_var($data['parent_task_id'], FILTER_VALIDATE_INT) : null,
                'is_subtask' => $isSubtask,
                'story_points' => isset($data['story_points']) && $data['story_points'] !== '' ?
                    filter_var($data['story_points'], FILTER_VALIDATE_INT) : null,
                'acceptance_criteria' => isset($data['acceptance_criteria']) && $data['acceptance_criteria'] !== '' ?
                    htmlspecialchars($data['acceptance_criteria']) : null,
                'task_type' => $taskType,
                'backlog_priority' => isset($data['backlog_priority']) && $data['backlog_priority'] !== '' ?
                    filter_var($data['backlog_priority'], FILTER_VALIDATE_INT) : null,
                'is_ready_for_sprint' => isset($data['is_ready_for_sprint']) && $data['is_ready_for_sprint'] !== '' ?
                    filter_var($data['is_ready_for_sprint'], FILTER_VALIDATE_BOOLEAN) : false
            ];

            $taskId = $this->taskModel->create($taskData);

            $_SESSION['success'] = 'Task created successfully.';
            header('Location: /tasks/view/' . $taskId);
            exit;

        } catch (InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            $_SESSION['form_data'] = $data;
            header('Location: /tasks/create');
            exit;
        } catch (\Exception $e) {
            $securityService = SecurityService::getInstance();
            $_SESSION['error'] = $securityService->handleError($e, 'TaskController::create', 'An error occurred while creating the task.');
            header('Location: /tasks/create');
            exit;
        }
    }

    /**
     * Display task edit form
     * @param string $requestMethod
     * @param array $data
     * @throws RuntimeException
     */
    public function editForm(string $requestMethod, array $data): void
    {
        try {
            $this->authMiddleware->hasPermission('edit_tasks');

            $id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
            if (!$id) {
                throw new InvalidArgumentException('Invalid task ID');
            }

            $task = $this->taskModel->findWithDetails($id);
            if (!$task || $task->is_deleted) {
                throw new InvalidArgumentException('Task not found');
            }

            // Check permissions
            $userId = $_SESSION['user']['id'] ?? null;
            if ($task->assigned_to !== $userId && !$this->authMiddleware->hasPermission('manage_tasks')) {
                throw new InvalidArgumentException('You do not have permission to edit this task');
            }

            $projects = $this->projectModel->getAll(['is_deleted' => 0]);
            $usersResult = $this->userModel->getAll(['is_deleted' => 0], 1, 1000); // Get up to 1000 users
            $users = $usersResult['records'];
            $statuses = $this->taskModel->getTaskStatuses();

            // Get available parent tasks from the same project
            $availableParentTasks = [];
            if ($task->project_id) {
                $projectTasksOrganized = $this->taskModel->getByProjectId($task->project_id);
                // Flatten the organized array to get all tasks
                $projectTasks = [];
                foreach ($projectTasksOrganized as $statusGroup) {
                    if (is_array($statusGroup)) {
                        $projectTasks = array_merge($projectTasks, $statusGroup);
                    }
                }

                foreach ($projectTasks as $projectTask) {
                    // Only main tasks can be parents, and task can't be its own parent
                    if (!$projectTask->is_subtask && $projectTask->id !== $task->id) {
                        $availableParentTasks[] = $projectTask;
                    }
                }
            }

            // Get company ID from task's project
            $companyId = null;
            if ($task->project_id) {
                $project = $this->projectModel->find($task->project_id);
                $companyId = $project->company_id ?? null;
            }
            // Load templates available for this company or global templates
            $templates = $this->templateModel->getAvailableTemplates('task', $companyId);

            // Get settings for form display
            $settingsService = SettingsService::getInstance();
            $taskSettings = $settingsService->getTaskSettings();
            $projectSettings = $settingsService->getProjectSettings();
            $timeSettings = $settingsService->getTimeIntervalSettings();

            include __DIR__ . '/../views/tasks/edit.php';
        } catch (InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /tasks');
            exit;
        } catch (\Exception $e) {
            error_log("Exception in TaskController::editForm: " . $e->getMessage());
            $_SESSION['error'] = 'An error occurred while loading the edit form.';
            header('Location: /tasks');
            exit;
        }
    }

    /**
     * Update existing task
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
            $this->authMiddleware->hasPermission('edit_tasks');

            $id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
            if (!$id) {
                throw new InvalidArgumentException('Invalid task ID');
            }

            // Parse time fields from minutes to seconds if provided
            if (isset($data['estimated_time']) && $data['estimated_time'] !== null && $data['estimated_time'] !== '') {
                $data['estimated_time'] = Time::parseTimeToSeconds($data['estimated_time']);
            } else {
                $data['estimated_time'] = null;
            }

            if (isset($data['time_spent']) && $data['time_spent'] !== null && $data['time_spent'] !== '') {
                $data['time_spent'] = Time::parseTimeToSeconds($data['time_spent']);
            } else {
                $data['time_spent'] = null;
            }

            if (isset($data['billable_time']) && $data['billable_time'] !== null && $data['billable_time'] !== '') {
                $data['billable_time'] = Time::parseTimeToSeconds($data['billable_time']);
            } else {
                $data['billable_time'] = null;
            }

            $validator = new Validator($data, [
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'priority' => 'required|in:none,low,medium,high',
                'status_id' => 'required|integer|exists:statuses_task,id',
                'project_id' => 'nullable|integer|exists:projects,id',
                'assigned_to' => 'nullable|integer|exists:users,id',
                'parent_task_id' => 'nullable|integer|exists:tasks,id',
                'start_date' => 'nullable|date',
                'due_date' => 'nullable|date',
                'complete_date' => 'nullable|date',
                'estimated_time' => 'nullable|integer|min:0',
                'time_spent' => 'nullable|integer|min:0',
                'billable_time' => 'nullable|integer|min:0',
                'hourly_rate' => 'nullable|integer|min:0',
                'is_hourly' => 'boolean',
                'story_points' => 'nullable|integer|min:1|max:13',
                'acceptance_criteria' => 'nullable|string',
                'task_type' => 'required|in:story,bug,task,epic,subtask',
                'backlog_priority' => 'nullable|integer|min:1',
                'is_ready_for_sprint' => 'boolean'
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException(implode(', ', $validator->errors()));
            }

            // Handle subtask logic - if task_type is 'subtask', set is_subtask=1 and task_type='task'
            $taskType = $data['task_type'] ?? 'task';
            $isSubtask = 0;

            if ($taskType === 'subtask') {
                $isSubtask = 1;
                $taskType = 'task'; // Store as 'task' in database
            } elseif (isset($data['parent_task_id']) && $data['parent_task_id'] !== '') {
                $isSubtask = 1; // Also set as subtask if parent task is selected
            }

            $taskData = [
                'title' => htmlspecialchars($data['title']),
                'description' => isset($data['description']) ?
                    htmlspecialchars($data['description']) : null,
                'priority' => $data['priority'],
                'status_id' => filter_var($data['status_id'], FILTER_VALIDATE_INT),
                'project_id' => isset($data['project_id']) && $data['project_id'] !== '' ?
                    filter_var($data['project_id'], FILTER_VALIDATE_INT) :
                    ($this->taskModel->find($id)->project_id ?? null),
                'assigned_to' => isset($data['assigned_to']) && $data['assigned_to'] !== '' ?
                    filter_var($data['assigned_to'], FILTER_VALIDATE_INT) : null,
                'parent_task_id' => isset($data['parent_task_id']) && $data['parent_task_id'] !== '' ?
                    filter_var($data['parent_task_id'], FILTER_VALIDATE_INT) : null,
                'is_subtask' => $isSubtask,
                'start_date' => $data['start_date'] ?? null,
                'due_date' => $data['due_date'] ?? null,
                'complete_date' => $data['complete_date'] ?? null,
                'estimated_time' => isset($data['estimated_time']) && $data['estimated_time'] !== '' ?
                    $data['estimated_time'] : null,
                'time_spent' => isset($data['time_spent']) && $data['time_spent'] !== '' ?
                    $data['time_spent'] : null,
                'billable_time' => isset($data['billable_time']) && $data['billable_time'] !== '' ?
                    $data['billable_time'] : null,
                'hourly_rate' => isset($data['hourly_rate']) && $data['hourly_rate'] !== '' ?
                    filter_var($data['hourly_rate'], FILTER_VALIDATE_INT) : null,
                'is_hourly' => isset($data['is_hourly']) && $data['is_hourly'] !== '' ?
                    filter_var($data['is_hourly'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) : 0,
                'story_points' => isset($data['story_points']) && $data['story_points'] !== '' ?
                    filter_var($data['story_points'], FILTER_VALIDATE_INT) : null,
                'acceptance_criteria' => isset($data['acceptance_criteria']) && $data['acceptance_criteria'] !== '' ?
                    htmlspecialchars($data['acceptance_criteria']) : null,
                'task_type' => $taskType,
                'backlog_priority' => isset($data['backlog_priority']) && $data['backlog_priority'] !== '' ?
                    filter_var($data['backlog_priority'], FILTER_VALIDATE_INT) : null,
                'is_ready_for_sprint' => isset($data['is_ready_for_sprint']) && $data['is_ready_for_sprint'] !== '' ?
                    filter_var($data['is_ready_for_sprint'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) : 0
            ];

            $this->taskModel->update($id, $taskData);

            $_SESSION['success'] = 'Task updated successfully.';
            header('Location: /tasks/view/' . $id);
            exit;

        } catch (InvalidArgumentException $e) {
            // Log form data for validation errors
            error_log("Form data that failed validation: " . print_r($data, true));

            $_SESSION['error'] = Config::getErrorMessage(
                $e,
                'TaskController::update (validation)',
                'Please check your input: ' . $e->getMessage(),
                (string)$id
            );
            $_SESSION['form_data'] = $data;
            header("Location: /tasks/edit/{$id}");
            exit;
        } catch (\Exception $e) {
            // Log additional context data
            error_log("Form data received: " . print_r($data, true));
            error_log("Processed task data: " . print_r($taskData ?? [], true));

            $_SESSION['error'] = Config::getErrorMessage(
                $e,
                'TaskController::update',
                'Unable to update task. Please check your input and try again. If the problem persists, contact support.',
                (string)$id
            );
            header("Location: /tasks/edit/{$id}");
            exit;
        }
    }

    /**
     * Update task status via API (for Kanban board)
     * @param string $requestMethod
     * @param array $data
     * @throws RuntimeException
     */
    public function updateStatus(string $requestMethod, array $data): void
    {
        if ($requestMethod !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        try {
            $this->authMiddleware->hasPermission('edit_tasks');

            // Get JSON input
            $input = json_decode(file_get_contents('php://input'), true);

            if (!isset($input['task_id']) || !isset($input['status_id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Task ID and status ID are required']);
                return;
            }

            $taskId = filter_var($input['task_id'], FILTER_VALIDATE_INT);
            $statusId = filter_var($input['status_id'], FILTER_VALIDATE_INT);

            if (!$taskId || !$statusId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid task ID or status ID']);
                return;
            }

            // Fetch and validate task
            $task = $this->taskModel->find($taskId);
            if (!$task || $task->is_deleted) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Task not found']);
                return;
            }

            // Check permissions
            $userId = $_SESSION['user']['id'] ?? null;
            if ($task->assigned_to !== $userId && !$this->authMiddleware->hasPermission('manage_tasks')) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'You do not have permission to update this task']);
                return;
            }

            // Validate status exists
            $statuses = $this->taskModel->getTaskStatuses();
            $validStatus = false;
            foreach ($statuses as $status) {
                if ($status->id == $statusId) {
                    $validStatus = true;
                    break;
                }
            }

            if (!$validStatus) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid status ID']);
                return;
            }

            // Update task status
            $updateData = ['status_id' => $statusId];

            // If moving to completed status, set complete date
            if ($statusId == 6) { // Assuming 6 is completed status
                $updateData['complete_date'] = date('Y-m-d H:i:s');
            }

            $result = $this->taskModel->update($taskId, $updateData);

            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Task status updated successfully',
                    'task_id' => $taskId,
                    'new_status_id' => $statusId
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to update task status']);
            }

        } catch (\Exception $e) {
            error_log("Exception in TaskController::updateStatus: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'An error occurred while updating task status']);
        }
    }

    /**
     * Delete task (soft delete)
     * @param string $requestMethod
     * @param array $data
     * @throws RuntimeException
     */
    public function delete(string $requestMethod, array $data): void
    {
        if ($requestMethod !== 'POST') {
            $_SESSION['error'] = 'Invalid request method.';
            header('Location: /tasks');
            exit;
        }

        try {
            $this->authMiddleware->hasPermission('delete_tasks');

            $id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
            if (!$id) {
                throw new InvalidArgumentException('Invalid task ID');
            }

            $task = $this->taskModel->find($id);
            if (!$task || $task->is_deleted) {
                throw new InvalidArgumentException('Task not found');
            }

            // Check permissions
            $userId = $_SESSION['user']['id'] ?? null;
            if ($task->assigned_to !== $userId && !$this->authMiddleware->hasPermission('manage_tasks')) {
                throw new InvalidArgumentException('You do not have permission to delete this task');
            }

            $this->taskModel->update($id, ['is_deleted' => true]);

            $_SESSION['success'] = 'Task deleted successfully.';
            header('Location: /tasks');
            exit;

        } catch (InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /tasks');
            exit;
        } catch (\Exception $e) {
            error_log("Exception in TaskController::delete: " . $e->getMessage());
            $_SESSION['error'] = 'An error occurred while deleting the task.';
            header('Location: /tasks');
            exit;
        }
    }

    /**
     * Start a timer for a task
     * @param string $requestMethod
     * @param array $data
     * @throws RuntimeException
     */
    public function startTimer(string $requestMethod, array $data): void
    {
        if ($requestMethod !== 'POST') {
            $_SESSION['error'] = 'Invalid request method.';
            header('Location: /tasks');
            exit;
        }

        try {
            $this->authMiddleware->hasPermission('edit_tasks');

            $taskId = filter_var($data['task_id'] ?? null, FILTER_VALIDATE_INT);
            if (!$taskId) {
                throw new InvalidArgumentException('Invalid task ID');
            }

            // Fetch and validate task
            $task = $this->taskModel->find($taskId);
            if (!$task || $task->is_deleted) {
                throw new InvalidArgumentException('Task not found');
            }

            // Check if user has permission to track time on this task
            $userId = $_SESSION['user']['id'] ?? null;
            if ($task->assigned_to !== $userId && !$this->authMiddleware->hasPermission('manage_tasks')) {
                throw new InvalidArgumentException('You do not have permission to track time for this task');
            }

            // Check if there's already an active timer
            if (!empty($_SESSION['active_timer'])) {
                throw new InvalidArgumentException('You already have an active timer running. Please stop it first.');
            }

            // Store timer start in session with task details
            $_SESSION['active_timer'] = [
                'task_id' => $taskId,
                'task_title' => $task->title,
                'project_name' => $task->project_name ?? 'Unknown Project',
                'start_time' => time()
            ];

            // Add timer start history entry
            $this->taskModel->addTimerStartHistory($taskId, $userId);

            $_SESSION['success'] = 'Timer started successfully for: ' . htmlspecialchars($task->title);
            header("Location: /tasks/view/{$taskId}");
            exit;

        } catch (InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /tasks');
            exit;
        } catch (\Exception $e) {
            error_log("Exception in TaskController::startTimer: " . $e->getMessage());
            $_SESSION['error'] = 'An error occurred while starting the timer.';
            header('Location: /tasks');
            exit;
        }
    }

    /**
     * Stop a timer for a task
     * @param string $requestMethod
     * @param array $data
     * @throws RuntimeException
     */
    public function stopTimer(string $requestMethod, array $data): void
    {
        if ($requestMethod !== 'POST') {
            $_SESSION['error'] = 'Invalid request method.';
            header('Location: /tasks');
            exit;
        }

        try {
            $this->authMiddleware->hasPermission('edit_tasks');

            // Check if there's an active timer
            if (empty($_SESSION['active_timer'])) {
                throw new InvalidArgumentException('No active timer found');
            }

            $activeTimer = $_SESSION['active_timer'];
            $taskId = $activeTimer['task_id'];
            $startTime = $activeTimer['start_time'];

            // Fetch and validate task
            $task = $this->taskModel->find($taskId);
            if (!$task || $task->is_deleted) {
                throw new InvalidArgumentException('Task not found');
            }

            // Calculate duration
            $duration = time() - $startTime;

            // Update task time
            $taskUpdate = [
                'time_spent' => ($task->time_spent ?? 0) + $duration
            ];

            // If task is billable, update billable time too
            if ($task->is_hourly) {
                $taskUpdate['billable_time'] = ($task->billable_time ?? 0) + $duration;
            }

            $this->taskModel->update($taskId, $taskUpdate);

            // Create time entry record
            $this->createTimeEntry($taskId, $startTime, time(), $duration, (bool)$task->is_hourly);

            // Add timer stop history entry
            $userId = $_SESSION['user']['id'] ?? null;
            if ($userId) {
                $this->taskModel->addTimerStopHistory($taskId, $userId, $duration);
            }

            // Clear the active timer
            unset($_SESSION['active_timer']);

            $_SESSION['success'] = 'Timer stopped successfully. Time logged: ' . gmdate('H:i:s', $duration);

            // Redirect back to the referring page or task view (with security validation)
            $securityService = SecurityService::getInstance();
            $referer = $_SERVER['HTTP_REFERER'] ?? "/tasks/view/{$taskId}";
            $safeRedirect = $securityService->getSafeRedirectUrl($referer, "/tasks/view/{$taskId}");
            header("Location: {$safeRedirect}");
            exit;

        } catch (InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /tasks');
            exit;
        } catch (\Exception $e) {
            error_log("Exception in TaskController::stopTimer: " . $e->getMessage());
            $_SESSION['error'] = 'An error occurred while stopping the timer.';
            header('Location: /tasks');
            exit;
        }
    }

    /**
     * Create a time entry record
     * @param int $taskId
     * @param int $startTime Unix timestamp
     * @param int $endTime Unix timestamp
     * @param int $duration Duration in seconds
     * @param bool $isBillable
     * @return int Time entry ID
     */
    private function createTimeEntry(int $taskId, int $startTime, int $endTime, int $duration, bool $isBillable): int
    {
        // Get the current user ID
        $userId = $_SESSION['user']['id'] ?? null;
        if (!$userId) {
            throw new RuntimeException('User session not found');
        }

        // Format start and end times for database using timezone-aware formatting
        $settingsService = SettingsService::getInstance();
        $timezone = $settingsService->getDefaultTimezone();

        try {
            $startDate = new \DateTime('@' . $startTime);
            $startDate->setTimezone(new \DateTimeZone($timezone));
            $startDateTime = $startDate->format('Y-m-d H:i:s');

            $endDate = new \DateTime('@' . $endTime);
            $endDate->setTimezone(new \DateTimeZone($timezone));
            $endDateTime = $endDate->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            // Fallback to original method if timezone conversion fails
            error_log("Time formatting error: " . $e->getMessage());
            $startDateTime = date('Y-m-d H:i:s', $startTime);
            $endDateTime = date('Y-m-d H:i:s', $endTime);
        }

        // Create time entry record
        $entryData = [
            'task_id' => $taskId,
            'user_id' => $userId,
            'start_time' => $startDateTime,
            'end_time' => $endDateTime,
            'duration' => $duration,
            'is_billable' => $isBillable ? 1 : 0,
        ];

        return $this->taskModel->createTimeEntry($entryData);
    }

    /**
     * Add a comment to a task
     * @param string $requestMethod
     * @param array $data
     * @throws RuntimeException
     */
    public function addComment(string $requestMethod, array $data): void
    {
        if ($requestMethod !== 'POST') {
            $_SESSION['error'] = 'Invalid request method.';
            header('Location: /tasks');
            exit;
        }

        try {
            $this->authMiddleware->hasPermission('edit_tasks');

            $taskId = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
            if (!$taskId) {
                throw new InvalidArgumentException('Invalid task ID');
            }

            $content = trim($data['content'] ?? '');
            if (empty($content)) {
                throw new InvalidArgumentException('Comment content cannot be empty');
            }

            // Fetch and validate task
            $task = $this->taskModel->find($taskId);
            if (!$task || $task->is_deleted) {
                throw new InvalidArgumentException('Task not found');
            }

            // Check if user has permission to comment on this task
            $userId = $_SESSION['user']['id'] ?? null;
            if ($task->assigned_to !== $userId && !$this->authMiddleware->hasPermission('manage_tasks')) {
                throw new InvalidArgumentException('You do not have permission to comment on this task');
            }

            // Add the comment
            $commentId = $this->taskModel->addComment($taskId, $userId, htmlspecialchars($content));

            // Add history entry for the comment
            $this->taskModel->addHistoryEntry(
                $taskId,
                $userId,
                'comment_added',
                null,
                null,
                'Comment added'
            );

            $_SESSION['success'] = 'Comment added successfully.';
            header("Location: /tasks/view/{$taskId}");
            exit;

        } catch (InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            header("Location: /tasks/view/{$taskId}");
            exit;
        } catch (\Exception $e) {
            error_log("Exception in TaskController::addComment: " . $e->getMessage());
            $_SESSION['error'] = 'An error occurred while adding the comment.';
            header("Location: /tasks/view/{$taskId}");
            exit;
        }
    }
}