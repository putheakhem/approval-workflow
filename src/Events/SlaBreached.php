<?php

declare(strict_types=1);

namespace PutheaKhem\ApprovalWorkflow\Events;

use Illuminate\Foundation\Events\Dispatchable;
use PutheaKhem\ApprovalWorkflow\Models\WorkflowTask;

final class SlaBreached
{
    use Dispatchable;

    public function __construct(
        public readonly WorkflowTask $task,
    ) {}
}
