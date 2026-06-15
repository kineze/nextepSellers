<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SellerPenalty extends Model
{
    protected $fillable = [
        'seller_id',
        'penalty_type_id',
        'order_id',
        'seller_sales_target_id',
        'delivery_score',
        'effective_areas',
        'rules',
        'charge_amount',
        'is_active',
        'applied_at',
    ];

    protected $casts = [
        'delivery_score' => 'integer',
        'effective_areas' => 'array',
        'rules' => 'array',
        'charge_amount' => 'decimal:2',
        'is_active' => 'boolean',
        'applied_at' => 'datetime',
    ];

    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }

    public function penaltyType()
    {
        return $this->belongsTo(PenaltyType::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function sellerSalesTarget()
    {
        return $this->belongsTo(SellerSalesTarget::class);
    }
}
