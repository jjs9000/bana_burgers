<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'total',
        'status',
        'notes',
        'delivery_time',
        'delivery_status',
        'customer_name',
        'customer_phone',
        'delivery_address',
        'display_id'
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    protected $casts = [
        'total' => 'decimal:2',
        'delivery_time' => 'datetime',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            // Only set display_id if not already set
            if (empty($order->display_id)) {
                // Generate a random 3-digit number
                $order->display_id = self::generateUniqueDisplayId();
            }
        });
    }

    /**
     * Generate a unique 3-digit display_id
     */
    protected static function generateUniqueDisplayId()
    {
        $isUnique = false;
        $displayId = null;

        // Keep trying until we find a unique display_id
        while (!$isUnique) {
            // Generate a random 3-digit number between 100 and 999
            $displayId = rand(100, 999);

            // Check if it's already in use
            $exists = self::where('display_id', $displayId)->exists();

            if (!$exists) {
                $isUnique = true;
            }
        }

        return $displayId;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
