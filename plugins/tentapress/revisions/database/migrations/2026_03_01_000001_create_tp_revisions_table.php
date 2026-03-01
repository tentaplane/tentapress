<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('tp_revisions', function (Blueprint $table): void {
            $table->id();
            $table->string('resource_type', 32);
            $table->unsignedBigInteger('resource_id');
            $table->string('title');
            $table->string('slug');
            $table->string('status', 32);
            $table->string('layout')->nullable();
            $table->string('editor_driver', 64)->default('blocks');
            $table->json('blocks')->nullable();
            $table->json('content')->nullable();
            $table->unsignedBigInteger('author_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->string('snapshot_hash', 40);
            $table->timestamps();

            $table->index(['resource_type', 'resource_id']);
            $table->index(['resource_type', 'resource_id', 'id']);
            $table->unique(['resource_type', 'resource_id', 'snapshot_hash'], 'tp_revisions_resource_hash_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tp_revisions');
    }
};
