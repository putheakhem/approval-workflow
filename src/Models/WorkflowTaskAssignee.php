<?php

declare(strict_types=1);

namespace PutheaKhem\ApprovalWorkflow\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class WorkflowTaskAssignee extends Model
{
    protected $table = 'workflow_task_assignees';

    protected $fillable = [
        'workflow_task_id',
        'team_id',
        'user_id',
        'status',
        'acted_at',
        'acted_by',
        'notes',
        'meta',
    ];

    protected $casts = [
        'acted_at' => 'datetime',
        'meta' => 'array',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(WorkflowTask::class, 'workflow_task_id');
    }
}
