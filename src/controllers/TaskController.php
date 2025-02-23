<?php
namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Models\Task;
use App\Models\Project;
use App\Models\TimeTracking;
use App\Utils\Validator;

class TaskController
{
    private $authMiddleware;
    private $csrfMiddleware;

    public function __construct()
    {
        // Ensure the user has the required permission
        $this->authMiddleware = new AuthMiddleware();
        $this->csrfMiddleware = new CsrfMiddleware();
        $this->authMiddleware->hasPermission('view_tasks'); // Default permission for all actions
    }

    /**
     * Display a list of tasks assigned to the logged-in user.
     */
    public function index($requestMethod, $data)
    {
        // Fetch all tasks assigned to the logged-in user (paginated)
        $limit = 10; // Number of tasks per page
        $page = isset($data['page']) ? intval($data['page']) : 1;
        $tasks = (new Task())->getByUserIdPaginated($_SESSION['user_id'], $limit, $page);

        // Prepare pagination data
        $totalTasks = (new Task())->countByUserId($_SESSION['user_id']);
        $totalPages = ceil($totalTasks / $limit);
        $prevPage = $page > 1 ? $page - 1 : null;
        $nextPage = $page < $totalPages ? $page + 1 : null;

        $pagination = [
            'prev_page' => $prevPage,
            'next_page' => $nextPage,
        ];

        include __DIR__ . '/../Views/Tasks/index.php';
    }

    /**
     * View details of a specific task.
     */
    public function view($requestMethod, $data)
    {
        $id = $data['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = 'Invalid task ID.';
            header('Location: /tasks');
            exit;
        }

        // Fetch a single task by ID
        $task = (new Task())->find($id);
        if (!$task || ($task->assigned_to !== $_SESSION['user_id'] && !in_array('manage_tasks', $_SESSION['user']['permissions']))) {
            $_SESSION['error'] = 'Task not found or you do not have permission to view it.';
            header('Location: /tasks');
            exit;
        }

        $project = (new Project())->find($task->project_id);
        // Fetch related subtasks
        $subtasks = (new Task())->getSubtasks($task->id);
        // Fetch time tracking entries
        $timeEntries = (new TimeTracking())->getByTaskId($task->id);

        include __DIR__ . '/../Views/Tasks/view.php';
    }

    /**
     * Show the form to create a new task.
     */
    public function createForm($requestMethod, $data)
    {
        $this->authMiddleware->hasPermission('create_tasks');

        // Fetch all projects and statuses for the form
        $statuses = (new Task())->getTaskStatuses();
        $projects = (new Project())->getAll();

        include __DIR__ . '/../Views/Tasks/create.php';
    }

    /**
     * Create a new task.
     */
    public function create($requestMethod, $data)
    {
        if ($requestMethod === 'POST') {
            $this->authMiddleware->hasPermission('create_tasks');

            // Validate input data
            $validator = new Validator($data, [
                'title' => 'required|string|max:255',
                'description' => 'nullable|string|max:500',
                'priority' => 'required|in:none,low,medium,high',
                'status_id' => 'required|integer',
                'project_id' => 'required|integer',
                'due_date' => 'nullable|date',
            ]);
            if ($validator->fails()) {
                $_SESSION['error'] = 'Validation failed: ' . implode(', ', $validator->errors());
                header('Location: /tasks/create');
                exit;
            }

            // Create the task
            $task = new Task();
            $task->title = htmlspecialchars($data['title']);
            $task->description = htmlspecialchars($data['description'] ?? null);
            $task->priority = $data['priority'];
            $task->status_id = intval($data['status_id']);
            $task->project_id = intval($data['project_id']);
            $task->assigned_to = $_SESSION['user_id']; // Assign to the logged-in user
            $task->due_date = $data['due_date'] ?? null;
            $task->save();

            $_SESSION['success'] = 'Task created successfully.';
            header('Location: /tasks');
            exit;
        }

        // Render the create form
        $this->createForm($requestMethod, $data);
    }

    /**
     * Show the form to edit an existing task.
     */
    public function editForm($requestMethod, $data)
    {
        $id = $data['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = 'Invalid task ID.';
            header('Location: /tasks');
            exit;
        }

        $this->authMiddleware->hasPermission('edit_tasks');

        // Fetch the task
        $task = (new Task())->find($id);
        if (!$task || ($task->assigned_to !== $_SESSION['user_id'] && !in_array('manage_tasks', $_SESSION['user']['permissions']))) {
            $_SESSION['error'] = 'Task not found or you do not have permission to edit it.';
            header('Location: /tasks');
            exit;
        }

        // Fetch all projects and statuses for the form
        $statuses = (new Task())->getTaskStatuses();
        $projects = (new Project())->getAll();

        include __DIR__ . '/../Views/Tasks/edit.php';
    }

    /**
     * Update an existing task.
     */
    public function update($requestMethod, $data)
    {
        if ($requestMethod === 'POST') {
            $id = $data['id'] ?? null;
            if (!$id) {
                $_SESSION['error'] = 'Invalid task ID.';
                header('Location: /tasks');
                exit;
            }

            $this->authMiddleware->hasPermission('edit_tasks');

            // Validate input data
            $validator = new Validator($data, [
                'title' => 'required|string|max:255',
                'description' => 'nullable|string|max:500',
                'priority' => 'required|in:none,low,medium,high',
                'status_id' => 'required|integer',
                'project_id' => 'required|integer',
                'due_date' => 'nullable|date',
            ]);
            if ($validator->fails()) {
                $_SESSION['error'] = 'Validation failed: ' . implode(', ', $validator->errors());
                header("Location: /tasks/edit/$id");
                exit;
            }

            // Update the task
            $task = (new Task())->find($id);
            if (!$task || ($task->assigned_to !== $_SESSION['user_id'] && !in_array('manage_tasks', $_SESSION['user']['permissions']))) {
                $_SESSION['error'] = 'Task not found or you do not have permission to edit it.';
                header('Location: /tasks');
                exit;
            }

            $task->title = htmlspecialchars($data['title']);
            $task->description = htmlspecialchars($data['description'] ?? null);
            $task->priority = $data['priority'];
            $task->status_id = intval($data['status_id']);
            $task->project_id = intval($data['project_id']);
            $task->due_date = $data['due_date'] ?? null;
            $task->save();

            $_SESSION['success'] = 'Task updated successfully.';
            header('Location: /tasks');
            exit;
        }

        // Fetch the task for the edit form
        $this->editForm($requestMethod, $data);
    }

    /**
     * Delete a task (soft delete).
     */
    public function delete($requestMethod, $data)
    {
        if ($requestMethod === 'POST') {
            $id = $data['id'] ?? null;
            if (!$id) {
                $_SESSION['error'] = 'Invalid task ID.';
                header('Location: /tasks');
                exit;
            }

            $this->authMiddleware->hasPermission('delete_tasks');

            // Soft delete the task
            $task = (new Task())->find($id);
            if (!$task || ($task->assigned_to !== $_SESSION['user_id'] && !in_array('manage_tasks', $_SESSION['user']['permissions']))) {
                $_SESSION['error'] = 'Task not found or you do not have permission to delete it.';
                header('Location: /tasks');
                exit;
            }

            $task->is_deleted = true;
            $task->save();

            $_SESSION['success'] = 'Task deleted successfully.';
            header('Location: /tasks');
            exit;
        }

        // Fetch the task for the delete confirmation form
        $id = $data['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = 'Invalid task ID.';
            header('Location: /tasks');
            exit;
        }

        $task = (new Task())->find($id);
        if (!$task || ($task->assigned_to !== $_SESSION['user_id'] && !in_array('manage_tasks', $_SESSION['user']['permissions']))) {
            $_SESSION['error'] = 'Task not found or you do not have permission to delete it.';
            header('Location: /tasks');
            exit;
        }

        include __DIR__ . '/../Views/Tasks/delete.php';
    }

    /**
     * Start a timer for a task.
     */
    public function startTimer($requestMethod, $data)
    {
        if ($requestMethod === 'POST') {
            // Ensure the user has the 'edit_tasks' permission
            $this->authMiddleware->hasPermission('edit_tasks');

            $taskId = $data['task_id'] ?? null;
            if (!$taskId) {
                $_SESSION['error'] = 'Invalid task ID.';
                header('Location: /tasks');
                exit;
            }

            // Start the timer
            $timeEntry = new TimeTracking();
            $timeEntry->user_id = $_SESSION['user_id'];
            $timeEntry->task_id = $taskId;
            $timeEntry->start_time = date('Y-m-d H:i:s');
            $timeEntry->save();

            $_SESSION['success'] = 'Timer started successfully.';
            header("Location: /tasks/view/$taskId");
            exit;
        }

        // Render the start timer form if needed
        include __DIR__ . '/../Views/Tasks/start_timer.php';
    }

    /**
     * Stop a timer for a task.
     */
    public function stopTimer($requestMethod, $data)
    {
        if ($requestMethod === 'POST') {
            // Ensure the user has the 'edit_tasks' permission
            $this->authMiddleware->hasPermission('edit_tasks');

            $timeEntryId = $data['time_entry_id'] ?? null;
            if (!$timeEntryId) {
                $_SESSION['error'] = 'Invalid time entry ID.';
                header('Location: /tasks');
                exit;
            }

            // Stop the timer
            $timeEntry = (new TimeTracking())->find($timeEntryId);
            if (!$timeEntry || $timeEntry->user_id !== $_SESSION['user_id']) {
                $_SESSION['error'] = 'Time entry not found or you do not have permission to modify it.';
                header('Location: /tasks');
                exit;
            }

            $timeEntry->end_time = date('Y-m-d H:i:s');
            $timeEntry->duration = strtotime($timeEntry->end_time) - strtotime($timeEntry->start_time);
            $timeEntry->save();

            // Update the task's total time spent
            $task = (new Task())->find($timeEntry->task_id);
            if (!$task) {
                $_SESSION['error'] = 'Task not found.';
                header('Location: /tasks');
                exit;
            }

            $task->time_spent += $timeEntry->duration; // Add the duration of this entry
            $task->save();

            $_SESSION['success'] = 'Timer stopped successfully.';
            header("Location: /tasks/view/{$timeEntry->task_id}");
            exit;
        }

        // Render the stop timer form if needed
        include __DIR__ . '/../Views/Tasks/stop_timer.php';
    }
}