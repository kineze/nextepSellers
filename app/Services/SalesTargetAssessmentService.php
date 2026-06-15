<?php

namespace App\Services;

use App\Models\Order;
use App\Models\SalesTarget;
use App\Models\Seller;
use App\Models\SellerPenalty;
use App\Models\SellerSalesTarget;
use App\Models\SystemData;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SalesTargetAssessmentService
{
    public function assessDueTargets(?Carbon $asOf = null): array
    {
        $asOf = ($asOf ?: now('Asia/Colombo'))->copy()->startOfDay();
        $systemData = SystemData::query()->with('quarters')->latest()->first();
        $yearStartMonth = (int) ($systemData?->year_start_month ?: 1);

        $targets = SalesTarget::query()
            ->with(['penaltyTypes' => fn ($query) => $query
                ->where('trigger_type', 'sales_target')
                ->where('is_active', true)])
            ->get()
            ->filter(fn (SalesTarget $target) => $this->periodWindow($target, $yearStartMonth)['end']->lt($asOf));

        $assessed = 0;
        $penalties = 0;

        Seller::query()
            ->where('status', 'approved')
            ->orderBy('id')
            ->chunkById(100, function ($sellers) use ($targets, $yearStartMonth, &$assessed, &$penalties) {
                foreach ($sellers as $seller) {
                    foreach ($targets as $target) {
                        $result = $this->assessSellerTarget($seller, $target, $yearStartMonth);
                        $assessed++;
                        $penalties += $result['penalties_applied'];
                    }
                }
            });

        return [
            'targets' => $targets->count(),
            'assessed' => $assessed,
            'penalties_applied' => $penalties,
        ];
    }

    public function ensureSellerHistory(Seller $seller, ?Carbon $asOf = null): void
    {
        $asOf = ($asOf ?: now('Asia/Colombo'))->copy()->startOfDay();
        $systemData = SystemData::query()->latest()->first();
        $yearStartMonth = (int) ($systemData?->year_start_month ?: 1);

        SalesTarget::query()
            ->with(['penaltyTypes' => fn ($query) => $query
                ->where('trigger_type', 'sales_target')
                ->where('is_active', true)])
            ->orderByDesc('year')
            ->orderByDesc('period_number')
            ->get()
            ->filter(fn (SalesTarget $target) => $this->periodWindow($target, $yearStartMonth)['end']->lt($asOf))
            ->each(fn (SalesTarget $target) => $this->assessSellerTarget($seller, $target, $yearStartMonth));
    }

    public function assessSellerTarget(Seller $seller, SalesTarget $target, int $yearStartMonth): array
    {
        return DB::transaction(function () use ($seller, $target, $yearStartMonth) {
            $window = $this->periodWindow($target, $yearStartMonth);
            $actual = $this->actualValue($seller, $target, $window['start'], $window['end']);
            $targetValue = (float) $target->target_value;
            $achievement = $targetValue > 0 ? round(($actual / $targetValue) * 100, 2) : 0.0;
            $isAchieved = $targetValue <= 0 || $actual >= $targetValue;

            $history = SellerSalesTarget::query()->updateOrCreate(
                [
                    'seller_id' => (int) $seller->id,
                    'sales_target_id' => (int) $target->id,
                ],
                [
                    'year' => (int) $target->year,
                    'frequency' => $target->frequency,
                    'target_type' => $target->target_type,
                    'period_number' => (int) $target->period_number,
                    'period_label' => $target->period_label,
                    'period_start_date' => $window['start']->toDateString(),
                    'period_end_date' => $window['end']->toDateString(),
                    'target_value' => $targetValue,
                    'actual_value' => $actual,
                    'achievement_percentage' => $achievement,
                    'is_achieved' => $isAchieved,
                    'assessed_at' => now('Asia/Colombo'),
                ]
            );

            $applied = 0;

            if (! $isAchieved && ! $history->penalty_applied) {
                foreach ($target->penaltyTypes as $penaltyType) {
                    $sellerPenalty = SellerPenalty::query()->firstOrCreate(
                        [
                            'seller_id' => (int) $seller->id,
                            'penalty_type_id' => (int) $penaltyType->id,
                            'seller_sales_target_id' => (int) $history->id,
                        ],
                        [
                            'order_id' => null,
                            'delivery_score' => (int) ($seller->dilivery_score ?? 100),
                            'effective_areas' => $penaltyType->effective_areas ?? [],
                            'rules' => $penaltyType->rules ?? [],
                            'charge_amount' => 0,
                            'is_active' => true,
                            'applied_at' => now('Asia/Colombo'),
                        ]
                    );

                    if ($sellerPenalty->wasRecentlyCreated) {
                        $applied++;
                    }
                }

                if ($target->penaltyTypes->isNotEmpty()) {
                    $history->forceFill(['penalty_applied' => true])->save();

                    if ($target->penaltyTypes->contains(fn ($penaltyType) => in_array('account', $penaltyType->effective_areas ?? [], true))) {
                        $seller->forceFill(['is_restrict' => true])->saveQuietly();
                    }
                }
            }

            return [
                'history' => $history->fresh('penalties.penaltyType'),
                'penalties_applied' => $applied,
            ];
        });
    }

    public function periodWindow(SalesTarget $target, int $yearStartMonth): array
    {
        $year = (int) $target->year;

        if ($target->frequency === 'yearly') {
            $startMonth = $yearStartMonth;
            $endMonth = $this->addMonths($startMonth, 11);

            return $this->windowForMonths($year, $yearStartMonth, $startMonth, $endMonth);
        }

        if ($target->frequency === 'quarterly') {
            $startMonth = $this->addMonths($yearStartMonth, ((int) $target->period_number - 1) * 3);
            $endMonth = $this->addMonths($startMonth, 2);

            return $this->windowForMonths($year, $yearStartMonth, $startMonth, $endMonth);
        }

        $month = $this->addMonths($yearStartMonth, (int) $target->period_number - 1);

        return $this->windowForMonths($year, $yearStartMonth, $month, $month);
    }

    private function actualValue(Seller $seller, SalesTarget $target, Carbon $start, Carbon $end): float
    {
        $query = Order::query()
            ->where('seller_id', $seller->id)
            ->where('status', 'completed')
            ->whereBetween('completed_at', [$start, $end]);

        if ($target->target_type === 'qty') {
            return (float) $query->count();
        }

        return round((float) $query->sum('total_collectable_amount'), 2);
    }

    private function windowForMonths(int $year, int $yearStartMonth, int $startMonth, int $endMonth): array
    {
        $startYear = $startMonth < $yearStartMonth ? $year + 1 : $year;
        $endYear = $endMonth < $yearStartMonth ? $year + 1 : $year;

        return [
            'start' => Carbon::create($startYear, $startMonth, 1, 0, 0, 0, 'Asia/Colombo')->startOfDay(),
            'end' => Carbon::create($endYear, $endMonth, 1, 23, 59, 59, 'Asia/Colombo')->endOfMonth(),
        ];
    }

    private function addMonths(int $month, int $amount): int
    {
        return (($month + $amount - 1) % 12) + 1;
    }
}
