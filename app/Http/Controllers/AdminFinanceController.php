<?php

namespace App\Http\Controllers;

use App\Models\AffiliateCommission;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Seller;
use App\Services\InvoiceGenerationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminFinanceController extends Controller
{
    public function __construct(private readonly InvoiceGenerationService $invoiceGenerationService)
    {
    }

    public function pendingPayments(Request $request)
    {
        return $this->ordersByPaymentStatus($request, 'pending');
    }

    public function availablePayments(Request $request)
    {
        return $this->ordersByPaymentStatus($request, 'available');
    }

    public function affiliatePayments(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'seller_id' => ['nullable', 'integer', 'exists:sellers,id'],
            'status' => ['nullable', 'in:all,available,paid'],
            'date_type' => ['nullable', 'in:available_at,paid_at'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:100'],
        ]);

        $search = trim((string) ($validated['search'] ?? ''));
        $status = (string) ($validated['status'] ?? 'all');
        $dateType = (string) ($validated['date_type'] ?? 'available_at');
        $perPage = (int) ($validated['per_page'] ?? 20);

        $commissionFilter = function ($query) use ($validated, $status, $dateType) {
            if ($status !== 'all') {
                $query->where('status', $status);
            }

            if (!empty($validated['date_from'])) {
                $query->whereDate($dateType, '>=', $validated['date_from']);
            }
            if (!empty($validated['date_to'])) {
                $query->whereDate($dateType, '<=', $validated['date_to']);
            }
        };

        $query = Seller::query()
            ->select(['id', 'first_name', 'last_name', 'email', 'phone'])
            ->whereHas('affiliateCommissionsEarned', $commissionFilter)
            ->withCount(['affiliateCommissionsEarned as commissions_count' => $commissionFilter])
            ->withSum(['affiliateCommissionsEarned as available_affiliate_value' => function ($q) use ($commissionFilter) {
                $commissionFilter($q);
                $q->where('status', 'available');
            }], 'amount')
            ->withSum(['affiliateCommissionsEarned as paid_affiliate_value' => function ($q) use ($commissionFilter) {
                $commissionFilter($q);
                $q->where('status', 'paid');
            }], 'amount')
            ->withSum(['affiliateCommissionsEarned as total_affiliate_value' => $commissionFilter], 'amount')
            ->latest('id');

        if (!empty($validated['seller_id'])) {
            $query->where('id', (int) $validated['seller_id']);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('id', $search)
                    ->orWhere('first_name', 'like', '%' . $search . '%')
                    ->orWhere('last_name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%');
            });
        }

        $sellers = $query->paginate($perPage);

        $summaryQuery = AffiliateCommission::query()
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->when(!empty($validated['seller_id']), fn ($q) => $q->where('affiliate_seller_id', (int) $validated['seller_id']))
            ->when(!empty($validated['date_from']), fn ($q) => $q->whereDate($dateType, '>=', $validated['date_from']))
            ->when(!empty($validated['date_to']), fn ($q) => $q->whereDate($dateType, '<=', $validated['date_to']));

        if ($search !== '') {
            $summaryQuery->whereHas('affiliateSeller', function ($q) use ($search) {
                $q->where('id', $search)
                    ->orWhere('first_name', 'like', '%' . $search . '%')
                    ->orWhere('last_name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%');
            });
        }

        $rows = collect($sellers->items())->map(function (Seller $seller) {
            return [
                'id' => (int) $seller->id,
                'first_name' => $seller->first_name,
                'last_name' => $seller->last_name,
                'email' => $seller->email,
                'phone' => $seller->phone,
                'commissions_count' => (int) ($seller->commissions_count ?? 0),
                'available_affiliate_value' => round((float) ($seller->available_affiliate_value ?? 0), 2),
                'paid_affiliate_value' => round((float) ($seller->paid_affiliate_value ?? 0), 2),
                'total_affiliate_value' => round((float) ($seller->total_affiliate_value ?? 0), 2),
            ];
        })->values();

        $availableValue = (float) (clone $summaryQuery)->where('status', 'available')->sum('amount');
        $paidValue = (float) (clone $summaryQuery)->where('status', 'paid')->sum('amount');
        $totalValue = (float) (clone $summaryQuery)->sum('amount');
        $sellersCount = (int) (clone $summaryQuery)->distinct('affiliate_seller_id')->count('affiliate_seller_id');
        $recordsCount = (int) (clone $summaryQuery)->count();

        return response()->json([
            'sellers' => $rows,
            'summary' => [
                'sellers_count' => $sellersCount,
                'records_count' => $recordsCount,
                'available_value' => round($availableValue, 2),
                'paid_value' => round($paidValue, 2),
                'total_value' => round($totalValue, 2),
            ],
            'meta' => [
                'current_page' => $sellers->currentPage(),
                'last_page' => $sellers->lastPage(),
                'per_page' => $sellers->perPage(),
                'total' => $sellers->total(),
            ],
        ]);
    }

    public function invoices(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'seller_id' => ['nullable', 'integer', 'exists:sellers,id'],
            'status' => ['nullable', 'in:draft,paid,cancelled'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:100'],
        ]);

        $search = trim((string) ($validated['search'] ?? ''));
        $perPage = (int) ($validated['per_page'] ?? 20);

        $query = Invoice::query()
            ->with(['seller:id,first_name,last_name,email,phone'])
            ->withCount('orders')
            ->latest('invoice_date')
            ->latest('id');

        if (!empty($validated['seller_id'])) {
            $query->where('seller_id', (int) $validated['seller_id']);
        }

        if (!empty($validated['status'])) {
            $query->where('status', (string) $validated['status']);
        }

        if (!empty($validated['date_from'])) {
            $query->whereDate('invoice_date', '>=', $validated['date_from']);
        }
        if (!empty($validated['date_to'])) {
            $query->whereDate('invoice_date', '<=', $validated['date_to']);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('id', $search)
                    ->orWhereHas('seller', function ($sellerQuery) use ($search) {
                        $sellerQuery
                            ->where('first_name', 'like', '%' . $search . '%')
                            ->orWhere('last_name', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%')
                            ->orWhere('phone', 'like', '%' . $search . '%');
                    });
            });
        }

        $invoices = $query->paginate($perPage);

        $rows = collect($invoices->items())->map(function (Invoice $invoice) {
            return [
                'id' => (int) $invoice->id,
                'seller' => $invoice->seller,
                'invoice_date' => $invoice->invoice_date,
                'invoice_time' => $invoice->invoice_time,
                'total_commission_value' => (float) ($invoice->total_commission_value ?? 0),
                'status' => (string) ($invoice->status ?? 'draft'),
                'orders_count' => (int) ($invoice->orders_count ?? 0),
                'created_at' => $invoice->created_at,
            ];
        })->values();

        $summaryQuery = clone $query;

        return response()->json([
            'invoices' => $rows,
            'summary' => [
                'invoice_count' => (int) (clone $summaryQuery)->reorder()->count(),
                'total_commission_value' => round((float) (clone $summaryQuery)->reorder()->sum('total_commission_value'), 2),
            ],
            'meta' => [
                'current_page' => $invoices->currentPage(),
                'last_page' => $invoices->lastPage(),
                'per_page' => $invoices->perPage(),
                'total' => $invoices->total(),
            ],
        ]);
    }

    public function exportInvoicesBankDocument(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'seller_id' => ['nullable', 'integer', 'exists:sellers,id'],
            'status' => ['nullable', 'in:draft,paid,cancelled'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'invoice_ids' => ['nullable', 'array'],
            'invoice_ids.*' => ['integer', 'exists:invoices,id'],
        ]);

        $search = trim((string) ($validated['search'] ?? ''));

        $query = Invoice::query()
            ->with([
                'seller:id,first_name,last_name,email,phone',
                'seller.bankDetail:id,seller_id,bank_id,bank,account_no,swift_code,name,branch',
                'seller.bankDetail.bank:id,name',
            ])
            ->withCount('orders')
            ->latest('invoice_date')
            ->latest('id');

        if (!empty($validated['seller_id'])) {
            $query->where('seller_id', (int) $validated['seller_id']);
        }
        if (!empty($validated['status'])) {
            $query->where('status', (string) $validated['status']);
        }
        if (!empty($validated['date_from'])) {
            $query->whereDate('invoice_date', '>=', $validated['date_from']);
        }
        if (!empty($validated['date_to'])) {
            $query->whereDate('invoice_date', '<=', $validated['date_to']);
        }
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('id', $search)
                    ->orWhereHas('seller', function ($sellerQuery) use ($search) {
                        $sellerQuery
                            ->where('first_name', 'like', '%' . $search . '%')
                            ->orWhere('last_name', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%')
                            ->orWhere('phone', 'like', '%' . $search . '%');
                    });
            });
        }
        if (!empty($validated['invoice_ids'])) {
            $query->whereIn('id', $validated['invoice_ids']);
        }

        $invoices = $query->get();

        $grouped = $invoices->groupBy(function (Invoice $invoice) {
            $bankDetail = $invoice->seller?->bankDetail;
            $bankName = (string) ($bankDetail?->bank ?? '');
            return $bankName ?: 'Unknown Bank';
        })->sortKeys();

        $fileName = 'invoice-bank-document-' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($grouped) {
            $output = fopen('php://output', 'w');

            fputcsv($output, [
                'Bank',
                'Invoice ID',
                'Invoice Date',
                'Invoice Time',
                'Seller ID',
                'Seller Name',
                'Seller Email',
                'Account Name',
                'Account Number',
                'Branch',
                'SWIFT Code',
                'Orders Count',
                'Total Commission Value',
                'Invoice Status',
            ]);

            foreach ($grouped as $bankName => $bankInvoices) {
                fputcsv($output, [$bankName]);

                foreach ($bankInvoices as $invoice) {
                    $seller = $invoice->seller;
                    $bankDetail = $seller?->bankDetail;
                    $sellerName = trim(((string) ($seller?->first_name ?? '')) . ' ' . ((string) ($seller?->last_name ?? '')));

                    fputcsv($output, [
                        $bankName,
                        (int) $invoice->id,
                        (string) $invoice->invoice_date,
                        (string) ($invoice->invoice_time ?? ''),
                        (int) ($seller?->id ?? 0),
                        $sellerName !== '' ? $sellerName : ($seller?->email ?? ''),
                        (string) ($seller?->email ?? ''),
                        (string) ($bankDetail?->name ?? ''),
                        (string) ($bankDetail?->account_no ?? ''),
                        (string) ($bankDetail?->branch ?? ''),
                        (string) ($bankDetail?->swift_code ?? ''),
                        (int) ($invoice->orders_count ?? 0),
                        number_format((float) ($invoice->total_commission_value ?? 0), 2, '.', ''),
                        (string) ($invoice->status ?? ''),
                    ]);
                }

                fputcsv($output, []);
            }

            fclose($output);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function paymentManagerSellers(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'seller_id' => ['nullable', 'integer', 'exists:sellers,id'],
            'date_type' => ['nullable', 'in:order_datetime,completed_at'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:100'],
        ]);

        $search = trim((string) ($validated['search'] ?? ''));
        $perPage = (int) ($validated['per_page'] ?? 20);

        $orderFilter = function ($query) use ($validated) {
            $query->where('payment_status', 'available')
                ->whereNull('invoice_id');
            $this->applyDateFilter($query, $validated);
        };

        $query = Seller::query()
            ->select(['id', 'first_name', 'last_name', 'email', 'phone', 'status', 'seller_level_id'])
            ->with('level:id,level_no,level_name')
            ->whereHas('orders', $orderFilter)
            ->withCount(['orders as available_orders_count' => $orderFilter])
            ->withSum(['orders as available_commission_value' => $orderFilter], 'commission_amount')
            ->latest('id');

        if (!empty($validated['seller_id'])) {
            $query->where('id', (int) $validated['seller_id']);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('id', $search)
                    ->orWhere('first_name', 'like', '%' . $search . '%')
                    ->orWhere('last_name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%');
            });
        }

        $aggregateOrdersQuery = Order::query()
            ->where('payment_status', 'available')
            ->whereNull('invoice_id');
        $this->applyDateFilter($aggregateOrdersQuery, $validated);

        if (!empty($validated['seller_id'])) {
            $aggregateOrdersQuery->where('seller_id', (int) $validated['seller_id']);
        }

        if ($search !== '') {
            $aggregateOrdersQuery->whereHas('seller', function ($sellerQuery) use ($search) {
                $sellerQuery->where('id', $search)
                    ->orWhere('first_name', 'like', '%' . $search . '%')
                    ->orWhere('last_name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%');
            });
        }

        $sellers = $query->paginate($perPage);

        $rows = collect($sellers->items())->map(function (Seller $seller) {
            return [
                'id' => (int) $seller->id,
                'first_name' => $seller->first_name,
                'last_name' => $seller->last_name,
                'email' => $seller->email,
                'phone' => $seller->phone,
                'status' => $seller->status,
                'level' => $seller->level,
                'available_orders_count' => (int) ($seller->available_orders_count ?? 0),
                'available_commission_value' => round((float) ($seller->available_commission_value ?? 0), 2),
            ];
        })->values();

        return response()->json([
            'sellers' => $rows,
            'summary' => [
                'sellers_count' => (int) (clone $aggregateOrdersQuery)->distinct('seller_id')->count('seller_id'),
                'available_orders_count' => (int) (clone $aggregateOrdersQuery)->count(),
                'total_commission_value' => round((float) (clone $aggregateOrdersQuery)->sum('commission_amount'), 2),
            ],
            'meta' => [
                'current_page' => $sellers->currentPage(),
                'last_page' => $sellers->lastPage(),
                'per_page' => $sellers->perPage(),
                'total' => $sellers->total(),
            ],
        ]);
    }

    public function paymentManagerSellerOrders(Request $request, Seller $seller)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'date_type' => ['nullable', 'in:order_datetime,completed_at'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:100'],
        ]);

        $search = trim((string) ($validated['search'] ?? ''));
        $perPage = (int) ($validated['per_page'] ?? 20);

        $query = Order::query()
            ->where('seller_id', $seller->id)
            ->where('payment_status', 'available')
            ->whereNull('invoice_id')
            ->latest('completed_at')
            ->latest('id');

        $this->applyDateFilter($query, $validated);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('id', $search)
                    ->orWhere('customer_name', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%')
                    ->orWhere('waybill_no', 'like', '%' . $search . '%');
            });
        }

        $summaryQuery = clone $query;
        $orders = $query->paginate($perPage);

        $rows = collect($orders->items())->map(function (Order $order) {
            $netSaleAmount = max(0, (float) ($order->net_total ?? 0) - (float) ($order->total_discount ?? 0));

            return [
                'id' => (int) $order->id,
                'order_datetime' => $order->order_datetime,
                'completed_at' => $order->completed_at,
                'payment_status' => (string) ($order->payment_status ?? ''),
                'customer_name' => $order->customer_name,
                'phone' => $order->phone,
                'waybill_no' => $order->waybill_no,
                'total_collectable_amount' => (float) ($order->total_collectable_amount ?? 0),
                'commission_amount' => (float) ($order->commission_amount ?? 0),
                'delivery_charge' => (float) ($order->delivery_charge ?? 0),
                'net_sale_amount' => $netSaleAmount,
            ];
        })->values();

        $orderCount = (int) (clone $summaryQuery)->reorder()->count();
        $totalCommissionValue = (float) (clone $summaryQuery)->reorder()->sum('commission_amount');

        return response()->json([
            'seller' => [
                'id' => (int) $seller->id,
                'first_name' => $seller->first_name,
                'last_name' => $seller->last_name,
                'email' => $seller->email,
                'phone' => $seller->phone,
            ],
            'orders' => $rows,
            'summary' => [
                'order_count' => $orderCount,
                'total_commission_value' => round($totalCommissionValue, 2),
            ],
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    public function generateInvoices(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'seller_id' => ['nullable', 'integer', 'exists:sellers,id'],
            'date_type' => ['nullable', 'in:order_datetime,completed_at'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        $query = Order::query()
            ->where('payment_status', 'available')
            ->whereNull('invoice_id');
        $this->applyDateFilter($query, $validated);

        if (!empty($validated['seller_id'])) {
            $query->where('seller_id', (int) $validated['seller_id']);
        }

        $search = trim((string) ($validated['search'] ?? ''));
        if ($search !== '') {
            $query->whereHas('seller', function ($sellerQuery) use ($search) {
                $sellerQuery->where('id', $search)
                    ->orWhere('first_name', 'like', '%' . $search . '%')
                    ->orWhere('last_name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%');
            });
        }

        $sellerIds = (clone $query)->select('seller_id')->distinct()->pluck('seller_id');
        $affiliateQuery = AffiliateCommission::query()
            ->where('status', 'available')
            ->whereNull('invoice_id');

        if (!empty($validated['seller_id'])) {
            $affiliateQuery->where('affiliate_seller_id', (int) $validated['seller_id']);
        }

        if (!empty($validated['date_from'])) {
            $affiliateQuery->whereDate('available_at', '>=', $validated['date_from']);
        }
        if (!empty($validated['date_to'])) {
            $affiliateQuery->whereDate('available_at', '<=', $validated['date_to']);
        }

        if ($search !== '') {
            $affiliateQuery->whereHas('affiliateSeller', function ($sellerQuery) use ($search) {
                $sellerQuery->where('id', $search)
                    ->orWhere('first_name', 'like', '%' . $search . '%')
                    ->orWhere('last_name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%');
            });
        }

        $affiliateSellerIds = $affiliateQuery
            ->distinct()
            ->pluck('affiliate_seller_id');

        $sellerIds = $sellerIds
            ->merge($affiliateSellerIds)
            ->filter()
            ->unique()
            ->values();

        if ($sellerIds->isEmpty()) {
            return response()->json([
                'message' => 'No eligible sellers found to generate invoices.',
            ], 422);
        }

        $createdCount = 0;
        $totalOrdersAssigned = 0;
        $invoiceIds = [];

        DB::transaction(function () use ($sellerIds, $validated, &$createdCount, &$totalOrdersAssigned, &$invoiceIds) {
            foreach ($sellerIds as $sellerId) {
                $result = $this->invoiceGenerationService->generateDraftInvoiceForSeller((int) $sellerId, $validated, now('Asia/Colombo'));
                if ($result['invoice_id']) {
                    $createdCount++;
                    $totalOrdersAssigned += (int) $result['orders_assigned'];
                    $invoiceIds[] = (int) $result['invoice_id'];
                }
            }
        });

        if ($createdCount === 0) {
            return response()->json([
                'message' => 'No eligible orders found to generate invoices.',
            ], 422);
        }

        return response()->json([
            'message' => "Generated {$createdCount} invoice(s) successfully.",
            'invoice_ids' => $invoiceIds,
            'summary' => [
                'invoice_count' => $createdCount,
                'orders_assigned' => $totalOrdersAssigned,
            ],
        ]);
    }

    public function generateSellerInvoice(Request $request, Seller $seller)
    {
        $validated = $request->validate([
            'date_type' => ['nullable', 'in:order_datetime,completed_at'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        $result = $this->invoiceGenerationService->generateDraftInvoiceForSeller((int) $seller->id, $validated, now('Asia/Colombo'));

        if (!(int) (($result['orders_assigned'] ?? 0) + ($result['affiliate_commissions_assigned'] ?? 0))) {
            return response()->json([
                'message' => 'No eligible orders found for this seller.',
            ], 422);
        }

        return response()->json([
            'message' => 'Invoice generated successfully.',
            'invoice_id' => (int) $result['invoice_id'],
            'orders_assigned' => (int) $result['orders_assigned'],
        ]);
    }

    public function markInvoicePaid(Invoice $invoice)
    {
        if ((string) $invoice->status === 'paid') {
            return response()->json([
                'message' => 'Invoice is already marked as paid.',
            ], 422);
        }

        if ((string) $invoice->status === 'cancelled') {
            return response()->json([
                'message' => 'Cancelled invoice cannot be marked as paid.',
            ], 422);
        }

        $result = DB::transaction(function () use ($invoice) {
            $orderIds = Order::query()
                ->where('invoice_id', $invoice->id)
                ->pluck('id');

            $ordersUpdated = 0;
            $paymentsUpdated = 0;

            if ($orderIds->isNotEmpty()) {
                $ordersUpdated = Order::query()
                    ->whereIn('id', $orderIds)
                    ->update([
                        'payment_status' => 'paid',
                    ]);

                $paymentsUpdated = Payment::query()
                    ->whereIn('order_id', $orderIds)
                    ->update([
                        'status' => 'paid',
                        'paid_at' => now(),
                    ]);
            }

            $affiliateCommissionsUpdated = AffiliateCommission::query()
                ->where('invoice_id', $invoice->id)
                ->where('status', 'available')
                ->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);

            $invoice->update([
                'status' => 'paid',
            ]);

            return [
                'orders_updated' => (int) $ordersUpdated,
                'payments_updated' => (int) $paymentsUpdated,
                'affiliate_commissions_updated' => (int) $affiliateCommissionsUpdated,
            ];
        });

        return response()->json([
            'message' => 'Invoice marked as paid successfully.',
            'invoice_id' => (int) $invoice->id,
            'summary' => $result,
        ]);
    }

    private function ordersByPaymentStatus(Request $request, string $paymentStatus)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'seller_id' => ['nullable', 'integer', 'exists:sellers,id'],
            'date_type' => ['nullable', 'in:order_datetime,completed_at'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'order_date_from' => ['nullable', 'date'],
            'order_date_to' => ['nullable', 'date'],
            'completed_date_from' => ['nullable', 'date'],
            'completed_date_to' => ['nullable', 'date'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:100'],
        ]);

        $search = trim((string) ($validated['search'] ?? ''));
        $perPage = (int) ($validated['per_page'] ?? 20);

        $query = Order::query()
            ->with([
                'seller:id,first_name,last_name,email,phone',
                'city:id,name_en',
                'customer:id,default_name,primary_phone,additional_phone,email,notes',
            ])
            ->where('payment_status', $paymentStatus)
            ->latest('order_datetime')
            ->latest('id');

        if (!empty($validated['seller_id'])) {
            $query->where('seller_id', (int) $validated['seller_id']);
        }

        $this->applyDateFilter($query, $validated);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('id', $search)
                    ->orWhere('customer_name', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%')
                    ->orWhere('waybill_no', 'like', '%' . $search . '%')
                    ->orWhereHas('seller', function ($sellerQuery) use ($search) {
                        $sellerQuery
                            ->where('first_name', 'like', '%' . $search . '%')
                            ->orWhere('last_name', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%');
                    });
            });
        }

        $summaryQuery = clone $query;
        $orders = $query->paginate($perPage);

        $rows = collect($orders->items())->map(function ($order) {
            $netSaleAmount = max(0, (float) ($order->net_total ?? 0) - (float) ($order->total_discount ?? 0));

            return [
                'id' => (int) $order->id,
                'order_datetime' => $order->order_datetime,
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'customer_name' => $order->customer_name,
                'phone' => $order->phone,
                'waybill_no' => $order->waybill_no,
                'city' => $order->city,
                'seller' => $order->seller,
                'total_collectable_amount' => (float) ($order->total_collectable_amount ?? 0),
                'delivery_charge' => (float) ($order->delivery_charge ?? 0),
                'net_sale_amount' => $netSaleAmount,
                'commission_amount' => (float) ($order->commission_amount ?? 0),
                'completed_at' => $order->completed_at,
            ];
        })->values();

        $statusOrderValue = (float) (clone $summaryQuery)
            ->reorder()
            ->sum('total_collectable_amount');

        $statusCommissionValue = (float) (clone $summaryQuery)
            ->reorder()
            ->sum('commission_amount');

        $statusDeliveryChargeValue = (float) (clone $summaryQuery)
            ->reorder()
            ->sum('delivery_charge');

        $statusNetSaleValue = (float) (clone $summaryQuery)
            ->reorder()
            ->selectRaw('COALESCE(SUM(GREATEST(net_total - total_discount, 0)), 0) as total')
            ->value('total');

        return response()->json([
            'orders' => $rows,
            'summary' => [
                'order_value' => round($statusOrderValue, 2),
                'commission_value' => round($statusCommissionValue, 2),
                'delivery_charge_value' => round($statusDeliveryChargeValue, 2),
                'net_sale_value' => round($statusNetSaleValue, 2),
            ],
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
            'payment_status' => $paymentStatus,
        ]);
    }

    private function applyDateFilter($query, array $validated): void
    {
        $dateType = (string) ($validated['date_type'] ?? 'order_datetime');
        $dateFrom = $validated['date_from'] ?? null;
        $dateTo = $validated['date_to'] ?? null;

        if (!empty($dateFrom)) {
            $query->whereDate($dateType, '>=', $dateFrom);
        }
        if (!empty($dateTo)) {
            $query->whereDate($dateType, '<=', $dateTo);
        }

        // Backward compatibility for older clients that send dedicated date params.
        if (empty($dateFrom) && empty($dateTo)) {
            $orderDateFrom = $validated['order_date_from'] ?? null;
            $orderDateTo = $validated['order_date_to'] ?? null;
            $completedDateFrom = $validated['completed_date_from'] ?? null;
            $completedDateTo = $validated['completed_date_to'] ?? null;

            if (!empty($orderDateFrom)) {
                $query->whereDate('order_datetime', '>=', $orderDateFrom);
            }
            if (!empty($orderDateTo)) {
                $query->whereDate('order_datetime', '<=', $orderDateTo);
            }
            if (!empty($completedDateFrom)) {
                $query->whereDate('completed_at', '>=', $completedDateFrom);
            }
            if (!empty($completedDateTo)) {
                $query->whereDate('completed_at', '<=', $completedDateTo);
            }
        }
    }

}
