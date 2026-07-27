<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'default_admin_area')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('default_admin_area')->nullable()->after('role');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('users', 'default_admin_area')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('default_admin_area');
        });
    }
};
