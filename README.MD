[![Latest Stable Version](https://img.shields.io/packagist/v/putheakhem/approval-workflow.svg?style=flat-square)](https://packagist.org/packages/putheakhem/approval-workflow)
[![Total Downloads](https://img.shields.io/packagist/dt/putheakhem/approval-workflow.svg?style=flat-square)](https://packagist.org/packages/putheakhem/approval-workflow)

# Laravel Approval Workflow Package

A flexible, domain-driven approval workflow engine for Laravel applications.

This package is designed for projects that require **multi-step approval chains**, such as government systems, enterprise platforms, and internal business workflows — without enforcing any UI, API, or notification structure.

---

## ✨ Features

- **Declarative Workflow Configuration**  
  Define approval workflows using PHP or JSON definitions with versioning support.

- **Multi-Step & Parallel Approvals**  
  Support sequential and parallel approval steps using `any` or `all` approval modes.

- **Flexible Approver Resolution**  
  Resolve approvers by:
    - Explicit users
    - Roles (Spatie Laravel Permission – auto detected)
    - Manager hierarchy
    - Delegation rules

- **Team / Department Scoping**  
  Optionally scope workflows, tasks, and approvers by `team_id`.

- **Event-Driven Architecture**  
  Emit workflow domain events (`TaskAssigned`, `WorkflowCompleted`, `SlaBreached`, etc.) so applications can plug in notifications (Telegram, Email, SMS).

- **Comprehensive Audit Trail**  
  Record every workflow action in an immutable event log for transparency and compliance.

- **Delegation Support**  
  Allow users to delegate approval authority within a defined time window.

- **SLA Support (Optional)**  
  Track deadlines and detect overdue approval tasks via an opt-in SLA checker.

- **UI & API Agnostic**  
  No controllers, routes, views, or APIs are enforced.  
  Each application decides how workflows are exposed.

---

## 📦 Installation

You can install the package via Packagist:

composer require putheakhem/approval-workflow

---

## ⚙️ Setup

Publish configuration and migrations:

php artisan vendor:publish --tag=approval-workflow-config  
php artisan vendor:publish --tag=approval-workflow-migrations  
php artisan migrate

---

## 🧩 Make a Model Approvable

Use the HasWorkflow trait on any Eloquent model:

use PutheaKhem\ApprovalWorkflow\Concerns\HasWorkflow;

class Term extends Model
{
use HasWorkflow;
}

---

## 🚀 Starting a Workflow

$instance = $service->startWorkflow(
workflowKey: 'service-approval',
context: [
'team_id' => 1,
'requester_id' => auth()->id(),
],
teamId: 1,
startedBy: auth()->id(),
);

---

## 🧠 Example Workflow Definition

[
'steps' => [
['key' => 'start', 'type' => 'start', 'next' => 'review'],
[
'key' => 'review',
'type' => 'approval',
'mode' => 'any',
'sla_hours' => 24,
'assignment' => [
'type' => 'role',
'roles' => ['reviewer'],
],
'next' => 'end',
],
['key' => 'end', 'type' => 'end'],
],
'transitions' => [
['from' => 'review', 'on' => 'reject', 'to' => 'end'],
],
]

---

## ✅ Approving & Rejecting Tasks

use PutheaKhem\ApprovalWorkflow\Services\ApprovalService;

$approval = app(ApprovalService::class);

$approval->approve($instance, $taskId, auth()->id(), 'Approved');
$approval->reject($instance, $taskId, auth()->id(), 'Rejected');
$approval->requestChanges($instance, $taskId, auth()->id(), 'Please revise');

---

## 📜 Audit Trail

All workflow actions are recorded in the workflow_events table, including:

- workflow_started
- task_created
- task_assigned
- assignee_acted
- task_finished
- workflow_completed
- sla_breached

---

## ⏱ SLA Support (Optional)

Enable via environment variable:

APPROVAL_WORKFLOW_SLA_COMMAND=true

Run manually:

php artisan workflow:check-sla

---

## 🚫 What This Package Does Not Do

- Controllers or API routes
- UI or view components
- Notification delivery

These concerns are intentionally handled by the consuming application.

---

## 📄 License

MIT License © Puthea Khem