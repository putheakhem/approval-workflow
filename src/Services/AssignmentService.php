<?php

declare(strict_types=1);

namespace PutheaKhem\ApprovalWorkflow\Services;

use PutheaKhem\ApprovalWorkflow\Exceptions\NoAssigneesResolved;
use PutheaKhem\ApprovalWorkflow\Models\WorkflowEvent;
use PutheaKhem\ApprovalWorkflow\Models\WorkflowInstance;
use PutheaKhem\ApprovalWorkflow\Models\WorkflowTask;
use PutheaKhem\ApprovalWorkflow\Models\WorkflowTaskAssignee;

final class AssignmentService
{
    public function __construct(
        private readonly ResolverRegistry $registry,
        private readonly DelegationService $delegations,
    ) {}

    /**
     * @param  array<string,mixed>  $step
     * @return array<int,int> user_ids assigned
     */
    public function assignTask(WorkflowInstance $instance, WorkflowTask $task, array $step): array
    {
        $assignment = $step['assignment'] ?? null;
        if (! is_array($assignment)) {
            return [];
        }

        $type = (string) ($assignment['type'] ?? 'users');
        $resolver = $this->registry->get($type);

        // normalize resolver output
        $userIds = $resolver->resolveUserIds($instance, $assignment);
        $userIds = array_values(array_unique(array_filter(array_map(
            static fn ($v) => is_numeric($v) ? (int) $v : null,
            $userIds
        ))));

        if ($userIds === [] && (bool) config('approval-workflow.fail_if_no_assignees', true)) {
            throw new NoAssigneesResolved($task->step_key);
        }

        $final = [];

        foreach ($userIds as $uid) {
            $delegate = $this->delegations->resolveDelegate($uid, $task->team_id ? (int) $task->team_id : null);
            $final[] = $delegate ?? $uid;
        }

        // normalize again after delegation
        $userIds = array_values(array_unique(array_filter(array_map(
            static fn ($v) => is_numeric($v) ? (int) $v : null,
            $final
        ))));

        foreach ($userIds as $uid) {
            WorkflowTaskAssignee::query()->firstOrCreate(
                ['workflow_task_id' => $task->id, 'user_id' => $uid],
                [
                    'team_id' => $task->team_id,
                    'status' => 'pending',
                ]
            );
        }

        WorkflowEvent::create([
            'workflow_instance_id' => $instance->id,
            'actor_id' => $instance->started_by,
            'type' => 'task_assigned',
            'payload' => [
                'task_id' => $task->id,
                'step_key' => $task->step_key,
                'assignment_type' => $type,
                'user_ids' => $userIds,
            ],
        ]);

        // optional: event(new TaskAssigned($task, $userIds));

        return $userIds;
    }
}
