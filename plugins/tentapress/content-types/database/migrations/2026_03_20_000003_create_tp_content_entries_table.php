<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('tp_content_entries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('content_type_id')->constrained('tp_content_types')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->string('status')->default('draft');
            $table->string('layout')->nullable();
            $table->string('editor_driver')->default('blocks');
            $table->json('blocks')->nullable();
            $table->json('content')->nullable();
            $table->json('field_values')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->unique(['content_type_id', 'slug']);
            $table->index(['content_type_id', 'status', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tp_content_entries');
    }
};
