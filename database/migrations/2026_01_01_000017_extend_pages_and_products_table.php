<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            if (!Schema::hasColumn('pages', 'show_in_nav')) {
                $table->boolean('show_in_nav')->default(false)->after('is_published');
            }
            if (!Schema::hasColumn('pages', 'show_in_footer')) {
                $table->boolean('show_in_footer')->default(false)->after('show_in_nav');
            }
        });

        Schema::table('product_variants', function (Blueprint $table) {
            if (!Schema::hasColumn('product_variants', 'low_stock_threshold')) {
                $table->integer('low_stock_threshold')->default(5)->after('stock_quantity');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            if (Schema::hasColumn('pages', 'show_in_footer')) {
                $table->dropColumn('show_in_footer');
            }
            if (Schema::hasColumn('pages', 'show_in_nav')) {
                $table->dropColumn('show_in_nav');
            }
        });

        Schema::table('product_variants', function (Blueprint $table) {
            if (Schema::hasColumn('product_variants', 'low_stock_threshold')) {
                $table->dropColumn('low_stock_threshold');
            }
        });
    }
};
