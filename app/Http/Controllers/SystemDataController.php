<?php

namespace App\Http\Controllers;

use App\Models\SystemData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SystemDataController extends Controller
{
    public function indexView()
    {
        return view('dashboards.admin.settings.systemData');
    }

    public function index(Request $request)
    {
        $systemData = SystemData::query()->with('quarters')->latest()->first();

        return response()->json([
            'data' => $systemData ? $this->systemDataPayload($systemData) : null,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateSystemData($request);

        $payload = $this->formatSystemData($validated);
        $systemData = SystemData::query()->latest()->first();

        if ($request->hasFile('logo')) {
            if ($systemData?->logo) {
                Storage::disk('public')->delete($systemData->logo);
            }
            $payload['logo'] = $request->file('logo')->store('system-data', 'public');
        }

        if ($systemData) {
            $systemData->update($payload);
        } else {
            $systemData = SystemData::create($payload);
        }

        $this->syncSystemQuarters($systemData, $payload['year_start_month']);

        return response()->json([
            'message' => 'System data saved successfully.',
            'data' => $this->systemDataPayload($systemData->fresh('quarters')),
        ], $systemData->wasRecentlyCreated ? 201 : 200);
    }

    public function update(Request $request, SystemData $systemData)
    {
        $validated = $this->validateSystemData($request);

        $payload = $this->formatSystemData($validated);
        if ($request->hasFile('logo')) {
            if ($systemData->logo) {
                Storage::disk('public')->delete($systemData->logo);
            }
            $payload['logo'] = $request->file('logo')->store('system-data', 'public');
        }

        $systemData->update($payload);
        $this->syncSystemQuarters($systemData, $payload['year_start_month']);

        return response()->json([
            'message' => 'System data updated successfully.',
            'data' => $this->systemDataPayload($systemData->fresh('quarters')),
        ]);
    }

    public function destroy(SystemData $systemData)
    {
        if ($systemData->logo) {
            Storage::disk('public')->delete($systemData->logo);
        }

        $systemData->delete();

        return response()->json([
            'message' => 'System data deleted successfully.',
        ]);
    }

    private function validateSystemData(Request $request): array
    {
        return $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'country' => ['required', 'string', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:255'],
            'fax' => ['nullable', 'string', 'max:255'],
            'year_start_month' => ['required', 'integer', 'between:1,12'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);
    }

    private function formatSystemData(array $validated): array
    {
        $yearStartMonth = (int) $validated['year_start_month'];

        return [
            'company_name' => trim($validated['company_name']),
            'address' => isset($validated['address']) ? trim((string) $validated['address']) : null,
            'country' => trim($validated['country']),
            'phone_number' => isset($validated['phone_number']) ? trim((string) $validated['phone_number']) : null,
            'fax' => isset($validated['fax']) ? trim((string) $validated['fax']) : null,
            'year_start_month' => $yearStartMonth,
            'year_end_month' => $this->yearEndMonthFor($yearStartMonth),
        ];
    }

    private function systemDataPayload(SystemData $systemData): array
    {
        return [
            'id' => (int) $systemData->id,
            'company_name' => $systemData->company_name,
            'address' => $systemData->address,
            'country' => $systemData->country,
            'phone_number' => $systemData->phone_number,
            'fax' => $systemData->fax,
            'year_start_month' => (int) ($systemData->year_start_month ?: 1),
            'year_end_month' => (int) ($systemData->year_end_month ?: 12),
            'quarters' => $systemData->quarters
                ->sortBy('quarter_number')
                ->values()
                ->map(fn ($quarter) => [
                    'id' => (int) $quarter->id,
                    'quarter_number' => (int) $quarter->quarter_number,
                    'name' => $quarter->name,
                    'start_month' => (int) $quarter->start_month,
                    'start_day' => (int) $quarter->start_day,
                    'end_month' => (int) $quarter->end_month,
                    'end_day' => (int) $quarter->end_day,
                    'start_label' => $quarter->start_label,
                    'end_label' => $quarter->end_label,
                ])
                ->all(),
            'logo' => $systemData->logo,
            'logo_url' => $systemData->logo ? Storage::disk('public')->url($systemData->logo) : null,
            'created_at' => $systemData->created_at,
            'updated_at' => $systemData->updated_at,
        ];
    }

    private function yearEndMonthFor(int $yearStartMonth): int
    {
        return $yearStartMonth === 1 ? 12 : $yearStartMonth - 1;
    }

    private function syncSystemQuarters(SystemData $systemData, int $yearStartMonth): void
    {
        foreach ($this->quarterRows($yearStartMonth) as $quarter) {
            $systemData->quarters()->updateOrCreate(
                ['quarter_number' => $quarter['quarter_number']],
                $quarter
            );
        }
    }

    private function quarterRows(int $yearStartMonth): array
    {
        return collect([0, 1, 2, 3])->map(function (int $quarterIndex) use ($yearStartMonth) {
            $startMonth = $this->addMonths($yearStartMonth, $quarterIndex * 3);
            $endMonth = $this->addMonths($startMonth, 2);

            return [
                'quarter_number' => $quarterIndex + 1,
                'name' => 'Q' . ($quarterIndex + 1),
                'start_month' => $startMonth,
                'start_day' => 1,
                'end_month' => $endMonth,
                'end_day' => $endMonth === 2 ? 29 : (int) $this->monthEndDays()[$endMonth],
                'start_label' => $this->monthNames()[$startMonth] . ' 1',
                'end_label' => $this->monthNames()[$endMonth] . ' ' . $this->monthEndDays()[$endMonth],
            ];
        })->all();
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
