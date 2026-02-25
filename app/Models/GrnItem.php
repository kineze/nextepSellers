<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GrnItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'grn_id',
        'variant_id',
        'lot_id',
        'quantity',
        'unit_cost',
        'line_total',
        'lot_number',
        'barcode',
        'manufactured_at',
        'expires_at',
    ];

    protected $casts = [
        'manufactured_at' => 'date',
        'expires_at' => 'date',
    ];

    public function grn()
    {
        return $this->belongsTo(Grn::class);
    }

    public function variant()
    {
        return $this->belongsTo(Varient::class, 'variant_id');
    }

    public function lot()
    {
        return $this->belongsTo(Lot::class);
    }

    public function lotItems()
    {
        return $this->hasMany(LotItem::class, 'grn_item_id');
    }

    protected static function booted(): void
    {
        static::saving(function (GrnItem $item) {
            $qty = (int) ($item->quantity ?? 0);
            $cost = (float) ($item->unit_cost ?? 0);
            $item->line_total = round($qty * $cost, 2);
        });

        $recompute = function (GrnItem $item) {
            $item->grn?->recomputeTotals();
        };

        static::created($recompute);
        static::updated($recompute);
        static::deleted($recompute);
    }
}

