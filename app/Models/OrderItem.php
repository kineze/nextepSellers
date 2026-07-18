<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_variant_id',
        'quantity',
        'price',
        'pricing_model',
        'reseller_price',
        'seller_earning_amount',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price' => 'decimal:2',
        'reseller_price' => 'decimal:2',
        'seller_earning_amount' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(Varient::class, 'product_variant_id');
    }
}
