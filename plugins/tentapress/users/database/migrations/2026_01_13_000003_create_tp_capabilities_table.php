<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('tp_capabilities', function (Blueprint $table): void {
            $table->string('key')->primary(); // e.g. manage_pages
            $table->string('label');
            $table->string('group')->nullable(); // e.g. Content, Appearance, System
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('tp_role_capability', function (Blueprint $table): void {
            $table->unsignedBigInteger('role_id');
            $table->string('capability_key');

            $table->primary(['role_id', 'capability_key']);

            $table->foreign('role_id')->references('id')->on('tp_roles')->cascadeOnDelete();
            $table->foreign('capability_key')->references('key')->on('tp_capabilities')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tp_role_capabilities');
        Schema::dropIfExists('tp_capabilities');
    }
};
