<?php

declare(strict_types=1);

namespace PutheaKhem\ApprovalWorkflow\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class WorkflowEvent extends Model
{
    protected $table = 'workflow_events';

    protected $fillable = [
        'workflow_instance_id',
        'actor_id',
        'type',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function instance(): BelongsTo
    {
        return $this->belongsTo(WorkflowInstance::class, 'workflow_instance_id');
    }
}
