<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number', 'user_id', 'status', 'payment_status', 'shipping_status',
        'shipping_zone_id', 'subtotal', 'discount_amount', 'shipping_fee',
        'delivery_charge', 'delivery_advance_required', 'delivery_advance_amount',
        'delivery_advance_paid', 'remaining_amount', 'tax_amount', 'tax_name', 'tax_rate',
        'tax_snapshot_json', 'grand_total', 'payment_method', 'shipping_address_json',
        'billing_address_json', 'notes'
    ];

    protected $casts = [
        'shipping_address_json' => 'array',
        'billing_address_json' => 'array',
        'tax_snapshot_json' => 'array',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'shipping_fee' => 'decimal:2',
        'delivery_charge' => 'decimal:2',
        'delivery_advance_required' => 'boolean',
        'delivery_advance_amount' => 'decimal:2',
        'delivery_advance_paid' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'grand_total' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shippingZone(): BelongsTo
    {
        return $this->belongsTo(ShippingZone::class, 'shipping_zone_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->latest();
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function manualPayment(): HasOne
    {
        return $this->hasOne(ManualPayment::class)->latestOfMany();
    }
}
