<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Models\Task;
use RuntimeException;
use InvalidArgumentException;

class TimeTrackingController
{
    private AuthMiddleware $authMiddleware;
    private Task $taskModel;

    public function __construct()
    {
        $this->authMiddleware = new AuthMiddleware();
        $this->taskModel = new Task();
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

            // Store timer start in session
            $_SESSION['active_timer'] = [
                'task_id' => $taskId,
                'start_time' => time()
            ];

            $_SESSION['success'] = 'Timer started successfully.';
            header("Location: /tasks/view/{$taskId}");
            exit;

        } catch (InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /tasks');
            exit;
        } catch (\Exception $e) {
            error_log("Exception in TimeTrackingController::startTimer: " . $e->getMessage());
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

            // Clear the active timer
            unset($_SESSION['active_timer']);

            $_SESSION['success'] = 'Timer stopped successfully.';
            header("Location: /tasks/view/{$taskId}");
            exit;

        } catch (InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /tasks');
            exit;
        } catch (\Exception $e) {
            error_log("Exception in TimeTrackingController::stopTimer: " . $e->getMessage());
            $_SESSION['error'] = 'An error occurred while stopping the timer.';
            header('Location: /tasks');
            exit;
        }
    }
}