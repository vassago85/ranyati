<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('motivation_enquiries', function (Blueprint $table) {
            if (! Schema::hasColumn('motivation_enquiries', 'status')) {
                // Lightweight workflow state so the queue can be triaged
                // beyond raw unread/read: new → in_progress → awaiting_docs → closed.
                $table->string('status', 32)->default('new')->after('read_at');
            }

            if (! Schema::hasColumn('motivation_enquiries', 'replied_at')) {
                // Stamped when an operator explicitly logs that they've sent a
                // reply (mailto is out-of-band so we can't detect it automatically).
                $table->timestamp('replied_at')->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('motivation_enquiries', function (Blueprint $table) {
            if (Schema::hasColumn('motivation_enquiries', 'replied_at')) {
                $table->dropColumn('replied_at');
            }

            if (Schema::hasColumn('motivation_enquiries', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
