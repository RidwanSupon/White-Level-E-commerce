<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'type', 'value', 'min_spend', 'max_discount', 'usage_limit',
        'usage_per_user', 'starts_at', 'expires_at', 'is_active'
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_spend' => 'decimal:2',
        'max_discount' => 'decimal:2',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function isValid(float $subtotal): bool
    {
        if (!$this->is_active) return false;
        if ($this->starts_at && now()->lt($this->starts_at)) return false;
        if ($this->expires_at && now()->gt($this->expires_at)) return false;
        if ($subtotal < $this->min_spend) return false;

        return true;
    }

    public function calculateDiscount(float $subtotal): float
    {
        if (!$this->isValid($subtotal)) return 0.00;

        if ($this->type === 'percentage') {
            $discount = ($subtotal * $this->value) / 100;
            if ($this->max_discount && $discount > $this->max_discount) {
                return (float) $this->max_discount;
            }
            return (float) $discount;
        }

        return (float) min($this->value, $subtotal);
    }
}
