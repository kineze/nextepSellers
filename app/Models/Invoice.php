<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'seller_id',
        'invoice_date',
        'invoice_time',
        'total_commission_value',
        'status',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'invoice_time' => 'datetime:H:i:s',
        'total_commission_value' => 'decimal:2',
    ];

    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
