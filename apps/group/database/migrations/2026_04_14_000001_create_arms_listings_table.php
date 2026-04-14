<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('arms_listings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('make');
            $table->string('model');
            $table->string('calibre');
            $table->text('accessories')->nullable();
            $table->decimal('price', 10, 2);
            $table->text('description')->nullable();
            $table->json('images')->nullable();
            $table->enum('status', ['active', 'reduced', 'archived'])->default('active');
            $table->timestamp('featured_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['status', 'featured_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('arms_listings');
    }
};
