<?php

declare(strict_types=1);

namespace PutheaKhem\ApprovalWorkflow\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Workflow extends Model
{
    protected $table = 'workflows';

    protected $fillable = [
        'key',
        'name',
        'is_active',
        'created_by_type',
        'created_by_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function versions(): HasMany
    {
        return $this->hasMany(WorkflowVersion::class, 'workflow_id');
    }

    public function latestPublishedVersion(): ?WorkflowVersion
    {
        /** @var WorkflowVersion|null $v */
        $v = $this->versions()
            ->whereNotNull('published_at')
            ->orderByDesc('version')
            ->first();

        return $v;
    }
}
