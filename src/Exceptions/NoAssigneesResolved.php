<?php

declare(strict_types=1);

namespace PutheaKhem\ApprovalWorkflow\Exceptions;

final class NoAssigneesResolved extends WorkflowException
{
    public function __construct(string $stepKey)
    {
        parent::__construct("No assignees resolved for step [{$stepKey}].");
    }
}
