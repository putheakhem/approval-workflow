[![Tests](https://github.com/putheakhem/approval-workflow/actions/workflows/tests.yml/badge.svg)](https://github.com/putheakhem/approval-workflow/actions/workflows/tests.yml)
[![Latest Stable Version](https://poser.pugx.org/putheakhem/approval-workflow/v/stable)](https://packagist.org/packages/putheakhem/approval-workflow)
[![License](https://poser.pugx.org/putheakhem/approval-workflow/license)](https://packagist.org/packages/putheakhem/approval-workflow)

---

<div align="center">

## 🇰🇭 Stand with Cambodia • កម្ពុជា

### 🕊️ **Cambodia Needs Peace** 🕊️

We stand in solidarity with our brave soldiers defending Cambodia's sovereignty and territorial integrity. Our hearts are with those protecting our homeland during these challenging times. We call upon the international community to support peaceful resolution and respect for Cambodia's borders.

**🙏 កម្ពុជាត្រូវការសន្តិភាព • Together we stand for peace and sovereignty**

</div>
---

# Approval Workflow for Laravel

A flexible, database-driven approval workflow engine for Laravel applications. It supports multi-step approval chains, parallel approvals, SLA monitoring, and dynamic assignment.

## Features

- **Multiple Workflow Versions**: Define and evolve workflows over time without breaking
  existing instances.
- **Dynamic Assignment**: Assign tasks to users, roles (Spatie Permission integration),
  or managers.
- **Flexible Modes**: Support for 'any' (one person approves) or 'all' (consensus required)
  modes.
- **SLA Monitoring**: Built-in support for task deadlines and breach recording.
- **Delegation**: Automatic redirection of tasks based on user availability
  (vacation/out-of-office).
- **Audit Trail**: Detailed event logging (started, assigned, acted, breached, completed)
  for every action.
- **Conditional Transitions**: Override default flow based on actions
  (approve/reject/changes_requested).

## Documentation

For detailed documentation, please visit [https://laravel-approval-workflow.netlify.app](https://laravel-approval-workflow.netlify.app)


## Installation

Add the package to your `composer.json` or install it via composer:

```bash
composer require putheakhem/approval-workflow
```

## Detailed Examples

For practical code snippets of each feature, check out the [EXAMPLES.md](EXAMPLES.md) file.

## Setup

1. **Publish Configuration & Migrations**:
```bash
php artisan vendor:publish --provider="PutheaKhem\ApprovalWorkflow\ApprovalWorkflowServiceProvider"
```

2. **Run Migrations**:
```bash
php artisan migrate
```

3. **Configure the User Model**:
Ensure `config/approval-workflow.php` points to your User model (usually `App\Models\User`).

4. **Add the Trait**:
Add the `HasWorkflow` trait to any Eloquent model that requires an approval process:

```php
use PutheaKhem\ApprovalWorkflow\Concerns\HasWorkflow;

class ExpenseClaim extends Model
{
    use HasWorkflow;
}
```

## Usage

### 1. Defining a Workflow

Workflows are stored in the database. You typically create them via a seeder or an admin UI.

```php
use PutheaKhem\ApprovalWorkflow\Models\Workflow;
use PutheaKhem\ApprovalWorkflow\Models\WorkflowVersion;

$workflow = Workflow::create(['key' => 'expense-approval', 'name' => 'Expense Approval']);

WorkflowVersion::create([
    'workflow_id' => $workflow->id,
    'version' => 1,
    'published_at' => now(),
    'definition' => [
        'steps' => [
            ['key' => 'start', 'type' => 'start', 'next' => 'manager_step'],
            [
                'key' => 'manager_step',
                'name' => 'Manager Approval',
                'assignment' => ['type' => 'manager_of', 'field' => 'requester_id'],
                'next' => 'end'
            ],
            ['key' => 'end', 'type' => 'end'],
        ],
    ],
]);
```

### 2. Starting a Workflow

```php
$expense = ExpenseClaim::create([...]);
$instance = $expense->startWorkflow('expense-approval', [
    'requester_id' => auth()->id(),
    'amount' => 500
]);
```

### 3. Approving / Rejecting

Tasks are created for each step. Approvers can act on these tasks.

```php
use PutheaKhem\ApprovalWorkflow\Services\ApprovalService;

$service = app(ApprovalService::class);
$task = $instance->currentTasks()->first();

$service->approve($instance, $task->id, auth()->id(), 'Looks good!');
```

### 4. Checking Status

```php
if ($expense->isApproved()) {
    // Logic for approved expense
}

if ($expense->isRejected()) {
    // Logic for rejected expense
}

// Get the full history
$events = $expense->latestWorkflowInstance()->events;
```

## Configuration

The `config/approval-workflow.php` file allows you to customize:
- `user_model`: The model used for approvers.
- `manager_id_column`: The column on the User model pointing to their manager.
- `team_enabled`: Enable/disable team scoping.
- `fail_if_no_assignees`: Whether to throw an exception if no users are found for a step.

## Testing

Run the tests using Pest:

```bash
vendor/bin/pest
```

## License

The MIT License (MIT). Please see [LICENSE](LICENSE) for more information.

