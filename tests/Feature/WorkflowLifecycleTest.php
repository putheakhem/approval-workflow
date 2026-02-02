<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use PutheaKhem\ApprovalWorkflow\Concerns\HasWorkflow;
use PutheaKhem\ApprovalWorkflow\Models\Workflow;
use PutheaKhem\ApprovalWorkflow\Models\WorkflowInstance;
use PutheaKhem\ApprovalWorkflow\Models\WorkflowVersion;
use PutheaKhem\ApprovalWorkflow\Services\ApprovalService;
use PutheaKhem\ApprovalWorkflow\Services\WorkflowEngine;
use PutheaKhem\ApprovalWorkflow\Tests\TestUser;

final class TestSubject extends Model
{
    use HasWorkflow;

    protected $guarded = [];
}

beforeEach(function () {
    $this->workflow = Workflow::create([
        'key' => 'test-workflow',
        'name' => 'Test Workflow',
        'is_active' => true,
    ]);

    $this->version = WorkflowVersion::create([
        'workflow_id' => $this->workflow->id,
        'version' => 1,
        'published_at' => now(),
        'definition' => [
            'steps' => [
                ['key' => 'start', 'type' => 'start', 'next' => 'step_1'],
                [
                    'key' => 'step_1',
                    'name' => 'Step 1',
                    'mode' => 'any',
                    'assignment' => [
                        'type' => 'users',
                        'users' => [1],
                    ],
                    'next' => 'end',
                ],
                ['key' => 'end', 'type' => 'end'],
            ],
        ],
    ]);

    $this->user = TestUser::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);
});

test('it can start a workflow', function () {
    $subject = TestSubject::create([]);

    /** @var WorkflowEngine $engine */
    $engine = app(WorkflowEngine::class);
    $instance = $engine->start($subject, 'test-workflow', ['requester_id' => $this->user->id]);

    expect($instance)->toBeInstanceOf(WorkflowInstance::class)
        ->and($instance->status)->toBe('running')
        ->and($instance->tasks()->count())->toBe(1);

    $task = $instance->tasks()->first();
    expect($task->step_key)->toBe('step_1')
        ->and($task->assignees()->count())->toBe(1)
        ->and($task->assignees()->first()->user_id)->toBe(1);
});

test('it can approve a workflow', function () {
    $subject = TestSubject::create([]);

    $instance = $subject->startWorkflow('test-workflow', ['requester_id' => $this->user->id]);
    $task = $instance->tasks()->first();

    /** @var ApprovalService $service */
    $service = app(ApprovalService::class);
    $service->approve($instance, $task->id, $this->user->id, 'Looks good');

    $instance->refresh();
    expect($instance->status)->toBe('completed')
        ->and($subject->isApproved())->toBeTrue();
});

test('it can reject a workflow', function () {
    $subject = TestSubject::create([]);

    $instance = $subject->startWorkflow('test-workflow', ['requester_id' => $this->user->id]);
    $task = $instance->tasks()->first();

    /** @var ApprovalService $service */
    $service = app(ApprovalService::class);
    $service->reject($instance, $task->id, $this->user->id, 'No way');

    $instance->refresh();
    expect($instance->status)->toBe('rejected')
        ->and($subject->isRejected())->toBeTrue();
});
