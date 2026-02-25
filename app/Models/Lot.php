<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Lot extends Model
{
    use HasFactory;

    protected $fillable = [
        'variant_id',
        'lot_number',
        'barcode',
        'manufactured_at',
        'expires_at',
        'quantity',
        'is_active',
    ];

    protected $casts = [
        'manufactured_at' => 'date',
        'expires_at' => 'date',
        'is_active' => 'boolean',
        'quantity' => 'integer',
    ];

    public function variant()
    {
        return $this->belongsTo(Varient::class, 'variant_id');
    }

    public function items()
    {
        return $this->hasMany(LotItem::class);
    }

    public function lotItems()
    {
        return $this->hasMany(LotItem::class);
    }

    public function grnItems()
    {
        return $this->hasMany(GrnItem::class);
    }

    public static function nextLotNumber(): int
    {
        return DB::transaction(function () {
            $last = DB::table('lots')
                ->lockForUpdate()
                ->max(DB::raw('CAST(lot_number AS UNSIGNED)'));

            return $last && $last >= 10 ? $last + 1 : 10;
        });
    }

    public function nextLotItemCounter(): int
    {
        $lastBarcode = $this->items()
            ->select('barcode')
            ->where('barcode', 'like', $this->lot_number . '-%')
            ->orderByRaw("CAST(SUBSTRING_INDEX(barcode, '-', -1) AS UNSIGNED) DESC")
            ->value('barcode');

        if (!$lastBarcode) {
            return 1;
        }

        $lastCounter = (int) substr($lastBarcode, strrpos($lastBarcode, '-') + 1);

        return $lastCounter + 1;
    }
}
