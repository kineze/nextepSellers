<?php

namespace App\Http\Controllers;

use App\Models\DispatchNote;
use App\Models\DispatchNoteItem;
use App\Models\LotItem;
use App\Models\Order;
use App\Models\Seller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DispatchNoteController extends Controller
{
    public function adminDispatchNotes(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'string', 'max:40'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:100'],
        ]);

        $search = trim((string) ($validated['search'] ?? ''));
        $status = trim((string) ($validated['status'] ?? ''));
        $perPage = (int) ($validated['per_page'] ?? 20);

        $query = DispatchNote::query()
            ->with([
                'creator:id,name',
                'items:id,dispatch_note_id,order_id',
                'items.order:id,status',
            ])
            ->latest('dispatch_date')
            ->latest('id');

        if ($status !== '' && $status !== 'all') {
            $query->where('status', $status);
        }

        if (!empty($validated['date_from'])) {
            $query->whereDate('dispatch_date', '>=', $validated['date_from']);
        }
        if (!empty($validated['date_to'])) {
            $query->whereDate('dispatch_date', '<=', $validated['date_to']);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('ref_no', 'like', '%' . $search . '%')
                    ->orWhere('remarks', 'like', '%' . $search . '%')
                    ->orWhere('id', $search);
            });
        }

        $notes = $query->paginate($perPage);

        $rows = collect($notes->items())->map(function ($note) {
            $orders = collect($note->items)->pluck('order')->filter();
            $ordersCount = $orders->count();
            $shippedCount = $orders->filter(function ($order) {
                return in_array(strtolower((string) $order->status), ['shipped', 'completed'], true);
            })->count();

            return [
                'id' => $note->id,
                'ref_no' => $note->ref_no,
                'dispatch_date' => $note->dispatch_date,
                'dispatch_time' => $note->dispatch_time,
                'remarks' => $note->remarks,
                'status' => $note->status,
                'net_total' => (float) $note->net_total,
                'delivery_total' => (float) $note->delivery_total,
                'total_collectable' => (float) $note->total_collectable,
                'orders_count' => $ordersCount,
                'shipped_count' => $shippedCount,
                'creator' => $note->creator ? [
                    'id' => $note->creator->id,
                    'name' => $note->creator->name,
                ] : null,
            ];
        })->values();

        return response()->json([
            'notes' => $rows,
            'meta' => [
                'current_page' => $notes->currentPage(),
                'last_page' => $notes->lastPage(),
                'per_page' => $notes->perPage(),
                'total' => $notes->total(),
            ],
        ]);
    }

    public function adminDispatchNoteShow(DispatchNote $dispatchNote)
    {
        $dispatchNote->load([
            'creator:id,name',
            'items:id,dispatch_note_id,order_id,waybill_snapshot,collectable_amount_snapshot,item_remarks',
            'items.order:id,status,order_datetime,waybill_no,customer_name,phone,additional_phone,address,total_collectable_amount,net_total,delivery_charge,total_discount,seller_id,city_id',
            'items.order.seller:id,first_name,last_name,email',
            'items.order.city:id,name_en',
            'items.order.items:id,order_id,product_id,product_variant_id,quantity,price',
            'items.order.items.product:id,title',
            'items.order.items.variant:id,sku,attributes',
        ]);

        $orderIds = collect($dispatchNote->items)->pluck('order.id')->filter()->unique()->values();
        $linkedItemsByOrder = collect();
        if ($orderIds->isNotEmpty()) {
            $linkedItemsByOrder = LotItem::query()
                ->whereIn('order_id', $orderIds)
                ->whereIn('status', ['reserved', 'sold'])
                ->get(['order_id', 'variant_id', 'barcode', 'status'])
                ->groupBy('order_id');
        }

        $orders = collect($dispatchNote->items)->map(function ($item) use ($linkedItemsByOrder) {
            $order = $item->order;
            if (!$order) {
                return null;
            }

            $status = strtolower((string) $order->status);
            $isShipped = in_array($status, ['shipped', 'completed'], true);
            $statusAllowsShip = in_array($status, ['packed', 'approved', 'confirmed'], true);

            $requiredLines = collect($order->items)
                ->filter(fn ($orderItem) => !empty($orderItem->product_variant_id))
                ->groupBy('product_variant_id')
                ->map(function ($rows, $variantId) {
                    $first = $rows->first();
                    return [
                        'variant_id' => (int) $variantId,
                        'required_qty' => (int) $rows->sum('quantity'),
                        'product_title' => $first?->product?->title,
                        'sku' => $first?->variant?->sku,
                    ];
                })
                ->values();

            $linkedItems = collect($linkedItemsByOrder->get($order->id, []));
            $linkedCountByVariant = $linkedItems
                ->groupBy('variant_id')
                ->map(fn ($rows) => $rows->count());

            $requiredMap = $requiredLines->pluck('required_qty', 'variant_id');
            $hasRequiredVariants = $requiredMap->isNotEmpty();
            $isLinkingComplete = !$hasRequiredVariants || $requiredMap->every(function ($requiredQty, $variantId) use ($linkedCountByVariant) {
                return (int) ($linkedCountByVariant[(int) $variantId] ?? 0) >= (int) $requiredQty;
            });

            $anyLinked = $linkedItems->isNotEmpty();
            $linkingStatus = !$hasRequiredVariants
                ? 'not_required'
                : ($isLinkingComplete ? 'linked' : ($anyLinked ? 'partial' : 'pending'));

            $canShip = $statusAllowsShip && $isLinkingComplete;

            $requiredLines = $requiredLines->map(function ($line) use ($linkedCountByVariant) {
                $line['linked_qty'] = (int) ($linkedCountByVariant[(int) $line['variant_id']] ?? 0);
                return $line;
            })->values();

            $linkedByVariant = $linkedItems
                ->groupBy('variant_id')
                ->map(fn ($rows) => $rows->pluck('barcode')->values())
                ->mapWithKeys(fn ($barcodes, $variantId) => [(string) $variantId => $barcodes]);

            return [
                'dispatch_note_item_id' => $item->id,
                'order_id' => $order->id,
                'status' => $order->status,
                'is_shipped' => $isShipped,
                'can_ship' => $canShip,
                'linking_status' => $linkingStatus,
                'order_datetime' => $order->order_datetime,
                'waybill_no' => $order->waybill_no ?: $item->waybill_snapshot,
                'customer_name' => $order->customer_name,
                'phone' => $order->phone,
                'additional_phone' => $order->additional_phone,
                'address' => $order->address,
                'collectable_amount' => (float) ($order->total_collectable_amount ?? $item->collectable_amount_snapshot ?? 0),
                'net_total' => (float) ($order->net_total ?? 0),
                'delivery_charge' => (float) ($order->delivery_charge ?? 0),
                'total_discount' => (float) ($order->total_discount ?? 0),
                'item_remarks' => $item->item_remarks,
                'seller' => $order->seller ? [
                    'id' => $order->seller->id,
                    'name' => trim(($order->seller->first_name ?? '') . ' ' . ($order->seller->last_name ?? '')) ?: ($order->seller->email ?? 'Seller'),
                ] : null,
                'city' => $order->city ? [
                    'id' => $order->city->id,
                    'name_en' => $order->city->name_en,
                ] : null,
                'required_lines' => $requiredLines,
                'linked_barcodes' => $linkedItems->pluck('barcode')->values(),
                'linked_by_variant' => $linkedByVariant,
                'items' => collect($order->items)->map(function ($orderItem) {
                    return [
                        'id' => $orderItem->id,
                        'quantity' => (int) $orderItem->quantity,
                        'price' => (float) $orderItem->price,
                        'product_variant_id' => $orderItem->product_variant_id ? (int) $orderItem->product_variant_id : null,
                        'product' => [
                            'title' => $orderItem->product?->title,
                        ],
                        'variant' => [
                            'id' => $orderItem->variant?->id,
                            'sku' => $orderItem->variant?->sku,
                            'attributes' => $orderItem->variant?->attributes,
                        ],
                    ];
                })->values(),
            ];
        })->filter()->values();

        $shippedCount = $orders->where('is_shipped', true)->count();

        return response()->json([
            'dispatch_note' => [
                'id' => $dispatchNote->id,
                'ref_no' => $dispatchNote->ref_no,
                'dispatch_date' => $dispatchNote->dispatch_date,
                'dispatch_time' => $dispatchNote->dispatch_time,
                'remarks' => $dispatchNote->remarks,
                'status' => $dispatchNote->status,
                'net_total' => (float) $dispatchNote->net_total,
                'delivery_total' => (float) $dispatchNote->delivery_total,
                'total_collectable' => (float) $dispatchNote->total_collectable,
                'creator' => $dispatchNote->creator ? [
                    'id' => $dispatchNote->creator->id,
                    'name' => $dispatchNote->creator->name,
                ] : null,
                'orders_count' => $orders->count(),
                'shipped_count' => $shippedCount,
            ],
            'orders' => $orders,
        ]);
    }

    public function scanWaybillForShipping(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:120'],
        ]);

        $code = trim((string) $validated['code']);
        $codeLower = strtolower($code);

        $order = Order::query()
            ->whereRaw('LOWER(waybill_no) = ?', [$codeLower])
            ->first();

        if (!$order && is_numeric($code)) {
            $order = Order::query()->find((int) $code);
        }

        if (!$order) {
            return response()->json([
                'message' => 'No order found for this waybill/order code.',
            ], 404);
        }

        $dispatchItem = DispatchNoteItem::query()
            ->with('dispatchNote:id,ref_no,status')
            ->where('order_id', $order->id)
            ->latest('id')
            ->first();

        if (!$dispatchItem || !$dispatchItem->dispatchNote) {
            return response()->json([
                'message' => 'Order is not attached to a dispatch note.',
            ], 422);
        }

        $requiredByVariant = \App\Models\OrderItem::query()
            ->where('order_id', $order->id)
            ->whereNotNull('product_variant_id')
            ->selectRaw('product_variant_id as variant_id, SUM(quantity) as qty')
            ->groupBy('product_variant_id')
            ->pluck('qty', 'variant_id')
            ->map(fn ($qty) => (int) $qty);

        $linkedByVariant = LotItem::query()
            ->where('order_id', $order->id)
            ->whereIn('status', ['reserved', 'sold'])
            ->selectRaw('variant_id, COUNT(*) as qty')
            ->groupBy('variant_id')
            ->pluck('qty', 'variant_id')
            ->map(fn ($qty) => (int) $qty);

        $status = strtolower((string) $order->status);
        $statusAllowsShip = in_array($status, ['packed', 'approved', 'confirmed'], true);
        $isAlreadyShipped = in_array($status, ['shipped', 'completed'], true);

        $isLinkingComplete = $requiredByVariant->isEmpty()
            || $requiredByVariant->every(function ($requiredQty, $variantId) use ($linkedByVariant) {
                return (int) ($linkedByVariant[(int) $variantId] ?? 0) >= (int) $requiredQty;
            });

        $canShip = !$isAlreadyShipped && $statusAllowsShip && $isLinkingComplete;

        $reason = null;
        if ($isAlreadyShipped) {
            $reason = 'Order is already shipped/completed.';
        } elseif (!$statusAllowsShip) {
            $reason = 'Order status is not eligible for shipping.';
        } elseif (!$isLinkingComplete) {
            $reason = 'Lot items are not fully linked for this order.';
        }

        return response()->json([
            'order' => [
                'id' => (int) $order->id,
                'status' => $order->status,
                'waybill_no' => $order->waybill_no,
                'customer_name' => $order->customer_name,
                'can_ship' => $canShip,
                'reason' => $reason,
            ],
            'dispatch_note' => [
                'id' => (int) $dispatchItem->dispatchNote->id,
                'ref_no' => $dispatchItem->dispatchNote->ref_no,
                'status' => $dispatchItem->dispatchNote->status,
            ],
        ]);
    }

    public function ship(DispatchNote $dispatchNote, Request $request)
    {
        $validated = $request->validate([
            'order_ids' => ['nullable', 'array'],
            'order_ids.*' => ['integer', 'exists:orders,id'],
        ]);

        $result = DB::transaction(function () use ($dispatchNote, $validated) {
            $note = DispatchNote::query()
                ->whereKey($dispatchNote->id)
                ->with(['items:id,dispatch_note_id,order_id'])
                ->lockForUpdate()
                ->firstOrFail();

            $itemOrderIds = collect($note->items)->pluck('order_id')->filter()->unique()->values();
            if ($itemOrderIds->isEmpty()) {
                throw ValidationException::withMessages([
                    'order_ids' => ['No orders found in this dispatch note.'],
                ]);
            }

            $requested = collect($validated['order_ids'] ?? [])->map(fn ($id) => (int) $id)->unique()->values();
            $targetIds = $requested->isNotEmpty()
                ? $requested->intersect($itemOrderIds)->values()
                : $itemOrderIds;

            if ($targetIds->isEmpty()) {
                throw ValidationException::withMessages([
                    'order_ids' => ['No matching orders found in this dispatch note.'],
                ]);
            }

            $orders = Order::query()
                ->whereIn('id', $targetIds)
                ->lockForUpdate()
                ->get(['id', 'status', 'shipped_at', 'is_draft']);

            $updated = 0;
            $alreadyShipped = 0;
            $skipped = 0;

            foreach ($orders as $order) {
                $status = strtolower((string) $order->status);
                if (in_array($status, ['shipped', 'completed'], true)) {
                    $alreadyShipped++;
                    continue;
                }

                if (!in_array($status, ['packed', 'approved', 'confirmed'], true)) {
                    $skipped++;
                    continue;
                }

                $requiredByVariant = \App\Models\OrderItem::query()
                    ->where('order_id', $order->id)
                    ->whereNotNull('product_variant_id')
                    ->selectRaw('product_variant_id as variant_id, SUM(quantity) as qty')
                    ->groupBy('product_variant_id')
                    ->pluck('qty', 'variant_id')
                    ->map(fn ($qty) => (int) $qty);

                if ($requiredByVariant->isNotEmpty()) {
                    $linkedByVariant = LotItem::query()
                        ->where('order_id', $order->id)
                        ->whereIn('status', ['reserved', 'sold'])
                        ->selectRaw('variant_id, COUNT(*) as qty')
                        ->groupBy('variant_id')
                        ->pluck('qty', 'variant_id')
                        ->map(fn ($qty) => (int) $qty);

                    $isFullyLinked = $requiredByVariant->every(function ($requiredQty, $variantId) use ($linkedByVariant) {
                        return (int) ($linkedByVariant[(int) $variantId] ?? 0) >= (int) $requiredQty;
                    });

                    if (!$isFullyLinked) {
                        $skipped++;
                        continue;
                    }
                }

                $order->update([
                    'status' => 'shipped',
                    'shipped_at' => now(),
                    'is_draft' => false,
                ]);

                LotItem::query()
                    ->where('order_id', $order->id)
                    ->where('status', 'reserved')
                    ->update(['status' => 'sold']);
                $updated++;
            }

            $allOrders = Order::query()
                ->whereIn('id', $itemOrderIds)
                ->get(['status']);

            $allShipped = $allOrders->isNotEmpty()
                && $allOrders->every(fn ($row) => in_array(strtolower((string) $row->status), ['shipped', 'completed'], true));

            $anyShipped = $allOrders->contains(fn ($row) => in_array(strtolower((string) $row->status), ['shipped', 'completed'], true));

            $note->status = $allShipped ? 'shipped' : ($anyShipped ? 'partial_shipped' : 'draft');
            $note->save();

            return [
                'updated' => $updated,
                'already_shipped' => $alreadyShipped,
                'skipped' => $skipped,
                'note_status' => $note->status,
            ];
        });

        return response()->json([
            'message' => "{$result['updated']} order(s) marked as shipped.",
            ...$result,
        ]);
    }

    public function validateLotBarcode(DispatchNote $dispatchNote, Request $request)
    {
        $validated = $request->validate([
            'order_id' => ['required', 'integer', 'exists:orders,id'],
            'variant_id' => ['nullable', 'integer', 'exists:varients,id'],
            'barcode' => ['required', 'string', 'max:120'],
        ]);

        $orderId = (int) $validated['order_id'];
        $variantId = isset($validated['variant_id']) ? (int) $validated['variant_id'] : null;
        $barcode = trim((string) $validated['barcode']);

        $belongsToNote = DispatchNoteItem::query()
            ->where('dispatch_note_id', $dispatchNote->id)
            ->where('order_id', $orderId)
            ->exists();

        if (!$belongsToNote) {
            throw ValidationException::withMessages([
                'order_id' => ['The order is not part of this dispatch note.'],
            ]);
        }

        $requiredVariantIds = \App\Models\OrderItem::query()
            ->where('order_id', $orderId)
            ->whereNotNull('product_variant_id')
            ->pluck('product_variant_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $lotItem = LotItem::query()->where('barcode', $barcode)->first();
        if (!$lotItem) {
            throw ValidationException::withMessages([
                'barcode' => ['Barcode not found.'],
            ]);
        }

        if ($variantId !== null && (int) $lotItem->variant_id !== $variantId) {
            throw ValidationException::withMessages([
                'barcode' => ['Barcode variant does not match selected order item.'],
            ]);
        }

        if (!$requiredVariantIds->contains((int) $lotItem->variant_id)) {
            throw ValidationException::withMessages([
                'barcode' => ['This barcode variant is not required for the selected order.'],
            ]);
        }

        $isUsable = (
            $lotItem->status === 'available'
            && empty($lotItem->order_id)
        ) || (
            (int) $lotItem->order_id === $orderId
            && in_array($lotItem->status, ['reserved', 'sold'], true)
        );

        if (!$isUsable) {
            throw ValidationException::withMessages([
                'barcode' => ['Barcode is already allocated or unavailable.'],
            ]);
        }

        return response()->json([
            'valid' => true,
            'barcode' => $lotItem->barcode,
            'variant_id' => (int) $lotItem->variant_id,
            'status' => $lotItem->status,
            'order_id' => $lotItem->order_id ? (int) $lotItem->order_id : null,
        ]);
    }

    public function linkLotItems(DispatchNote $dispatchNote, Request $request)
    {
        $validated = $request->validate([
            'order_id' => ['required', 'integer', 'exists:orders,id'],
            'variants' => ['required', 'array', 'min:1'],
            'variants.*.variant_id' => ['required', 'integer', 'exists:varients,id'],
            'variants.*.barcodes' => ['required', 'array', 'min:1'],
            'variants.*.barcodes.*' => ['required', 'string', 'max:120'],
        ]);

        $orderId = (int) $validated['order_id'];
        $variantPayload = collect($validated['variants'])
            ->map(function ($row) {
                return [
                    'variant_id' => (int) $row['variant_id'],
                    'barcodes' => collect($row['barcodes'])
                        ->map(fn ($barcode) => trim((string) $barcode))
                        ->filter()
                        ->unique()
                        ->values(),
                ];
            });

        DB::transaction(function () use ($dispatchNote, $orderId, $variantPayload) {
            $belongsToNote = DispatchNoteItem::query()
                ->where('dispatch_note_id', $dispatchNote->id)
                ->where('order_id', $orderId)
                ->exists();

            if (!$belongsToNote) {
                throw ValidationException::withMessages([
                    'order_id' => ['The order is not part of this dispatch note.'],
                ]);
            }

            $requiredByVariant = \App\Models\OrderItem::query()
                ->where('order_id', $orderId)
                ->whereNotNull('product_variant_id')
                ->selectRaw('product_variant_id as variant_id, SUM(quantity) as qty')
                ->groupBy('product_variant_id')
                ->pluck('qty', 'variant_id')
                ->map(fn ($qty) => (int) $qty);

            if ($requiredByVariant->isEmpty()) {
                throw ValidationException::withMessages([
                    'variants' => ['This order has no variant-linked items for lot scanning.'],
                ]);
            }

            $submittedByVariant = $variantPayload->pluck('barcodes', 'variant_id')
                ->map(fn ($barcodes) => $barcodes->count());

            $missingVariants = $requiredByVariant->keys()->diff($submittedByVariant->keys());
            if ($missingVariants->isNotEmpty()) {
                throw ValidationException::withMessages([
                    'variants' => ['Some required variants are missing scanned barcodes.'],
                ]);
            }

            foreach ($requiredByVariant as $variantId => $requiredQty) {
                $submittedQty = (int) ($submittedByVariant[(int) $variantId] ?? 0);
                if ($submittedQty !== (int) $requiredQty) {
                    throw ValidationException::withMessages([
                        'variants' => ["Variant {$variantId} requires {$requiredQty} barcode(s), received {$submittedQty}."],
                    ]);
                }
            }

            $allBarcodes = $variantPayload->flatMap(fn ($row) => $row['barcodes'])->values();
            if ($allBarcodes->count() !== $allBarcodes->unique()->count()) {
                throw ValidationException::withMessages([
                    'variants' => ['Duplicate barcodes detected in scan payload.'],
                ]);
            }

            $currentReserved = LotItem::query()
                ->where('order_id', $orderId)
                ->where('status', 'reserved')
                ->lockForUpdate()
                ->get(['id', 'barcode']);

            $toReleaseIds = $currentReserved
                ->filter(fn ($row) => !$allBarcodes->contains($row->barcode))
                ->pluck('id');

            if ($toReleaseIds->isNotEmpty()) {
                LotItem::query()->whereIn('id', $toReleaseIds)->update([
                    'order_id' => null,
                    'status' => 'available',
                ]);
            }

            foreach ($variantPayload as $variantRow) {
                $variantId = (int) $variantRow['variant_id'];
                foreach ($variantRow['barcodes'] as $barcode) {
                    $lotItem = LotItem::query()
                        ->where('barcode', $barcode)
                        ->lockForUpdate()
                        ->first();

                    if (!$lotItem) {
                        throw ValidationException::withMessages([
                            'variants' => ["Barcode {$barcode} not found."],
                        ]);
                    }

                    if ((int) $lotItem->variant_id !== $variantId) {
                        throw ValidationException::withMessages([
                            'variants' => ["Barcode {$barcode} does not match variant {$variantId}."],
                        ]);
                    }

                    $isUsable = (
                        $lotItem->status === 'available'
                        && empty($lotItem->order_id)
                    ) || (
                        (int) $lotItem->order_id === $orderId
                        && in_array($lotItem->status, ['reserved', 'sold'], true)
                    );

                    if (!$isUsable) {
                        throw ValidationException::withMessages([
                            'variants' => ["Barcode {$barcode} is already allocated."],
                        ]);
                    }

                    if ((int) $lotItem->order_id !== $orderId || $lotItem->status !== 'reserved') {
                        if ($lotItem->status !== 'sold') {
                            $lotItem->order_id = $orderId;
                            $lotItem->status = 'reserved';
                            $lotItem->save();
                        }
                    }
                }
            }
        });

        return response()->json([
            'message' => 'Lot items linked to order successfully.',
        ]);
    }

    public function adminPackedOrders(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'seller_id' => ['nullable', 'integer', 'exists:sellers,id'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:100'],
        ]);

        $search = trim((string) ($validated['search'] ?? ''));
        $perPage = (int) ($validated['per_page'] ?? 20);

        $query = Order::query()
            ->with([
                'seller:id,first_name,last_name,email,phone',
                'city:id,name_en',
                'customer:id,default_name,primary_phone,additional_phone,email,notes',
                'items:id,order_id,product_id,product_variant_id,quantity,price',
                'items.product:id,title,product_code',
                'items.variant:id,sku,attributes',
                'dispatchNoteItems:id,dispatch_note_id,order_id,waybill_snapshot,collectable_amount_snapshot,item_remarks',
                'dispatchNoteItems.dispatchNote:id,ref_no,dispatch_date,dispatch_time,status',
            ])
            ->where('status', 'packed')
            ->where('is_draft', false)
            ->latest('packed_at')
            ->latest('id');

        if (!empty($validated['seller_id'])) {
            $query->where('seller_id', (int) $validated['seller_id']);
        }
        if (!empty($validated['date_from'])) {
            $query->whereDate('packed_at', '>=', $validated['date_from']);
        }
        if (!empty($validated['date_to'])) {
            $query->whereDate('packed_at', '<=', $validated['date_to']);
        }
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('id', $search)
                    ->orWhere('customer_name', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%')
                    ->orWhere('waybill_no', 'like', '%' . $search . '%')
                    ->orWhereHas('seller', function ($sellerQuery) use ($search) {
                        $sellerQuery
                            ->where('first_name', 'like', '%' . $search . '%')
                            ->orWhere('last_name', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('dispatchNoteItems.dispatchNote', function ($dispatchQuery) use ($search) {
                        $dispatchQuery->where('ref_no', 'like', '%' . $search . '%');
                    });
            });
        }

        $orders = $query->paginate($perPage);

        $rows = collect($orders->items())->map(function ($order) {
            $dispatchItem = collect($order->dispatchNoteItems)
                ->sortByDesc('dispatch_note_id')
                ->first();

            $dispatchNote = $dispatchItem?->dispatchNote;

            $order->is_dispatched = !empty($dispatchItem);
            $order->dispatch_note = $dispatchNote ? [
                'id' => (int) $dispatchNote->id,
                'ref_no' => $dispatchNote->ref_no,
                'dispatch_date' => $dispatchNote->dispatch_date,
                'dispatch_time' => $dispatchNote->dispatch_time,
                'status' => $dispatchNote->status,
            ] : null;

            return $order;
        })->values();

        return response()->json([
            'orders' => $rows,
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    public function adminShippedOrders(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'seller_id' => ['nullable', 'integer', 'exists:sellers,id'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'date_basis' => ['nullable', 'in:order_date,shipped_date'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:100'],
        ]);

        $search = trim((string) ($validated['search'] ?? ''));
        $perPage = (int) ($validated['per_page'] ?? 20);
        $dateBasis = (string) ($validated['date_basis'] ?? 'shipped_date');
        $dateColumn = $dateBasis === 'order_date' ? 'order_datetime' : 'shipped_at';

        $query = Order::query()
            ->with([
                'seller:id,first_name,last_name,email,phone',
                'city:id,name_en',
                'customer:id,default_name,primary_phone,additional_phone,email,notes',
                'items:id,order_id,product_id,product_variant_id,quantity,price',
                'items.product:id,title,product_code',
                'items.variant:id,sku,attributes',
                'dispatchNoteItems:id,dispatch_note_id,order_id,waybill_snapshot,collectable_amount_snapshot,item_remarks',
                'dispatchNoteItems.dispatchNote:id,ref_no,dispatch_date,dispatch_time,status',
            ])
            ->whereIn('status', ['shipped', 'completed'])
            ->where('is_draft', false)
            ->latest('shipped_at')
            ->latest('id');

        if (!empty($validated['seller_id'])) {
            $query->where('seller_id', (int) $validated['seller_id']);
        }
        if (!empty($validated['date_from'])) {
            $query->whereDate($dateColumn, '>=', $validated['date_from']);
        }
        if (!empty($validated['date_to'])) {
            $query->whereDate($dateColumn, '<=', $validated['date_to']);
        }
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('id', $search)
                    ->orWhere('customer_name', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%')
                    ->orWhere('waybill_no', 'like', '%' . $search . '%')
                    ->orWhereHas('seller', function ($sellerQuery) use ($search) {
                        $sellerQuery
                            ->where('first_name', 'like', '%' . $search . '%')
                            ->orWhere('last_name', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('dispatchNoteItems.dispatchNote', function ($dispatchQuery) use ($search) {
                        $dispatchQuery->where('ref_no', 'like', '%' . $search . '%');
                    });
            });
        }

        $orders = $query->paginate($perPage);

        $rows = collect($orders->items())->map(function ($order) {
            $dispatchItem = collect($order->dispatchNoteItems)
                ->sortByDesc('dispatch_note_id')
                ->first();

            $dispatchNote = $dispatchItem?->dispatchNote;
            $order->dispatch_note = $dispatchNote ? [
                'id' => (int) $dispatchNote->id,
                'ref_no' => $dispatchNote->ref_no,
                'dispatch_date' => $dispatchNote->dispatch_date,
                'dispatch_time' => $dispatchNote->dispatch_time,
                'status' => $dispatchNote->status,
            ] : null;

            return $order;
        })->values();

        return response()->json([
            'orders' => $rows,
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
            'filters' => [
                'date_basis' => $dateBasis,
            ],
        ]);
    }

    public function adminCompletedOrders(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'seller_id' => ['nullable', 'integer', 'exists:sellers,id'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'date_basis' => ['nullable', 'in:order_date,completed_date'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:100'],
        ]);

        $search = trim((string) ($validated['search'] ?? ''));
        $perPage = (int) ($validated['per_page'] ?? 20);
        $dateBasis = (string) ($validated['date_basis'] ?? 'completed_date');
        $dateColumn = $dateBasis === 'order_date' ? 'order_datetime' : 'completed_at';

        $query = Order::query()
            ->with([
                'seller:id,first_name,last_name,email,phone',
                'city:id,name_en',
                'customer:id,default_name,primary_phone,additional_phone,email,notes',
                'items:id,order_id,product_id,product_variant_id,quantity,price',
                'items.product:id,title,product_code',
                'items.variant:id,sku,attributes',
                'dispatchNoteItems:id,dispatch_note_id,order_id,waybill_snapshot,collectable_amount_snapshot,item_remarks',
                'dispatchNoteItems.dispatchNote:id,ref_no,dispatch_date,dispatch_time,status',
            ])
            ->where('status', 'completed')
            ->where('is_draft', false)
            ->latest('completed_at')
            ->latest('id');

        if (!empty($validated['seller_id'])) {
            $query->where('seller_id', (int) $validated['seller_id']);
        }
        if (!empty($validated['date_from'])) {
            $query->whereDate($dateColumn, '>=', $validated['date_from']);
        }
        if (!empty($validated['date_to'])) {
            $query->whereDate($dateColumn, '<=', $validated['date_to']);
        }
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('id', $search)
                    ->orWhere('customer_name', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%')
                    ->orWhere('waybill_no', 'like', '%' . $search . '%')
                    ->orWhereHas('seller', function ($sellerQuery) use ($search) {
                        $sellerQuery
                            ->where('first_name', 'like', '%' . $search . '%')
                            ->orWhere('last_name', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('dispatchNoteItems.dispatchNote', function ($dispatchQuery) use ($search) {
                        $dispatchQuery->where('ref_no', 'like', '%' . $search . '%');
                    });
            });
        }

        $orders = $query->paginate($perPage);

        $rows = collect($orders->items())->map(function ($order) {
            $dispatchItem = collect($order->dispatchNoteItems)
                ->sortByDesc('dispatch_note_id')
                ->first();

            $dispatchNote = $dispatchItem?->dispatchNote;
            $order->dispatch_note = $dispatchNote ? [
                'id' => (int) $dispatchNote->id,
                'ref_no' => $dispatchNote->ref_no,
                'dispatch_date' => $dispatchNote->dispatch_date,
                'dispatch_time' => $dispatchNote->dispatch_time,
                'status' => $dispatchNote->status,
            ] : null;

            return $order;
        })->values();

        return response()->json([
            'orders' => $rows,
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
            'filters' => [
                'date_basis' => $dateBasis,
            ],
        ]);
    }

    public function adminCancelledOrders(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'seller_id' => ['nullable', 'integer', 'exists:sellers,id'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'date_basis' => ['nullable', 'in:order_date,cancelled_date'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:100'],
        ]);

        $search = trim((string) ($validated['search'] ?? ''));
        $perPage = (int) ($validated['per_page'] ?? 20);
        $dateBasis = (string) ($validated['date_basis'] ?? 'cancelled_date');
        $dateColumn = $dateBasis === 'order_date' ? 'order_datetime' : 'cancelled_at';

        $query = Order::query()
            ->with([
                'seller:id,first_name,last_name,email,phone',
                'city:id,name_en',
                'customer:id,default_name,primary_phone,additional_phone,email,notes',
                'items:id,order_id,product_id,product_variant_id,quantity,price',
                'items.product:id,title,product_code',
                'items.variant:id,sku,attributes',
                'dispatchNoteItems:id,dispatch_note_id,order_id,waybill_snapshot,collectable_amount_snapshot,item_remarks',
                'dispatchNoteItems.dispatchNote:id,ref_no,dispatch_date,dispatch_time,status',
            ])
            ->where('status', 'cancelled')
            ->latest('cancelled_at')
            ->latest('id');

        if (!empty($validated['seller_id'])) {
            $query->where('seller_id', (int) $validated['seller_id']);
        }
        if (!empty($validated['date_from'])) {
            $query->whereDate($dateColumn, '>=', $validated['date_from']);
        }
        if (!empty($validated['date_to'])) {
            $query->whereDate($dateColumn, '<=', $validated['date_to']);
        }
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('id', $search)
                    ->orWhere('customer_name', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%')
                    ->orWhere('waybill_no', 'like', '%' . $search . '%')
                    ->orWhereHas('seller', function ($sellerQuery) use ($search) {
                        $sellerQuery
                            ->where('first_name', 'like', '%' . $search . '%')
                            ->orWhere('last_name', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('dispatchNoteItems.dispatchNote', function ($dispatchQuery) use ($search) {
                        $dispatchQuery->where('ref_no', 'like', '%' . $search . '%');
                    });
            });
        }

        $orders = $query->paginate($perPage);

        $rows = collect($orders->items())->map(function ($order) {
            $dispatchItem = collect($order->dispatchNoteItems)
                ->sortByDesc('dispatch_note_id')
                ->first();

            $dispatchNote = $dispatchItem?->dispatchNote;
            $order->dispatch_note = $dispatchNote ? [
                'id' => (int) $dispatchNote->id,
                'ref_no' => $dispatchNote->ref_no,
                'dispatch_date' => $dispatchNote->dispatch_date,
                'dispatch_time' => $dispatchNote->dispatch_time,
                'status' => $dispatchNote->status,
            ] : null;

            return $order;
        })->values();

        return response()->json([
            'orders' => $rows,
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
            'filters' => [
                'date_basis' => $dateBasis,
            ],
        ]);
    }

    public function adminApprovedOrders(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'seller_id' => ['nullable', 'integer', 'exists:sellers,id'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:100'],
        ]);

        $search = trim((string) ($validated['search'] ?? ''));
        $perPage = (int) ($validated['per_page'] ?? 20);

        $query = Order::query()
            ->with([
                'seller:id,first_name,last_name,email,phone',
                'city:id,name_en',
                'customer:id,default_name,primary_phone,additional_phone,email,notes',
                'items:id,order_id,product_id,product_variant_id,quantity,price',
                'items.product:id,title,product_code',
                'items.variant:id,sku,attributes',
            ])
            ->where('status', 'approved')
            ->where('is_draft', false)
            ->latest('order_datetime')
            ->latest('id');

        if (!empty($validated['seller_id'])) {
            $query->where('seller_id', (int) $validated['seller_id']);
        }
        if (!empty($validated['date_from'])) {
            $query->whereDate('order_datetime', '>=', $validated['date_from']);
        }
        if (!empty($validated['date_to'])) {
            $query->whereDate('order_datetime', '<=', $validated['date_to']);
        }
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('id', $search)
                    ->orWhere('customer_name', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%')
                    ->orWhere('waybill_no', 'like', '%' . $search . '%')
                    ->orWhereHas('seller', function ($sellerQuery) use ($search) {
                        $sellerQuery
                            ->where('first_name', 'like', '%' . $search . '%')
                            ->orWhere('last_name', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%');
                    });
            });
        }

        $orders = $query->paginate($perPage);

        $variantStock = LotItem::query()
            ->selectRaw('variant_id, COUNT(*) as available_qty')
            ->where('status', 'available')
            ->whereNull('order_id')
            ->groupBy('variant_id')
            ->pluck('available_qty', 'variant_id');

        $alreadyAssigned = DispatchNoteItem::query()
            ->whereIn('order_id', collect($orders->items())->pluck('id'))
            ->pluck('dispatch_note_id', 'order_id');

        $rows = collect($orders->items())->map(function ($order) use ($variantStock, $alreadyAssigned) {
            $requiredByVariant = collect($order->items)
                ->filter(fn ($item) => !empty($item->product_variant_id))
                ->groupBy('product_variant_id')
                ->map(fn ($items) => (int) collect($items)->sum('quantity'));

            $stockChecks = $requiredByVariant->map(function ($requiredQty, $variantId) use ($variantStock) {
                $available = (int) ($variantStock[$variantId] ?? 0);
                return [
                    'variant_id' => (int) $variantId,
                    'required_qty' => (int) $requiredQty,
                    'available_qty' => $available,
                    'is_available' => $available >= (int) $requiredQty,
                ];
            })->values();

            $canShip = $requiredByVariant->isNotEmpty() && $stockChecks->every(fn ($x) => $x['is_available']);

            $assignedDispatchId = $alreadyAssigned[$order->id] ?? null;
            if ($assignedDispatchId) {
                $canShip = false;
            }

            $order->can_ship = $canShip;
            $order->stock_checks = $stockChecks;
            $order->dispatch_note_id = $assignedDispatchId ? (int) $assignedDispatchId : null;

            return $order;
        })->values();

        return response()->json([
            'orders' => $rows,
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    public function createFromApproved(Request $request)
    {
        $validated = $request->validate([
            'order_ids' => ['required', 'array', 'min:1'],
            'order_ids.*' => ['integer', 'exists:orders,id'],
            'dispatch_date' => ['nullable', 'date'],
            'dispatch_time' => ['nullable', 'date_format:H:i'],
            'remarks' => ['nullable', 'string', 'max:2000'],
        ]);

        $note = DB::transaction(function () use ($validated, $request) {
            $orders = Order::query()
                ->with(['items:id,order_id,product_variant_id,quantity'])
                ->whereIn('id', $validated['order_ids'])
                ->where('status', 'approved')
                ->where('is_draft', false)
                ->lockForUpdate()
                ->get();

            if ($orders->isEmpty()) {
                throw ValidationException::withMessages([
                    'order_ids' => ['No eligible approved orders found for dispatch.'],
                ]);
            }

            $alreadyInDispatch = DispatchNoteItem::query()
                ->whereIn('order_id', $orders->pluck('id'))
                ->pluck('order_id')
                ->all();

            if (!empty($alreadyInDispatch)) {
                throw ValidationException::withMessages([
                    'order_ids' => ['One or more selected orders are already assigned to a dispatch note.'],
                ]);
            }

            $requiredByVariant = [];
            foreach ($orders as $order) {
                foreach ($order->items as $item) {
                    if (empty($item->product_variant_id)) {
                        continue;
                    }
                    $variantId = (int) $item->product_variant_id;
                    $requiredByVariant[$variantId] = ($requiredByVariant[$variantId] ?? 0) + (int) $item->quantity;
                }
            }

            $availableByVariant = LotItem::query()
                ->selectRaw('variant_id, COUNT(*) as available_qty')
                ->where('status', 'available')
                ->whereNull('order_id')
                ->whereIn('variant_id', array_keys($requiredByVariant))
                ->groupBy('variant_id')
                ->pluck('available_qty', 'variant_id');

            $insufficient = [];
            foreach ($requiredByVariant as $variantId => $requiredQty) {
                $available = (int) ($availableByVariant[$variantId] ?? 0);
                if ($available < $requiredQty) {
                    $insufficient[] = [
                        'variant_id' => (int) $variantId,
                        'required_qty' => (int) $requiredQty,
                        'available_qty' => $available,
                    ];
                }
            }

            if (!empty($insufficient)) {
                throw ValidationException::withMessages([
                    'order_ids' => ['Insufficient lot stock for selected orders.'],
                ]);
            }

            $now = now();
            $dispatchDate = $validated['dispatch_date'] ?? $now->toDateString();
            $dispatchTime = $validated['dispatch_time'] ?? $now->format('H:i');

            $nextRefNo = 'DN-' . str_pad((string) (((int) DispatchNote::max('id')) + 1), 6, '0', STR_PAD_LEFT);

            $note = DispatchNote::create([
                'ref_no' => $nextRefNo,
                'dispatch_date' => $dispatchDate,
                'dispatch_time' => $dispatchTime,
                'remarks' => $validated['remarks'] ?? null,
                'created_by' => $request->user()?->id,
                'net_total' => (float) $orders->sum('net_total'),
                'delivery_total' => (float) $orders->sum('delivery_charge'),
                'total_collectable' => (float) $orders->sum('total_collectable_amount'),
                'status' => 'draft',
            ]);

            $rows = $orders->map(function ($order) use ($note) {
                return [
                    'dispatch_note_id' => $note->id,
                    'order_id' => $order->id,
                    'waybill_snapshot' => $order->waybill_no,
                    'collectable_amount_snapshot' => (float) ($order->total_collectable_amount ?? 0),
                    'item_remarks' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->all();

            DispatchNoteItem::insert($rows);

            Order::query()
                ->whereIn('id', $orders->pluck('id'))
                ->update([
                    'status' => 'packed',
                    'packed_at' => now(),
                    'is_draft' => false,
                ]);

            return $note;
        });

        return response()->json([
            'message' => 'Dispatch note created successfully.',
            'dispatch_note_id' => $note->id,
            'ref_no' => $note->ref_no,
        ], 201);
    }

    public function sellerOptions()
    {
        $sellers = Seller::query()
            ->select('id', 'first_name', 'last_name', 'email')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->map(function ($seller) {
                $name = trim(($seller->first_name ?? '') . ' ' . ($seller->last_name ?? ''));
                return [
                    'id' => $seller->id,
                    'label' => $name !== '' ? $name : ($seller->email ?: ('Seller #' . $seller->id)),
                ];
            })
            ->values();

        return response()->json(['sellers' => $sellers]);
    }
}
