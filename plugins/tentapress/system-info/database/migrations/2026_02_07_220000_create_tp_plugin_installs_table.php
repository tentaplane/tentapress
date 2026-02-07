<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('tp_plugin_installs', function (Blueprint $table): void {
            $table->id();
            $table->string('package');
            $table->string('status')->index();
            $table->unsignedBigInteger('requested_by')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->longText('output')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();

            $table->index(['package', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tp_plugin_installs');
    }
};
