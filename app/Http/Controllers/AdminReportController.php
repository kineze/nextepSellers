<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminReportController extends Controller
{
    public function orderCount(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'seller_id' => ['nullable', 'integer', 'exists:sellers,id'],
        ]);

        $query = Order::query();

        if (!empty($validated['date_from'])) {
            $query->whereDate('order_datetime', '>=', $validated['date_from']);
        }

        if (!empty($validated['date_to'])) {
            $query->whereDate('order_datetime', '<=', $validated['date_to']);
        }

        if (!empty($validated['seller_id'])) {
            $query->where('seller_id', (int) $validated['seller_id']);
        }

        $search = trim((string) ($validated['search'] ?? ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('id', $search)
                    ->orWhere('customer_name', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%')
                    ->orWhere('additional_phone', 'like', '%' . $search . '%')
                    ->orWhere('waybill_no', 'like', '%' . $search . '%')
                    ->orWhereHas('seller', function ($sellerQuery) use ($search) {
                        $sellerQuery->where('first_name', 'like', '%' . $search . '%')
                            ->orWhere('last_name', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%')
                            ->orWhere('phone', 'like', '%' . $search . '%');
                    });
            });
        }

        $counts = (clone $query)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $statuses = [
            'draft',
            'approved',
            'confirmed',
            'packed',
            'shipped',
            'completed',
            'cancelled',
            'rejected',
        ];

        $statusCounts = collect($statuses)
            ->mapWithKeys(fn ($status) => [$status => (int) ($counts[$status] ?? 0)])
            ->all();

        $completed = $statusCounts['completed'];
        $unsuccessful = $statusCounts['cancelled'] + $statusCounts['rejected'];
        $inTransit = $statusCounts['shipped'];
        $deliveryTotal = $completed + $unsuccessful;

        return response()->json([
            'total_orders' => array_sum($statusCounts),
            'status_counts' => $statusCounts,
            'delivery' => [
                'completed' => $completed,
                'unsuccessful' => $unsuccessful,
                'in_transit' => $inTransit,
                'success_ratio' => $deliveryTotal > 0 ? round(($completed / $deliveryTotal) * 100, 1) : 0,
            ],
        ]);
    }
}
