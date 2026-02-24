<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Attribute;
use App\Models\Level;
use App\Models\Varient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function indexView()
    {
        return view('dashboards.admin.settings.products');
    }

    public function createView()
    {
        return view('dashboards.admin.settings.productCreate');
    }

    public function editView(Product $product)
    {
        return view('dashboards.admin.settings.productCreate');
    }

    public function index(Request $request)
    {
        $search = $request->get('search');
        $perPage = (int) $request->get('per_page', 10);
        $perPage = max(1, min($perPage, 100));

        $query = Product::query()
            ->with(['category:id,name'])
            ->withCount('images');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('product_code', 'like', "%{$search}%")
                    ->orWhereHas('category', function ($categoryQuery) use ($search) {
                        $categoryQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $products = $query->latest()->paginate($perPage);

        return response()->json([
            'products' => $products->items(),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
                'from' => $products->firstItem(),
                'to' => $products->lastItem(),
            ],
        ]);
    }

    public function createOptions()
    {
        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        $attributes = Attribute::query()
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'type', 'values']);

        $levels = Level::query()
            ->orderBy('level_no')
            ->get(['id', 'level_no', 'level_name']);

        return response()->json([
            'categories' => $categories,
            'attributes' => $attributes,
            'levels' => $levels,
        ]);
    }

    public function uploadImage(Request $request)
    {
        $validated = $request->validate([
            'image' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $path = $request->file('image')->store('product-images', 'public');

        return response()->json([
            'path' => $path,
        ]);
    }

    public function show(Product $product)
    {
        $product->load([
            'category:id,name',
            'images:id,product_id,path,is_primary',
            'varients:id,product_id,sku,attributes,price,stock_quantity,reorder_level,is_active',
            'productLevels.level:id,level_no,level_name',
        ]);

        return response()->json($product);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'small_description' => ['required', 'string', 'max:255'],
            'long_description' => ['nullable', 'string'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'product_code' => ['required', 'string', 'max:255', 'unique:products,product_code'],
            'is_active' => ['nullable', 'boolean'],
            'has_varients' => ['required', 'boolean'],
            'images' => ['nullable', 'array'],
            'images.*.path' => ['required_with:images', 'string', 'max:1000'],
            'images.*.is_primary' => ['nullable', 'boolean'],

            'sku' => ['required_if:has_varients,0', 'nullable', 'string', 'max:255', 'unique:varients,sku'],
            'price' => ['required_if:has_varients,0', 'nullable', 'numeric', 'min:0'],
            'reorder_level' => ['required_if:has_varients,0', 'nullable', 'integer', 'min:0'],
            'stock_quantity' => ['nullable', 'integer', 'min:0'],

            'varients' => ['nullable', 'array'],
            'varients.*.sku' => ['required_if:has_varients,1', 'string', 'max:255', 'distinct', 'unique:varients,sku'],
            'varients.*.attributes' => ['required_with:varients', 'array', 'min:1'],
            'varients.*.price' => ['required_if:has_varients,1', 'numeric', 'min:0'],
            'varients.*.reorder_level' => ['required_if:has_varients,1', 'integer', 'min:0'],
            'varients.*.stock_quantity' => ['nullable', 'integer', 'min:0'],
            'varients.*.is_active' => ['nullable', 'boolean'],

            'levels' => ['nullable', 'array'],
            'levels.*.level_id' => ['required_with:levels', 'integer', 'exists:levels,id', 'distinct'],
            'levels.*.type' => ['required_with:levels', 'string', 'in:percentage,amount'],
            'levels.*.value' => ['required_with:levels', 'numeric', 'min:0'],
        ]);

        if ((bool) $validated['has_varients'] && empty($validated['varients'])) {
            return response()->json([
                'message' => 'Please generate at least one variant when variants are enabled.',
            ], 422);
        }

        $product = DB::transaction(function () use ($validated) {
            $product = Product::create([
                'title' => $validated['title'],
                'small_description' => $validated['small_description'],
                'long_description' => $validated['long_description'] ?? null,
                'category_id' => $validated['category_id'],
                'product_code' => $validated['product_code'],
                'is_active' => array_key_exists('is_active', $validated)
                    ? (bool) $validated['is_active']
                    : true,
                'has_varients' => (bool) $validated['has_varients'],
            ]);

            $images = collect($validated['images'] ?? [])
                ->map(fn ($image) => [
                    'path' => $image['path'],
                    'is_primary' => (bool) ($image['is_primary'] ?? false),
                ])
                ->values();

            if ($images->isNotEmpty() && $images->where('is_primary', true)->isEmpty()) {
                $images[0]['is_primary'] = true;
            }

            if ($images->isNotEmpty()) {
                $product->images()->createMany($images->all());
            }

            if ((bool) $validated['has_varients']) {
                $rows = collect($validated['varients'] ?? [])->map(function ($row) {
                    return [
                        'sku' => $row['sku'],
                        'attributes' => $row['attributes'],
                        'price' => $row['price'],
                        'stock_quantity' => (int) ($row['stock_quantity'] ?? 0),
                        'reorder_level' => (int) ($row['reorder_level'] ?? 0),
                        'is_active' => array_key_exists('is_active', $row)
                            ? (bool) $row['is_active']
                            : true,
                    ];
                })->all();

                $product->varients()->createMany($rows);
            } else {
                $product->varients()->create([
                    'sku' => $validated['sku'],
                    'attributes' => [],
                    'price' => $validated['price'],
                    'stock_quantity' => (int) ($validated['stock_quantity'] ?? 0),
                    'reorder_level' => (int) ($validated['reorder_level'] ?? 0),
                    'is_active' => true,
                ]);
            }

            $levelRows = collect($validated['levels'] ?? [])->map(function ($row) {
                return [
                    'level_id' => (int) $row['level_id'],
                    'type' => $row['type'],
                    'value' => $row['value'],
                ];
            })->all();

            if (!empty($levelRows)) {
                $product->productLevels()->createMany($levelRows);
            }

            return $product;
        });

        return response()->json([
            'message' => 'Product created successfully.',
            'product_id' => $product->id,
        ], 201);
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'small_description' => ['required', 'string', 'max:255'],
            'long_description' => ['nullable', 'string'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'product_code' => ['required', 'string', 'max:255', Rule::unique('products', 'product_code')->ignore($product->id)],
            'is_active' => ['nullable', 'boolean'],
            'has_varients' => ['required', 'boolean'],
            'images' => ['nullable', 'array'],
            'images.*.path' => ['required_with:images', 'string', 'max:1000'],
            'images.*.is_primary' => ['nullable', 'boolean'],

            'sku' => ['required_if:has_varients,0', 'nullable', 'string', 'max:255'],
            'price' => ['required_if:has_varients,0', 'nullable', 'numeric', 'min:0'],
            'reorder_level' => ['required_if:has_varients,0', 'nullable', 'integer', 'min:0'],
            'stock_quantity' => ['nullable', 'integer', 'min:0'],

            'varients' => ['nullable', 'array'],
            'varients.*.sku' => ['required_if:has_varients,1', 'string', 'max:255', 'distinct'],
            'varients.*.attributes' => ['required_with:varients', 'array', 'min:1'],
            'varients.*.price' => ['required_if:has_varients,1', 'numeric', 'min:0'],
            'varients.*.reorder_level' => ['required_if:has_varients,1', 'integer', 'min:0'],
            'varients.*.stock_quantity' => ['nullable', 'integer', 'min:0'],
            'varients.*.is_active' => ['nullable', 'boolean'],

            'levels' => ['nullable', 'array'],
            'levels.*.level_id' => ['required_with:levels', 'integer', 'exists:levels,id', 'distinct'],
            'levels.*.type' => ['required_with:levels', 'string', 'in:percentage,amount'],
            'levels.*.value' => ['required_with:levels', 'numeric', 'min:0'],
        ]);

        if ((bool) $validated['has_varients'] && empty($validated['varients'])) {
            return response()->json([
                'message' => 'Please generate at least one variant when variants are enabled.',
            ], 422);
        }

        $submittedSkus = collect($validated['varients'] ?? [])
            ->pluck('sku')
            ->merge([(bool) $validated['has_varients'] ? null : ($validated['sku'] ?? null)])
            ->filter()
            ->unique()
            ->values();

        if ($submittedSkus->isNotEmpty()) {
            $conflicts = Varient::query()
                ->whereIn('sku', $submittedSkus->all())
                ->where('product_id', '!=', $product->id)
                ->exists();

            if ($conflicts) {
                return response()->json([
                    'message' => 'One or more SKUs are already used by another product.',
                ], 422);
            }
        }

        DB::transaction(function () use ($validated, $product) {
            $product->update([
                'title' => $validated['title'],
                'small_description' => $validated['small_description'],
                'long_description' => $validated['long_description'] ?? null,
                'category_id' => $validated['category_id'],
                'product_code' => $validated['product_code'],
                'is_active' => array_key_exists('is_active', $validated)
                    ? (bool) $validated['is_active']
                    : true,
                'has_varients' => (bool) $validated['has_varients'],
            ]);

            $images = collect($validated['images'] ?? [])
                ->map(fn ($image) => [
                    'path' => $image['path'],
                    'is_primary' => (bool) ($image['is_primary'] ?? false),
                ])
                ->values();

            if ($images->isNotEmpty() && $images->where('is_primary', true)->isEmpty()) {
                $images[0]['is_primary'] = true;
            }

            $product->images()->delete();
            if ($images->isNotEmpty()) {
                $product->images()->createMany($images->all());
            }

            $product->varients()->delete();
            if ((bool) $validated['has_varients']) {
                $rows = collect($validated['varients'] ?? [])->map(function ($row) {
                    return [
                        'sku' => $row['sku'],
                        'attributes' => $row['attributes'],
                        'price' => $row['price'],
                        'stock_quantity' => (int) ($row['stock_quantity'] ?? 0),
                        'reorder_level' => (int) ($row['reorder_level'] ?? 0),
                        'is_active' => array_key_exists('is_active', $row)
                            ? (bool) $row['is_active']
                            : true,
                    ];
                })->all();

                $product->varients()->createMany($rows);
            } else {
                $product->varients()->create([
                    'sku' => $validated['sku'],
                    'attributes' => [],
                    'price' => $validated['price'],
                    'stock_quantity' => (int) ($validated['stock_quantity'] ?? 0),
                    'reorder_level' => (int) ($validated['reorder_level'] ?? 0),
                    'is_active' => true,
                ]);
            }

            $product->productLevels()->delete();
            $levelRows = collect($validated['levels'] ?? [])->map(function ($row) {
                return [
                    'level_id' => (int) $row['level_id'],
                    'type' => $row['type'],
                    'value' => $row['value'],
                ];
            })->all();

            if (!empty($levelRows)) {
                $product->productLevels()->createMany($levelRows);
            }
        });

        return response()->json([
            'message' => 'Product updated successfully.',
            'product_id' => $product->id,
        ]);
    }

    public function toggleActive(Request $request, Product $product)
    {
        $validated = $request->validate([
            'is_active' => ['nullable', 'boolean'],
        ]);

        $product->is_active = array_key_exists('is_active', $validated)
            ? (bool) $validated['is_active']
            : !$product->is_active;
        $product->save();

        return response()->json([
            'message' => 'Product status updated successfully.',
            'product_id' => $product->id,
            'is_active' => (bool) $product->is_active,
        ]);
    }
}
