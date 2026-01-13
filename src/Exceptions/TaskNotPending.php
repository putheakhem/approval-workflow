<?php

declare(strict_types=1);

namespace PutheaKhem\ApprovalWorkflow\Exceptions;

final class TaskNotPending extends WorkflowException
{
    public function __construct(int $taskId, string $status)
    {
        parent::__construct("Task [{$taskId}] is not pending (status: {$status}).");
    }
}
