# Approval Workflow Examples

This document provides detailed examples of how to use the various features of the
`approval-workflow` package.

## 1. Multiple Workflow Versions

You can define and evolve workflows over time. When starting a workflow, the latest
published version is automatically used.

```php
use PutheaKhem\ApprovalWorkflow\Models\Workflow;
use PutheaKhem\ApprovalWorkflow\Models\WorkflowVersion;

// 1. Create a Workflow
$workflow = Workflow::create([
    'key' => 'expense-approval',
    'name' => 'Expense Approval Process',
    'is_active' => true,
]);

// 2. Create Version 1
WorkflowVersion::create([
    'workflow_id' => $workflow->id,
    'version' => 1,
    'published_at' => now(),
    'definition' => [
        'steps' => [
            ['key' => 'start', 'type' => 'start', 'next' => 'manager_approval'],
            [
                'key' => 'manager_approval',
                'name' => 'Manager Approval',
                'assignment' => ['type' => 'manager_of', 'field' => 'requester_id'],
                'next' => 'end'
            ],
            ['key' => 'end', 'type' => 'end'],
        ],
    ],
]);

// 3. Later, create Version 2 with an extra Finance step
WorkflowVersion::create([
    'workflow_id' => $workflow->id,
    'version' => 2,
    'published_at' => now(), // New instances will now use this version
    'definition' => [
        'steps' => [
            ['key' => 'start', 'type' => 'start', 'next' => 'manager_approval'],
            [
                'key' => 'manager_approval',
                'name' => 'Manager Approval',
                'assignment' => ['type' => 'manager_of', 'field' => 'requester_id'],
                'next' => 'finance_review'
            ],
            [
                'key' => 'finance_review',
                'name' => 'Finance Review',
                'assignment' => ['type' => 'role', 'roles' => ['finance-officer']],
                'next' => 'end'
            ],
            ['key' => 'end', 'type' => 'end'],
        ],
    ],
]);
```

---

## 2. Dynamic Assignment

Support for different ways to assign tasks to users.

### Assignment by User IDs
```php
'assignment' => [
    'type' => 'users',
    'users' => [1, 2, 3], // Direct IDs
],
```

### Assignment by Role (using Spatie Permission)
```php
'assignment' => [
    'type' => 'role',
    'roles' => ['admin', 'supervisor'],
    'team_from' => 'team_id', // Optional: pull team_id from context
],
```

### Assignment by Manager
```php
'assignment' => [
    'type' => 'manager_of',
    'field' => 'requester_id', // Looks up the manager of the user ID
                                 // provided in this context field
],
```

---

## 3. Flexible Modes

Control whether one person or everyone in a step must approve.

### 'any' Mode (Default)
Any one of the assigned users can approve to move to the next step.
```php
[
    'key' => 'quick_check',
    'mode' => 'any',
    'assignment' => ['type' => 'users', 'users' => [1, 2, 3]],
]
```

### 'all' Mode
Every assigned user must approve before moving to the next step. If any one of them rejects
or requests changes, the task is finished with that status.
```php
[
    'key' => 'board_approval',
    'mode' => 'all',
    'assignment' => ['type' => 'users', 'users' => [10, 11, 12]],
]
```

---

## 4. Conditional Transitions

You can override the default `next` step based on the action taken (approve, reject,
or changes_requested).

```php
'definition' => [
    'steps' => [
        ['key' => 'start', 'type' => 'start', 'next' => 'initial_review'],
        [
            'key' => 'initial_review',
            'name' => 'Initial Review',
            'assignment' => ['type' => 'role', 'roles' => ['reviewer']],
            'next' => 'manager_approval', // Default if approved
        ],
        [
            'key' => 'manager_approval',
            'name' => 'Manager Approval',
            'assignment' => ['type' => 'manager_of', 'field' => 'requester_id'],
            'next' => 'end'
        ],
        ['key' => 'resubmission_step', 'name' => 'Resubmit'],
        ['key' => 'end', 'type' => 'end'],
    ],
    'transitions' => [
        [
            'from' => 'initial_review',
            'on' => 'changes_requested',
            'to' => 'resubmission_step', // Reroute to resubmission instead of manager
        ],
        [
            'from' => 'initial_review',
            'on' => 'reject',
            'to' => 'end', // End immediately on reject
        ],
    ],
],
```

---

## 5. SLA Monitoring

Define deadlines for tasks and track breaches.

### Defining SLA in Workflow
```php
[
    'key' => 'urgent_task',
    'sla_hours' => 24, // Task must be completed within 24 hours
    'assignment' => ['type' => 'role', 'roles' => ['support']],
]
```

### Checking for Breaches
You can run a command or use the service to record SLA breaches in the audit trail.
```php
use PutheaKhem\ApprovalWorkflow\Services\SlaService;

app(SlaService::class)->checkAndRecordBreaches();
```

---

## 6. Delegation

Users can delegate their approval authority to someone else for a period of time.

```php
use PutheaKhem\ApprovalWorkflow\Models\WorkflowDelegation;

WorkflowDelegation::create([
    'from_user_id' => 1, // Manager is on leave
    'to_user_id' => 2,   // Delegate handles approvals
    'starts_at' => now(),
    'ends_at' => now()->addWeek(),
    'is_active' => true,
]);
```
When a task is assigned to User 1 during this period, it will automatically be assigned
to User 2 instead.

---

## 7. Audit Trail

Every action is logged as an event attached to the workflow instance.

```php
$instance = $model->latestWorkflowInstance();

foreach ($instance->events as $event) {
    echo "Event: {$event->type} by User ID: {$event->actor_id}\n";
    print_r($event->payload);
}
```

Common Event Types:
- `workflow_started`
- `task_created`
- `task_assigned`
- `assignee_acted`
- `task_finished`
- `moved_to_next_step`
- `sla_breached`
- `workflow_completed`

