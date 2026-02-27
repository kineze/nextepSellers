<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class SellerPaymentController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'in:all,available,paid'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:100'],
        ]);

        $seller = $request->user()?->seller;
        if (!$seller) {
            return response()->json([
                'message' => 'Seller profile not found for this user.',
            ], 422);
        }

        $status = (string) ($validated['status'] ?? 'available');
        $search = trim((string) ($validated['search'] ?? ''));
        $perPage = (int) ($validated['per_page'] ?? 20);

        $query = Order::query()
            ->where('seller_id', $seller->id)
            ->whereIn('payment_status', ['available', 'paid'])
            ->latest('completed_at')
            ->latest('id');

        if ($status !== 'all') {
            $query->where('payment_status', $status);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('id', $search)
                    ->orWhere('customer_name', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%')
                    ->orWhere('waybill_no', 'like', '%' . $search . '%');
            });
        }

        $orders = $query->paginate($perPage);

        $totalPendingOrderValue = (float) Order::query()
            ->where('seller_id', $seller->id)
            ->where('payment_status', 'pending')
            ->sum('total_collectable_amount');

        $availablePaymentsValue = (float) Order::query()
            ->where('seller_id', $seller->id)
            ->where('payment_status', 'available')
            ->sum('commission_amount');

        $paidPaymentsValue = (float) Order::query()
            ->where('seller_id', $seller->id)
            ->where('payment_status', 'paid')
            ->sum('commission_amount');

        $pendingPaymentsValue = (float) Order::query()
            ->where('seller_id', $seller->id)
            ->where('payment_status', 'pending')
            ->sum('commission_amount');

        $payments = collect($orders->items())->map(function (Order $order) {
            return [
                'id' => (int) $order->id,
                'order_id' => (int) $order->id,
                'amount' => (float) ($order->commission_amount ?? 0),
                'status' => (string) ($order->payment_status ?? 'pending'),
                'available_at' => $order->completed_at,
                'paid_at' => null,
                'order' => [
                    'id' => (int) $order->id,
                    'customer_name' => $order->customer_name,
                    'phone' => $order->phone,
                    'waybill_no' => $order->waybill_no,
                    'commission_amount' => (float) ($order->commission_amount ?? 0),
                    'payment_status' => (string) ($order->payment_status ?? 'pending'),
                    'completed_at' => $order->completed_at,
                ],
            ];
        })->values();

        return response()->json([
            'summary' => [
                'total_pending_order_value' => round($totalPendingOrderValue, 2),
                'available_payments_value' => round($availablePaymentsValue, 2),
                'paid_payments_value' => round($paidPaymentsValue, 2),
                'pending_payments_value' => round($pendingPaymentsValue, 2),
            ],
            'payments' => $payments,
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ]);
    }
}
