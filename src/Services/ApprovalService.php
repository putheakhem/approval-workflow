<?php

declare(strict_types=1);

namespace PutheaKhem\ApprovalWorkflow\Services;

use Illuminate\Support\Facades\DB;
use PutheaKhem\ApprovalWorkflow\Exceptions\AssigneeAlreadyActed;
use PutheaKhem\ApprovalWorkflow\Exceptions\NotAssignedToTask;
use PutheaKhem\ApprovalWorkflow\Exceptions\TaskNotPending;
use PutheaKhem\ApprovalWorkflow\Models\WorkflowEvent;
use PutheaKhem\ApprovalWorkflow\Models\WorkflowInstance;
use PutheaKhem\ApprovalWorkflow\Models\WorkflowTask;
use PutheaKhem\ApprovalWorkflow\Models\WorkflowTaskAssignee;
use PutheaKhem\ApprovalWorkflow\Support\Definition;

final class ApprovalService
{
    public function approve(WorkflowInstance $instance, int $taskId, int $userId, ?string $notes = null): void
    {
        $this->act($instance, $taskId, $userId, 'approve', $notes);
    }

    public function reject(WorkflowInstance $instance, int $taskId, int $userId, ?string $notes = null): void
    {
        $this->act($instance, $taskId, $userId, 'reject', $notes);
    }

    public function requestChanges(WorkflowInstance $instance, int $taskId, int $userId, ?string $notes = null): void
    {
        $this->act($instance, $taskId, $userId, 'changes_requested', $notes);
    }

    private function act(WorkflowInstance $instance, int $taskId, int $userId, string $action, ?string $notes): void
    {
        DB::transaction(function () use ($instance, $taskId, $userId, $action, $notes) {
            /** @var WorkflowTask $task */
            $task = $instance->tasks()->whereKey($taskId)->lockForUpdate()->firstOrFail();

            if ($task->status !== 'pending') {
                throw new TaskNotPending($task->id, $task->status);
            }

            /** @var WorkflowTaskAssignee $assignee */
            $assignee = $task->assignees()
                ->where('user_id', $userId)
                ->lockForUpdate()
                ->first();

            if (! $assignee) {
                throw new NotAssignedToTask($task->id, $userId);
            }

            if ($assignee->status !== 'pending') {
                throw new AssigneeAlreadyActed($task->id, $userId);
            }

            // mark assignee action
            $assignee->status = match ($action) {
                'approve' => 'approved',
                'reject' => 'rejected',
                default => 'changes_requested',
            };
            $assignee->acted_at = now();
            $assignee->acted_by = $userId;
            $assignee->notes = $notes;
            $assignee->save();

            WorkflowEvent::create([
                'workflow_instance_id' => $instance->id,
                'actor_id' => $userId,
                'type' => 'assignee_acted',
                'payload' => [
                    'task_id' => $task->id,
                    'step_key' => $task->step_key,
                    'action' => $action,
                ],
            ]);

            // If reject/changes_requested: finish task immediately (common behavior)
            if (in_array($action, ['reject', 'changes_requested'], true)) {
                $task->status = $action === 'reject' ? 'rejected' : 'changes_requested';
                $task->save();

                WorkflowEvent::create([
                    'workflow_instance_id' => $instance->id,
                    'actor_id' => $userId,
                    'type' => 'task_finished',
                    'payload' => [
                        'task_id' => $task->id,
                        'step_key' => $task->step_key,
                        'status' => $task->status,
                    ],
                ]);

                $this->advance($instance, $task, $action, $userId);

                return;
            }

            // approve flow:
            $this->maybeCompleteTaskAfterApprove($instance, $task, $userId);
        });
    }

    private function maybeCompleteTaskAfterApprove(WorkflowInstance $instance, WorkflowTask $task, int $userId): void
    {
        $mode = $task->mode;

        if ($mode === 'any') {
            // approve task, cancel others
            $task->status = 'approved';
            $task->save();

            $task->assignees()
                ->where('status', 'pending')
                ->update(['status' => 'cancelled']);

            WorkflowEvent::create([
                'workflow_instance_id' => $instance->id,
                'actor_id' => $userId,
                'type' => 'task_finished',
                'payload' => [
                    'task_id' => $task->id,
                    'step_key' => $task->step_key,
                    'status' => 'approved',
                    'mode' => 'any',
                ],
            ]);

            $this->advance($instance, $task, 'approve', $userId);

            return;
        }

        // mode === all
        $pending = $task->assignees()->where('status', 'pending')->count();
        if ($pending > 0) {
            // still waiting for other approvers
            return;
        }

        $task->status = 'approved';
        $task->save();

        WorkflowEvent::create([
            'workflow_instance_id' => $instance->id,
            'actor_id' => $userId,
            'type' => 'task_finished',
            'payload' => [
                'task_id' => $task->id,
                'step_key' => $task->step_key,
                'status' => 'approved',
                'mode' => 'all',
            ],
        ]);

        $this->advance($instance, $task, 'approve', $userId);
    }

    private function advance(WorkflowInstance $instance, WorkflowTask $task, string $action, int $actorId): void
    {
        $definition = (array) $instance->version->definition;

        // transition override?
        $to = Definition::transitionTo($definition, $task->step_key, $action);

        if ($to === null) {
            // default next
            $step = Definition::findStep($definition, $task->step_key);
            $to = is_array($step) ? Definition::nextKey($step) : null;
        }

        if ($to === null) {
            // no next -> completed
            $instance->status = $action === 'reject' ? 'rejected' : 'completed';
            $instance->completed_at = now();
            $instance->save();

            WorkflowEvent::create([
                'workflow_instance_id' => $instance->id,
                'actor_id' => $actorId,
                'type' => 'workflow_completed',
                'payload' => ['reason' => 'No next step.'],
            ]);

            return;
        }

        // if next is end
        if (Definition::isEndStep($definition, $to)) {
            $instance->status = $action === 'reject' ? 'rejected' : 'completed';
            $instance->completed_at = now();
            $instance->save();

            WorkflowEvent::create([
                'workflow_instance_id' => $instance->id,
                'actor_id' => $actorId,
                'type' => 'workflow_completed',
                'payload' => [
                    'end_step' => $to,
                    'final_status' => $instance->status,
                ],
            ]);

            return;
        }

        // create next task
        /** @var WorkflowEngine $engine */
        $engine = app(WorkflowEngine::class);
        $engine->createNextTask($instance, $to, $actorId);
    }
}
