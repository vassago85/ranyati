<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('storage_agreements')) {
            return;
        }

        Schema::create('storage_agreements', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['deceased_estate', 'self_storage']);
            $table->enum('status', ['active', 'closed'])->default('active');

            // Deceased-estate fields
            $table->string('estate_late')->nullable();
            $table->string('bank')->nullable();
            $table->string('attorneys')->nullable();

            // Self-storage fields
            $table->string('client_name')->nullable();
            $table->string('email')->nullable();
            $table->decimal('storage_rate', 8, 2)->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('type');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('storage_agreements');
    }
};
