<?php

namespace App\Http\Controllers;

use App\Models\Attribute as ProductAttribute;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AttributeController extends Controller
{
    public function indexView()
    {
        return view('dashboards.admin.settings.attributes');
    }

    public function index(Request $request)
    {
        $search = $request->get('search');
        $perPage = (int) $request->get('per_page', 10);
        $perPage = max(1, min($perPage, 100));

        $query = ProductAttribute::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%");
            });
        }

        $attributes = $query->latest()->paginate($perPage);

        return response()->json([
            'attributes' => $attributes->items(),
            'pagination' => [
                'current_page' => $attributes->currentPage(),
                'last_page' => $attributes->lastPage(),
                'per_page' => $attributes->perPage(),
                'total' => $attributes->total(),
                'from' => $attributes->firstItem(),
                'to' => $attributes->lastItem(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('attributes', 'slug')],
            'type' => ['required', Rule::in(['string', 'color'])],
            'values' => ['required', 'array', 'min:1'],
        ]);

        $type = $validated['type'];
        $normalizedValues = $this->normalizeValues($type, $validated['values']);
        $slug = $this->buildUniqueSlug($validated['slug'] ?? null, $validated['name']);

        ProductAttribute::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'type' => $type,
            'values' => $normalizedValues,
        ]);

        return response()->json(['message' => 'Attribute created successfully.']);
    }

    public function update(Request $request, ProductAttribute $attribute)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('attributes', 'slug')->ignore($attribute->id)],
            'type' => ['required', Rule::in(['string', 'color'])],
            'values' => ['required', 'array', 'min:1'],
        ]);

        $type = $validated['type'];
        $normalizedValues = $this->normalizeValues($type, $validated['values']);
        $slug = $this->buildUniqueSlug($validated['slug'] ?? null, $validated['name'], $attribute->id);

        $attribute->update([
            'name' => $validated['name'],
            'slug' => $slug,
            'type' => $type,
            'values' => $normalizedValues,
        ]);

        return response()->json(['message' => 'Attribute updated successfully.']);
    }

    public function destroy(ProductAttribute $attribute)
    {
        $attribute->delete();

        return response()->json(['message' => 'Attribute deleted successfully.']);
    }

    private function normalizeValues(string $type, array $values): array
    {
        if ($type === 'string') {
            $normalized = collect($values)
                ->map(fn ($value) => is_string($value) ? trim($value) : '')
                ->filter()
                ->values()
                ->all();

            if (empty($normalized)) {
                throw ValidationException::withMessages([
                    'values' => 'At least one valid string value is required.',
                ]);
            }

            return $normalized;
        }

        $normalized = [];
        foreach ($values as $index => $value) {
            $name = is_array($value) ? trim((string) ($value['name'] ?? '')) : '';
            $hex = is_array($value) ? strtoupper(trim((string) ($value['color'] ?? ''))) : '';

            if ($name === '' || !preg_match('/^#[0-9A-F]{6}$/', $hex)) {
                throw ValidationException::withMessages([
                    "values.{$index}" => 'Each color value needs a name and a valid hex color (e.g. #FFAA00).',
                ]);
            }

            $normalized[] = [
                'name' => $name,
                'color' => $hex,
            ];
        }

        if (empty($normalized)) {
            throw ValidationException::withMessages([
                'values' => 'At least one valid color value is required.',
            ]);
        }

        return $normalized;
    }

    private function buildUniqueSlug(?string $requestedSlug, string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($requestedSlug ?: $name);
        if ($base === '') {
            $base = 'attribute';
        }

        $slug = $base;
        $counter = 2;

        while ($this->slugExists($slug, $ignoreId)) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        return ProductAttribute::query()
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists();
    }
}
