<?php

declare(strict_types=1);

namespace PutheaKhem\ApprovalWorkflow\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

final class WorkflowInstance extends Model
{
    protected $table = 'workflow_instances';

    protected $fillable = [
        'workflow_version_id',
        'subject_type',
        'subject_id',
        'team_id',
        'status',
        'context',
        'started_by',
        'completed_at',
    ];

    protected $casts = [
        'context' => 'array',
        'completed_at' => 'datetime',
    ];

    public function version(): BelongsTo
    {
        return $this->belongsTo(WorkflowVersion::class, 'workflow_version_id');
    }

    public function subject(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'subject_type', 'subject_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(WorkflowTask::class, 'workflow_instance_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(WorkflowEvent::class, 'workflow_instance_id');
    }

    public function currentTasks(): HasMany
    {
        return $this->tasks()->where('status', 'pending');
    }
}
