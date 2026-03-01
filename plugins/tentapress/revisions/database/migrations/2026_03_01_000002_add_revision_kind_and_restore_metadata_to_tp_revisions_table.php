<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('tp_revisions', function (Blueprint $table): void {
            $table->dropUnique('tp_revisions_resource_hash_unique');

            $table->string('revision_kind', 32)->default('manual')->after('resource_id');
            $table->unsignedBigInteger('restored_from_revision_id')->nullable()->after('created_by');

            $table->unique(
                ['resource_type', 'resource_id', 'revision_kind', 'snapshot_hash'],
                'tp_revisions_resource_kind_hash_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('tp_revisions', function (Blueprint $table): void {
            $table->dropUnique('tp_revisions_resource_kind_hash_unique');
            $table->dropColumn(['revision_kind', 'restored_from_revision_id']);
            $table->unique(['resource_type', 'resource_id', 'snapshot_hash'], 'tp_revisions_resource_hash_unique');
        });
    }
};
