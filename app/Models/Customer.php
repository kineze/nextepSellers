<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id',
        'customer_key',
        'primary_phone',
        'additional_phone',
        'email',
        'default_name',
        'default_address',
        'city_id',
        'status',
        'blocked_reason',
        'notes',
        'last_order_at',
        'orders_count',
    ];

    protected $casts = [
        'last_order_at' => 'datetime',
        'orders_count' => 'integer',
    ];

    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
