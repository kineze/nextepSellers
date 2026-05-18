<?php

namespace App\Http\Controllers;

use App\Models\PenaltyType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PenaltyTypeController extends Controller
{
    public function indexView()
    {
        return view('dashboards.admin.settings.penaltyTypes');
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $perPage = (int) $request->query('per_page', 10);
        $perPage = max(1, min($perPage, 100));

        $query = PenaltyType::query()
            ->orderByDesc('is_active')
            ->latest();

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('penalty', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%')
                    ->orWhere('effective_areas', 'like', '%' . $search . '%');
            });
        }

        return response()->json($query->paginate($perPage));
    }

    public function store(Request $request)
    {
        $validated = $this->validatePenaltyType($request);

        $penaltyType = PenaltyType::create([
            'penalty' => $validated['penalty'],
            'description' => $validated['description'] ?? null,
            'effective_areas' => $validated['effective_areas'],
            'rules' => $this->formatRules($validated),
            'effective_percentage' => $validated['effective_percentage'],
            'is_active' => (bool) ($validated['is_active'] ?? true),
        ]);

        return response()->json([
            'message' => 'Penalty type created successfully.',
            'data' => $penaltyType,
        ], 201);
    }

    public function update(Request $request, PenaltyType $penaltyType)
    {
        $validated = $this->validatePenaltyType($request);

        $penaltyType->update([
            'penalty' => $validated['penalty'],
            'description' => $validated['description'] ?? null,
            'effective_areas' => $validated['effective_areas'],
            'rules' => $this->formatRules($validated),
            'effective_percentage' => $validated['effective_percentage'],
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ]);

        return response()->json([
            'message' => 'Penalty type updated successfully.',
            'data' => $penaltyType->fresh(),
        ]);
    }

    public function destroy(PenaltyType $penaltyType)
    {
        $penaltyType->delete();

        return response()->json([
            'message' => 'Penalty type deleted successfully.',
        ]);
    }

    private function validatePenaltyType(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'penalty' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'effective_areas' => ['required', 'array', 'min:1'],
            'effective_areas.*' => ['required', 'string', 'in:orders,return_charges,account'],
            'rules' => ['nullable', 'array'],
            'rules.orders' => ['nullable', 'array'],
            'rules.orders.daily_order_limit' => ['nullable', 'integer', 'min:0'],
            'rules.return_charges' => ['nullable', 'array'],
            'rules.return_charges.charge_type' => ['nullable', 'string', 'in:fixed,percentage'],
            'rules.return_charges.charge_amount' => ['nullable', 'numeric', 'min:0'],
            'rules.account' => ['nullable', 'array'],
            'rules.account.block_withdrawals' => ['nullable', 'boolean'],
            'rules.account.block_order_placing' => ['nullable', 'boolean'],
            'effective_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validator->after(function ($validator) use ($request) {
            $areas = $request->input('effective_areas', []);

            if (in_array('orders', $areas, true) && $request->input('rules.orders.daily_order_limit') === null) {
                $validator->errors()->add('rules.orders.daily_order_limit', 'The daily order limit is required when Orders is selected.');
            }

            if (in_array('return_charges', $areas, true)) {
                if (! $request->input('rules.return_charges.charge_type')) {
                    $validator->errors()->add('rules.return_charges.charge_type', 'The charge type is required when Return Charges is selected.');
                }

                if ($request->input('rules.return_charges.charge_amount') === null) {
                    $validator->errors()->add('rules.return_charges.charge_amount', 'The charge amount is required when Return Charges is selected.');
                }

                if ($request->input('rules.return_charges.charge_type') === 'percentage'
                    && (float) $request->input('rules.return_charges.charge_amount', 0) > 100) {
                    $validator->errors()->add('rules.return_charges.charge_amount', 'The charge percentage may not be greater than 100.');
                }
            }

            if (in_array('account', $areas, true)
                && ! $request->boolean('rules.account.block_withdrawals')
                && ! $request->boolean('rules.account.block_order_placing')) {
                $validator->errors()->add('rules.account', 'Select at least one account restriction.');
            }
        });

        return $validator->validate();
    }

    private function formatRules(array $validated): array
    {
        $areas = $validated['effective_areas'];
        $rules = $validated['rules'] ?? [];
        $payload = [];

        if (in_array('orders', $areas, true)) {
            $payload['orders'] = [
                'daily_order_limit' => (int) data_get($rules, 'orders.daily_order_limit', 0),
            ];
        }

        if (in_array('return_charges', $areas, true)) {
            $payload['return_charges'] = [
                'charge_type' => data_get($rules, 'return_charges.charge_type', 'fixed'),
                'charge_amount' => (float) data_get($rules, 'return_charges.charge_amount', 0),
            ];
        }

        if (in_array('account', $areas, true)) {
            $payload['account'] = [
                'block_withdrawals' => (bool) data_get($rules, 'account.block_withdrawals', false),
                'block_order_placing' => (bool) data_get($rules, 'account.block_order_placing', false),
            ];
        }

        return $payload;
    }
}
