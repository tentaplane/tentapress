<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('tp_content_type_fields', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('content_type_id')->constrained('tp_content_types')->cascadeOnDelete();
            $table->string('key');
            $table->string('label');
            $table->string('field_type');
            $table->unsignedInteger('sort_order')->default(1);
            $table->boolean('required')->default(false);
            $table->json('config')->nullable();
            $table->timestamps();

            $table->unique(['content_type_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tp_content_type_fields');
    }
};
