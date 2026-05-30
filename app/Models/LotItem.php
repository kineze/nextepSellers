<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LotItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'lot_id',
        'variant_id',
        'grn_item_id',
        'barcode',
        'status',
        'order_id',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public function lot()
    {
        return $this->belongsTo(Lot::class);
    }

    public function variant()
    {
        return $this->belongsTo(Varient::class, 'variant_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function grnItem()
    {
        return $this->belongsTo(GrnItem::class, 'grn_item_id');
    }

    public function returnItem()
    {
        return $this->hasOne(ReturnItem::class);
    }

    public function scopeAvailable($query)
    {
        return $query
            ->where('status', 'available')
            ->whereNull('order_id');
    }

    public function scopeAllocated($query)
    {
        return $query->whereNotNull('order_id');
    }
}
