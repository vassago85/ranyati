<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('register_books')) {
            return;
        }

        Schema::create('register_books', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->enum('type', ['deceased_estate', 'self_storage']);
            $table->unsignedSmallInteger('pages')->default(101);
            $table->unsignedTinyInteger('positions_per_page')->default(26);
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('register_books');
    }
};
