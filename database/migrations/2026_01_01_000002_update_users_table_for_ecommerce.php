<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->string('avatar')->nullable()->after('phone');
            $table->enum('status', ['active', 'suspended', 'pending'])->default('active')->after('avatar');
            $table->boolean('is_admin')->default(false)->after('status');
            $table->unsignedBigInteger('customer_group_id')->nullable()->after('is_admin');
            $table->timestamp('last_login_at')->nullable()->after('remember_token');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'avatar', 'status', 'is_admin', 'customer_group_id', 'last_login_at']);
        });
    }
};
