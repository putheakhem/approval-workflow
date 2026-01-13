<?php

declare(strict_types=1);

namespace PutheaKhem\ApprovalWorkflow;

use Illuminate\Support\ServiceProvider;
use PutheaKhem\ApprovalWorkflow\Console\Commands\CheckSlaCommand;
use PutheaKhem\ApprovalWorkflow\Repositories\WorkflowRepository;
use PutheaKhem\ApprovalWorkflow\Resolvers\ManagerOfResolver;
use PutheaKhem\ApprovalWorkflow\Resolvers\RoleResolver;
use PutheaKhem\ApprovalWorkflow\Resolvers\UsersResolver;
use PutheaKhem\ApprovalWorkflow\Services\ApprovalService;
use PutheaKhem\ApprovalWorkflow\Services\AssignmentService;
use PutheaKhem\ApprovalWorkflow\Services\ResolverRegistry;
use PutheaKhem\ApprovalWorkflow\Services\SlaService;
use PutheaKhem\ApprovalWorkflow\Services\WorkflowEngine;

final class ApprovalWorkflowServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/approval-workflow.php', 'approval-workflow');

        $this->app->singleton(ResolverRegistry::class, function () {
            $r = new ResolverRegistry();
            $r->register('users', new UsersResolver());
            $r->register('role', new RoleResolver());
            $r->register('manager_of', new ManagerOfResolver());

            return $r;
        });

        $this->app->singleton(AssignmentService::class);
        $this->app->singleton(ApprovalService::class);

        $this->app->singleton(WorkflowRepository::class);
        $this->app->singleton(WorkflowEngine::class);

        $this->app->singleton(SlaService::class);
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/approval-workflow.php' => config_path('approval-workflow.php'),
        ], 'approval-workflow-config');

        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'approval-workflow-migrations');

        if ($this->app->runningInConsole() && (bool) config('approval-workflow.features.sla_command', false)) {
            if (class_exists(CheckSlaCommand::class)) {
                $this->commands([CheckSlaCommand::class]);
            }
        }
    }
}
