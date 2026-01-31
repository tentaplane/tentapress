<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('tp_roles', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique(); // e.g. administrator, editor
            $table->timestamps();
        });

        Schema::create('tp_user_roles', function (Blueprint $table): void {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('role_id');

            $table->primary(['user_id', 'role_id']);

            $table->foreign('user_id')->references('id')->on('tp_users')->cascadeOnDelete();
            $table->foreign('role_id')->references('id')->on('tp_roles')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tp_user_roles');
        Schema::dropIfExists('tp_roles');
    }
};
