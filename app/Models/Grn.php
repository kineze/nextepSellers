<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Grn extends Model
{
    use HasFactory;

    protected $fillable = [
        'grn_no',
        'purchase_order_id',
        'supplier_id',
        'received_date',
        'received_time',
        'status',
        'notes',
        'total_items',
        'total_quantity',
        'total_amount',
    ];

    protected $casts = [
        'received_date' => 'date',
        'received_time' => 'datetime:H:i',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function items()
    {
        return $this->hasMany(GrnItem::class);
    }

    public function recomputeTotals(): void
    {
        $totals = $this->items()
            ->selectRaw('
                COUNT(*) as total_items,
                COALESCE(SUM(quantity),0) as total_quantity,
                COALESCE(SUM(line_total),0) as total_amount
            ')
            ->first();

        $this->forceFill([
            'total_items' => (int) $totals->total_items,
            'total_quantity' => (int) $totals->total_quantity,
            'total_amount' => (float) $totals->total_amount,
        ])->saveQuietly();
    }

    public function post(): void
    {
        if ($this->status === 'posted') {
            return;
        }

        DB::transaction(function () {
            $this->loadMissing('items.variant', 'items.lot');

            foreach ($this->items as $item) {
                $variant = $item->variant;
                $lot = $item->lot;

                if (!$lot) {
                    $lotNumber = $item->lot_number
                        ? (int) $item->lot_number
                        : Lot::nextLotNumber();

                    $lot = Lot::firstOrCreate(
                        [
                            'variant_id' => $variant->id,
                            'lot_number' => (string) $lotNumber,
                        ],
                        [
                            'manufactured_at' => $item->manufactured_at,
                            'expires_at' => $item->expires_at,
                            'quantity' => 0,
                            'is_active' => true,
                        ]
                    );

                    $item->forceFill(['lot_id' => $lot->id])->saveQuietly();
                }

                $qty = (int) $item->quantity;

                $lot->increment('quantity', $qty);
                $variant->increment('stock_quantity', $qty);

                DB::table('lot_items')
                    ->where('lot_id', $lot->id)
                    ->lockForUpdate()
                    ->get();

                $counter = $lot->nextLotItemCounter();

                for ($i = 0; $i < $qty; $i++) {
                    $barcode = $lot->lot_number . '-' . str_pad((string) $counter, 6, '0', STR_PAD_LEFT);

                    LotItem::create([
                        'lot_id' => $lot->id,
                        'variant_id' => $variant->id,
                        'grn_item_id' => $item->id,
                        'barcode' => $barcode,
                        'status' => 'available',
                    ]);

                    $counter++;
                }

                if (class_exists(\App\Models\InventoryLog::class)) {
                    InventoryLog::create([
                        'variant_id' => $variant->id,
                        'lot_id' => $lot->id,
                        'product_id' => $variant->product_id,
                        'change_type' => 'in',
                        'quantity' => $qty,
                        'note' => 'GRN ' . ($this->grn_no ?? '#' . $this->id) . ' posted',
                        'user_id' => Auth::id(),
                    ]);
                }
            }

            $this->forceFill(['status' => 'posted'])->save();
        });
    }

    public function unpost(): void
    {
        if ($this->status !== 'posted') {
            return;
        }

        DB::transaction(function () {
            $this->loadMissing('items.variant', 'items.lot', 'items.lotItems');

            foreach ($this->items as $item) {
                $variant = $item->variant;
                $lot = $item->lot;
                $qty = (int) $item->quantity;

                $movedLotItems = $item->lotItems()
                    ->where(function ($q) {
                        $q->whereNotIn('status', ['available', 'reserved'])
                            ->orWhereNotNull('order_id');
                    })
                    ->exists();

                if ($movedLotItems) {
                    throw new \Exception('Cannot unpost GRN: some lot items are already sold/allocated.');
                }

                $item->lotItems()->delete();

                if ($lot) {
                    $lot->decrement('quantity', $qty);
                }

                $variant->decrement('stock_quantity', $qty);

                if (class_exists(\App\Models\InventoryLog::class)) {
                    InventoryLog::create([
                        'variant_id' => $variant->id,
                        'lot_id' => $lot?->id,
                        'product_id' => $variant->product_id,
                        'change_type' => 'out',
                        'quantity' => $qty,
                        'note' => 'GRN ' . ($this->grn_no ?? '#' . $this->id) . ' unposted',
                        'user_id' => Auth::id(),
                    ]);
                }
            }

            $this->forceFill(['status' => 'cancelled'])->save();
        });
    }
}
