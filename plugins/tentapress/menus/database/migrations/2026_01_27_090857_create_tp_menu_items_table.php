<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('tp_menu_items', function (Blueprint $table): void {
            $table->id();

            $table->unsignedBigInteger('menu_id')->index();
            $table->unsignedBigInteger('parent_id')->nullable()->index();

            $table->string('title');
            $table->string('url', 2048);
            $table->string('target', 16)->nullable();

            $table->integer('sort_order')->default(0)->index();
            $table->json('meta')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tp_menu_items');
    }
};
