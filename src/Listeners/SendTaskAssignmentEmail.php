<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\TaskAssigned;
use App\Models\Task;
use App\Models\User;
use App\Services\LoggerService;

/**
 * Send Task Assignment Email Listener
 *
 * Sends email notification when task is assigned
 */
class SendTaskAssignmentEmail
{
    private Task $taskModel;
    private User $userModel;
    private LoggerService $logger;

    public function __construct(
        ?Task $taskModel = null,
        ?User $userModel = null,
        ?LoggerService $logger = null
    ) {
        $this->taskModel = $taskModel ?? new Task();
        $this->userModel = $userModel ?? new User();
        $this->logger = $logger ?? new LoggerService();
    }

    /**
     * Handle the TaskAssigned event
     *
     * @param TaskAssigned $event
     */
    public function handle(TaskAssigned $event): void
    {
        try {
            $task = $this->taskModel->find($event->getTaskId());
            $user = $this->userModel->find($event->getUserId());

            if (!$task || !$user) {
                return;
            }

            // TODO: Implement actual email sending
            // For now, just log the intent
            $this->logger->log('info', 'Task assignment email queued', [
                'task_id' => $event->getTaskId(),
                'task_title' => $task->title,
                'user_email' => $user->email,
                'user_name' => "{$user->first_name} {$user->last_name}",
            ]);

            // Example email sending (placeholder):
            // $this->emailService->send([
            //     'to' => $user->email,
            //     'subject' => "Task Assigned: {$task->title}",
            //     'template' => 'task-assigned',
            //     'data' => ['task' => $task, 'user' => $user]
            // ]);

        } catch (\Exception $e) {
            $this->logger->log('error', 'Failed to send task assignment email', [
                'error' => $e->getMessage(),
                'task_id' => $event->getTaskId(),
            ]);
        }
    }
}
