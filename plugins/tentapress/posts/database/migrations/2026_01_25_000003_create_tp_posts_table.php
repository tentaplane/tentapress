<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('tp_posts', function (Blueprint $table): void {
            $table->id();

            $table->string('title');
            $table->string('slug')->unique();

            $table->string('status')->default('draft')->index(); // draft|published

            $table->string('layout')->nullable();
            $table->json('blocks')->nullable();

            $table->timestamp('published_at')->nullable()->index();

            $table->unsignedBigInteger('author_id')->nullable()->index();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->unsignedBigInteger('updated_by')->nullable()->index();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tp_posts');
    }
};
