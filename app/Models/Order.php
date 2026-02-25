<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_datetime',
        'status',
        'is_draft',
        'net_total',
        'total_collectable_amount',
        'delivery_charge',
        'total_discount',
        'commission_amount',
        'waybill_no',
        'packed_at',
        'shipped_at',
        'completed_at',
        'cancelled_at',
        'is_damaged',
        'delivery_status',
        'payment_status',
        'seller_id',
        'customer_id',
        'customer_name',
        'address',
        'phone',
        'additional_phone',
        'city_id',
    ];

    protected $casts = [
        'order_datetime' => 'datetime',
        'packed_at' => 'datetime',
        'shipped_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'is_draft' => 'boolean',
        'is_damaged' => 'boolean',
        'net_total' => 'decimal:2',
        'total_collectable_amount' => 'decimal:2',
        'delivery_charge' => 'decimal:2',
        'total_discount' => 'decimal:2',
        'commission_amount' => 'decimal:2',
    ];

    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
