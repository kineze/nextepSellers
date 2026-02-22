<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CategoryController extends Controller
{
    public function indexView()
    {
        return view('dashboards.admin.settings.categories');
    }

    public function tree()
    {
        $tree = Category::query()
            ->whereNull('parent_id')
            ->with('childrenRecursive')
            ->orderBy('name')
            ->get();

        return response()->json($tree);
    }

    public function options()
    {
        $categories = Category::query()
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'parent_id', 'is_active']);

        return response()->json($categories);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'parent_id' => ['nullable', 'integer', 'exists:categories,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $slug = $this->buildUniqueSlug(
            $validated['slug'] ?? null,
            $validated['name']
        );

        Category::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'parent_id' => $validated['parent_id'] ?? null,
            'is_active' => array_key_exists('is_active', $validated)
                ? (bool) $validated['is_active']
                : true,
        ]);

        return response()->json(['message' => 'Category created successfully.']);
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'parent_id' => ['nullable', 'integer', 'exists:categories,id', Rule::notIn([$category->id])],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if (!empty($validated['parent_id'])) {
            $descendantIds = $this->descendantIds($category->id);
            if (in_array((int) $validated['parent_id'], $descendantIds, true)) {
                throw ValidationException::withMessages([
                    'parent_id' => 'A category cannot be moved under its own child.',
                ]);
            }
        }

        $slug = $this->buildUniqueSlug(
            $validated['slug'] ?? null,
            $validated['name'],
            $category->id
        );

        $category->update([
            'name' => $validated['name'],
            'slug' => $slug,
            'parent_id' => $validated['parent_id'] ?? null,
            'is_active' => array_key_exists('is_active', $validated)
                ? (bool) $validated['is_active']
                : $category->is_active,
        ]);

        return response()->json(['message' => 'Category updated successfully.']);
    }

    public function destroy(Category $category)
    {
        DB::transaction(function () use ($category) {
            Category::where('parent_id', $category->id)->update(['parent_id' => null]);
            $category->delete();
        });

        return response()->json(['message' => 'Category deleted successfully.']);
    }

    private function descendantIds(int $categoryId): array
    {
        $descendants = [];
        $queue = [$categoryId];

        while (!empty($queue)) {
            $children = Category::query()
                ->whereIn('parent_id', $queue)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

            $queue = [];
            foreach ($children as $childId) {
                if (in_array($childId, $descendants, true)) {
                    continue;
                }
                $descendants[] = $childId;
                $queue[] = $childId;
            }
        }

        return $descendants;
    }

    private function buildUniqueSlug(?string $requestedSlug, string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($requestedSlug ?: $name);
        if ($base === '') {
            $base = 'category';
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
        return Category::query()
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists();
    }
}
