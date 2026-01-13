<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_instance_id')->constrained('workflow_instances')->cascadeOnDelete();

            $table->unsignedBigInteger('team_id')->nullable();

            $table->string('step_key'); // matches definition step key
            $table->string('status')->default('pending'); // pending|approved|rejected|changes_requested|skipped|cancelled

            // parallel approval strategy for assignees
            $table->string('mode')->default('any'); // any|all

            $table->timestamp('due_at')->nullable();

            $table->timestamps();

            $table->index(['workflow_instance_id', 'status']);
            $table->index(['team_id', 'status']);
            $table->index(['step_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_tasks');
    }
};
