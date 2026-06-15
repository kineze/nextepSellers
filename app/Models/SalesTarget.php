<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesTarget extends Model
{
    use HasFactory;

    protected $fillable = [
        'year',
        'frequency',
        'target_type',
        'period_number',
        'period_label',
        'target_value',
    ];

    protected $casts = [
        'year' => 'integer',
        'period_number' => 'integer',
        'target_value' => 'decimal:2',
    ];

    public function penaltyTypes(): BelongsToMany
    {
        return $this->belongsToMany(PenaltyType::class)->withTimestamps();
    }

    public function sellerResults(): HasMany
    {
        return $this->hasMany(SellerSalesTarget::class);
    }
}
