<?php

declare(strict_types=1);

namespace PutheaKhem\ApprovalWorkflow\Resolvers;

use PutheaKhem\ApprovalWorkflow\Contracts\ApproverResolverContract;
use PutheaKhem\ApprovalWorkflow\Models\WorkflowInstance;

final class UsersResolver implements ApproverResolverContract
{
    public function resolveUserIds(WorkflowInstance $instance, array $assignment): array
    {
        $users = $assignment['users'] ?? [];

        if (! is_array($users)) {
            return [];
        }

        $ids = array_values(array_unique(array_filter(array_map(
            static fn ($v) => is_numeric($v) ? (int) $v : null,
            $users
        ))));

        return $ids;
    }
}
