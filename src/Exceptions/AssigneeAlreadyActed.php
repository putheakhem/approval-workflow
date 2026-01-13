<?php

declare(strict_types=1);

namespace PutheaKhem\ApprovalWorkflow\Exceptions;

final class AssigneeAlreadyActed extends WorkflowException
{
    public function __construct(int $taskId, int $userId)
    {
        parent::__construct("User [{$userId}] already acted on task [{$taskId}].");
    }
}
