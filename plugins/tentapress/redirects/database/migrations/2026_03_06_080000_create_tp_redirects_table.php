<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('tp_redirects', function (Blueprint $table): void {
            $table->id();
            $table->string('source_path')->unique();
            $table->string('target_path');
            $table->unsignedSmallInteger('status_code')->default(301);
            $table->boolean('is_enabled')->default(true)->index();
            $table->string('origin')->default('manual')->index();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->unsignedBigInteger('updated_by')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tp_redirects');
    }
};
