<?php

namespace App\Http\Controllers;

use App\Models\PenaltyType;
use App\Models\SalesTarget;
use App\Models\SystemData;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SalesTargetController extends Controller
{
    public function indexView()
    {
        return view('dashboards.admin.settings.salesTargets');
    }

    public function index(Request $request)
    {
        $year = (int) $request->query('year', now()->year);
        $year = max(2000, min($year, 2100));

        $systemData = SystemData::query()->with('quarters')->latest()->first();
        $periods = $this->periods($year, (int) ($systemData?->year_start_month ?: 1), $systemData);
        $targets = SalesTarget::query()
            ->with('penaltyTypes:id,penalty,is_active')
            ->where('year', $year)
            ->get()
            ->keyBy(fn (SalesTarget $target) => $this->targetKey($target->frequency, (int) $target->period_number));

        return response()->json([
            'data' => [
                'year' => $year,
                'year_start_month' => (int) ($systemData?->year_start_month ?: 1),
                'periods' => $periods,
                'targets' => collect($periods)->mapWithKeys(function (array $frequencyPeriods, string $frequency) use ($targets) {
                    return [
                        $frequency => collect($frequencyPeriods)->map(function (array $period) use ($frequency, $targets) {
                            $target = $targets->get($this->targetKey($frequency, (int) $period['period_number']));

                            return $this->targetPayload($period, $frequency, $target);
                        })->values()->all(),
                    ];
                })->all(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateTargets($request);
        $year = (int) $validated['year'];
        $systemData = SystemData::query()->with('quarters')->latest()->first();
        $periods = $this->flatPeriods($this->periods($year, (int) ($systemData?->year_start_month ?: 1), $systemData));
        $records = [];

        foreach ($validated['targets'] as $targetInput) {
            $key = $this->targetKey($targetInput['frequency'], (int) $targetInput['period_number']);
            $period = $periods[$key] ?? null;

            if (! $period) {
                continue;
            }

            $target = SalesTarget::query()->firstOrCreate(
                [
                    'year' => $year,
                    'frequency' => $targetInput['frequency'],
                    'period_number' => (int) $targetInput['period_number'],
                ],
                [
                    'target_type' => $targetInput['target_type'],
                    'period_label' => $period['period_label'],
                    'target_value' => $targetInput['target_value'] ?? 0,
                ]
            );

            $target->update([
                'target_type' => $targetInput['target_type'],
                'period_label' => $period['period_label'],
                'target_value' => $targetInput['target_value'] ?? 0,
            ]);

            $target->penaltyTypes()->sync($targetInput['penalty_type_ids'] ?? []);
            $records[] = $target->fresh('penaltyTypes:id,penalty,is_active');
        }

        return response()->json([
            'message' => 'Sales targets saved successfully.',
            'data' => $records,
        ]);
    }

    public function penaltyOptions(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $query = PenaltyType::query()
            ->select(['id', 'penalty', 'is_active'])
            ->where('trigger_type', 'sales_target')
            ->orderByDesc('is_active')
            ->orderBy('penalty');

        if ($search !== '') {
            $query->where('penalty', 'like', '%' . $search . '%');
        }

        return response()->json([
            'data' => $query->limit(30)->get(),
        ]);
    }

    private function validateTargets(Request $request): array
    {
        return $request->validate([
            'year' => ['required', 'integer', 'between:2000,2100'],
            'targets' => ['required', 'array', 'min:1'],
            'targets.*.frequency' => ['required', Rule::in(['monthly', 'quarterly', 'yearly'])],
            'targets.*.target_type' => ['required', Rule::in(['qty', 'sales_amount'])],
            'targets.*.period_number' => ['required', 'integer', 'between:1,12'],
            'targets.*.target_value' => ['required', 'numeric', 'min:0'],
            'targets.*.penalty_type_ids' => ['nullable', 'array'],
            'targets.*.penalty_type_ids.*' => ['integer', 'exists:penalty_types,id'],
        ]);
    }

    private function targetPayload(array $period, string $frequency, ?SalesTarget $target): array
    {
        return [
            'id' => $target?->id,
            'frequency' => $frequency,
            'target_type' => $target?->target_type ?? 'qty',
            'period_number' => (int) $period['period_number'],
            'period_label' => $period['period_label'],
            'target_value' => $target ? (string) $target->target_value : '0.00',
            'penalty_type_ids' => $target?->penaltyTypes->pluck('id')->map(fn ($id) => (int) $id)->values()->all() ?? [],
            'penalty_types' => $target?->penaltyTypes->map(fn (PenaltyType $penaltyType) => [
                'id' => (int) $penaltyType->id,
                'penalty' => $penaltyType->penalty,
                'is_active' => (bool) $penaltyType->is_active,
            ])->values()->all() ?? [],
            'is_created' => (bool) $target,
        ];
    }

    private function periods(int $year, int $yearStartMonth, ?SystemData $systemData): array
    {
        $months = collect(range(0, 11))->map(function (int $index) use ($year, $yearStartMonth) {
            $month = $this->addMonths($yearStartMonth, $index);
            $calendarYear = $month < $yearStartMonth ? $year + 1 : $year;

            return [
                'period_number' => $index + 1,
                'period_label' => $this->monthNames()[$month] . ' ' . $calendarYear,
            ];
        })->all();

        $quarters = $systemData?->quarters?->sortBy('quarter_number')->values()->map(function ($quarter) use ($year, $yearStartMonth) {
            $startYear = (int) $quarter->start_month < $yearStartMonth ? $year + 1 : $year;
            $endYear = (int) $quarter->end_month < $yearStartMonth ? $year + 1 : $year;

            return [
                'period_number' => (int) $quarter->quarter_number,
                'period_label' => $quarter->name . ' (' . $quarter->start_label . ' ' . $startYear . ' - ' . $quarter->end_label . ' ' . $endYear . ')',
            ];
        })->all();

        if (empty($quarters)) {
            $quarters = collect(range(0, 3))->map(function (int $index) use ($year, $yearStartMonth) {
                $startMonth = $this->addMonths($yearStartMonth, $index * 3);
                $endMonth = $this->addMonths($startMonth, 2);
                $startYear = $startMonth < $yearStartMonth ? $year + 1 : $year;
                $endYear = $endMonth < $yearStartMonth ? $year + 1 : $year;

                return [
                    'period_number' => $index + 1,
                    'period_label' => 'Q' . ($index + 1) . ' (' . $this->monthNames()[$startMonth] . ' 1 ' . $startYear . ' - ' . $this->monthNames()[$endMonth] . ' ' . $this->monthEndDays()[$endMonth] . ' ' . $endYear . ')',
                ];
            })->all();
        }

        return [
            'monthly' => $months,
            'quarterly' => $quarters,
            'yearly' => [
                [
                    'period_number' => 1,
                    'period_label' => 'Fiscal Year ' . $year,
                ],
            ],
        ];
    }

    private function flatPeriods(array $periods): array
    {
        $flat = [];

        foreach ($periods as $frequency => $frequencyPeriods) {
            foreach ($frequencyPeriods as $period) {
                $flat[$this->targetKey($frequency, (int) $period['period_number'])] = $period;
            }
        }

        return $flat;
    }

    private function targetKey(string $frequency, int $periodNumber): string
    {
        return $frequency . ':' . $periodNumber;
    }

    private function addMonths(int $month, int $amount): int
    {
        return (($month + $amount - 1) % 12) + 1;
    }

    private function monthNames(): array
    {
        return [
            1 => 'January',
            2 => 'February',
            3 => 'March',
            4 => 'April',
            5 => 'May',
            6 => 'June',
            7 => 'July',
            8 => 'August',
            9 => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December',
        ];
    }

    private function monthEndDays(): array
    {
        return [
            1 => '31',
            2 => '28/29',
            3 => '31',
            4 => '30',
            5 => '31',
            6 => '30',
            7 => '31',
            8 => '31',
            9 => '30',
            10 => '31',
            11 => '30',
            12 => '31',
        ];
    }
}
