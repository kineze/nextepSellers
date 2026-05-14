<?php

namespace App\Http\Controllers;

use App\Jobs\FetchTrackingJob;
use App\Models\Order;
use App\Services\OrderTrackingSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            'queued_count' => $this->queuedTrackingJobsCount(),
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
            ->where('status', 'shipped')
            ->whereNotNull('waybill_no')
            ->orderBy('shipped_at')
            ->orderBy('id')
            ->limit($limit)
            ->get(['id']);

        foreach ($orders as $order) {
            FetchTrackingJob::dispatch((int) $order->id)->onQueue('tracking');
        }

        return response()->json([
            'ready' => true,
            'message' => 'Tracking jobs queued successfully.',
            'queued_count' => $orders->count(),
            'processed' => 0,
            'completed_count' => 0,
            'cancelled_count' => 0,
            'updated_count' => 0,
            'error_count' => 0,
            'remaining_shipped_count' => Order::query()
                ->where('status', 'shipped')
                ->whereNotNull('waybill_no')
                ->count(),
            'results' => [],
        ]);
    }

    private function queuedTrackingJobsCount(): int
    {
        try {
            return (int) DB::table('jobs')
                ->where('queue', 'tracking')
                ->count();
        } catch (\Throwable) {
            return 0;
        }
    }
}
