<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('motivation_enquiries', function (Blueprint $table) {
            if (! Schema::hasColumn('motivation_enquiries', 'services')) {
                $table->json('services')->nullable()->after('purpose');
            }
        });
    }

    public function down(): void
    {
        Schema::table('motivation_enquiries', function (Blueprint $table) {
            if (Schema::hasColumn('motivation_enquiries', 'services')) {
                $table->dropColumn('services');
            }
        });
    }
};
