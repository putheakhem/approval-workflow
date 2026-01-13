<?php

declare(strict_types=1);

namespace PutheaKhem\ApprovalWorkflow\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use PutheaKhem\ApprovalWorkflow\Models\WorkflowEvent;
use PutheaKhem\ApprovalWorkflow\Models\WorkflowInstance;
use PutheaKhem\ApprovalWorkflow\Models\WorkflowTask;
use PutheaKhem\ApprovalWorkflow\Repositories\WorkflowRepository;
use PutheaKhem\ApprovalWorkflow\Support\Definition;
use RuntimeException;

final class WorkflowEngine
{
    public function __construct(
        private readonly WorkflowRepository $workflows,
        private readonly AssignmentService $assignments,
    ) {}

    /**
     * @param  array<string,mixed>  $context
     */
    public function start(Model $subject, string $workflowKey, array $context = [], ?int $teamId = null, ?int $startedBy = null): WorkflowInstance
    {
        $version = $this->workflows->latestPublishedVersion($workflowKey);
        $definition = (array) $version->definition;

        $firstKey = Definition::firstStepKey($definition);
        $firstStep = Definition::findStep($definition, $firstKey);

        if (! is_array($firstStep)) {
            throw new RuntimeException("Invalid workflow definition: first step [{$firstKey}] not found.");
        }

        // Start step should point to the first actionable step
        $nextKey = Definition::nextKey($firstStep);
        if ($nextKey === null) {
            // workflow starts and ends immediately
            $nextKey = null;
        }

        return DB::transaction(function () use ($subject, $version, $context, $teamId, $startedBy, $workflowKey, $definition, $firstKey, $nextKey) {

            $instance = new WorkflowInstance([
                'workflow_version_id' => $version->id,
                'subject_type' => $subject::class,
                'subject_id' => $subject->getKey(),
                'team_id' => $teamId ?? ($context['team_id'] ?? null),
                'status' => 'running',
                'context' => $context,
                'started_by' => $startedBy,
            ]);

            $instance->save();

            WorkflowEvent::create([
                'workflow_instance_id' => $instance->id,
                'actor_id' => $startedBy,
                'type' => 'workflow_started',
                'payload' => [
                    'workflow_key' => $workflowKey,
                    'workflow_version_id' => $version->id,
                    'first_step' => $firstKey,
                    'next_step' => $nextKey,
                ],
            ]);

            if ($nextKey !== null) {
                $this->createTaskForStep($instance, $definition, $nextKey);
            } else {
                // No next step => complete immediately
                $instance->status = 'completed';
                $instance->completed_at = now();
                $instance->save();

                WorkflowEvent::create([
                    'workflow_instance_id' => $instance->id,
                    'actor_id' => $startedBy,
                    'type' => 'workflow_completed',
                    'payload' => ['reason' => 'No actionable steps defined.'],
                ]);
            }

            return $instance;
        });
    }

    public function createNextTask(WorkflowInstance $instance, string $stepKey, ?int $actorId = null): void
    {
        $definition = (array) $instance->version->definition;

        $task = $this->createTaskForStep($instance, $definition, $stepKey);

        WorkflowEvent::create([
            'workflow_instance_id' => $instance->id,
            'actor_id' => $actorId,
            'type' => 'moved_to_next_step',
            'payload' => [
                'next_step' => $stepKey,
                'task_id' => $task->id,
            ],
        ]);
    }

    /**
     * Create a pending task for a step key (assignees resolution will be in Step 4).
     *
     * @param  array<string,mixed>  $definition
     */
    private function createTaskForStep(WorkflowInstance $instance, array $definition, string $stepKey): WorkflowTask
    {
        $step = Definition::findStep($definition, $stepKey);

        if (! is_array($step)) {
            throw new RuntimeException("Step [{$stepKey}] not found in workflow definition.");
        }

        $mode = (string) ($step['mode'] ?? 'any');
        if (! in_array($mode, ['any', 'all'], true)) {
            $mode = 'any';
        }

        // SLA support (optional)
        $dueAt = null;
        $slaHours = $step['sla_hours'] ?? null;
        if (is_int($slaHours) && $slaHours > 0) {
            $dueAt = now()->addHours($slaHours);
        }

        $task = new WorkflowTask([
            'workflow_instance_id' => $instance->id,
            'team_id' => $instance->team_id,
            'step_key' => $stepKey,
            'status' => 'pending',
            'mode' => $mode,
            'due_at' => $dueAt,
        ]);

        $task->save();

        WorkflowEvent::create([
            'workflow_instance_id' => $instance->id,
            'actor_id' => $instance->started_by,
            'type' => 'task_created',
            'payload' => [
                'step_key' => $stepKey,
                'mode' => $mode,
                'due_at' => $dueAt?->toISOString(),
            ],
        ]);

        $this->assignments->assignTask($instance, $task, $step);

        return $task;
    }
}
