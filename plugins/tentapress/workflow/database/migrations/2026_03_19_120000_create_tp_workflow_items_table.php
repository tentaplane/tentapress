<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('tp_workflow_items', function (Blueprint $table): void {
            $table->id();
            $table->string('resource_type', 32)->index();
            $table->unsignedBigInteger('resource_id')->index();
            $table->string('editorial_state', 32)->default('draft')->index();
            $table->foreignId('owner_user_id')->nullable()->constrained('tp_users')->nullOnDelete();
            $table->foreignId('reviewer_user_id')->nullable()->constrained('tp_users')->nullOnDelete();
            $table->foreignId('approver_user_id')->nullable()->constrained('tp_users')->nullOnDelete();
            $table->foreignId('pending_revision_id')->nullable()->constrained('tp_revisions')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('scheduled_publish_at')->nullable()->index();
            $table->foreignId('last_transitioned_by')->nullable()->constrained('tp_users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['resource_type', 'resource_id'], 'tp_workflow_items_unique_resource');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tp_workflow_items');
    }
};
