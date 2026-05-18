<?php

namespace App\Http\Controllers;

use App\Models\Level;
use App\Models\Order;
use App\Models\Seller;
use App\Models\SellerPenalty;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SellerDashboardController extends Controller
{
    public function analytics(Request $request)
    {
        $seller = $request->user()?->seller?->loadMissing('level');

        if (!$seller) {
            return response()->json([
                'message' => 'Seller profile not found.',
            ], 422);
        }

        $currentPoints = (int) ($seller->points ?? 0);
        $level = $seller->level;
        $nextLevel = $this->nextLevel($level);
        $lkrPerPoint = $this->lkrPerPoint();

        $pendingBaseAmount = (float) Order::query()
            ->where('seller_id', $seller->id)
            ->whereIn('status', ['draft', 'approved', 'confirmed', 'packed', 'shipped'])
            ->selectRaw('COALESCE(SUM(GREATEST(net_total - total_discount, 0)), 0) as pending_amount')
            ->value('pending_amount');

        $pendingPoints = (int) floor($pendingBaseAmount / $lkrPerPoint);
        $projectedPoints = $currentPoints + $pendingPoints;
        $currentLevelPoints = $level ? (int) ($level->points ?? 0) : 0;
        $nextLevelPoints = $nextLevel ? (int) $nextLevel->points : null;
        $currentPointsToNextLevel = $nextLevelPoints ? max(0, $nextLevelPoints - $currentPoints) : null;
        $currentLevelProgressPoints = $nextLevelPoints ? max(0, $currentPoints - $currentLevelPoints) : 0;
        $currentLevelProgressTarget = $nextLevelPoints ? max(1, $nextLevelPoints - $currentLevelPoints) : null;
        $currentProgressPct = $currentLevelProgressTarget
            ? min(100, max(0, (int) floor(($currentLevelProgressPoints / $currentLevelProgressTarget) * 100)))
            : 100;
        $pointsToNextLevel = $nextLevelPoints ? max(0, $nextLevelPoints - $projectedPoints) : null;
        $levelProgressPoints = $nextLevelPoints ? max(0, $projectedPoints - $currentLevelPoints) : 0;
        $levelProgressTarget = $nextLevelPoints ? max(1, $nextLevelPoints - $currentLevelPoints) : null;
        $progressPct = $levelProgressTarget
            ? min(100, max(0, (int) floor(($levelProgressPoints / $levelProgressTarget) * 100)))
            : 100;

        return response()->json([
            'seller' => [
                'id' => (int) $seller->id,
                'name' => $this->sellerName($seller, $request->user()?->name),
                'status' => $seller->status,
                'is_restrict' => (bool) $seller->is_restrict,
                'restrictions' => $this->restrictionSummary($seller),
                'delivery_score' => (int) ($seller->dilivery_score ?? 100),
                'delivery_score_penalty_limit' => (int) config('seller.penalty_limit_start_for_failed_to_delivery', 60),
            ],
            'points' => [
                'level_name' => $level?->level_name,
                'level_no' => $level?->level_no,
                'current_level_points' => $currentLevelPoints,
                'current_points' => $currentPoints,
                'pending_points' => $pendingPoints,
                'projected_points' => $projectedPoints,
                'pending_base_amount' => round($pendingBaseAmount, 2),
                'next_level_name' => $nextLevel?->level_name,
                'next_level_no' => $nextLevel?->level_no,
                'next_level_points' => $nextLevelPoints,
                'current_points_to_next_level' => $currentPointsToNextLevel,
                'current_level_progress_points' => $currentLevelProgressPoints,
                'current_level_progress_target' => $currentLevelProgressTarget,
                'current_progress_pct' => $currentProgressPct,
                'points_to_next_level' => $pointsToNextLevel,
                'level_progress_points' => $levelProgressPoints,
                'level_progress_target' => $levelProgressTarget,
                'progress_pct' => $progressPct,
                'lkr_per_point' => $lkrPerPoint,
            ],
            'orders' => $this->orderAnalytics($seller),
            'finance' => $this->financeAnalytics($seller),
            'trend' => $this->trend($seller),
            'recent_orders' => $this->recentOrders($seller),
            'links' => [
                'products' => route('sellerProducts'),
                'orders' => route('sellerOrders'),
                'bulk_orders' => route('sellerBulkOrders'),
                'affiliate' => route('sellerAffiliate'),
                'payments' => route('sellerPayments'),
                'website' => route('home'),
            ],
        ]);
    }

    private function nextLevel(?Level $level): ?Level
    {
        $query = Level::query()->orderBy('points');

        if ($level) {
            $query->where('points', '>', (int) ($level->points ?? 0));
        }

        return $query->first(['id', 'level_no', 'level_name', 'points']);
    }

    private function lkrPerPoint(): float
    {
        $value = (float) config('seller.lkr_per_point', 100);

        return $value > 0 ? $value : 100.0;
    }

    private function sellerName(Seller $seller, ?string $fallback): string
    {
        $name = trim((string) $seller->first_name . ' ' . (string) $seller->last_name);

        return $name !== '' ? $name : (string) ($fallback ?: 'Seller');
    }

    private function restrictionSummary(Seller $seller): array
    {
        if (! $seller->is_restrict) {
            return [
                'blocked_withdrawals' => false,
                'blocked_order_placing' => false,
                'daily_order_limit' => null,
                'active_penalties_count' => 0,
            ];
        }

        $penalties = SellerPenalty::query()
            ->where('seller_id', $seller->id)
            ->where('is_active', true)
            ->get();

        $dailyLimits = $penalties
            ->map(fn (SellerPenalty $penalty) => data_get($penalty->rules, 'orders.daily_order_limit'))
            ->filter(fn ($limit) => $limit !== null)
            ->map(fn ($limit) => (int) $limit);

        return [
            'blocked_withdrawals' => $penalties->contains(fn (SellerPenalty $penalty) => (bool) data_get($penalty->rules, 'account.block_withdrawals', false)),
            'blocked_order_placing' => $penalties->contains(fn (SellerPenalty $penalty) => (bool) data_get($penalty->rules, 'account.block_order_placing', false)),
            'daily_order_limit' => $dailyLimits->isEmpty() ? null : $dailyLimits->min(),
            'active_penalties_count' => $penalties->count(),
        ];
    }

    private function orderAnalytics(Seller $seller): array
    {
        $statuses = ['draft', 'approved', 'confirmed', 'packed', 'shipped', 'completed', 'cancelled', 'rejected'];
        $counts = Order::query()
            ->select('status', DB::raw('COUNT(*) as aggregate'))
            ->where('seller_id', $seller->id)
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $byStatus = collect($statuses)
            ->mapWithKeys(fn (string $status) => [$status => (int) ($counts[$status] ?? 0)])
            ->all();

        return [
            'total' => (int) Order::query()->where('seller_id', $seller->id)->count(),
            'by_status' => $byStatus,
        ];
    }

    private function financeAnalytics(Seller $seller): array
    {
        $base = Order::query()->where('seller_id', $seller->id);

        return [
            'pending_commission' => round((float) (clone $base)->where('payment_status', 'pending')->sum('commission_amount'), 2),
            'available_commission' => round((float) (clone $base)->where('payment_status', 'available')->sum('commission_amount'), 2),
            'paid_commission' => round((float) (clone $base)->where('payment_status', 'paid')->sum('commission_amount'), 2),
            'pending_order_value' => round((float) (clone $base)
                ->whereIn('status', ['draft', 'approved', 'confirmed', 'packed', 'shipped'])
                ->sum('total_collectable_amount'), 2),
            'completed_order_value' => round((float) (clone $base)->where('status', 'completed')->sum('total_collectable_amount'), 2),
        ];
    }

    private function trend(Seller $seller): array
    {
        $today = Carbon::today();
        $from = (clone $today)->subDays(13)->startOfDay();
        $to = (clone $today)->endOfDay();

        $rows = Order::query()
            ->selectRaw('DATE(order_datetime) as order_date')
            ->selectRaw('COUNT(*) as orders_count')
            ->selectRaw("SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_count")
            ->selectRaw('COALESCE(SUM(total_collectable_amount), 0) as order_value')
            ->where('seller_id', $seller->id)
            ->whereBetween('order_datetime', [$from, $to])
            ->groupBy(DB::raw('DATE(order_datetime)'))
            ->orderBy('order_date')
            ->get()
            ->keyBy('order_date');

        return collect(range(0, 13))->map(function (int $index) use ($today, $rows) {
            $date = (clone $today)->subDays(13 - $index)->toDateString();
            $row = $rows->get($date);

            return [
                'date' => $date,
                'orders_count' => (int) ($row->orders_count ?? 0),
                'completed_count' => (int) ($row->completed_count ?? 0),
                'order_value' => round((float) ($row->order_value ?? 0), 2),
            ];
        })->values()->all();
    }

    private function recentOrders(Seller $seller): array
    {
        return Order::query()
            ->where('seller_id', $seller->id)
            ->latest('order_datetime')
            ->latest('id')
            ->limit(6)
            ->get(['id', 'order_datetime', 'customer_name', 'status', 'delivery_status', 'payment_status', 'total_collectable_amount'])
            ->map(fn (Order $order) => [
                'id' => (int) $order->id,
                'order_datetime' => $order->order_datetime?->toDateTimeString(),
                'customer_name' => $order->customer_name,
                'status' => $order->status,
                'delivery_status' => $order->delivery_status,
                'payment_status' => $order->payment_status,
                'total_collectable_amount' => round((float) $order->total_collectable_amount, 2),
            ])
            ->values()
            ->all();
    }
}
