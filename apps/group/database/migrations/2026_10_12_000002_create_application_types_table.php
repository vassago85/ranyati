<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('category', 30);
            $table->text('description')->nullable();
            $table->boolean('has_motivation')->default(true);
            $table->json('questions')->nullable();
            $table->json('document_requirements')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('application_type_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_type_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sort')->default(0);
            $table->string('kind', 40);
            $table->json('settings')->nullable();
            $table->json('condition')->nullable();
            $table->timestamps();

            $table->index(['application_type_id', 'sort']);
        });

        Schema::create('template_assets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('path');
            $table->string('mime', 100)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('template_assets');
        Schema::dropIfExists('application_type_blocks');
        Schema::dropIfExists('application_types');
    }
};
