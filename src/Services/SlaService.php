<?php

declare(strict_types=1);

namespace PutheaKhem\ApprovalWorkflow\Services;

use Illuminate\Support\Facades\DB;
use PutheaKhem\ApprovalWorkflow\Events\SlaBreached;
use PutheaKhem\ApprovalWorkflow\Models\WorkflowEvent;
use PutheaKhem\ApprovalWorkflow\Models\WorkflowTask;

final class SlaService
{
    public function checkAndRecordBreaches(?int $limit = null): int
    {
        $grace = (int) (config('approval-workflow.sla.grace_minutes', 0));
        $maxPerRun = (int) (config('approval-workflow.sla.max_per_run', 200));
        $limit = $limit ?? $maxPerRun;

        $threshold = now()->subMinutes($grace);

        // tasks overdue and still pending
        $tasks = WorkflowTask::query()
            ->with('instance')
            ->where('status', 'pending')
            ->whereNotNull('due_at')
            ->where('due_at', '<', $threshold)
            ->orderBy('due_at')
            ->limit($limit)
            ->get();

        $count = 0;

        foreach ($tasks as $task) {
            $created = DB::transaction(function () use ($task) {
                // prevent duplicates: check if we already logged sla_breached for this task
                $already = WorkflowEvent::query()
                    ->where('workflow_instance_id', $task->workflow_instance_id)
                    ->where('type', 'sla_breached')
                    ->where('payload->task_id', $task->id)
                    ->exists();

                if ($already) {
                    return false;
                }

                WorkflowEvent::create([
                    'workflow_instance_id' => $task->workflow_instance_id,
                    'actor_id' => null,
                    'type' => 'sla_breached',
                    'payload' => [
                        'task_id' => $task->id,
                        'step_key' => $task->step_key,
                        'due_at' => optional($task->due_at)?->toISOString(),
                        'team_id' => $task->team_id,
                    ],
                ]);

                return true;
            });

            if ($created) {
                $count++;
                event(new SlaBreached($task));
            }
        }

        return $count;
    }
}
