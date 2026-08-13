<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipping_zones', function (Blueprint $table) {
            if (!Schema::hasColumn('shipping_zones', 'zone_type')) {
                $table->string('zone_type', 30)->default('outside_dhaka')->after('name');
            }
            if (!Schema::hasColumn('shipping_zones', 'districts_json')) {
                $table->json('districts_json')->nullable()->after('regions_json');
            }
            if (!Schema::hasColumn('shipping_zones', 'areas_json')) {
                $table->json('areas_json')->nullable()->after('districts_json');
            }
            if (!Schema::hasColumn('shipping_zones', 'delivery_charge')) {
                $table->decimal('delivery_charge', 12, 2)->default(150.00)->after('areas_json');
            }
            if (!Schema::hasColumn('shipping_zones', 'advance_payment_required')) {
                $table->boolean('advance_payment_required')->default(false)->after('delivery_charge');
            }
            if (!Schema::hasColumn('shipping_zones', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('advance_payment_required');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'shipping_zone_id')) {
                $table->foreignId('shipping_zone_id')->nullable()->after('shipping_status')->constrained('shipping_zones')->onDelete('set null');
            }
            if (!Schema::hasColumn('orders', 'delivery_charge')) {
                $table->decimal('delivery_charge', 12, 2)->default(0.00)->after('shipping_fee');
            }
            if (!Schema::hasColumn('orders', 'delivery_advance_required')) {
                $table->boolean('delivery_advance_required')->default(false)->after('delivery_charge');
            }
            if (!Schema::hasColumn('orders', 'delivery_advance_amount')) {
                $table->decimal('delivery_advance_amount', 12, 2)->default(0.00)->after('delivery_advance_required');
            }
            if (!Schema::hasColumn('orders', 'delivery_advance_paid')) {
                $table->decimal('delivery_advance_paid', 12, 2)->default(0.00)->after('delivery_advance_amount');
            }
            if (!Schema::hasColumn('orders', 'remaining_amount')) {
                $table->decimal('remaining_amount', 12, 2)->default(0.00)->after('delivery_advance_paid');
            }
        });

        Schema::table('manual_payments', function (Blueprint $table) {
            if (!Schema::hasColumn('manual_payments', 'payment_type')) {
                $table->string('payment_type', 30)->default('full_order')->after('user_id'); // full_order, delivery_advance
            }
        });
    }

    public function down(): void
    {
        Schema::table('manual_payments', function (Blueprint $table) {
            if (Schema::hasColumn('manual_payments', 'payment_type')) {
                $table->dropColumn('payment_type');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'shipping_zone_id')) {
                $table->dropForeign(['shipping_zone_id']);
                $table->dropColumn([
                    'shipping_zone_id', 'delivery_charge', 'delivery_advance_required',
                    'delivery_advance_amount', 'delivery_advance_paid', 'remaining_amount'
                ]);
            }
        });

        Schema::table('shipping_zones', function (Blueprint $table) {
            if (Schema::hasColumn('shipping_zones', 'zone_type')) {
                $table->dropColumn([
                    'zone_type', 'districts_json', 'areas_json',
                    'delivery_charge', 'advance_payment_required', 'is_active'
                ]);
            }
        });
    }
};
