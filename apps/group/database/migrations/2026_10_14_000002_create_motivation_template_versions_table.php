<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('motivation_template_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('motivation_templates')->cascadeOnDelete();
            $table->unsignedInteger('version');
            // Blocks: ordered array of {id, kind, settings, condition}.
            $table->json('blocks');
            // Page + header/footer + letter_mode settings.
            $table->json('settings');
            $table->timestamp('published_at')->nullable();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['template_id', 'version']);
            $table->index(['template_id', 'published_at']);
        });

        // Close the pointer loop from motivation_templates now that
        // motivation_template_versions exists.
        Schema::table('motivation_templates', function (Blueprint $table) {
            $table->foreign('published_version_id')
                ->references('id')->on('motivation_template_versions')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('motivation_templates', function (Blueprint $table) {
            $table->dropForeign(['published_version_id']);
        });
        Schema::dropIfExists('motivation_template_versions');
    }
};
