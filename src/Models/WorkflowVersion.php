<?php

declare(strict_types=1);

namespace PutheaKhem\ApprovalWorkflow\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class WorkflowVersion extends Model
{
    protected $table = 'workflow_versions';

    protected $fillable = [
        'workflow_id',
        'version',
        'definition',
        'published_at',
    ];

    protected $casts = [
        'definition' => 'array',
        'published_at' => 'datetime',
    ];

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class, 'workflow_id');
    }

    public function instances(): HasMany
    {
        return $this->hasMany(WorkflowInstance::class, 'workflow_version_id');
    }
}
