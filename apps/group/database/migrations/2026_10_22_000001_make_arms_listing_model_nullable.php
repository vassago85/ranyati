<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('arms_listings', function (Blueprint $table) {
            $table->string('model')->nullable()->change();
        });
    }

    public function down(): void
    {
        DB::table('arms_listings')->whereNull('model')->update(['model' => '']);

        Schema::table('arms_listings', function (Blueprint $table) {
            $table->string('model')->nullable(false)->change();
        });
    }
};
