<?php

declare(strict_types=1);

return [
    /**
     * The user model class used for approvals (defaults to auth provider model).
     */
    'user_model' => env('APPROVAL_WORKFLOW_USER_MODEL', config('auth.providers.users.model')),

    /**
     * If your system uses team scope, set this to true (keeps team_id optional/nullable).
     * Even if false, schema still supports team_id as nullable.
     */
    'team_enabled' => env('APPROVAL_WORKFLOW_TEAM_ENABLED', true),

    /**
     * Column name to use for team scoping.
     */
    'team_column' => env('APPROVAL_WORKFLOW_TEAM_COLUMN', 'team_id'),

    /**
     * Default pagination per page for tasks/events endpoints (if you add later).
     */
    'per_page' => 20,

    /**
     * Manager resolver config:
     * - manager_id_column: the column on User that points to their manager user_id
     */
    'manager_id_column' => env('APPROVAL_WORKFLOW_MANAGER_ID_COLUMN', 'manager_id'),

    /**
     * If you use Spatie Permission team support, set the team foreign key column
     * used by Spatie (commonly: team_id).
     */
    'spatie_team_foreign_key' => env('APPROVAL_WORKFLOW_SPATIE_TEAM_FK', 'team_id'),

    'fail_if_no_assignees' => env('APPROVAL_WORKFLOW_FAIL_IF_NO_ASSIGNEES', true),

    'sla' => [
        // consider a task breached only after this grace period
        'grace_minutes' => (int) env('APPROVAL_WORKFLOW_SLA_GRACE_MINUTES', 0),

        // safety cap per run
        'max_per_run' => (int) env('APPROVAL_WORKFLOW_SLA_MAX_PER_RUN', 200),

    ],
    'features' => [
        'sla_command' => (bool) env('APPROVAL_WORKFLOW_SLA_COMMAND', false),
    ],
];
