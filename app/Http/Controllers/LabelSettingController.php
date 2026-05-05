<?php

namespace App\Http\Controllers;

use App\Models\LabelSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LabelSettingController extends Controller
{
    public function indexView()
    {
        return view('dashboards.admin.settings.labelSettings');
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $perPage = (int) $request->query('per_page', 10);
        $perPage = max(1, min($perPage, 100));

        $query = LabelSetting::query()
            ->orderByDesc('is_active')
            ->latest();

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%');
            });
        }

        return response()->json($query->paginate($perPage));
    }

    public function active()
    {
        return response()->json([
            'data' => LabelSetting::query()
                ->where('is_active', true)
                ->latest()
                ->first(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $labelSetting = DB::transaction(function () use ($validated) {
            $isActive = (bool) ($validated['is_active'] ?? false);

            if ($isActive) {
                LabelSetting::query()->update(['is_active' => false]);
            }

            return LabelSetting::create([
                'name' => $validated['name'],
                'phone' => $validated['phone'],
                'is_active' => $isActive,
            ]);
        });

        return response()->json([
            'message' => 'Label setting created successfully.',
            'data' => $labelSetting,
        ], 201);
    }

    public function update(Request $request, LabelSetting $labelSetting)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $labelSetting = DB::transaction(function () use ($validated, $labelSetting) {
            $isActive = (bool) ($validated['is_active'] ?? false);

            if ($isActive) {
                LabelSetting::query()
                    ->whereKeyNot($labelSetting->id)
                    ->update(['is_active' => false]);
            }

            $labelSetting->update([
                'name' => $validated['name'],
                'phone' => $validated['phone'],
                'is_active' => $isActive,
            ]);

            return $labelSetting->fresh();
        });

        return response()->json([
            'message' => 'Label setting updated successfully.',
            'data' => $labelSetting,
        ]);
    }

    public function toggleActive(LabelSetting $labelSetting)
    {
        $labelSetting = DB::transaction(function () use ($labelSetting) {
            LabelSetting::query()
                ->whereKeyNot($labelSetting->id)
                ->update(['is_active' => false]);

            $labelSetting->update(['is_active' => ! $labelSetting->is_active]);

            return $labelSetting->fresh();
        });

        return response()->json([
            'message' => $labelSetting->is_active
                ? 'Label setting activated successfully.'
                : 'Label setting deactivated successfully.',
            'data' => $labelSetting,
        ]);
    }

    public function destroy(LabelSetting $labelSetting)
    {
        $labelSetting->delete();

        return response()->json([
            'message' => 'Label setting deleted successfully.',
        ]);
    }
}
