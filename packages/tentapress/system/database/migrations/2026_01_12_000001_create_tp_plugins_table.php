<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('tp_plugins', function (Blueprint $table): void {
            $table->string('id')->primary();          // vendor/name
            $table->boolean('enabled')->default(false)->index();
            $table->string('version')->nullable();
            $table->string('provider');               // FQCN
            $table->string('path');                   // relative path in repo
            $table->json('manifest');                 // raw tentapress.json

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tp_plugins');
    }
};
