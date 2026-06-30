<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Optional long-form description for richer, higher-quality listing pages.
     * The existing short `description` is the fallback; this column is only
     * rendered when populated, so existing listings are unaffected.
     */
    public function up(): void
    {
        Schema::table('arms_listings', function (Blueprint $table) {
            $table->text('description_long')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('arms_listings', function (Blueprint $table) {
            $table->dropColumn('description_long');
        });
    }
};
