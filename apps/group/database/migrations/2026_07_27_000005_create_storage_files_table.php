<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('storage_files')) {
            return;
        }

        Schema::create('storage_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('storage_item_id')->constrained('storage_items')->cascadeOnDelete();
            $table->enum('kind', ['licence', 'photo', 'other']);
            $table->string('disk')->default('custody');
            $table->string('path');
            $table->string('original_name')->nullable();
            $table->timestamps();

            $table->index(['storage_item_id', 'kind']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('storage_files');
    }
};
