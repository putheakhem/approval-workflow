<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_task_assignees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_task_id')->constrained('workflow_tasks')->cascadeOnDelete();

            $table->unsignedBigInteger('team_id')->nullable();
            $table->unsignedBigInteger('user_id');

            $table->string('status')->default('pending'); // pending|approved|rejected|changes_requested|delegated|cancelled
            $table->timestamp('acted_at')->nullable();

            // if delegate/substitute acted
            $table->unsignedBigInteger('acted_by')->nullable();

            $table->text('notes')->nullable();
            $table->json('meta')->nullable();

            $table->timestamps();

            $table->unique(['workflow_task_id', 'user_id']);
            $table->index(['user_id', 'status']);
            $table->index(['team_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_task_assignees');
    }
};
