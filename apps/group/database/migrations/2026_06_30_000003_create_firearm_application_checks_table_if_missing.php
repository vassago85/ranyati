<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Recovery migration.
     *
     * The `firearm_application_checks` table was added to the already-shipped
     * 2026_06_14_000001 migration after it had run on some environments, so that
     * migration is marked complete and never creates the table — causing
     * "Base table or view not found: firearm_application_checks" on the admin
     * SAPS tracker. This migration creates the table only when it is missing,
     * which is a no-op on fresh installs (where 000001 already creates it).
     */
    public function up(): void
    {
        if (Schema::hasTable('firearm_application_checks')) {
            return;
        }

        Schema::create('firearm_application_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('firearm_application_id')->constrained()->cascadeOnDelete();
            $table->boolean('success')->default(false);
            $table->boolean('status_changed')->default(false);
            $table->string('status')->nullable();
            $table->string('status_date')->nullable();
            $table->text('status_description')->nullable();
            $table->text('next_step')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('checked_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // Intentionally not dropped here — ownership of this table belongs to the
        // original 2026_06_14_000001 migration's down().
    }
};
