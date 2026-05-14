<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class AdminOrderSearchController extends Controller
{
    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'type' => ['nullable', 'in:all,order,phone,waybill,customer,seller,status'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $q = trim((string) ($validated['q'] ?? ''));
        $type = (string) ($validated['type'] ?? 'all');
        $page = (int) ($validated['page'] ?? 1);
        $perPage = 8;

        if (mb_strlen($q) < 2) {
            return response()->json([
                'data' => [],
                'next_page_url' => null,
            ]);
        }

        $orders = Order::query()
            ->with(['seller:id,first_name,last_name,email,phone'])
            ->select([
                'id',
                'seller_id',
                'customer_name',
                'phone',
                'additional_phone',
                'waybill_no',
                'status',
                'delivery_status',
                'payment_status',
                'order_datetime',
                'total_collectable_amount',
            ])
            ->when($type === 'order', fn ($query) => $query->where('id', 'like', "{$q}%"))
            ->when($type === 'phone', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('phone', 'like', "{$q}%")
                        ->orWhere('additional_phone', 'like', "{$q}%");
                });
            })
            ->when($type === 'waybill', fn ($query) => $query->where('waybill_no', 'like', "{$q}%"))
            ->when($type === 'customer', fn ($query) => $query->where('customer_name', 'like', "%{$q}%"))
            ->when($type === 'seller', function ($query) use ($q) {
                $query->whereHas('seller', function ($sellerQuery) use ($q) {
                    $sellerQuery
                        ->where('first_name', 'like', "%{$q}%")
                        ->orWhere('last_name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhere('phone', 'like', "{$q}%");
                });
            })
            ->when($type === 'status', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('status', 'like', "{$q}%")
                        ->orWhere('delivery_status', 'like', "%{$q}%")
                        ->orWhere('payment_status', 'like', "{$q}%");
                });
            })
            ->when($type === 'all', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('id', 'like', "{$q}%")
                        ->orWhere('waybill_no', 'like', "{$q}%")
                        ->orWhere('customer_name', 'like', "%{$q}%")
                        ->orWhere('phone', 'like', "{$q}%")
                        ->orWhere('additional_phone', 'like', "{$q}%")
                        ->orWhere('status', 'like', "{$q}%")
                        ->orWhere('delivery_status', 'like', "%{$q}%")
                        ->orWhereHas('seller', function ($sellerQuery) use ($q) {
                            $sellerQuery
                                ->where('first_name', 'like', "%{$q}%")
                                ->orWhere('last_name', 'like', "%{$q}%")
                                ->orWhere('email', 'like', "%{$q}%")
                                ->orWhere('phone', 'like', "{$q}%");
                        });
                });
            })
            ->latest('id')
            ->simplePaginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'data' => collect($orders->items())->map(fn (Order $order) => $this->formatOrder($order))->values(),
            'next_page_url' => $orders->nextPageUrl(),
        ]);
    }

    private function formatOrder(Order $order): array
    {
        $sellerName = trim((string) ($order->seller?->first_name ?? '') . ' ' . (string) ($order->seller?->last_name ?? ''));

        return [
            'id' => (int) $order->id,
            'label' => 'Order #' . $order->id,
            'customer_name' => $order->customer_name,
            'phone' => $order->phone ?: $order->additional_phone,
            'seller_name' => $sellerName !== '' ? $sellerName : ($order->seller?->email ?? null),
            'waybill_no' => $order->waybill_no,
            'status' => $order->status,
            'delivery_status' => $order->delivery_status,
            'payment_status' => $order->payment_status,
            'order_datetime' => $order->order_datetime,
            'total_collectable_amount' => (float) ($order->total_collectable_amount ?? 0),
            'url' => '/admin/orders/' . $order->id,
        ];
    }
}
