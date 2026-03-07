<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('tp_global_content_usages', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('global_content_id');
            $table->string('owner_type', 40);
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->string('owner_label');
            $table->string('editor_driver', 40)->default('blocks');
            $table->timestamps();

            $table->foreign('global_content_id')
                ->references('id')
                ->on('tp_global_contents')
                ->cascadeOnDelete();

            $table->unique(['global_content_id', 'owner_type', 'owner_id', 'editor_driver'], 'tp_global_content_usage_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tp_global_content_usages');
    }
};
