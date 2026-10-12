<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('full_names')->nullable();
            $table->string('surname')->nullable();
            $table->string('id_number', 20)->nullable();
            $table->text('physical_address')->nullable();
            $table->text('postal_address')->nullable();
            $table->string('employer')->nullable();
            $table->boolean('self_employed')->default(false);
            $table->text('business_address')->nullable();
            $table->string('occupation')->nullable();
            $table->string('cell', 30)->nullable();
            $table->string('fax', 30)->nullable();
            $table->string('tel_work', 30)->nullable();
            $table->string('tel_home', 30)->nullable();
            $table->string('email')->nullable();
            $table->string('spouse_details')->nullable();
            $table->boolean('facebook_consent')->default(false);
            $table->string('facebook_name')->nullable();
            $table->string('association_name')->nullable();
            $table->string('association_number')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_profiles');
    }
};
