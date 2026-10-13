<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('motivation_letters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('motivation_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('template_version_id')->constrained('motivation_template_versions')->restrictOnDelete();
            // Per-firearm letter: index into the firearms_table answer (1-based).
            // Null for a single combined letter that covers all firearms.
            $table->unsignedInteger('firearm_index')->nullable();
            // Snapshot of the firearm row at generation time so later edits to
            // the member's answers don't rewrite history on a printed letter.
            $table->string('firearm_serial')->nullable();
            // Ordered segments: each one is either {type: html, html} or
            // {type: documents, title, document_ids}. Admin edits to a draft
            // modify the html entries in place; the pdf is re-rendered off
            // this snapshot.
            $table->json('snapshot')->nullable();
            $table->string('pdf_disk', 20)->nullable();
            $table->string('pdf_path')->nullable();
            $table->string('status', 20)->default('draft');
            // Queue lifecycle: queued -> running -> done | failed.
            $table->string('generation_status', 20)->default('queued');
            $table->text('error')->nullable();
            $table->json('missing')->nullable();
            $table->json('warnings')->nullable();
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('generated_at')->nullable();
            $table->foreignId('finalised_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('finalised_at')->nullable();
            $table->timestamps();

            $table->index(['motivation_request_id', 'status']);
            $table->index(['motivation_request_id', 'firearm_index']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('motivation_letters');
    }
};
