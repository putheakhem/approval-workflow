<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_instances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_version_id')->constrained('workflow_versions')->cascadeOnDelete();

            // Subject model (anything that is approvable)
            $table->string('subject_type');
            $table->unsignedBigInteger('subject_id');

            // Optional team scope (department/regulator/etc.)
            $table->unsignedBigInteger('team_id')->nullable();

            $table->string('status')->default('running'); // running|completed|rejected|cancelled
            $table->json('context')->nullable();          // snapshot data for routing/conditions

            $table->unsignedBigInteger('started_by')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->index(['subject_type', 'subject_id']);
            $table->index(['workflow_version_id', 'status']);
            $table->index(['team_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_instances');
    }
};
