<?php

namespace App\Http\Controllers;

use App\Models\Lot;
use App\Models\LotItem;
use App\Models\Varient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LotController extends Controller
{
    public function filterLots(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:200'],
        ]);

        $search = trim((string) ($validated['search'] ?? ''));
        $perPage = (int) ($validated['per_page'] ?? 15);

        $baseQuery = Lot::query();

        $query = Lot::query()
            ->with([
                'variant:id,product_id,sku,attributes',
                'variant.product:id,title,product_code',
            ])
            ->latest('id');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('lot_number', 'like', '%' . $search . '%')
                    ->orWhereHas('variant', function ($variantQ) use ($search) {
                        $variantQ
                            ->where('sku', 'like', '%' . $search . '%')
                            ->orWhereHas('product', function ($productQ) use ($search) {
                                $productQ
                                    ->where('title', 'like', '%' . $search . '%')
                                    ->orWhere('product_code', 'like', '%' . $search . '%');
                            });
                    })
                    ->orWhereHas('items', function ($itemQ) use ($search) {
                        $itemQ->where('barcode', 'like', '%' . $search . '%');
                    });
            });
        }

        $lots = $query->paginate($perPage);
        $lotIds = collect($lots->items())->pluck('id')->all();

        $ranges = collect();
        if (!empty($lotIds)) {
            $ranges = DB::table('lot_items')
                ->selectRaw('lot_id, MIN(barcode) as barcode_start, MAX(barcode) as barcode_end')
                ->whereIn('lot_id', $lotIds)
                ->groupBy('lot_id')
                ->get()
                ->keyBy('lot_id');
        }

        $data = collect($lots->items())->map(function ($lot) use ($ranges) {
            $range = $ranges->get($lot->id);
            $lot->barcode_start = $range->barcode_start ?? null;
            $lot->barcode_end = $range->barcode_end ?? null;
            return $lot;
        });

        return response()->json([
            'data' => $data,
            'pagination' => [
                'current_page' => $lots->currentPage(),
                'last_page' => $lots->lastPage(),
                'per_page' => $lots->perPage(),
                'total' => $lots->total(),
            ],
            'stats' => [
                'total_lots' => (clone $baseQuery)->count(),
                'total_quantity' => (clone $baseQuery)->sum('quantity'),
                'active_lots' => (clone $baseQuery)->where('is_active', true)->count(),
                'expiring_soon' => (clone $baseQuery)
                    ->whereDate('expires_at', '<=', now()->addDays(30))
                    ->count(),
            ],
        ]);
    }

    public function items(Lot $lot, Request $request)
    {
        $validated = $request->validate([
            'status' => ['nullable', 'in:available,reserved,sold,damaged,returned,all'],
        ]);

        $status = $validated['status'] ?? 'available';

        $itemsQuery = $lot->items()->orderBy('barcode');
        if ($status !== 'all') {
            $itemsQuery->where('status', $status);
        }

        $items = $itemsQuery->get(['id', 'barcode', 'status', 'order_id', 'created_at']);

        return response()->json([
            'lot' => [
                'id' => $lot->id,
                'lot_number' => $lot->lot_number,
                'quantity' => $lot->quantity,
            ],
            'items' => $items,
            'count' => $items->count(),
        ]);
    }

    public function markDamaged(LotItem $lotItem)
    {
        if ($lotItem->status !== 'available') {
            return response()->json([
                'message' => 'Only available items can be marked as damaged.',
            ], 422);
        }

        DB::transaction(function () use ($lotItem) {
            $lotItem->update(['status' => 'damaged']);
            $this->recomputeLotAndVariant($lotItem->lot_id, $lotItem->variant_id);
        });

        return response()->json([
            'message' => 'Item marked as damaged successfully.',
        ]);
    }

    public function addItems(Request $request, Lot $lot)
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:10000'],
        ]);

        $qty = (int) $validated['quantity'];

        DB::transaction(function () use ($lot, $qty) {
            $variant = Varient::query()->lockForUpdate()->findOrFail($lot->variant_id);

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
                    'barcode' => $barcode,
                    'status' => 'available',
                ]);

                $counter++;
            }

            $this->recomputeLotAndVariant($lot->id, $variant->id);
        });

        return response()->json([
            'message' => 'Items added to lot successfully.',
        ]);
    }

    public function adjust(Request $request, Lot $lot)
    {
        $validated = $request->validate([
            'adjustment' => ['required', 'integer', 'min:-10000', 'max:10000', 'not_in:0'],
        ]);

        $adjustment = (int) $validated['adjustment'];

        DB::transaction(function () use ($lot, $adjustment) {
            $variant = Varient::query()->lockForUpdate()->findOrFail($lot->variant_id);

            DB::table('lot_items')
                ->where('lot_id', $lot->id)
                ->lockForUpdate()
                ->get();

            if ($adjustment > 0) {
                $counter = $lot->nextLotItemCounter();
                for ($i = 0; $i < $adjustment; $i++) {
                    $barcode = $lot->lot_number . '-' . str_pad((string) $counter, 6, '0', STR_PAD_LEFT);
                    LotItem::create([
                        'lot_id' => $lot->id,
                        'variant_id' => $variant->id,
                        'barcode' => $barcode,
                        'status' => 'available',
                    ]);
                    $counter++;
                }
            } else {
                $removeCount = abs($adjustment);
                $availableIds = LotItem::query()
                    ->where('lot_id', $lot->id)
                    ->where('status', 'available')
                    ->orderByDesc('id')
                    ->limit($removeCount)
                    ->pluck('id');

                if ($availableIds->count() < $removeCount) {
                    throw ValidationException::withMessages([
                        'adjustment' => ['Not enough available items in this lot for the requested negative adjustment.'],
                    ]);
                }

                LotItem::query()
                    ->whereIn('id', $availableIds)
                    ->update(['status' => 'reserved']);
            }

            $this->recomputeLotAndVariant($lot->id, $variant->id);
        });

        return response()->json([
            'message' => 'Lot adjusted successfully.',
        ]);
    }

    private function recomputeLotAndVariant(int $lotId, int $variantId): void
    {
        $availableLotQty = LotItem::query()
            ->where('lot_id', $lotId)
            ->where('status', 'available')
            ->count();

        Lot::query()->where('id', $lotId)->update([
            'quantity' => $availableLotQty,
            'is_active' => $availableLotQty > 0,
        ]);

        $availableVariantQty = LotItem::query()
            ->where('variant_id', $variantId)
            ->where('status', 'available')
            ->count();

        Varient::query()->where('id', $variantId)->update([
            'stock_quantity' => $availableVariantQty,
        ]);
    }
}
