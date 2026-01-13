<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_instance_id')->constrained('workflow_instances')->cascadeOnDelete();

            $table->unsignedBigInteger('actor_id')->nullable(); // user who triggered event
            $table->string('type'); // e.g. workflow_started, task_assigned, task_approved, comment...
            $table->json('payload')->nullable();

            $table->timestamps();

            $table->index(['workflow_instance_id', 'type']);
            $table->index(['actor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_events');
    }
};
