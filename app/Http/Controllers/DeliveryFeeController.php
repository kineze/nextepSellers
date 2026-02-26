<?php

namespace App\Http\Controllers;

use App\Models\DeliveryFee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeliveryFeeController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $perPage = (int) $request->query('per_page', 10);
        $perPage = max(1, min($perPage, 100));

        $query = DeliveryFee::query()
            ->orderByDesc('is_default')
            ->orderBy('fee');

        if ($search !== '') {
            $query->where('fee', 'like', '%' . $search . '%');
        }

        return response()->json($query->paginate($perPage));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'fee' => ['required', 'numeric', 'min:0'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $fee = DB::transaction(function () use ($validated) {
            $isDefault = (bool) ($validated['is_default'] ?? false);

            if ($isDefault) {
                DeliveryFee::query()->update(['is_default' => false]);
            }

            return DeliveryFee::create([
                'fee' => $validated['fee'],
                'is_default' => $isDefault,
            ]);
        });

        return response()->json([
            'message' => 'Delivery fee created successfully.',
            'data' => $fee,
        ], 201);
    }

    public function update(Request $request, DeliveryFee $deliveryFee)
    {
        $validated = $request->validate([
            'fee' => ['required', 'numeric', 'min:0'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $fee = DB::transaction(function () use ($validated, $deliveryFee) {
            $isDefault = (bool) ($validated['is_default'] ?? false);

            if ($isDefault) {
                DeliveryFee::query()
                    ->whereKeyNot($deliveryFee->id)
                    ->update(['is_default' => false]);
            }

            $deliveryFee->update([
                'fee' => $validated['fee'],
                'is_default' => $isDefault,
            ]);

            return $deliveryFee->fresh();
        });

        return response()->json([
            'message' => 'Delivery fee updated successfully.',
            'data' => $fee,
        ]);
    }

    public function destroy(DeliveryFee $deliveryFee)
    {
        $deliveryFee->delete();

        return response()->json([
            'message' => 'Delivery fee deleted successfully.',
        ]);
    }
}
