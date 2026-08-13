<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title')->default('Home');
            $table->string('full_name');
            $table->string('phone');
            $table->string('address_line_1');
            $table->string('address_line_2')->nullable();
            $table->string('city');
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->default('Bangladesh');
            $table->boolean('is_default_shipping')->default(false);
            $table->boolean('is_default_billing')->default(false);
            $table->timestamps();
        });

        Schema::create('shipping_zones', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('zone_type', 30)->default('outside_dhaka');
            $table->json('regions_json')->nullable();
            $table->json('districts_json')->nullable();
            $table->json('areas_json')->nullable();
            $table->decimal('delivery_charge', 12, 2)->default(150.00);
            $table->boolean('advance_payment_required')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('shipping_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zone_id')->nullable()->constrained('shipping_zones')->onDelete('cascade');
            $table->string('name');
            $table->decimal('charge', 12, 2)->default(0);
            $table->decimal('free_shipping_threshold', 12, 2)->nullable();
            $table->string('estimated_days')->default('2-4 Days');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_methods');
        Schema::dropIfExists('shipping_zones');
        Schema::dropIfExists('customer_addresses');
    }
};
