<?php

namespace App\Http\Controllers;

use App\Models\SellerSalesTarget;
use App\Services\SalesTargetAssessmentService;
use Illuminate\Http\Request;

class SellerSalesTargetController extends Controller
{
    public function index(Request $request, SalesTargetAssessmentService $service)
    {
        $seller = $request->user()?->seller;

        if (! $seller) {
            return response()->json([
                'message' => 'Seller profile not found.',
            ], 422);
        }

        $service->ensureSellerHistory($seller);

        $year = $request->query('year');

        $query = SellerSalesTarget::query()
            ->with(['penalties.penaltyType:id,penalty,description,effective_areas,rules'])
            ->where('seller_id', $seller->id)
            ->orderByDesc('period_start_date');

        if ($year) {
            $query->where('year', (int) $year);
        }

        $items = $query->get()->map(fn (SellerSalesTarget $target) => [
            'id' => (int) $target->id,
            'year' => (int) $target->year,
            'frequency' => $target->frequency,
            'target_type' => $target->target_type,
            'period_label' => $target->period_label,
            'period_start_date' => $target->period_start_date?->toDateString(),
            'period_end_date' => $target->period_end_date?->toDateString(),
            'target_value' => (float) $target->target_value,
            'actual_value' => (float) $target->actual_value,
            'achievement_percentage' => (float) $target->achievement_percentage,
            'is_achieved' => (bool) $target->is_achieved,
            'penalty_applied' => (bool) $target->penalty_applied,
            'assessed_at' => $target->assessed_at?->toDateTimeString(),
            'penalties' => $target->penalties->map(fn ($penalty) => [
                'id' => (int) $penalty->id,
                'penalty' => $penalty->penaltyType?->penalty,
                'description' => $penalty->penaltyType?->description,
                'applied_at' => $penalty->applied_at?->toDateTimeString(),
                'is_active' => (bool) $penalty->is_active,
            ])->values()->all(),
        ])->values();

        return response()->json([
            'data' => $items,
            'summary' => [
                'total' => $items->count(),
                'achieved' => $items->where('is_achieved', true)->count(),
                'missed' => $items->where('is_achieved', false)->count(),
                'penalties' => $items->where('penalty_applied', true)->count(),
            ],
        ]);
    }
}
