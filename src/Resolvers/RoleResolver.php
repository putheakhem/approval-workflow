<?php

declare(strict_types=1);

namespace PutheaKhem\ApprovalWorkflow\Resolvers;

use Illuminate\Database\Eloquent\Model;
use PutheaKhem\ApprovalWorkflow\Contracts\ApproverResolverContract;
use PutheaKhem\ApprovalWorkflow\Models\WorkflowInstance;
use Throwable;

final class RoleResolver implements ApproverResolverContract
{
    public function resolveUserIds(WorkflowInstance $instance, array $assignment): array
    {
        if (! $this->spatieInstalled()) {
            return [];
        }

        $roles = $assignment['roles'] ?? [];
        if (! is_array($roles) || $roles === []) {
            return [];
        }

        $teamId = $this->resolveTeamId($instance, $assignment);

        $userModel = (string) config('approval-workflow.user_model');
        /** @var Model $query */
        $query = $userModel::query();

        // If app uses Spatie Teams mode, many setups filter roles by registrar team id.
        // We attempt to set registrar team id only for this resolution, then reset.
        $reset = null;

        if ($teamId !== null) {
            $reset = $this->setSpatieTeamContext($teamId);
        }

        try {
            $ids = $query->role($roles)->pluck('id')->all();

            return array_values(array_unique(array_map('intval', $ids)));
        } finally {
            if (is_callable($reset)) {
                $reset();
            }
        }
    }

    private function spatieInstalled(): bool
    {
        return trait_exists(\Spatie\Permission\Traits\HasRoles::class)
            && class_exists(\Spatie\Permission\PermissionRegistrar::class);
    }

    private function resolveTeamId(WorkflowInstance $instance, array $assignment): ?int
    {
        $teamFrom = $assignment['team_from'] ?? null;

        if (is_string($teamFrom) && $teamFrom !== '') {
            $val = $instance->context[$teamFrom] ?? null;

            return is_numeric($val) ? (int) $val : null;
        }

        return $instance->team_id ? (int) $instance->team_id : null;
    }

    /**
     * Attempt to set Spatie PermissionRegistrar team id (if teams feature used).
     * Returns a reset closure if changed.
     */
    private function setSpatieTeamContext(int $teamId): ?callable
    {
        try {
            /** @var \Spatie\Permission\PermissionRegistrar $registrar */
            $registrar = app(\Spatie\Permission\PermissionRegistrar::class);

            // Common property in teams setups
            $old = $registrar->getPermissionsTeamId();

            $registrar->setPermissionsTeamId($teamId);

            return static function () use ($registrar, $old): void {
                $registrar->setPermissionsTeamId($old);
            };
        } catch (Throwable) {
            // If registrar doesn't support teams, just ignore
            return null;
        }
    }
}
