<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('tp_redirect_suggestions', function (Blueprint $table): void {
            $table->id();
            $table->string('source_path')->index();
            $table->string('target_path');
            $table->unsignedSmallInteger('status_code')->default(301);
            $table->string('origin')->default('manual')->index();
            $table->string('state')->default('pending')->index();
            $table->string('conflict_type')->nullable()->index();
            $table->unsignedBigInteger('decision_by')->nullable()->index();
            $table->timestamp('decision_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tp_redirect_suggestions');
    }
};
