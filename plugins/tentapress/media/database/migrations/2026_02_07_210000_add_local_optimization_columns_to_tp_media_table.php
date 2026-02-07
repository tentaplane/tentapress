<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('tp_media', function (Blueprint $table): void {
            $table->unsignedInteger('source_width')->nullable()->after('height');
            $table->unsignedInteger('source_height')->nullable()->after('source_width');
            $table->json('variants')->nullable()->after('source_height');
            $table->string('preview_variant')->nullable()->after('variants');
            $table->string('optimization_status')->nullable()->after('preview_variant');
            $table->text('optimization_error')->nullable()->after('optimization_status');
        });
    }

    public function down(): void
    {
        Schema::table('tp_media', function (Blueprint $table): void {
            $table->dropColumn([
                'source_width',
                'source_height',
                'variants',
                'preview_variant',
                'optimization_status',
                'optimization_error',
            ]);
        });
    }
};
