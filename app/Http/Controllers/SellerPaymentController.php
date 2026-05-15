<?php

namespace App\Http\Controllers;

use App\Models\AffiliateCommission;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\SystemData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SellerPaymentController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'in:all,available,paid'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:100'],
        ]);

        $seller = $request->user()?->seller;
        if (!$seller) {
            return response()->json([
                'message' => 'Seller profile not found for this user.',
            ], 422);
        }

        $status = (string) ($validated['status'] ?? 'available');
        $search = trim((string) ($validated['search'] ?? ''));
        $perPage = (int) ($validated['per_page'] ?? 20);

        $query = Order::query()
            ->where('seller_id', $seller->id)
            ->whereIn('payment_status', ['available', 'paid'])
            ->latest('completed_at')
            ->latest('id');

        if ($status !== 'all') {
            $query->where('payment_status', $status);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('id', $search)
                    ->orWhere('customer_name', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%')
                    ->orWhere('waybill_no', 'like', '%' . $search . '%');
            });
        }

        $orders = $query->paginate($perPage);

        $totalPendingOrderValue = (float) Order::query()
            ->where('seller_id', $seller->id)
            ->where('payment_status', 'pending')
            ->sum('total_collectable_amount');

        $availablePaymentsValue = (float) Order::query()
            ->where('seller_id', $seller->id)
            ->where('payment_status', 'available')
            ->sum('commission_amount');

        $paidPaymentsValue = (float) Order::query()
            ->where('seller_id', $seller->id)
            ->where('payment_status', 'paid')
            ->sum('commission_amount');

        $pendingPaymentsValue = (float) Order::query()
            ->where('seller_id', $seller->id)
            ->where('payment_status', 'pending')
            ->sum('commission_amount');

        $payments = collect($orders->items())->map(function (Order $order) {
            return [
                'id' => (int) $order->id,
                'order_id' => (int) $order->id,
                'amount' => (float) ($order->commission_amount ?? 0),
                'status' => (string) ($order->payment_status ?? 'pending'),
                'available_at' => $order->completed_at,
                'paid_at' => null,
                'order' => [
                    'id' => (int) $order->id,
                    'customer_name' => $order->customer_name,
                    'phone' => $order->phone,
                    'waybill_no' => $order->waybill_no,
                    'commission_amount' => (float) ($order->commission_amount ?? 0),
                    'payment_status' => (string) ($order->payment_status ?? 'pending'),
                    'completed_at' => $order->completed_at,
                ],
            ];
        })->values();

        return response()->json([
            'summary' => [
                'total_pending_order_value' => round($totalPendingOrderValue, 2),
                'available_payments_value' => round($availablePaymentsValue, 2),
                'paid_payments_value' => round($paidPaymentsValue, 2),
                'pending_payments_value' => round($pendingPaymentsValue, 2),
            ],
            'payments' => $payments,
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    public function invoices(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'in:all,draft,paid,cancelled'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:100'],
        ]);

        $seller = $request->user()?->seller;
        if (!$seller) {
            return response()->json([
                'message' => 'Seller profile not found for this user.',
            ], 422);
        }

        $search = trim((string) ($validated['search'] ?? ''));
        $status = (string) ($validated['status'] ?? 'all');
        $perPage = (int) ($validated['per_page'] ?? 10);

        $query = Invoice::query()
            ->where('seller_id', $seller->id)
            ->withCount('orders')
            ->latest('invoice_date')
            ->latest('id');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if (!empty($validated['date_from'])) {
            $query->whereDate('invoice_date', '>=', $validated['date_from']);
        }
        if (!empty($validated['date_to'])) {
            $query->whereDate('invoice_date', '<=', $validated['date_to']);
        }

        if ($search !== '') {
            $query->where('id', $search);
        }

        $summaryQuery = clone $query;
        $invoices = $query->paginate($perPage);

        return response()->json([
            'invoices' => collect($invoices->items())->map(fn (Invoice $invoice) => [
                'id' => (int) $invoice->id,
                'invoice_date' => $invoice->invoice_date,
                'invoice_time' => $invoice->invoice_time,
                'total_commission_value' => (float) ($invoice->total_commission_value ?? 0),
                'status' => (string) ($invoice->status ?? 'draft'),
                'orders_count' => (int) ($invoice->orders_count ?? 0),
                'created_at' => $invoice->created_at,
            ])->values(),
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

    public function invoicePdfData(Request $request, Invoice $invoice)
    {
        $seller = $request->user()?->seller;
        if (!$seller || (int) $invoice->seller_id !== (int) $seller->id) {
            abort(404);
        }

        $invoice->load([
            'seller:id,first_name,last_name,email,phone',
            'seller.bankDetail:id,seller_id,bank_id,bank,account_no,swift_code,name,branch',
            'orders' => fn ($query) => $query
                ->select([
                    'id',
                    'invoice_id',
                    'completed_at',
                    'customer_name',
                    'waybill_no',
                    'commission_amount',
                ])
                ->orderBy('completed_at')
                ->orderBy('id'),
            'affiliateCommissions' => fn ($query) => $query
                ->select(['id', 'invoice_id', 'seller_id', 'order_id', 'amount', 'status', 'available_at'])
                ->with(['seller:id,first_name,last_name,email,phone', 'order:id,waybill_no'])
                ->orderBy('available_at')
                ->orderBy('id'),
        ]);

        $systemData = SystemData::query()->latest()->first();
        $orders = $invoice->orders->map(fn (Order $order) => [
            'id' => (int) $order->id,
            'completed_at' => $order->completed_at,
            'customer_name' => $order->customer_name,
            'waybill_no' => $order->waybill_no,
            'commission_amount' => (float) ($order->commission_amount ?? 0),
        ])->values();

        $affiliateCommissions = $invoice->affiliateCommissions->map(function (AffiliateCommission $commission) {
            $sellerName = trim(((string) ($commission->seller?->first_name ?? '')) . ' ' . ((string) ($commission->seller?->last_name ?? '')));

            return [
                'id' => (int) $commission->id,
                'order_id' => (int) ($commission->order_id ?? 0),
                'order_waybill_no' => $commission->order?->waybill_no,
                'seller_name' => $sellerName !== '' ? $sellerName : ($commission->seller?->email ?? '-'),
                'amount' => (float) ($commission->amount ?? 0),
                'status' => (string) ($commission->status ?? ''),
                'available_at' => $commission->available_at,
            ];
        })->values();

        $orderCommissionValue = round((float) $orders->sum('commission_amount'), 2);
        $affiliateCommissionValue = round((float) $affiliateCommissions->sum('amount'), 2);

        return response()->json([
            'system_data' => $systemData ? [
                'company_name' => $systemData->company_name,
                'address' => $systemData->address,
                'country' => $systemData->country,
                'phone_number' => $systemData->phone_number,
                'fax' => $systemData->fax,
                'logo_url' => $systemData->logo ? Storage::disk('public')->url($systemData->logo) : null,
            ] : null,
            'invoice' => [
                'id' => (int) $invoice->id,
                'invoice_date' => $invoice->invoice_date,
                'invoice_time' => $invoice->invoice_time,
                'total_commission_value' => (float) ($invoice->total_commission_value ?? 0),
                'status' => (string) ($invoice->status ?? 'draft'),
            ],
            'seller' => $invoice->seller,
            'payment_information' => [
                'bank_name' => $invoice->seller?->bankDetail?->bank,
                'account_name' => $invoice->seller?->bankDetail?->name,
                'account_number' => $invoice->seller?->bankDetail?->account_no,
                'branch' => $invoice->seller?->bankDetail?->branch,
                'swift_code' => $invoice->seller?->bankDetail?->swift_code,
            ],
            'orders' => $orders,
            'affiliate_commissions' => $affiliateCommissions,
            'summary' => [
                'payment_status' => (string) ($invoice->status ?? 'draft'),
                'orders_count' => $orders->count(),
                'affiliate_commissions_count' => $affiliateCommissions->count(),
                'order_commission_value' => $orderCommissionValue,
                'affiliate_commission_value' => $affiliateCommissionValue,
                'total_commission_value' => (float) ($invoice->total_commission_value ?? ($orderCommissionValue + $affiliateCommissionValue)),
            ],
        ]);
    }
}
