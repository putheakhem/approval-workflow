<?php

declare(strict_types=1);

namespace PutheaKhem\ApprovalWorkflow\Contracts;

use PutheaKhem\ApprovalWorkflow\Models\WorkflowInstance;

interface ApproverResolverContract
{
    /**
     * @param  array<string,mixed>  $assignment  Assignment config from workflow definition
     * @return array<int,int> user_ids
     */
    public function resolveUserIds(WorkflowInstance $instance, array $assignment): array;
}
