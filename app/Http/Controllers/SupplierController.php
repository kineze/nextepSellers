<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Supplier;
use App\Models\SupplierProduct;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function indexView()
    {
        return view('dashboards.admin.settings.suppliers');
    }

    public function index(Request $request)
    {
        $search = $request->get('search');
        $perPage = (int) $request->get('per_page', 10);
        $perPage = max(1, min($perPage, 100));

        $query = Supplier::query()->withCount('supplierProducts');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $suppliers = $query->latest()->paginate($perPage);

        return response()->json([
            'suppliers' => $suppliers->items(),
            'pagination' => [
                'current_page' => $suppliers->currentPage(),
                'last_page' => $suppliers->lastPage(),
                'per_page' => $suppliers->perPage(),
                'total' => $suppliers->total(),
                'from' => $suppliers->firstItem(),
                'to' => $suppliers->lastItem(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'website' => ['nullable', 'url', 'max:255'],
            'tax_number' => ['nullable', 'string', 'max:100'],
            'address_line_1' => ['nullable', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'postal_code' => ['nullable', 'string', 'max:40'],
            'country' => ['nullable', 'string', 'max:120'],
            'general_details' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $supplier = Supplier::create([
            ...$validated,
            'is_active' => array_key_exists('is_active', $validated) ? (bool) $validated['is_active'] : true,
        ]);

        return response()->json([
            'message' => 'Supplier created successfully.',
            'supplier' => $supplier,
        ], 201);
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'website' => ['nullable', 'url', 'max:255'],
            'tax_number' => ['nullable', 'string', 'max:100'],
            'address_line_1' => ['nullable', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'postal_code' => ['nullable', 'string', 'max:40'],
            'country' => ['nullable', 'string', 'max:120'],
            'general_details' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $supplier->update([
            ...$validated,
            'is_active' => array_key_exists('is_active', $validated) ? (bool) $validated['is_active'] : $supplier->is_active,
        ]);

        return response()->json([
            'message' => 'Supplier updated successfully.',
            'supplier' => $supplier,
        ]);
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();

        return response()->json([
            'message' => 'Supplier deleted successfully.',
        ]);
    }

    public function show(Supplier $supplier)
    {
        $supplier->load([
            'supplierProducts.product:id,title,product_code,has_varients',
            'supplierProducts.supplierVarients.varient:id,product_id,sku,attributes',
        ]);

        return response()->json($supplier);
    }

    public function productOptions(Request $request)
    {
        $search = trim((string) $request->get('search', ''));

        $query = Product::query()
            ->select(['id', 'title', 'product_code', 'has_varients'])
            ->with(['varients:id,product_id,sku,attributes'])
            ->orderBy('title');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('product_code', 'like', "%{$search}%");
            });
        }

        $products = $query->limit(25)->get();

        return response()->json([
            'products' => $products,
        ]);
    }

    public function linkProduct(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'supplier_product_code' => ['nullable', 'string', 'max:255'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'lead_days' => ['nullable', 'integer', 'min:0'],
            'moq' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],

            'varients' => ['nullable', 'array'],
            'varients.*.varient_id' => ['required_with:varients', 'integer', 'exists:varients,id'],
            'varients.*.supplier_sku' => ['nullable', 'string', 'max:255'],
            'varients.*.cost' => ['nullable', 'numeric', 'min:0'],
            'varients.*.lead_days' => ['nullable', 'integer', 'min:0'],
            'varients.*.moq' => ['nullable', 'integer', 'min:0'],
            'varients.*.notes' => ['nullable', 'string'],
            'varients.*.is_active' => ['nullable', 'boolean'],
        ]);

        $product = Product::query()->with('varients:id,product_id')->findOrFail($validated['product_id']);
        $productVarientIds = $product->varients->pluck('id')->all();
        $submittedVarients = collect($validated['varients'] ?? []);

        if ((bool) $product->has_varients && $submittedVarients->isEmpty()) {
            return response()->json([
                'message' => 'This product has variants. Please select at least one variant.',
            ], 422);
        }

        if ($submittedVarients->isNotEmpty()) {
            $invalid = $submittedVarients
                ->pluck('varient_id')
                ->filter(fn ($id) => !in_array($id, $productVarientIds, true))
                ->isNotEmpty();

            if ($invalid) {
                return response()->json([
                    'message' => 'One or more selected variants do not belong to the chosen product.',
                ], 422);
            }
        }

        $mapping = DB::transaction(function () use ($supplier, $validated, $submittedVarients, $product) {
            $supplierProduct = SupplierProduct::query()->updateOrCreate(
                [
                    'supplier_id' => $supplier->id,
                    'product_id' => $validated['product_id'],
                ],
                [
                    'supplier_product_code' => $validated['supplier_product_code'] ?? null,
                    'cost' => $validated['cost'] ?? null,
                    'lead_days' => $validated['lead_days'] ?? null,
                    'moq' => $validated['moq'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                    'is_active' => array_key_exists('is_active', $validated) ? (bool) $validated['is_active'] : true,
                ]
            );

            if ((bool) $product->has_varients) {
                $submittedIds = $submittedVarients->pluck('varient_id')->unique()->values()->all();

                $supplierProduct->supplierVarients()
                    ->whereNotIn('varient_id', $submittedIds)
                    ->delete();

                foreach ($submittedVarients as $row) {
                    $supplierProduct->supplierVarients()->updateOrCreate(
                        ['varient_id' => $row['varient_id']],
                        [
                            'supplier_sku' => $row['supplier_sku'] ?? null,
                            'cost' => $row['cost'] ?? null,
                            'lead_days' => $row['lead_days'] ?? null,
                            'moq' => $row['moq'] ?? null,
                            'notes' => $row['notes'] ?? null,
                            'is_active' => array_key_exists('is_active', $row) ? (bool) $row['is_active'] : true,
                        ]
                    );
                }
            } else {
                $supplierProduct->supplierVarients()->delete();
            }

            return $supplierProduct->load([
                'product:id,title,product_code,has_varients',
                'supplierVarients.varient:id,product_id,sku,attributes',
            ]);
        });

        return response()->json([
            'message' => 'Product linked to supplier successfully.',
            'supplier_product' => $mapping,
        ]);
    }

    public function unlinkProduct(Supplier $supplier, SupplierProduct $supplierProduct)
    {
        if ((int) $supplierProduct->supplier_id !== (int) $supplier->id) {
            return response()->json([
                'message' => 'Supplier mapping not found.',
            ], 404);
        }

        $supplierProduct->delete();

        return response()->json([
            'message' => 'Linked product removed from supplier.',
        ]);
    }
}
