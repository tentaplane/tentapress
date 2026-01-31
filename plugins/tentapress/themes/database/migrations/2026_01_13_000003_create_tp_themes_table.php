<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('tp_themes', function (Blueprint $table): void {
            $table->string('id')->primary(); // vendor/name
            $table->string('name');
            $table->string('version')->nullable();
            $table->string('path');          // relative path in repo
            $table->json('manifest');        // raw tentapress.json
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tp_themes');
    }
};
