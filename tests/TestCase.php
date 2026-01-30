<?php

declare(strict_types=1);

namespace PutheaKhem\ApprovalWorkflow\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use PutheaKhem\ApprovalWorkflow\ApprovalWorkflowServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            ApprovalWorkflowServiceProvider::class,
        ];
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        \Illuminate\Support\Facades\Schema::create('users', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('password');
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('test_subjects', function ($table) {
            $table->id();
            $table->timestamps();
        });
    }

    protected function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');
        config()->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        config()->set('auth.providers.users.model', TestUser::class);
        config()->set('approval-workflow.user_model', TestUser::class);
    }
}

final class TestUser extends \Illuminate\Foundation\Auth\User
{
    protected $table = 'users';

    protected $guarded = [];
}
