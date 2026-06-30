<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('firearm_status_transitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('firearm_application_id')->constrained()->cascadeOnDelete();
            $table->string('status');
            $table->timestamp('entered_at');
            $table->timestamp('exited_at')->nullable();
            $table->unsignedInteger('duration_days')->nullable();
            $table->timestamps();

            $table->index(['firearm_application_id', 'entered_at']);
            $table->index(['status', 'exited_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('firearm_status_transitions');
    }
};
