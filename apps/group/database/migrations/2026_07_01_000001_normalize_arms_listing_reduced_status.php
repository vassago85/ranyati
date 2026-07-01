<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Seed data left many listings with status = 'reduced' even though they
     * have no original_price (so no real price drop). "Reduced" should only
     * mean original_price > price. Re-derive status for every live listing
     * (leaving terminal sold/archived rows untouched).
     */
    public function up(): void
    {
        DB::table('arms_listings')
            ->whereIn('status', ['active', 'reduced'])
            ->update([
                'status' => DB::raw(
                    "CASE WHEN original_price IS NOT NULL AND original_price > price THEN 'reduced' ELSE 'active' END"
                ),
            ]);
    }

    public function down(): void
    {
        // No-op: the previous (inconsistent) statuses cannot be reliably restored.
    }
};
