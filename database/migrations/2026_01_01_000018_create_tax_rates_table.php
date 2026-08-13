<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_rates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->decimal('rate', 5, 2)->default(0.00); // Tax percentage (e.g. 15.00, 7.50, 5.00, 0.00)
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'tax_rate_id')) {
                $table->foreignId('tax_rate_id')->nullable()->constrained('tax_rates')->onDelete('set null');
            }
            if (!Schema::hasColumn('products', 'is_tax_exempt')) {
                $table->boolean('is_tax_exempt')->default(false)->after('tax_rate_id');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'tax_name')) {
                $table->string('tax_name')->nullable()->after('tax_amount');
            }
            if (!Schema::hasColumn('orders', 'tax_rate')) {
                $table->decimal('tax_rate', 5, 2)->default(0.00)->after('tax_name');
            }
            if (!Schema::hasColumn('orders', 'tax_snapshot_json')) {
                $table->json('tax_snapshot_json')->nullable()->after('tax_rate');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'tax_snapshot_json')) {
                $table->dropColumn('tax_snapshot_json');
            }
            if (Schema::hasColumn('orders', 'tax_rate')) {
                $table->dropColumn('tax_rate');
            }
            if (Schema::hasColumn('orders', 'tax_name')) {
                $table->dropColumn('tax_name');
            }
        });

        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'is_tax_exempt')) {
                $table->dropColumn('is_tax_exempt');
            }
            if (Schema::hasColumn('products', 'tax_rate_id')) {
                $table->dropForeign(['tax_rate_id']);
                $table->dropColumn('tax_rate_id');
            }
        });

        Schema::dropIfExists('tax_rates');
    }
};
