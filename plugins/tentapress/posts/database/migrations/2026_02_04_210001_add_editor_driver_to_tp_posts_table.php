<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('tp_posts')) {
            return;
        }

        Schema::table('tp_posts', function (Blueprint $table): void {
            if (! Schema::hasColumn('tp_posts', 'editor_driver')) {
                $table->string('editor_driver')->default('blocks')->after('layout');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('tp_posts')) {
            return;
        }

        Schema::table('tp_posts', function (Blueprint $table): void {
            if (Schema::hasColumn('tp_posts', 'editor_driver')) {
                $table->dropColumn('editor_driver');
            }
        });
    }
};
