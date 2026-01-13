<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_id')->constrained('workflows')->cascadeOnDelete();

            $table->unsignedInteger('version'); // 1,2,3...
            $table->json('definition');         // steps + transitions JSON
            $table->timestamp('published_at')->nullable();

            $table->timestamps();

            $table->unique(['workflow_id', 'version']);
            $table->index(['workflow_id', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_versions');
    }
};
