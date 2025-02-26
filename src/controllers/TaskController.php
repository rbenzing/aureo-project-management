<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Config;
use App\Middleware\AuthMiddleware;
use App\Models\Task;
use App\Models\Project;
use App\Utils\Validator;
use RuntimeException;
use InvalidArgumentException;

class TaskController
{
    private AuthMiddleware $authMiddleware;
    private Task $taskModel;
    private Project $projectModel;

    public function __construct()
    {
        $this->authMiddleware = new AuthMiddleware();
        $this->authMiddleware->hasPermission('manage_tasks');

        $this->taskModel = new Task();
        $this->projectModel = new Project();
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
            $limit = Config::get('max_pages', 10);
            $userId = $data['id'] ?? null;

            if (!empty($userId)) {
                $tasks = $this->taskModel->getByUserId(intval($userId), $limit, $page);
                $totalTasks = $this->taskModel->count(['assigned_to' => intval($userId), 'is_deleted' => 0]);
            } else {
                $tasks = $this->taskModel->getAllWithDetails($limit, $page);
                $totalTasks = $this->taskModel->count(['is_deleted' => 0]);
            }

            $totalPages = ceil($totalTasks / $limit);

            include __DIR__ . '/../Views/Tasks/index.php';
        } catch (\Exception $e) {
            error_log("Exception in TaskController::index: " . $e->getMessage());
            $_SESSION['error'] = 'An error occurred while fetching tasks.';
            header('Location: /dashboard');
            exit;
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

            include __DIR__ . '/../Views/Tasks/view.php';
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
            $statuses = $this->taskModel->getTaskStatuses();
            
            include __DIR__ . '/../Views/Tasks/create.php';
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

            $validator = new Validator($data, [
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'priority' => 'required|in:none,low,medium,high',
                'status_id' => 'required|integer|exists:task_statuses,id',
                'project_id' => 'required|integer|exists:projects,id',
                'due_date' => 'nullable|date',
                'estimated_time' => 'nullable|integer|min:0',
                'hourly_rate' => 'nullable|integer|min:0',
                'is_hourly' => 'boolean',
                'parent_task_id' => 'nullable|integer|exists:tasks,id'
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException(implode(', ', $validator->errors()));
            }

            $taskData = [
                'title' => htmlspecialchars($data['title']),
                'description' => isset($data['description']) ? 
                    htmlspecialchars($data['description']) : null,
                'priority' => $data['priority'],
                'status_id' => filter_var($data['status_id'], FILTER_VALIDATE_INT),
                'project_id' => filter_var($data['project_id'], FILTER_VALIDATE_INT),
                'assigned_to' => $_SESSION['user']['id'],
                'due_date' => $data['due_date'] ?? null,
                'estimated_time' => isset($data['estimated_time']) ? 
                    filter_var($data['estimated_time'], FILTER_VALIDATE_INT) : null,
                'hourly_rate' => isset($data['hourly_rate']) ? 
                    filter_var($data['hourly_rate'], FILTER_VALIDATE_INT) : null,
                'is_hourly' => isset($data['is_hourly']) ? 
                    filter_var($data['is_hourly'], FILTER_VALIDATE_BOOLEAN) : false,
                'parent_task_id' => isset($data['parent_task_id']) ? 
                    filter_var($data['parent_task_id'], FILTER_VALIDATE_INT) : null,
                'is_subtask' => isset($data['parent_task_id'])
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
            error_log("Exception in TaskController::create: " . $e->getMessage());
            $_SESSION['error'] = 'An error occurred while creating the task.';
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
            $statuses = $this->taskModel->getTaskStatuses();

            include __DIR__ . '/../Views/Tasks/edit.php';
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

            $validator = new Validator($data, [
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'priority' => 'required|in:none,low,medium,high',
                'status_id' => 'required|integer|exists:task_statuses,id',
                'project_id' => 'required|integer|exists:projects,id',
                'due_date' => 'nullable|date',
                'estimated_time' => 'nullable|integer|min:0',
                'hourly_rate' => 'nullable|integer|min:0',
                'is_hourly' => 'boolean'
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException(implode(', ', $validator->errors()));
            }

            $taskData = [
                'title' => htmlspecialchars($data['title']),
                'description' => isset($data['description']) ? 
                    htmlspecialchars($data['description']) : null,
                'priority' => $data['priority'],
                'status_id' => filter_var($data['status_id'], FILTER_VALIDATE_INT),
                'project_id' => filter_var($data['project_id'], FILTER_VALIDATE_INT),
                'due_date' => $data['due_date'] ?? null,
                'estimated_time' => isset($data['estimated_time']) ? 
                    filter_var($data['estimated_time'], FILTER_VALIDATE_INT) : null,
                'hourly_rate' => isset($data['hourly_rate']) ? 
                    filter_var($data['hourly_rate'], FILTER_VALIDATE_INT) : null,
                'is_hourly' => isset($data['is_hourly']) ? 
                    filter_var($data['is_hourly'], FILTER_VALIDATE_BOOLEAN) : false
            ];

            $this->taskModel->update($id, $taskData);

            $_SESSION['success'] = 'Task updated successfully.';
            header('Location: /tasks/view/' . $id);
            exit;

        } catch (InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            $_SESSION['form_data'] = $data;
            header("Location: /tasks/edit/{$id}");
            exit;
        } catch (\Exception $e) {
            error_log("Exception in TaskController::update: " . $e->getMessage());
            $_SESSION['error'] = 'An error occurred while updating the task.';
            header("Location: /tasks/edit/{$id}");
            exit;
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
}