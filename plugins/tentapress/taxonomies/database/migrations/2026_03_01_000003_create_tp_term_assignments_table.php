<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('tp_term_assignments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('taxonomy_id')->constrained('tp_taxonomies')->cascadeOnDelete();
            $table->foreignId('term_id')->constrained('tp_terms')->cascadeOnDelete();
            $table->morphs('assignable');
            $table->timestamps();

            $table->unique(['term_id', 'assignable_type', 'assignable_id'], 'tp_term_assignments_unique_term_assignable');
            $table->index(['taxonomy_id', 'assignable_type', 'assignable_id'], 'tp_term_assignments_taxonomy_assignable_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tp_term_assignments');
    }
};
