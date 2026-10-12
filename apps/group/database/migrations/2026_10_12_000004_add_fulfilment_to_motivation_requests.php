<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fulfilment columns: once the book has been printed Ranyati issues an
 * invoice, waits for EFT (or payment on collection), then either hands the
 * motivation over at the office or ships it via a courier with a waybill.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('motivation_requests', function (Blueprint $table) {
            $table->string('delivery_method', 20)->nullable()->after('reviewer_notes');
            $table->string('courier', 60)->nullable()->after('delivery_method');
            $table->string('waybill_number', 60)->nullable()->after('courier');
            $table->decimal('invoice_total', 10, 2)->nullable()->after('waybill_number');
            $table->string('payment_method', 30)->nullable()->after('invoice_total');
            $table->string('payment_reference', 120)->nullable()->after('payment_method');
            $table->timestamp('invoiced_at')->nullable()->after('payment_reference');
            $table->timestamp('paid_at')->nullable()->after('invoiced_at');
            $table->timestamp('shipped_at')->nullable()->after('paid_at');
            $table->timestamp('ready_at')->nullable()->after('shipped_at');
            $table->timestamp('delivered_at')->nullable()->after('ready_at');
        });
    }

    public function down(): void
    {
        Schema::table('motivation_requests', function (Blueprint $table) {
            $table->dropColumn([
                'delivery_method',
                'courier',
                'waybill_number',
                'invoice_total',
                'payment_method',
                'payment_reference',
                'invoiced_at',
                'paid_at',
                'shipped_at',
                'ready_at',
                'delivered_at',
            ]);
        });
    }
};
