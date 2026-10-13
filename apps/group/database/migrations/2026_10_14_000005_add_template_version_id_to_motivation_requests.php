<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('motivation_requests', function (Blueprint $table) {
            // Which template version was last used to generate letters.
            // Nullable so legacy requests (that still use blocks_snapshot)
            // keep working through the fallback path in BookRenderer.
            $table->foreignId('template_version_id')
                ->nullable()
                ->after('application_type_id')
                ->constrained('motivation_template_versions')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('motivation_requests', function (Blueprint $table) {
            $table->dropForeign(['template_version_id']);
            $table->dropColumn('template_version_id');
        });
    }
};
