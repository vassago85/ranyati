<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('arms_listings', 'is_featured')) {
            Schema::table('arms_listings', function (Blueprint $table) {
                $table->boolean('is_featured')->default(false)->after('status');
            });
        }

        // Listing lifecycle is now fully manual — any leftover 'reduced' status
        // from the old 7-day auto-deprioritiser becomes a normal live listing.
        // Real price drops are expressed via original_price > price, not status.
        DB::table('arms_listings')
            ->where('status', 'reduced')
            ->update(['status' => 'active']);
    }

    public function down(): void
    {
        if (! Schema::hasColumn('arms_listings', 'is_featured')) {
            return;
        }

        Schema::table('arms_listings', function (Blueprint $table) {
            $table->dropColumn('is_featured');
        });
    }
};
