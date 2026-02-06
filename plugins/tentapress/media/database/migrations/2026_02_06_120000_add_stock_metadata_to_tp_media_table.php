<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('tp_media', function (Blueprint $table): void {
            $table->string('source')->nullable()->after('caption');
            $table->string('source_item_id')->nullable()->after('source');
            $table->string('source_url')->nullable()->after('source_item_id');
            $table->string('license')->nullable()->after('source_url');
            $table->string('license_url')->nullable()->after('license');
            $table->text('attribution')->nullable()->after('license_url');
            $table->text('attribution_html')->nullable()->after('attribution');
            $table->json('stock_meta')->nullable()->after('attribution_html');
        });
    }

    public function down(): void
    {
        Schema::table('tp_media', function (Blueprint $table): void {
            $table->dropColumn([
                'source',
                'source_item_id',
                'source_url',
                'license',
                'license_url',
                'attribution',
                'attribution_html',
                'stock_meta',
            ]);
        });
    }
};
