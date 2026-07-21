<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('motivation_enquiries', 'saps_station')) {
            return;
        }

        Schema::table('motivation_enquiries', function (Blueprint $table) {
            $table->string('saps_station')->nullable()->after('endorsement_type');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('motivation_enquiries', 'saps_station')) {
            return;
        }

        Schema::table('motivation_enquiries', function (Blueprint $table) {
            $table->dropColumn('saps_station');
        });
    }
};
