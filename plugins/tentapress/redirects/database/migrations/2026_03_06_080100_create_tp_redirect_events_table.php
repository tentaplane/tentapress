<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('tp_redirect_events', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('redirect_id')->nullable()->index();
            $table->string('action');
            $table->string('source_path');
            $table->string('target_path');
            $table->unsignedBigInteger('actor_user_id')->nullable()->index();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tp_redirect_events');
    }
};
