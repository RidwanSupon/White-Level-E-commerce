<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ManualPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'user_id', 'payment_type', 'payment_method', 'merchant_number', 'amount',
        'transaction_id', 'payment_proof', 'status', 'customer_note', 'admin_note',
        'verified_by', 'verified_at', 'rejected_by', 'rejected_at', 'rejection_reason'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'verified_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function rejecter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function getPaymentProofUrlAttribute(): ?string
    {
        if (empty($this->payment_proof)) {
            return null;
        }

        return app(\App\Services\StorageService::class)->temporaryUrl($this->payment_proof, 60);
    }
}
