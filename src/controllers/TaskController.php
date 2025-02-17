<?php
namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Models\Task;
use App\Models\Subtask;
use App\Models\TimeTracking;
use App\Utils\Validator;

class TaskController {
    public function __construct() {
        // Ensure the user has the required permission
        $middleware = new AuthMiddleware();
        $middleware->hasPermission('view_tasks'); // Default permission for all actions
    }

    /**
     * Display a list of tasks assigned to the logged-in user.
     */
    public function index() {
        // Fetch all tasks assigned to the logged-in user (paginated)
        $tasks = (new Task())->getByUserIdPaginated($_SESSION['user_id'], 10); // Paginate results (e.g., 10 per page)
        include __DIR__ . '/../views/tasks/index.php';
    }

    /**
     * View details of a specific task.
     */
    public function view($id) {
        // Fetch a single task by ID
        $task = (new Task())->find($id);
        if (!$task || ($task->assigned_to !== $_SESSION['user_id'] && !in_array('manage_tasks', $_SESSION['user']['permissions']))) {
            $_SESSION['error'] = 'Task not found or you do not have permission to view it.';
            header('Location: /tasks/index.php');
            exit;
        }

        $project = (new \App\Models\Project())->find($task->project_id);

        // Fetch related subtasks
        $subtasks = (new Subtask())->getByTaskId($task->id);

        // Fetch time tracking entries
        $timeEntries = (new TimeTracking())->getByTaskId($task->id);

        // Render the view
        include __DIR__ . '/../views/tasks/view.php';
    }

    /**
     * Create a new task.
     */
    public function create($data) {
        // Ensure the user has the 'create_tasks' permission
        $middleware = new AuthMiddleware();
        $middleware->hasPermission('create_tasks');

        if (isset($data)) {
            // Validate CSRF token
            if (!isset($data['csrf_token']) || $data['csrf_token'] !== $_SESSION['csrf_token']) {
                $_SESSION['error'] = 'Invalid CSRF token.';
                header('Location: /create_task');
                exit;
            }

            // Validate input data
            $validator = new Validator($data, [
                'title' => 'required|string|max:255',
                'description' => 'nullable|string|max:500',
                'priority' => 'required|in:low,medium,high',
                'status' => 'required|in:todo,in_progress,done',
                'project_id' => 'required|integer',
                'due_date' => 'nullable|date',
            ]);
            if ($validator->fails()) {
                $_SESSION['error'] = 'Validation failed: ' . implode(', ', $validator->errors());
                header('Location: /create_task');
                exit;
            }

            // Create the task
            $task = new Task();
            $task->title = htmlspecialchars($data['title']);
            $task->description = htmlspecialchars($data['description'] ?? null);
            $task->priority = $data['priority'];
            $task->status = $data['status'];
            $task->project_id = $data['project_id'];
            $task->assigned_to = $_SESSION['user_id']; // Assign to the logged-in user
            $task->due_date = $data['due_date'] ?? null;
            $task->save();

            $_SESSION['success'] = 'Task created successfully.';
            header('Location: /tasks');
            exit;
        }

        // Render the create form
        include __DIR__ . '/../views/tasks/create.php';
    }

    /**
     * Show the form to edit an existing task.
     */
    public function edit($id) {
        // Fetch the task
        $task = (new Task())->find($id);
        if (!$task || ($task->assigned_to !== $_SESSION['user_id'] && !in_array('manage_tasks', $_SESSION['user']['permissions']))) {
            $_SESSION['error'] = 'Task not found or you do not have permission to edit it.';
            header('Location: /tasks');
            exit;
        }

        // Render the edit form
        include __DIR__ . '/../views/tasks/edit.php';
    }

    /**
     * Update an existing task.
     */
    public function update($data, $id) {
        // Ensure the user has the 'edit_tasks' permission
        $middleware = new AuthMiddleware();
        $middleware->hasPermission('edit_tasks');

        // Validate CSRF token
        if (!isset($data['csrf_token']) || $data['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['error'] = 'Invalid CSRF token.';
            header("Location: /edit_task?id=$id");
            exit;
        }

        // Validate input data
        $validator = new Validator($data, [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'priority' => 'required|in:low,medium,high',
            'status' => 'required|in:todo,in_progress,done',
            'project_id' => 'required|integer',
            'due_date' => 'nullable|date',
        ]);
        if ($validator->fails()) {
            $_SESSION['error'] = 'Validation failed: ' . implode(', ', $validator->errors());
            header("Location: /edit_task?id=$id");
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
        $task->status = $data['status'];
        $task->project_id = $data['project_id'];
        $task->due_date = $data['due_date'] ?? null;
        $task->save();

        $_SESSION['success'] = 'Task updated successfully.';
        header('Location: /tasks');
        exit;
    }

    /**
     * Delete a task (soft delete).
     */
    public function delete($id) {
        // Ensure the user has the 'delete_tasks' permission
        $middleware = new AuthMiddleware();
        $middleware->hasPermission('delete_tasks');

        // Soft delete the task
        $task = (new Task())->find($id);
        if (!$task || ($task->assigned_to !== $_SESSION['user_id'] && !in_array('manage_tasks', $_SESSION['user']['permissions']))) {
            $_SESSION['error'] = 'Task not found or you do not have permission to delete it.';
            header('Location: /tasks');
            exit;
        }

        // Mark as deleted instead of permanently removing
        $task->is_deleted = true;
        $task->save();

        $_SESSION['success'] = 'Task deleted successfully.';
        header('Location: /tasks');
        exit;
    }

    /**
     * Start a timer for a task.
     */
    public function startTimer($taskId) {
        // Ensure the user has the 'edit_tasks' permission
        $middleware = new AuthMiddleware();
        $middleware->hasPermission('edit_tasks');

        // Start the timer
        $timeEntry = new TimeTracking();
        $timeEntry->user_id = $_SESSION['user_id'];
        $timeEntry->task_id = $taskId;
        $timeEntry->start_time = date('Y-m-d H:i:s');
        $timeEntry->save();

        $_SESSION['success'] = 'Timer started successfully.';
        header("Location: /view_task?id=$taskId");
        exit;
    }

    /**
     * Stop a timer for a task.
     */
    public function stopTimer($timeEntryId) {
        // Ensure the user has the 'edit_tasks' permission
        $middleware = new AuthMiddleware();
        $middleware->hasPermission('edit_tasks');

        // Stop the timer
        $timeEntry = (new TimeTracking())->find($timeEntryId);
        if (!$timeEntry || $timeEntry->user_id !== $_SESSION['user_id']) {
            $_SESSION['error'] = 'Time entry not found or you do not have permission to modify it.';
            header('Location: /tasks');
            exit;
        }

        $timeEntry->end_time = date('Y-m-d H:i:s');
        $timeEntry->save();

        // Update the task's total time spent
        $task = (new Task())->find($timeEntry->task_id);
        $task->time_spent += $timeEntry->duration; // Add the duration of this entry
        $task->save();

        $_SESSION['success'] = 'Timer stopped successfully.';
        header("Location: /view_task?id={$timeEntry->task_id}");
        exit;
    }
}