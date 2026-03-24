<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductLevel extends Model
{
    protected $fillable = [
        'product_id',
        'level_id',
        'type',
        'value',
        'affiliate_commission',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'affiliate_commission' => 'decimal:2',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function calculateCommission($price): float
    {
        if ($this->type === 'percentage') {
            return ($price * $this->value) / 100;
        }

        return (float) $this->value;
    }
}
