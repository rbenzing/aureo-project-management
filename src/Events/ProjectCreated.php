<?php

declare(strict_types=1);

namespace App\Events;

/**
 * Project Created Event
 *
 * Fired when a new project is created
 */
class ProjectCreated extends Event
{
    public function __construct(int $projectId, string $projectName, int $ownerId)
    {
        parent::__construct([
            'project_id' => $projectId,
            'project_name' => $projectName,
            'owner_id' => $ownerId,
        ]);
    }

    public function getProjectId(): int
    {
        return $this->payload['project_id'];
    }

    public function getProjectName(): string
    {
        return $this->payload['project_name'];
    }

    public function getOwnerId(): int
    {
        return $this->payload['owner_id'];
    }
}
