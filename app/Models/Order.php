<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'total',
        'status',
        'notes',
        'delivery_time',
        'delivery_status',
        'customer_name',
        'customer_phone',
        'delivery_address'
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'delivery_time' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
