<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_type_template', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('template_id')->constrained('motivation_templates')->cascadeOnDelete();
            $table->boolean('is_default')->default(false);
            $table->unsignedInteger('sort')->default(0);
            // Same {field, operator, value} shape as block conditions. When
            // non-null, auto-select picks the first attached template whose
            // condition passes.
            $table->json('condition')->nullable();
            $table->timestamps();

            $table->unique(['application_type_id', 'template_id']);
            $table->index(['application_type_id', 'sort']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_type_template');
    }
};
