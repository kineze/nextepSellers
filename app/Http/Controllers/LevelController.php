<?php

namespace App\Http\Controllers;

use App\Models\Level;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class LevelController extends Controller
{
    public function indexView()
    {
        return view('dashboards.admin.settings.levels');
    }

    public function index(Request $request)
    {
        $search = $request->get('search');
        $perPage = (int) $request->get('per_page', 10);
        $perPage = max(1, min($perPage, 100));

        $query = Level::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('level_no', 'like', "%{$search}%")
                    ->orWhere('level_name', 'like', "%{$search}%")
                    ->orWhere('points', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $levels = $query->orderBy('level_no')->paginate($perPage);

        return response()->json([
            'levels' => $levels->items(),
            'pagination' => [
                'current_page' => $levels->currentPage(),
                'last_page' => $levels->lastPage(),
                'per_page' => $levels->perPage(),
                'total' => $levels->total(),
                'from' => $levels->firstItem(),
                'to' => $levels->lastItem(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'level_no' => ['required', 'integer', 'min:1', 'unique:levels,level_no'],
            'level_name' => ['required', 'string', 'max:255'],
            'points' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
        ]);

        if ($request->hasFile('icon')) {
            $validated['icon_path'] = $request->file('icon')->store('level-icons', 'public');
        }

        unset($validated['icon']);

        Level::create($validated);

        return response()->json(['message' => 'Level created successfully.']);
    }

    public function update(Request $request, Level $level)
    {
        $validated = $request->validate([
            'level_no' => ['required', 'integer', 'min:1', 'unique:levels,level_no,' . $level->id],
            'level_name' => ['required', 'string', 'max:255'],
            'points' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
        ]);

        if ($request->hasFile('icon')) {
            if ($level->icon_path) {
                Storage::disk('public')->delete($level->icon_path);
            }

            $validated['icon_path'] = $request->file('icon')->store('level-icons', 'public');
        }

        unset($validated['icon']);

        $level->update($validated);

        return response()->json(['message' => 'Level updated successfully.']);
    }

    public function destroy(Level $level)
    {
        if ($level->icon_path) {
            Storage::disk('public')->delete($level->icon_path);
        }

        $level->delete();

        return response()->json(['message' => 'Level deleted successfully.']);
    }

    public function toggleDefault(Request $request, Level $level)
    {
        $validated = $request->validate([
            'is_default' => ['required', 'boolean'],
        ]);

        DB::transaction(function () use ($level, $validated) {
            if ($validated['is_default']) {
                Level::where('id', '!=', $level->id)->update(['is_default' => false]);
            }

            $level->update(['is_default' => (bool) $validated['is_default']]);
        });

        return response()->json([
            'message' => $validated['is_default']
                ? 'Default level updated successfully.'
                : 'Default level removed successfully.',
        ]);
    }
}
