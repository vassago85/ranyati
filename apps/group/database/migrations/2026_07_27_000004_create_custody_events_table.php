<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('custody_events')) {
            return;
        }

        // Append-only ledger. There is no update path in the admin UI, and
        // the CustodyEvent model itself throws on any update/delete attempt
        // (see App\Models\CustodyEvent::booted()). Corrections are added as
        // new rows with event_type = 'correction'.
        Schema::create('custody_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('storage_item_id')->constrained('storage_items')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();

            $table->enum('event_type', [
                'intake',
                'inspection',
                'transfer_internal',
                'release',
                'correction',
                'note',
            ]);
            $table->text('notes')->nullable();

            // Release-only fields
            $table->string('released_to_name')->nullable();
            $table->string('released_to_id_number')->nullable();

            // Transfer-only fields (shelf/tag moves)
            $table->string('old_tag')->nullable();
            $table->string('new_tag')->nullable();

            $table->timestamp('occurred_at')->useCurrent();
            $table->timestamps();

            $table->index(['storage_item_id', 'occurred_at']);
            $table->index(['event_type', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custody_events');
    }
};
