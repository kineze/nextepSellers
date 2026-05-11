<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderTrackingSyncService;
use Illuminate\Http\Request;

class AdminOrderTrackingController extends Controller
{
    public function summary(OrderTrackingSyncService $service)
    {
        $context = $service->validateTrackingContext();

        return response()->json([
            'ready' => (bool) ($context['ready'] ?? false),
            'message' => $context['message'] ?? null,
            'shipped_count' => Order::query()
                ->where('status', 'shipped')
                ->whereNotNull('waybill_no')
                ->count(),
        ]);
    }

    public function fetchShipped(Request $request, OrderTrackingSyncService $service)
    {
        $validated = $request->validate([
            'limit' => ['nullable', 'integer', 'min:1', 'max:200'],
        ]);

        $context = $service->validateTrackingContext();
        if (!($context['ready'] ?? false)) {
            return response()->json([
                'message' => $context['message'] ?? 'Tracking context is not ready.',
                'ready' => false,
                'results' => [],
            ], 422);
        }

        $limit = (int) ($validated['limit'] ?? 50);
        $orders = Order::query()
            ->with('seller:id,first_name,last_name,email')
            ->where('status', 'shipped')
            ->whereNotNull('waybill_no')
            ->orderBy('shipped_at')
            ->orderBy('id')
            ->limit($limit)
            ->get(['id', 'seller_id', 'customer_name', 'waybill_no', 'status', 'delivery_status', 'shipped_at']);

        $results = $orders->map(function (Order $order) use ($service) {
            $beforeStatus = $order->status;
            $beforeDeliveryStatus = $order->delivery_status;
            $result = $service->syncOrderById((int) $order->id);
            $fresh = Order::query()->find($order->id);

            return [
                'order_id' => (int) $order->id,
                'customer_name' => $order->customer_name,
                'seller_name' => $this->sellerName($order),
                'waybill_no' => $order->waybill_no,
                'before_status' => $beforeStatus,
                'before_delivery_status' => $beforeDeliveryStatus,
                'courier_status' => $result['courier_status'] ?? null,
                'local_status' => $result['local_status'] ?? null,
                'after_status' => $fresh?->status ?? ($result['order_status'] ?? null),
                'after_delivery_status' => $fresh?->delivery_status ?? ($result['courier_status'] ?? null),
                'updated' => (bool) ($result['updated'] ?? false),
                'completed' => (bool) ($result['completed'] ?? false),
                'cancelled' => (bool) ($result['cancelled'] ?? false),
                'points_awarded' => (int) ($result['points_awarded'] ?? 0),
                'level_upgraded' => (bool) ($result['level_upgraded'] ?? false),
                'error' => $result['error'] ?? null,
            ];
        })->values();

        return response()->json([
            'ready' => true,
            'processed' => $results->count(),
            'completed_count' => $results->where('completed', true)->count(),
            'cancelled_count' => $results->where('cancelled', true)->count(),
            'updated_count' => $results->where('updated', true)->count(),
            'error_count' => $results->filter(fn ($row) => !empty($row['error']))->count(),
            'remaining_shipped_count' => Order::query()
                ->where('status', 'shipped')
                ->whereNotNull('waybill_no')
                ->count(),
            'results' => $results,
        ]);
    }

    private function sellerName(Order $order): string
    {
        $seller = $order->seller;
        if (!$seller) {
            return '-';
        }

        $name = trim((string) $seller->first_name . ' ' . (string) $seller->last_name);

        return $name !== '' ? $name : ((string) $seller->email ?: 'Seller #' . $seller->id);
    }
}
