<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('tp_workflow_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('workflow_item_id')->constrained('tp_workflow_items')->cascadeOnDelete();
            $table->string('resource_type', 32)->index();
            $table->unsignedBigInteger('resource_id')->index();
            $table->string('event_type', 64)->index();
            $table->string('from_state', 32)->nullable();
            $table->string('to_state', 32)->nullable();
            $table->foreignId('actor_user_id')->nullable()->constrained('tp_users')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tp_workflow_events');
    }
};
