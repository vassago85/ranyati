<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('firearm_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('label')->nullable();
            $table->string('reference_number', 40);
            $table->string('serial_number', 40)->nullable();
            $table->boolean('competency_only')->default(false);
            $table->boolean('monitoring_enabled')->default(true);
            $table->string('application_type')->nullable();
            $table->string('calibre')->nullable();
            $table->string('make')->nullable();
            $table->string('status')->nullable();
            $table->string('status_date')->nullable();
            $table->text('status_description')->nullable();
            $table->text('next_step')->nullable();
            $table->string('status_fingerprint', 64)->nullable();
            $table->date('saps_data_updated_on')->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->text('last_check_error')->nullable();
            $table->timestamps();

            $table->index(['monitoring_enabled', 'last_checked_at']);
            $table->unique(['user_id', 'reference_number', 'serial_number']);
        });

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
        Schema::dropIfExists('firearm_application_checks');
        Schema::dropIfExists('firearm_applications');
    }
};
