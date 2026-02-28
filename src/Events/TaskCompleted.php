<?php

declare(strict_types=1);

namespace App\Events;

/**
 * Task Completed Event
 *
 * Fired when a task is marked as completed
 */
class TaskCompleted extends Event
{
    public function __construct(int $taskId, int $completedBy, int $timeSpent)
    {
        parent::__construct([
            'task_id' => $taskId,
            'completed_by' => $completedBy,
            'time_spent' => $timeSpent,
        ]);
    }

    public function getTaskId(): int
    {
        return $this->payload['task_id'];
    }

    public function getCompletedBy(): int
    {
        return $this->payload['completed_by'];
    }

    public function getTimeSpent(): int
    {
        return $this->payload['time_spent'];
    }
}
