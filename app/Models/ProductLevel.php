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
        'affiliate_commission_type',
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

    public function calculateAffiliateCommission(float $price, int $quantity = 1): float
    {
        $quantity = max(0, $quantity);
        $value = max(0, (float) $this->affiliate_commission);

        if ($this->affiliate_commission_type === 'amount') {
            return round($value * $quantity, 2);
        }

        return round(($price * $value / 100) * $quantity, 2);
    }
}
