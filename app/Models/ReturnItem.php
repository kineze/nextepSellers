<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_return_id',
        'lot_item_id',
        'lot_id',
        'variant_id',
        'barcode',
        'disposition',
    ];

    public function orderReturn()
    {
        return $this->belongsTo(OrderReturn::class);
    }

    public function lotItem()
    {
        return $this->belongsTo(LotItem::class);
    }

    public function lot()
    {
        return $this->belongsTo(Lot::class);
    }

    public function variant()
    {
        return $this->belongsTo(Varient::class, 'variant_id');
    }
}
