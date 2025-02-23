<?php
namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Models\TimeTracking;
use App\Models\Task;
use App\Utils\Validator;

class TimeTrackingController
{
    private $authMiddleware;
    private $csrfMiddleware;

    public function __construct()
    {
        // Ensure the user has the required permission
        $this->authMiddleware = new AuthMiddleware();
        $this->csrfMiddleware = new CsrfMiddleware();
        $this->authMiddleware->hasPermission('edit_tasks'); // Default permission for all actions
    }

    /**
     * Start a timer for a task.
     */
    public function startTimer($requestMethod, $data)
    {
        if ($requestMethod === 'POST') {
            $taskId = $data['task_id'] ?? null;
            if (!$taskId) {
                $_SESSION['error'] = 'Invalid task ID.';
                header('Location: /tasks');
                exit;
            }

            // Fetch the task
            $task = (new Task())->find($taskId);
            if (!$task || ($task->assigned_to !== $_SESSION['user_id'] && !in_array('manage_tasks', $_SESSION['user']['permissions']))) {
                $_SESSION['error'] = 'Task not found or you do not have permission to start the timer.';
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
            $timeEntryId = $data['time_entry_id'] ?? null;
            if (!$timeEntryId) {
                $_SESSION['error'] = 'Invalid time entry ID.';
                header('Location: /tasks');
                exit;
            }

            // Fetch the time entry
            $timeEntry = (new TimeTracking())->find($timeEntryId);
            if (!$timeEntry || $timeEntry->user_id !== $_SESSION['user_id']) {
                $_SESSION['error'] = 'Time entry not found or you do not have permission to stop the timer.';
                header('Location: /tasks');
                exit;
            }

            // Stop the timer
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