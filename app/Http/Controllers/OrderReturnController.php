<?php

namespace App\Http\Controllers;

use App\Models\Lot;
use App\Models\LotItem;
use App\Models\Order;
use App\Models\OrderReturn;
use App\Models\ReturnItem;
use App\Models\Varient;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderReturnController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'in:all,pending,completed'],
            'seller_id' => ['nullable', 'integer', 'exists:sellers,id'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'date_basis' => ['nullable', 'in:return_date,order_date'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:100'],
        ]);

        $search = trim((string) ($validated['search'] ?? ''));
        $status = (string) ($validated['status'] ?? 'all');
        $dateBasis = (string) ($validated['date_basis'] ?? 'return_date');
        $perPage = (int) ($validated['per_page'] ?? 20);

        $query = OrderReturn::query()
            ->with([
                'order:id,seller_id,customer_name,phone,waybill_no,delivery_status,order_datetime,cancelled_at',
                'order.seller:id,first_name,last_name,email',
                'user:id,name',
            ])
            ->withCount('items')
            ->latest('return_date')
            ->latest('id');

        if ($status !== 'all') {
            $query->where('status', $status);
        }
        if (!empty($validated['seller_id'])) {
            $query->whereHas('order', fn ($orderQ) => $orderQ->where('seller_id', (int) $validated['seller_id']));
        }
        if ($dateBasis === 'order_date') {
            if (!empty($validated['date_from'])) {
                $query->whereHas('order', fn ($orderQ) => $orderQ->whereDate('order_datetime', '>=', $validated['date_from']));
            }
            if (!empty($validated['date_to'])) {
                $query->whereHas('order', fn ($orderQ) => $orderQ->whereDate('order_datetime', '<=', $validated['date_to']));
            }
        } else {
            if (!empty($validated['date_from'])) {
                $query->where(
                    'return_date',
                    '>=',
                    Carbon::parse($validated['date_from'], 'Asia/Colombo')->startOfDay()->utc()
                );
            }
            if (!empty($validated['date_to'])) {
                $query->where(
                    'return_date',
                    '<=',
                    Carbon::parse($validated['date_to'], 'Asia/Colombo')->endOfDay()->utc()
                );
            }
        }
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('return_number', 'like', '%' . $search . '%')
                    ->orWhere('waybill_no', 'like', '%' . $search . '%')
                    ->orWhereHas('order', function ($orderQ) use ($search) {
                        $orderQ->where('id', $search)
                            ->orWhere('customer_name', 'like', '%' . $search . '%')
                            ->orWhere('phone', 'like', '%' . $search . '%')
                            ->orWhereHas('seller', function ($sellerQ) use ($search) {
                                $sellerQ->where('first_name', 'like', '%' . $search . '%')
                                    ->orWhere('last_name', 'like', '%' . $search . '%')
                                    ->orWhere('email', 'like', '%' . $search . '%');
                            });
                    });
            });
        }

        $returns = $query->paginate($perPage);

        return response()->json([
            'returns' => $returns->items(),
            'meta' => [
                'current_page' => $returns->currentPage(),
                'last_page' => $returns->lastPage(),
                'per_page' => $returns->perPage(),
                'total' => $returns->total(),
            ],
            'filters' => [
                'date_basis' => $dateBasis,
            ],
        ]);
    }

    public function scan(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:120'],
        ]);

        $code = trim((string) $validated['code']);
        $order = Order::query()
            ->whereRaw('LOWER(waybill_no) = ?', [strtolower($code)])
            ->first();

        if (!$order) {
            return response()->json(['message' => 'No order found for this waybill.'], 404);
        }
        if ($order->status !== 'cancelled') {
            return response()->json(['message' => 'Only cancelled orders can be returned.'], 422);
        }

        $existingReturn = OrderReturn::query()
            ->where('order_id', $order->id)
            ->first();

        if (!$existingReturn && !$order->lotItems()->whereIn('status', ['sold', 'reserved'])->exists()) {
            return response()->json([
                'message' => 'This cancelled order has no allocated lot items to return.',
            ], 422);
        }

        $orderReturn = DB::transaction(function () use ($order, $request) {
            $orderReturn = OrderReturn::query()
                ->where('order_id', $order->id)
                ->lockForUpdate()
                ->first();

            if (!$orderReturn) {
                $orderReturn = OrderReturn::create([
                    'order_id' => $order->id,
                    'user_id' => $request->user()?->id,
                    'waybill_no' => $order->waybill_no,
                    'status' => 'pending',
                    'return_date' => now(),
                ]);

                $orderReturn->update([
                    'return_number' => 'RTN-' . str_pad((string) $orderReturn->id, 6, '0', STR_PAD_LEFT),
                ]);
            }

            return $orderReturn;
        });

        return response()->json($this->buildReturnPayload($orderReturn));
    }

    public function scanLotItem(Request $request, OrderReturn $orderReturn)
    {
        $validated = $request->validate([
            'barcode' => ['required', 'string', 'max:120'],
            'disposition' => ['required', 'in:returned,damaged'],
        ]);

        DB::transaction(function () use ($validated, $orderReturn) {
            $orderReturn = OrderReturn::query()->lockForUpdate()->findOrFail($orderReturn->id);
            if ($orderReturn->status !== 'pending') {
                throw ValidationException::withMessages([
                    'barcode' => ['This return has already been finalized.'],
                ]);
            }

            $lotItem = LotItem::query()
                ->where('barcode', trim((string) $validated['barcode']))
                ->where('order_id', $orderReturn->order_id)
                ->lockForUpdate()
                ->first();

            if (!$lotItem) {
                throw ValidationException::withMessages([
                    'barcode' => ['Barcode is not allocated to this order.'],
                ]);
            }
            if (!in_array($lotItem->status, ['sold', 'reserved'], true)) {
                throw ValidationException::withMessages([
                    'barcode' => ['This barcode has already been scanned.'],
                ]);
            }

            ReturnItem::create([
                'order_return_id' => $orderReturn->id,
                'lot_item_id' => $lotItem->id,
                'lot_id' => $lotItem->lot_id,
                'variant_id' => $lotItem->variant_id,
                'barcode' => $lotItem->barcode,
                'disposition' => $validated['disposition'],
            ]);

            $lotItem->update([
                'status' => $validated['disposition'],
            ]);
        });

        return response()->json([
            'message' => $validated['disposition'] === 'damaged'
                ? 'Item marked as damaged.'
                : 'Item accepted for restocking.',
            'return' => $this->buildReturnPayload($orderReturn->fresh()),
        ]);
    }

    public function finalize(OrderReturn $orderReturn)
    {
        DB::transaction(function () use ($orderReturn) {
            $orderReturn = OrderReturn::query()->lockForUpdate()->findOrFail($orderReturn->id);
            if ($orderReturn->status !== 'pending') {
                return;
            }

            $lotItems = LotItem::query()
                ->where('order_id', $orderReturn->order_id)
                ->lockForUpdate()
                ->get();

            $returnItems = ReturnItem::query()
                ->where('order_return_id', $orderReturn->id)
                ->lockForUpdate()
                ->get()
                ->keyBy('lot_item_id');

            if ($lotItems->isEmpty()) {
                throw ValidationException::withMessages([
                    'return' => ['No allocated lot items were found for this order.'],
                ]);
            }
            if ($lotItems->count() !== $returnItems->count()
                || $lotItems->contains(fn (LotItem $item) => !$returnItems->has($item->id))) {
                throw ValidationException::withMessages([
                    'return' => ['Scan every allocated barcode before finalizing this return.'],
                ]);
            }

            $lotIds = $lotItems->pluck('lot_id')->filter()->unique()->values()->all();
            $variantIds = $lotItems->pluck('variant_id')->filter()->unique()->values()->all();

            foreach ($lotItems as $lotItem) {
                $returnItem = $returnItems->get($lotItem->id);
                $lotItem->update([
                    'order_id' => null,
                    'status' => $returnItem->disposition === 'returned' ? 'available' : 'damaged',
                ]);
            }

            $this->recomputeInventory($lotIds, $variantIds);

            $orderReturn->update([
                'status' => 'completed',
                'finalized_at' => now(),
            ]);
        });

        return response()->json([
            'message' => 'Return finalized and accepted items restocked successfully.',
            'return' => $this->buildReturnPayload($orderReturn->fresh()),
        ]);
    }

    private function buildReturnPayload(OrderReturn $orderReturn): array
    {
        $orderReturn->loadMissing([
            'order:id,seller_id,customer_name,phone,waybill_no,delivery_status,cancelled_at',
            'order.seller:id,first_name,last_name,email',
            'items:id,order_return_id,lot_item_id,lot_id,variant_id,barcode,disposition',
        ]);

        $scannedByLotItem = $orderReturn->items->keyBy('lot_item_id');
        $lotItems = $orderReturn->status === 'completed'
            ? $orderReturn->items->map(function (ReturnItem $item) {
                return [
                    'id' => (int) $item->lot_item_id,
                    'barcode' => $item->barcode,
                    'lot_id' => (int) $item->lot_id,
                    'variant_id' => (int) $item->variant_id,
                    'scanned' => true,
                    'disposition' => $item->disposition,
                ];
            })
            : LotItem::query()
                ->with(['lot:id,lot_number', 'variant:id,sku,attributes'])
                ->where('order_id', $orderReturn->order_id)
                ->orderBy('id')
                ->get()
                ->map(function (LotItem $item) use ($scannedByLotItem) {
                    $scanned = $scannedByLotItem->get($item->id);

                    return [
                        'id' => (int) $item->id,
                        'barcode' => $item->barcode,
                        'lot_id' => (int) $item->lot_id,
                        'lot_number' => $item->lot?->lot_number,
                        'variant_id' => (int) $item->variant_id,
                        'sku' => $item->variant?->sku,
                        'attributes' => $item->variant?->attributes,
                        'scanned' => (bool) $scanned,
                        'disposition' => $scanned?->disposition,
                    ];
                });

        if ($lotItems->isEmpty()) {
            throw ValidationException::withMessages([
                'code' => ['This cancelled order has no allocated lot items to return.'],
            ]);
        }

        return [
            'id' => (int) $orderReturn->id,
            'return_number' => $orderReturn->return_number,
            'status' => $orderReturn->status,
            'return_date' => $orderReturn->return_date,
            'finalized_at' => $orderReturn->finalized_at,
            'order' => $orderReturn->order,
            'items' => $lotItems->values(),
            'items_count' => $lotItems->count(),
            'scanned_count' => $lotItems->where('scanned', true)->count(),
        ];
    }

    private function recomputeInventory(array $lotIds, array $variantIds): void
    {
        foreach ($lotIds as $lotId) {
            $availableQty = LotItem::query()
                ->where('lot_id', (int) $lotId)
                ->where('status', 'available')
                ->whereNull('order_id')
                ->count();

            Lot::query()->whereKey((int) $lotId)->update([
                'quantity' => $availableQty,
                'is_active' => $availableQty > 0,
            ]);
        }

        foreach ($variantIds as $variantId) {
            $availableQty = LotItem::query()
                ->where('variant_id', (int) $variantId)
                ->where('status', 'available')
                ->whereNull('order_id')
                ->count();

            Varient::query()->whereKey((int) $variantId)->update([
                'stock_quantity' => $availableQty,
            ]);
        }
    }
}
