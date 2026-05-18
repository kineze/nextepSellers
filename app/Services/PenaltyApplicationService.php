<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PenaltyType;
use App\Models\Seller;
use App\Models\SellerPenalty;
use Illuminate\Support\Facades\DB;

class PenaltyApplicationService
{
    public function applyForCancelledOrder(Seller $seller, Order $order): int
    {
        $score = (int) ($seller->fresh()?->dilivery_score ?? $seller->dilivery_score ?? 100);

        $penaltyTypes = PenaltyType::query()
            ->where('is_active', true)
            ->where('effective_percentage', '>=', $score)
            ->get();

        if ($penaltyTypes->isEmpty()) {
            return 0;
        }

        return DB::transaction(function () use ($seller, $order, $score, $penaltyTypes) {
            $appliedCount = 0;
            $shouldRestrictSeller = false;

            foreach ($penaltyTypes as $penaltyType) {
                $areas = $penaltyType->effective_areas ?? [];
                $rules = $penaltyType->rules ?? [];

                $sellerPenalty = SellerPenalty::query()->firstOrCreate(
                    [
                        'seller_id' => (int) $seller->id,
                        'penalty_type_id' => (int) $penaltyType->id,
                        'order_id' => (int) $order->id,
                    ],
                    [
                        'delivery_score' => $score,
                        'effective_areas' => $areas,
                        'rules' => $rules,
                        'charge_amount' => $this->calculateChargeAmount($penaltyType, $order),
                        'is_active' => true,
                        'applied_at' => now('Asia/Colombo'),
                    ]
                );

                if ($sellerPenalty->wasRecentlyCreated) {
                    $appliedCount++;
                }

                $accountRules = data_get($rules, 'account', []);
                if (in_array('account', $areas, true)
                    && ((bool) data_get($accountRules, 'block_withdrawals', false)
                        || (bool) data_get($accountRules, 'block_order_placing', false))) {
                    $shouldRestrictSeller = true;
                }
            }

            if ($shouldRestrictSeller) {
                $seller->forceFill(['is_restrict' => true])->saveQuietly();
            }

            return $appliedCount;
        });
    }

    public function sellerHasRestriction(Seller $seller, string $restriction): bool
    {
        if (! $seller->is_restrict) {
            return false;
        }

        return SellerPenalty::query()
            ->where('seller_id', $seller->id)
            ->where('is_active', true)
            ->whereJsonContains('effective_areas', 'account')
            ->get()
            ->contains(fn (SellerPenalty $penalty) => (bool) data_get($penalty->rules, 'account.' . $restriction, false));
    }

    public function sellerDailyOrderLimit(Seller $seller): ?int
    {
        $limits = SellerPenalty::query()
            ->where('seller_id', $seller->id)
            ->where('is_active', true)
            ->whereJsonContains('effective_areas', 'orders')
            ->get()
            ->map(fn (SellerPenalty $penalty) => data_get($penalty->rules, 'orders.daily_order_limit'))
            ->filter(fn ($limit) => $limit !== null && (int) $limit >= 0)
            ->map(fn ($limit) => (int) $limit);

        return $limits->isEmpty() ? null : $limits->min();
    }

    public function sellerCanPlaceOrders(Seller $seller, int $newOrderCount = 1): array
    {
        if ($this->sellerHasRestriction($seller, 'block_order_placing')) {
            return [
                'allowed' => false,
                'message' => 'Your account is restricted from placing orders. Please contact support.',
            ];
        }

        $dailyLimit = $this->sellerDailyOrderLimit($seller);
        if ($dailyLimit === null) {
            return [
                'allowed' => true,
                'message' => null,
            ];
        }

        $todayOrderCount = Order::query()
            ->where('seller_id', $seller->id)
            ->whereDate('created_at', now('Asia/Colombo')->toDateString())
            ->count();

        if (($todayOrderCount + $newOrderCount) > $dailyLimit) {
            return [
                'allowed' => false,
                'message' => "Your daily order limit is {$dailyLimit}. You have already placed {$todayOrderCount} order(s) today.",
            ];
        }

        return [
            'allowed' => true,
            'message' => null,
        ];
    }

    private function calculateChargeAmount(PenaltyType $penaltyType, Order $order): float
    {
        $rules = $penaltyType->rules ?? [];

        if (! in_array('return_charges', $penaltyType->effective_areas ?? [], true)) {
            return 0.0;
        }

        $chargeType = (string) data_get($rules, 'return_charges.charge_type', 'fixed');
        $chargeAmount = (float) data_get($rules, 'return_charges.charge_amount', 0);

        if ($chargeType === 'percentage') {
            $baseAmount = (float) ($order->net_total ?: $order->total_collectable_amount ?: 0);

            return round(($baseAmount * $chargeAmount) / 100, 2);
        }

        return round($chargeAmount, 2);
    }
}
