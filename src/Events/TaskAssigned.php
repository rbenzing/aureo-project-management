<?php

declare(strict_types=1);

namespace App\Events;

/**
 * Task Assigned Event
 *
 * Fired when a task is assigned to a user
 */
class TaskAssigned extends Event
{
    public function __construct(int $taskId, int $userId, int $assignedBy)
    {
        parent::__construct([
            'task_id' => $taskId,
            'user_id' => $userId,
            'assigned_by' => $assignedBy,
        ]);
    }

    public function getTaskId(): int
    {
        return $this->payload['task_id'];
    }

    public function getUserId(): int
    {
        return $this->payload['user_id'];
    }

    public function getAssignedBy(): int
    {
        return $this->payload['assigned_by'];
    }
}
