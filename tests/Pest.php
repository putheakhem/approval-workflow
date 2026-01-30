<?php

declare(strict_types=1);

use PutheaKhem\ApprovalWorkflow\Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in(__DIR__);
