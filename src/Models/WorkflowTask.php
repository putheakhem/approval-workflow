<?php

declare(strict_types=1);

namespace PutheaKhem\ApprovalWorkflow\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class WorkflowTask extends Model
{
    protected $table = 'workflow_tasks';

    protected $fillable = [
        'workflow_instance_id',
        'team_id',
        'step_key',
        'status',
        'mode',
        'due_at',
    ];

    protected $casts = [
        'due_at' => 'datetime',
    ];

    public function instance(): BelongsTo
    {
        return $this->belongsTo(WorkflowInstance::class, 'workflow_instance_id');
    }

    public function assignees(): HasMany
    {
        return $this->hasMany(WorkflowTaskAssignee::class, 'workflow_task_id');
    }
}
