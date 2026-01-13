<?php

declare(strict_types=1);

namespace PutheaKhem\ApprovalWorkflow\Repositories;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use PutheaKhem\ApprovalWorkflow\Models\Workflow;
use PutheaKhem\ApprovalWorkflow\Models\WorkflowVersion;

final class WorkflowRepository
{
    public function findWorkflowByKey(string $key): Workflow
    {
        /** @var Workflow $workflow */
        $workflow = Workflow::query()->where('key', $key)->where('is_active', true)->firstOrFail();

        return $workflow;
    }

    public function latestPublishedVersion(string $workflowKey): WorkflowVersion
    {
        $workflow = $this->findWorkflowByKey($workflowKey);

        $version = $workflow->versions()
            ->whereNotNull('published_at')
            ->orderByDesc('version')
            ->first();

        if (! $version instanceof WorkflowVersion) {
            throw new ModelNotFoundException("No published version found for workflow key [{$workflowKey}].");
        }

        return $version;
    }
}
