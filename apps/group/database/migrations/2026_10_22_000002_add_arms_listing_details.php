<?php

use App\Support\ArmsDescriptions;
use App\Support\ArmsStock;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('arms_listings', function (Blueprint $table) {
            if (! Schema::hasColumn('arms_listings', 'type')) {
                $table->string('type')->nullable()->after('calibre');
            }
            if (! Schema::hasColumn('arms_listings', 'condition')) {
                $table->string('condition')->nullable()->after('type');
            }
            if (! Schema::hasColumn('arms_listings', 'stock_number')) {
                $table->string('stock_number')->nullable()->unique()->after('slug');
            }
        });

        ArmsStock::backfill();
        ArmsDescriptions::fillEmptyFromLong();
    }

    public function down(): void
    {
        Schema::table('arms_listings', function (Blueprint $table) {
            if (Schema::hasColumn('arms_listings', 'stock_number')) {
                $table->dropUnique(['stock_number']);
                $table->dropColumn('stock_number');
            }
            if (Schema::hasColumn('arms_listings', 'condition')) {
                $table->dropColumn('condition');
            }
            if (Schema::hasColumn('arms_listings', 'type')) {
                $table->dropColumn('type');
            }
        });
    }
};
