<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('motivation_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('application_type_id')->constrained()->restrictOnDelete();
            $table->string('status', 30)->default('draft');
            $table->json('answers')->nullable();
            $table->json('profile_snapshot')->nullable();
            $table->json('blocks_snapshot')->nullable();
            $table->string('pdf_disk', 20)->nullable();
            $table->string('pdf_path')->nullable();
            $table->timestamp('pdf_generated_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('printed_at')->nullable();
            $table->text('reviewer_notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'updated_at']);
        });

        Schema::create('motivation_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('motivation_request_id')->constrained()->cascadeOnDelete();
            $table->string('kind', 50);
            $table->string('disk', 20);
            $table->string('path');
            $table->string('original_name')->nullable();
            $table->string('mime', 100)->nullable();
            $table->unsignedInteger('size')->nullable();
            $table->string('reference_number')->nullable();
            $table->date('issued_on')->nullable();
            $table->date('expires_on')->nullable();
            $table->foreignId('checked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('checked_at')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['motivation_request_id', 'kind']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('motivation_documents');
        Schema::dropIfExists('motivation_requests');
    }
};
