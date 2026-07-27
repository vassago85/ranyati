<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('storage_items')) {
            return;
        }

        Schema::create('storage_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('storage_agreement_id')->constrained('storage_agreements')->cascadeOnDelete();

            // Register reference — page/position slots are never reassigned,
            // even after a firearm is released. Uniqueness enforced at the
            // DB layer as the legal register is the source of truth.
            $table->foreignId('register_book_id')->constrained('register_books')->restrictOnDelete();
            $table->unsignedSmallInteger('page');
            $table->unsignedTinyInteger('position');

            // Physical location tag — reusable once the item is released,
            // so uniqueness is enforced in the FormRequest against active
            // (in_custody) items only, not at the DB layer.
            $table->string('shelf', 2);
            $table->string('tag_colour', 1);
            $table->unsignedSmallInteger('tag_number');

            $table->string('firearm_make');
            $table->string('cartridge');
            $table->string('serial_number');
            $table->enum('firearm_type', ['rifle', 'shotgun', 'handgun']);
            $table->string('action_type');
            $table->text('condition_notes')->nullable();

            $table->date('date_in');
            $table->enum('status', ['in_custody', 'released'])->default('in_custody');

            $table->timestamps();

            $table->unique(['register_book_id', 'page', 'position'], 'storage_items_register_slot_unique');
            $table->index(['status', 'date_in']);
            $table->index(['shelf', 'tag_colour', 'tag_number']);
            $table->index('serial_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('storage_items');
    }
};
