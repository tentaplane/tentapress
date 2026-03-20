<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('tp_content_types', function (Blueprint $table): void {
            $table->id();
            $table->string('key')->unique();
            $table->string('singular_label');
            $table->string('plural_label');
            $table->text('description')->nullable();
            $table->string('base_path')->unique();
            $table->string('default_layout')->nullable();
            $table->string('default_editor_driver')->default('blocks');
            $table->boolean('archive_enabled')->default(true);
            $table->string('api_visibility')->default('disabled');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tp_content_types');
    }
};
