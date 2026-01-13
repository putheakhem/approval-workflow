<?php

declare(strict_types=1);

namespace PutheaKhem\ApprovalWorkflow\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use PutheaKhem\ApprovalWorkflow\Models\WorkflowInstance;
use PutheaKhem\ApprovalWorkflow\Services\WorkflowEngine;

trait HasWorkflow
{
    public function workflowInstances(): MorphMany
    {
        return $this->morphMany(WorkflowInstance::class, 'subject', 'subject_type', 'subject_id');
    }

    public function latestWorkflowInstance(): ?WorkflowInstance
    {
        /** @var WorkflowInstance|null $instance */
        $instance = $this->workflowInstances()->latest('id')->first();

        return $instance;
    }

    /**
     * Start a workflow for this model.
     *
     * @param  string  $workflowKey  workflows.key (e.g. "service-approval")
     * @param  array<string,mixed>  $context
     */
    public function startWorkflow(string $workflowKey, array $context = [], ?int $teamId = null, ?int $startedBy = null): WorkflowInstance
    {
        /** @var WorkflowEngine $engine */
        $engine = app(WorkflowEngine::class);

        return $engine->start(
            subject: $this,
            workflowKey: $workflowKey,
            context: $context,
            teamId: $teamId,
            startedBy: $startedBy,
        );
    }
}
