<?php

declare(strict_types=1);

namespace PutheaKhem\ApprovalWorkflow\Services;

use PutheaKhem\ApprovalWorkflow\Models\WorkflowDelegation;

final class DelegationService
{
    /**
     * Resolve delegate user id for a given approver within a team.
     */
    public function resolveDelegate(int $fromUserId, ?int $teamId): ?int
    {
        $q = WorkflowDelegation::query()
            ->where('from_user_id', $fromUserId)
            ->where('is_active', true);

        if ($teamId !== null) {
            $q->where(function ($qq) use ($teamId) {
                $qq->whereNull('team_id')->orWhere('team_id', $teamId);
            });
        }

        /** @var WorkflowDelegation|null $d */
        $d = $q->latest('id')->first();

        if (! $d || ! $d->isCurrentlyActive()) {
            return null;
        }

        return (int) $d->to_user_id;
    }
}
