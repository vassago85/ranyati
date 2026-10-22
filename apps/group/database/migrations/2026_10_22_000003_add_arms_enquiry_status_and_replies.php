<?php

use App\Models\ArmsEnquiry;
use App\Support\ArmsPhone;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('arms_enquiries', function (Blueprint $table) {
            if (! Schema::hasColumn('arms_enquiries', 'status')) {
                $table->string('status')->default('replied')->after('message');
            }
            if (! Schema::hasColumn('arms_enquiries', 'phone_e164')) {
                $table->string('phone_e164')->nullable()->after('phone');
            }
        });

        DB::table('arms_enquiries')->whereNull('status')->orWhere('status', '')->update(['status' => 'replied']);

        Schema::table('arms_enquiries', function (Blueprint $table) {
            $table->string('status')->default('new')->change();
        });

        ArmsEnquiry::query()->orderBy('id')->each(function (ArmsEnquiry $enquiry): void {
            $enquiry->phone_e164 = ArmsPhone::normalise($enquiry->phone);
            $enquiry->saveQuietly();
        });

        Schema::table('arms_enquiries', function (Blueprint $table) {
            $table->dropForeign(['arms_listing_id']);
        });

        Schema::table('arms_enquiries', function (Blueprint $table) {
            $table->unsignedBigInteger('arms_listing_id')->nullable()->change();
            $table->foreign('arms_listing_id')->references('id')->on('arms_listings')->cascadeOnDelete();
        });

        Schema::create('arms_saved_replies', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('body');
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        $now = now();
        DB::table('arms_saved_replies')->insert([
            [
                'title' => 'Still available',
                'body' => "PLACEHOLDER — rewrite this before using it.\n\nHi {name}, the {firearm} ({stock_number}) at {price} is still available.",
                'sort' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title' => 'Viewing',
                'body' => "PLACEHOLDER — rewrite this before using it.\n\nHi {name}, we can arrange a time for you to see the {firearm} ({stock_number}).",
                'sort' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title' => 'Payment and instalments',
                'body' => "PLACEHOLDER — rewrite this before using it. Do not send this until the client has written their own payment wording.\n\nHi {name}, here is how payment works for the {firearm} ({stock_number}) at {price}.",
                'sort' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title' => 'Transfer process',
                'body' => "PLACEHOLDER — rewrite this before using it. Do not send this until the client has written their own transfer wording.\n\nHi {name}, here is the transfer process for the {firearm} ({stock_number}).",
                'sort' => 4,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('arms_saved_replies');

        Schema::table('arms_enquiries', function (Blueprint $table) {
            $table->dropForeign(['arms_listing_id']);
        });

        Schema::table('arms_enquiries', function (Blueprint $table) {
            $table->unsignedBigInteger('arms_listing_id')->nullable(false)->change();
            $table->foreign('arms_listing_id')->references('id')->on('arms_listings')->cascadeOnDelete();
            if (Schema::hasColumn('arms_enquiries', 'phone_e164')) {
                $table->dropColumn('phone_e164');
            }
            if (Schema::hasColumn('arms_enquiries', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
