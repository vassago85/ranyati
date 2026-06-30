<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add a terminal "sold" state to the arms_listings status.
     *
     * The original column was created with $table->enum(), which is enforced
     * differently per driver (Postgres CHECK constraint, MySQL ENUM, SQLite
     * plain text). This migration widens the allowed values to include 'sold'
     * so a sold firearm keeps a live URL instead of being deleted/archived.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE arms_listings DROP CONSTRAINT IF EXISTS arms_listings_status_check');
            DB::statement("ALTER TABLE arms_listings ADD CONSTRAINT arms_listings_status_check CHECK (status::text IN ('active', 'reduced', 'archived', 'sold'))");
        } elseif (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE arms_listings MODIFY status ENUM('active', 'reduced', 'archived', 'sold') NOT NULL DEFAULT 'active'");
        }
        // sqlite stores the enum as plain text with no constraint — nothing to do.
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        // Demote any sold rows first so the narrower constraint can re-apply.
        DB::table('arms_listings')->where('status', 'sold')->update([
            'status' => 'archived',
            'archived_at' => now(),
        ]);

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE arms_listings DROP CONSTRAINT IF EXISTS arms_listings_status_check');
            DB::statement("ALTER TABLE arms_listings ADD CONSTRAINT arms_listings_status_check CHECK (status::text IN ('active', 'reduced', 'archived'))");
        } elseif (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE arms_listings MODIFY status ENUM('active', 'reduced', 'archived') NOT NULL DEFAULT 'active'");
        }
    }
};
