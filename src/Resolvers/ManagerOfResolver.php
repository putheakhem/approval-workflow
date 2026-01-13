<?php

declare(strict_types=1);

namespace PutheaKhem\ApprovalWorkflow\Resolvers;

use Illuminate\Database\Eloquent\Model;
use PutheaKhem\ApprovalWorkflow\Contracts\ApproverResolverContract;
use PutheaKhem\ApprovalWorkflow\Models\WorkflowInstance;

final class ManagerOfResolver implements ApproverResolverContract
{
    public function resolveUserIds(WorkflowInstance $instance, array $assignment): array
    {
        $field = (string) ($assignment['field'] ?? 'requester_id');
        $requesterId = $instance->context[$field] ?? null;

        if (! is_numeric($requesterId)) {
            return [];
        }

        $userModel = (string) config('approval-workflow.user_model');
        /** @var Model $user */
        $user = $userModel::query()->find((int) $requesterId);
        if (! $user) {
            return [];
        }

        $managerColumn = (string) config('approval-workflow.manager_id_column', 'manager_id');
        $managerId = $user->getAttribute($managerColumn);

        return is_numeric($managerId) ? [(int) $managerId] : [];
    }
}
