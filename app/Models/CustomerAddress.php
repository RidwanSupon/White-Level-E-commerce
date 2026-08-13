<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'title', 'full_name', 'phone', 'address_line_1', 'address_line_2',
        'city', 'state', 'postal_code', 'country', 'is_default_shipping', 'is_default_billing'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
