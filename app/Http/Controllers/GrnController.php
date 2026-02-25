<?php

namespace App\Http\Controllers;

use App\Models\Grn;
use App\Models\Supplier;
use App\Models\Varient;
use App\Models\SupplierProductVarient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GrnController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'in:all,draft,posted,cancelled'],
            'supplier_id' => ['nullable', 'integer', 'exists:suppliers,id'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:100'],
        ]);

        $query = Grn::query()
            ->with([
                'supplier:id,name,company_name',
                'items:id,grn_id,variant_id,quantity,unit_cost,line_total,lot_id,lot_number,manufactured_at,expires_at',
                'items.variant:id,product_id,sku,attributes',
                'items.variant.product:id,title,product_code',
            ])
            ->latest('id');

        $search = trim((string) ($validated['search'] ?? ''));
        $status = $validated['status'] ?? 'all';
        $perPage = (int) ($validated['per_page'] ?? 15);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if (!empty($validated['supplier_id'])) {
            $query->where('supplier_id', (int) $validated['supplier_id']);
        }

        if (!empty($validated['date_from'])) {
            $query->whereDate('received_date', '>=', $validated['date_from']);
        }

        if (!empty($validated['date_to'])) {
            $query->whereDate('received_date', '<=', $validated['date_to']);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('grn_no', 'like', '%' . $search . '%')
                    ->orWhere('id', $search)
                    ->orWhereHas('supplier', function ($supplierQuery) use ($search) {
                        $supplierQuery
                            ->where('name', 'like', '%' . $search . '%')
                            ->orWhere('company_name', 'like', '%' . $search . '%');
                    });
            });
        }

        $grns = $query->paginate($perPage);

        return response()->json([
            'grns' => $grns->items(),
            'pagination' => [
                'current_page' => $grns->currentPage(),
                'last_page' => $grns->lastPage(),
                'per_page' => $grns->perPage(),
                'total' => $grns->total(),
                'from' => $grns->firstItem(),
                'to' => $grns->lastItem(),
            ],
        ]);
    }

    public function options(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => ['nullable', 'integer', 'exists:suppliers,id'],
            'variant_search' => ['nullable', 'string', 'max:120'],
        ]);

        $search = trim((string) ($validated['variant_search'] ?? ''));
        $supplierId = $validated['supplier_id'] ?? null;

        $suppliers = Supplier::query()
            ->select('id', 'name', 'company_name')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $variantQuery = Varient::query()
            ->with('product:id,title,product_code')
            ->select('id', 'product_id', 'sku', 'attributes', 'price', 'is_active')
            ->where('is_active', true)
            ->orderByDesc('id');

        if ($supplierId) {
            $mappedVariantIds = SupplierProductVarient::query()
                ->select('supplier_product_varients.varient_id')
                ->join('supplier_products', 'supplier_products.id', '=', 'supplier_product_varients.supplier_product_id')
                ->where('supplier_products.supplier_id', (int) $supplierId)
                ->where('supplier_products.is_active', true)
                ->where('supplier_product_varients.is_active', true)
                ->pluck('supplier_product_varients.varient_id')
                ->unique()
                ->values()
                ->all();

            if (empty($mappedVariantIds)) {
                return response()->json([
                    'suppliers' => $suppliers,
                    'variants' => [],
                ]);
            }

            $variantQuery->whereIn('id', $mappedVariantIds);
        } else {
            $variantQuery->whereRaw('1 = 0');
        }

        if ($search !== '') {
            $variantQuery->where(function ($q) use ($search) {
                $q->where('sku', 'like', '%' . $search . '%')
                    ->orWhereHas('product', function ($productQ) use ($search) {
                        $productQ
                            ->where('title', 'like', '%' . $search . '%')
                            ->orWhere('product_code', 'like', '%' . $search . '%');
                    });
            });
        }

        $variants = $variantQuery->limit(150)->get();

        return response()->json([
            'suppliers' => $suppliers,
            'variants' => $variants,
        ]);
    }

    public function show(Grn $grn)
    {
        $grn->load([
            'supplier:id,name,company_name',
            'items.lot:id,lot_number',
            'items.variant:id,product_id,sku,attributes,price',
            'items.variant.product:id,title,product_code',
        ]);

        return response()->json(['grn' => $grn]);
    }

    public function store(Request $request)
    {
        $validated = $this->validatePayload($request);

        $grn = DB::transaction(function () use ($validated) {
            $grn = Grn::create([
                'grn_no' => $this->nextGrnNo(),
                'purchase_order_id' => $validated['purchase_order_id'] ?? null,
                'supplier_id' => $validated['supplier_id'] ?? null,
                'received_date' => $validated['received_date'] ?? now()->toDateString(),
                'received_time' => $validated['received_time'] ?? now()->format('H:i:s'),
                'status' => 'draft',
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                $grn->items()->create([
                    'variant_id' => (int) $item['variant_id'],
                    'quantity' => (int) $item['quantity'],
                    'unit_cost' => (float) $item['unit_cost'],
                    'lot_number' => $item['lot_number'] ?? null,
                    'manufactured_at' => $item['manufactured_at'] ?? null,
                    'expires_at' => $item['expires_at'] ?? null,
                ]);
            }

            $grn->recomputeTotals();

            return $grn;
        });

        return response()->json([
            'message' => 'GRN created successfully.',
            'grn' => $grn->load('items.variant.product', 'supplier'),
        ], 201);
    }

    public function update(Request $request, Grn $grn)
    {
        if ($grn->status !== 'draft') {
            return response()->json([
                'message' => 'Only draft GRNs can be updated.',
            ], 422);
        }

        $validated = $this->validatePayload($request);

        DB::transaction(function () use ($grn, $validated) {
            $grn->update([
                'purchase_order_id' => $validated['purchase_order_id'] ?? null,
                'supplier_id' => $validated['supplier_id'] ?? null,
                'received_date' => $validated['received_date'] ?? now()->toDateString(),
                'received_time' => $validated['received_time'] ?? now()->format('H:i:s'),
                'notes' => $validated['notes'] ?? null,
            ]);

            $grn->items()->delete();

            foreach ($validated['items'] as $item) {
                $grn->items()->create([
                    'variant_id' => (int) $item['variant_id'],
                    'quantity' => (int) $item['quantity'],
                    'unit_cost' => (float) $item['unit_cost'],
                    'lot_number' => $item['lot_number'] ?? null,
                    'manufactured_at' => $item['manufactured_at'] ?? null,
                    'expires_at' => $item['expires_at'] ?? null,
                ]);
            }

            $grn->recomputeTotals();
        });

        return response()->json([
            'message' => 'GRN updated successfully.',
            'grn' => $grn->fresh(['items.variant.product', 'supplier']),
        ]);
    }

    public function destroy(Grn $grn)
    {
        if ($grn->status !== 'draft') {
            return response()->json([
                'message' => 'Only draft GRNs can be deleted.',
            ], 422);
        }

        $grn->delete();

        return response()->json([
            'message' => 'GRN deleted successfully.',
        ]);
    }

    public function post(Grn $grn)
    {
        if ($grn->status === 'posted') {
            return response()->json([
                'message' => 'GRN already posted.',
                'grn' => $grn,
            ]);
        }

        $grn->post();

        return response()->json([
            'message' => 'GRN posted successfully.',
            'grn' => $grn->fresh(['items.variant.product', 'supplier']),
        ]);
    }

    public function unpost(Grn $grn)
    {
        if ($grn->status !== 'posted') {
            return response()->json([
                'message' => 'Only posted GRNs can be unposted.',
            ], 422);
        }

        $grn->unpost();

        return response()->json([
            'message' => 'GRN unposted successfully.',
            'grn' => $grn->fresh(['items.variant.product', 'supplier']),
        ]);
    }

    private function validatePayload(Request $request): array
    {
        $validated = $request->validate([
            'purchase_order_id' => ['nullable', 'integer'],
            'supplier_id' => ['required', 'integer', 'exists:suppliers,id'],
            'received_date' => ['nullable', 'date'],
            'received_time' => ['nullable', 'date_format:H:i'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.variant_id' => ['required', 'integer', 'exists:varients,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
            'items.*.lot_number' => ['nullable', 'string', 'max:50'],
            'items.*.manufactured_at' => ['nullable', 'date'],
            'items.*.expires_at' => ['nullable', 'date'],
        ]);

        $supplierId = (int) $validated['supplier_id'];
        $itemVariantIds = collect($validated['items'])
            ->pluck('variant_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $allowedVariantIds = SupplierProductVarient::query()
            ->select('supplier_product_varients.varient_id')
            ->join('supplier_products', 'supplier_products.id', '=', 'supplier_product_varients.supplier_product_id')
            ->where('supplier_products.supplier_id', $supplierId)
            ->where('supplier_products.is_active', true)
            ->where('supplier_product_varients.is_active', true)
            ->pluck('supplier_product_varients.varient_id')
            ->map(fn ($id) => (int) $id)
            ->unique();

        $invalidVariants = $itemVariantIds->diff($allowedVariantIds)->values();
        if ($invalidVariants->isNotEmpty()) {
            throw ValidationException::withMessages([
                'items' => ['One or more selected variants are not supplied by the selected supplier.'],
            ]);
        }

        return $validated;
    }

    private function nextGrnNo(): string
    {
        return DB::transaction(function () {
            $lastNo = Grn::query()
                ->lockForUpdate()
                ->select('grn_no')
                ->latest('id')
                ->value('grn_no');

            $lastInt = 0;
            if (is_string($lastNo) && preg_match('/(\d+)$/', $lastNo, $m)) {
                $lastInt = (int) $m[1];
            }

            return 'GRN-' . str_pad((string) ($lastInt + 1), 6, '0', STR_PAD_LEFT);
        });
    }
}
