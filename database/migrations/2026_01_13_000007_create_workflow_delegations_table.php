<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_delegations', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('team_id')->nullable();

            $table->unsignedBigInteger('from_user_id'); // original approver
            $table->unsignedBigInteger('to_user_id');   // delegate

            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();

            $table->string('reason')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['from_user_id', 'is_active']);
            $table->index(['to_user_id', 'is_active']);
            $table->index(['team_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_delegations');
    }
};
