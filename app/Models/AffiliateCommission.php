<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AffiliateCommission extends Model
{
    protected $fillable = [
        'affiliate_seller_id',
        'seller_id',
        'order_id',
        'invoice_id',
        'amount',
        'status',
        'available_at',
        'paid_at',
        'breakdown',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'available_at' => 'datetime',
        'paid_at' => 'datetime',
        'breakdown' => 'array',
    ];

    public function affiliateSeller()
    {
        return $this->belongsTo(Seller::class, 'affiliate_seller_id');
    }

    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
