<?php

namespace App\Http\Controllers;

use App\Models\Attribute;
use App\Models\Category;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Product;
use App\Models\Seller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function getAdminDashboard()
    {

        return view('dashboards.admin.dashboard');
    }

    public function getSellerSalesTargets()
    {
        return view('dashboards.seller.sales-targets');
    }

    public function adminAnalytics()
    {
        $today = Carbon::today();
        $from = (clone $today)->subDays(13)->startOfDay();
        $to = (clone $today)->endOfDay();

        $ordersBase = Order::query();
        $totals = [
            'orders_total' => (int) (clone $ordersBase)->count(),
            'completed_orders' => (int) (clone $ordersBase)->where('status', 'completed')->count(),
            'shipped_orders' => (int) (clone $ordersBase)->where('status', 'shipped')->count(),
            'cancelled_orders' => (int) (clone $ordersBase)->where('status', 'cancelled')->count(),
            'net_sales_total' => round((float) (clone $ordersBase)
                ->selectRaw('COALESCE(SUM(GREATEST(net_total - total_discount, 0)), 0) as total')
                ->value('total'), 2),
            'commission_total' => round((float) (clone $ordersBase)->sum('commission_amount'), 2),
        ];

        $payments = [
            'pending_commission' => round((float) Order::query()->where('payment_status', 'pending')->sum('commission_amount'), 2),
            'available_commission' => round((float) Order::query()->where('payment_status', 'available')->sum('commission_amount'), 2),
            'paid_commission' => round((float) Order::query()->where('payment_status', 'paid')->sum('commission_amount'), 2),
        ];

        $sellers = [
            'total' => (int) Seller::query()->count(),
            'approved' => (int) Seller::query()->where('status', 'approved')->count(),
            'pending' => (int) Seller::query()->where('status', 'pending')->count(),
            'blocked' => (int) Seller::query()->where('status', 'blocked')->count(),
            'rejected' => (int) Seller::query()->where('status', 'rejected')->count(),
        ];

        $invoices = [
            'total' => (int) Invoice::query()->count(),
            'draft' => (int) Invoice::query()->where('status', 'draft')->count(),
            'paid' => (int) Invoice::query()->where('status', 'paid')->count(),
            'cancelled' => (int) Invoice::query()->where('status', 'cancelled')->count(),
            'total_commission' => round((float) Invoice::query()->sum('total_commission_value'), 2),
        ];

        $trendRows = Order::query()
            ->selectRaw('DATE(order_datetime) as order_date')
            ->selectRaw('COUNT(*) as orders_count')
            ->selectRaw("SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_count")
            ->selectRaw('COALESCE(SUM(GREATEST(net_total - total_discount, 0)), 0) as net_sales')
            ->selectRaw('COALESCE(SUM(commission_amount), 0) as commission')
            ->whereBetween('order_datetime', [$from, $to])
            ->groupBy(DB::raw('DATE(order_datetime)'))
            ->orderBy('order_date')
            ->get()
            ->keyBy('order_date');

        $trend = collect(range(0, 13))->map(function ($index) use ($today, $trendRows) {
            $date = (clone $today)->subDays(13 - $index)->toDateString();
            $row = $trendRows->get($date);

            return [
                'date' => $date,
                'orders_count' => (int) ($row->orders_count ?? 0),
                'completed_count' => (int) ($row->completed_count ?? 0),
                'net_sales' => round((float) ($row->net_sales ?? 0), 2),
                'commission' => round((float) ($row->commission ?? 0), 2),
            ];
        })->values();

        $topSellers = Seller::query()
            ->select('id', 'first_name', 'last_name', 'email')
            ->withCount([
                'orders as completed_orders_count' => fn ($q) => $q->where('status', 'completed'),
            ])
            ->withSum('orders as total_commission_value', 'commission_amount')
            ->orderByDesc('total_commission_value')
            ->limit(8)
            ->get()
            ->map(function (Seller $seller) {
                return [
                    'id' => (int) $seller->id,
                    'first_name' => $seller->first_name,
                    'last_name' => $seller->last_name,
                    'email' => $seller->email,
                    'completed_orders_count' => (int) ($seller->completed_orders_count ?? 0),
                    'total_commission_value' => round((float) ($seller->total_commission_value ?? 0), 2),
                ];
            })
            ->values();

        return response()->json([
            'totals' => $totals,
            'payments' => $payments,
            'sellers' => $sellers,
            'invoices' => $invoices,
            'trend' => $trend,
            'top_sellers' => $topSellers,
        ]);
    }

    public function getSellerDashboard()
    {
        return redirect()->route('sellerProducts');
    }

    public function getSellerProducts(Request $request)
    {
        $validated = $request->validate([
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'sort' => ['nullable', 'in:latest,oldest'],
            'collection' => ['nullable', 'in:all,best_selling,new_arrivals'],
            'pricing_model' => ['nullable', 'in:all,commission,reseller'],
            'attributes' => ['nullable', 'array'],
            'attributes.*' => ['nullable', 'string', 'max:100'],
        ]);

        $categoryId = isset($validated['category_id']) ? (int) $validated['category_id'] : null;
        $sort = $validated['sort'] ?? 'latest';
        $collection = $validated['collection'] ?? 'all';
        $pricingModel = $validated['pricing_model'] ?? 'all';

        $filterAttributes = Attribute::query()
            ->orderBy('name')
            ->get(['name', 'slug', 'type', 'values'])
            ->map(function (Attribute $attribute) {
                $options = collect($attribute->values ?? [])
                    ->map(function ($value) use ($attribute) {
                        if ($attribute->type === 'color') {
                            $name = trim((string) ($value['name'] ?? ''));
                            if ($name === '') {
                                return null;
                            }

                            return [
                                'value' => $name,
                                'label' => $name,
                                'color' => $value['color'] ?? null,
                            ];
                        }

                        $label = trim((string) $value);

                        return $label === '' ? null : [
                            'value' => $label,
                            'label' => $label,
                            'color' => null,
                        ];
                    })
                    ->filter()
                    ->unique('value')
                    ->values();

                return [
                    'name' => $attribute->name,
                    'slug' => $attribute->slug,
                    'type' => $attribute->type,
                    'options' => $options,
                ];
            })
            ->filter(fn ($attribute) => $attribute['options']->isNotEmpty())
            ->values();

        $requestedAttributes = collect($validated['attributes'] ?? []);
        $selectedAttributes = $filterAttributes
            ->mapWithKeys(function ($attribute) use ($requestedAttributes) {
                $selected = $requestedAttributes->get($attribute['slug']);
                $valid = $attribute['options']->contains(
                    fn ($option) => $option['value'] === $selected
                );

                return $valid ? [$attribute['slug'] => $selected] : [];
            });
        $attributeTypes = $filterAttributes->pluck('type', 'slug');

        $catalogQuery = function () use ($attributeTypes, $categoryId, $collection, $pricingModel, $selectedAttributes) {
            return Product::query()
                ->with([
                    'category:id,name',
                    'images:id,product_id,path,is_primary',
                    'varients:id,product_id,price,reseller_price,maximum_selling_price,is_active',
                    'productLevels:id,product_id,level_id,type,value',
                ])
                ->where('is_active', true)
                ->when($categoryId, fn ($query) => $query->where('category_id', $categoryId))
                ->when($collection === 'best_selling', fn ($query) => $query->where('isbestseller', true))
                ->when($collection === 'new_arrivals', fn ($query) => $query->where('created_at', '>=', now()->subDays(30)))
                ->when($pricingModel !== 'all', fn ($query) => $query->where('pricing_model', $pricingModel))
                ->when($selectedAttributes->isNotEmpty(), function ($query) use ($attributeTypes, $selectedAttributes) {
                    $query->whereHas('varients', function ($variantQuery) use ($attributeTypes, $selectedAttributes) {
                        $variantQuery->where('is_active', true);

                        foreach ($selectedAttributes as $slug => $value) {
                            $jsonPath = $attributeTypes->get($slug) === 'color'
                                ? "attributes->{$slug}->name"
                                : "attributes->{$slug}";
                            $variantQuery->where($jsonPath, $value);
                        }
                    });
                });
        };

        $productsQuery = $catalogQuery();
        if ($sort === 'oldest') {
            $productsQuery->oldest();
        } else {
            $productsQuery->latest();
        }

        $products = $productsQuery
            ->paginate(15)
            ->withQueryString();

        $sellerLevelId = auth()->user()?->seller?->seller_level_id;
        $presentProduct = function (Product $product) use ($sellerLevelId) {
            $primaryImage = $product->images->firstWhere('is_primary', true) ?? $product->images->first();
            $isReseller = $product->pricing_model === 'reseller';
            $prices = $product->varients
                ->where('is_active', true)
                ->pluck($isReseller ? 'reseller_price' : 'price')
                ->filter(fn ($price) => ! is_null($price));
            $minPrice = $prices->isNotEmpty() ? (float) $prices->min() : null;
            $maximumPrices = $isReseller
                ? $product->varients->where('is_active', true)->pluck('maximum_selling_price')->filter(fn ($price) => ! is_null($price))
                : $prices;
            $maxPrice = $maximumPrices->isNotEmpty() ? (float) $maximumPrices->max() : null;
            $levelCommission = $product->productLevels
                ->first(fn ($row) => (int) $row->level_id === (int) $sellerLevelId);

            $commission = null;
            if ($isReseller && ! is_null($minPrice) && ! is_null($maxPrice)) {
                $commission = max(0, $maxPrice - $minPrice);
            } elseif ($levelCommission && ! is_null($minPrice)) {
                $commission = $levelCommission->type === 'percentage'
                    ? ($minPrice * (float) $levelCommission->value) / 100
                    : (float) $levelCommission->value;
            }

            return [
                'id' => (int) $product->id,
                'title' => $product->title,
                'small_description' => Str::limit($product->small_description, 90),
                'category' => $product->category?->name ?? 'Uncategorized',
                'image_url' => $primaryImage ? asset('storage/'.$primaryImage->path) : null,
                'min_price' => $minPrice,
                'max_price' => $maxPrice,
                'commission' => is_null($commission) ? null : round($commission, 2),
                'pricing_model' => $isReseller ? 'reseller' : 'commission',
                'isbestseller' => (bool) $product->isbestseller,
                'rating' => (float) $product->rating,
                'rating_user_count' => (int) $product->rating_user_count,
                'detail_url' => route('sellerProducts.show', $product),
            ];
        };

        $productRows = $products->getCollection()->map($presentProduct)->values();
        $pagination = [
            'current_page' => $products->currentPage(),
            'last_page' => $products->lastPage(),
            'next_page_url' => $products->nextPageUrl(),
            'total' => $products->total(),
        ];

        if ($request->expectsJson()) {
            return response()->json([
                'products' => $productRows,
                'pagination' => $pagination,
            ]);
        }

        $bestSellers = $catalogQuery()
            ->where('isbestseller', true)
            ->latest()
            ->limit(12)
            ->get()
            ->map($presentProduct)
            ->values();

        $newArrivals = $catalogQuery()
            ->latest()
            ->limit(6)
            ->get()
            ->map($presentProduct)
            ->values();

        $categories = Category::query()
            ->select('categories.id', 'categories.name')
            ->join('products', 'products.category_id', '=', 'categories.id')
            ->where('categories.is_active', true)
            ->where('products.is_active', true)
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('categories.name')
            ->get();

        return view('dashboards.seller.products', [
            'catalog' => [
                'products' => $productRows,
                'best_sellers' => $bestSellers,
                'new_arrivals' => $newArrivals,
                'pagination' => $pagination,
                'categories' => $categories,
                'filter_attributes' => $filterAttributes,
                'filters' => [
                    'category_id' => $categoryId,
                    'sort' => $sort,
                    'collection' => $collection,
                    'pricing_model' => $pricingModel,
                    'attributes' => $selectedAttributes,
                ],
                'selected_category_id' => $categoryId,
            ],
            'selectedCategoryId' => $categoryId,
        ]);
    }

    public function getSellerProductShow(Product $product)
    {
        if (! $product->is_active) {
            abort(404);
        }

        return view('dashboards.seller.product-show', [
            'productId' => $product->id,
        ]);
    }

    public function getSellerProductData(Product $product)
    {
        if (! $product->is_active) {
            abort(404);
        }

        $product->load([
            'category:id,name',
            'images:id,product_id,path,is_primary',
            'varients:id,product_id,sku,attributes,price,reseller_price,maximum_selling_price,stock_quantity,reorder_level,is_active',
            'productLevels.level:id,level_no,level_name,points',
        ]);

        $seller = auth()->user()?->seller;

        return response()->json([
            'product' => $product,
            'seller' => [
                'seller_level_id' => $seller?->seller_level_id,
                'points' => (int) ($seller?->points ?? 0),
            ],
        ]);
    }

    public function getSellerInventory()
    {
        return redirect()->route('sellerProducts');
    }

    public function getSellerOrders()
    {
        return view('dashboards.seller.orders');
    }

    public function getSellerOrderCreate()
    {
        return view('dashboards.seller.order-create');
    }

    public function getSellerBulkOrders()
    {
        return view('dashboards.seller.bulk-orders');
    }

    public function getSellerAffiliate()
    {
        return view('dashboards.seller.affiliate');
    }

    public function postSellerAffiliateGenerate(Request $request)
    {
        $seller = $request->user()?->seller;
        if (! $seller) {
            abort(403, 'Seller profile not found.');
        }

        $code = null;
        for ($attempt = 0; $attempt < 10; $attempt++) {
            $candidate = strtoupper('NEX-'.Str::random(8));
            $exists = Seller::query()
                ->where('referral_code', $candidate)
                ->where('id', '!=', $seller->id)
                ->exists();

            if (! $exists) {
                $code = $candidate;
                break;
            }
        }

        if (! $code) {
            return redirect()
                ->route('sellerAffiliate')
                ->withErrors(['affiliate' => 'Unable to generate referral code. Please try again.']);
        }

        $seller->referral_code = $code;
        $seller->referral_link = route('sellerRegistration', ['ref' => $code]);
        $seller->save();

        return redirect()->route('sellerAffiliate');
    }

    public function getSellerPayments()
    {
        return view('dashboards.seller.payments');
    }

    public function getSellerProfileManager()
    {
        return view('dashboards.seller.profile');
    }

    public function getSellerOrderShow(Order $order)
    {
        $seller = auth()->user()?->seller;
        if (! $seller || (int) $order->seller_id !== (int) $seller->id) {
            abort(404);
        }

        return view('dashboards.seller.order-show', [
            'orderId' => $order->id,
        ]);
    }

    public function getSellerCheckout()
    {
        return view('dashboards.seller.checkout');
    }

    public function getRoyalExpress()
    {
        return view('dashboards.admin.settings.royalExpress');
    }

    public function getDeliveryFees()
    {
        return view('dashboards.admin.settings.deliveryFees');
    }

    public function getManualDeliveryStatusFetcher()
    {
        return view('dashboards.admin.settings.manualDeliveryStatusFetcher');
    }

    public function getDeliveryWebhook()
    {
        return view('dashboards.admin.webhooks.deliveryWebhook');
    }

    public function getAdminDraftOrders()
    {
        return view('dashboards.admin.orders.draftOrders');
    }

    public function getAdminBulkOrderRequests()
    {
        return view('dashboards.admin.orders.bulkOrderRequests');
    }

    public function getAdminApprovedOrders()
    {
        return view('dashboards.admin.orders.approvedOrders');
    }

    public function getAdminPackedOrders()
    {
        return view('dashboards.admin.orders.packedOrders');
    }

    public function getAdminShippedOrders()
    {
        return view('dashboards.admin.orders.shippedOrders');
    }

    public function getAdminCompletedOrders()
    {
        return view('dashboards.admin.orders.completedOrders');
    }

    public function getAdminCancelledOrders()
    {
        return view('dashboards.admin.orders.cancelledOrders');
    }

    public function getAdminRejectedOrders()
    {
        return view('dashboards.admin.orders.rejectedOrders');
    }

    public function getAdminDispatchNotes()
    {
        return view('dashboards.admin.orders.dispatchNotes');
    }

    public function getAdminDispatchNoteShow($dispatchNote)
    {
        return view('dashboards.admin.orders.dispatchNoteShow', [
            'dispatchNoteId' => (int) $dispatchNote,
        ]);
    }

    public function getAdminFinancePendingPayments()
    {
        return view('dashboards.admin.finance.pendingPayments');
    }

    public function getAdminFinanceAvailablePayments()
    {
        return view('dashboards.admin.finance.availablePayments');
    }

    public function getAdminFinanceAffiliatePayments()
    {
        return view('dashboards.admin.finance.affiliatePayments');
    }

    public function getAdminFinanceInvoices()
    {
        return view('dashboards.admin.finance.invoices');
    }

    public function getAdminFinancePaymentManager()
    {
        return view('dashboards.admin.finance.paymentManager');
    }

    public function getAdminOrderCountReport()
    {
        return view('dashboards.admin.reports.orderCount');
    }

    public function getAdminSellerBreakdownReport()
    {
        return view('dashboards.admin.reports.sellerBreakdown');
    }

    public function getAdminProductBreakdownReport()
    {
        return view('dashboards.admin.reports.productBreakdown');
    }

    public function getAdminFinanceReport()
    {
        return view('dashboards.admin.reports.financeReport');
    }

    public function getAdminOrderShow(Order $order)
    {
        return view('dashboards.admin.orders.showOrder', [
            'orderId' => $order->id,
        ]);
    }

    public function getGrns()
    {
        return view('dashboards.admin.settings.grns');
    }

    public function getLots()
    {
        return view('dashboards.admin.settings.lots');
    }

    public function getReturnList()
    {
        return view('dashboards.admin.settings.returnList');
    }
}
