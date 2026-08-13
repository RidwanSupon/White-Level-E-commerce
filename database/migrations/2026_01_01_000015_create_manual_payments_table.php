<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manual_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('payment_method', 20); // bkash, nagad
            $table->string('merchant_number', 50);
            $table->decimal('amount', 12, 2);
            $table->string('transaction_id', 100);
            $table->string('payment_proof')->nullable();
            $table->enum('status', ['pending', 'verification_pending', 'verified', 'rejected', 'cancelled'])->default('verification_pending');
            $table->text('customer_note')->nullable();
            $table->text('admin_note')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            // Strict unique constraint preventing duplicate transaction IDs per gateway method
            $table->unique(['payment_method', 'transaction_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manual_payments');
    }
};
