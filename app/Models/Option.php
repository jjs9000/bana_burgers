<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Option extends Model
{
    protected $fillable = [
        'name',
        'type',
        'additional_price'
    ];

    protected $casts = [
        'additional_price' => 'decimal:2',
    ];
}
