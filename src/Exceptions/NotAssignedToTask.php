<?php

declare(strict_types=1);

namespace PutheaKhem\ApprovalWorkflow\Exceptions;

final class NotAssignedToTask extends WorkflowException
{
    public function __construct(int $taskId, int $userId)
    {
        parent::__construct("User [{$userId}] is not assigned to task [{$taskId}].");
    }
}
