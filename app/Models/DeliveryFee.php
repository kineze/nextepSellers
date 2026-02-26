<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryFee extends Model
{
    use HasFactory;

    protected $fillable = [
        'fee',
        'is_default',
    ];

    protected $casts = [
        'fee' => 'decimal:2',
        'is_default' => 'boolean',
    ];
}
