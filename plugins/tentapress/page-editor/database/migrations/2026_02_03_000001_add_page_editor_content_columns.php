<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('tp_pages') && ! Schema::hasColumn('tp_pages', 'content')) {
            Schema::table('tp_pages', function (Blueprint $table): void {
                $table->json('content')->nullable()->after('blocks');
            });
        }

        if (Schema::hasTable('tp_posts') && ! Schema::hasColumn('tp_posts', 'content')) {
            Schema::table('tp_posts', function (Blueprint $table): void {
                $table->json('content')->nullable()->after('blocks');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tp_pages') && Schema::hasColumn('tp_pages', 'content')) {
            Schema::table('tp_pages', function (Blueprint $table): void {
                $table->dropColumn('content');
            });
        }

        if (Schema::hasTable('tp_posts') && Schema::hasColumn('tp_posts', 'content')) {
            Schema::table('tp_posts', function (Blueprint $table): void {
                $table->dropColumn('content');
            });
        }
    }
};
