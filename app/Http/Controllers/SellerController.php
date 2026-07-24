<?php

namespace App\Http\Controllers;

use App\Models\Level;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Seller;
use App\Models\User;
use DomainException;
use RuntimeException;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Services\BrevoMailer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

class SellerController extends Controller
{
    public function __construct(private readonly BrevoMailer $mailer)
    {
    }

    public function indexView()
    {
        return view('dashboards.admin.settings.sellers');
    }

    public function activeSellersView()
    {
        return view('dashboards.admin.settings.activeSellers');
    }

    public function profileView(Seller $seller)
    {
        return view('dashboards.admin.settings.sellerProfile', [
            'sellerId' => $seller->id,
        ]);
    }

    public function index(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status');
        $perPage = (int) $request->get('per_page', 10);
        $perPage = max(1, min($perPage, 100));

        $query = Seller::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        $sellers = $query->latest()->paginate($perPage);

        return response()->json([
            'sellers' => $sellers->items(),
            'pagination' => [
                'current_page' => $sellers->currentPage(),
                'last_page' => $sellers->lastPage(),
                'per_page' => $sellers->perPage(),
                'total' => $sellers->total(),
                'from' => $sellers->firstItem(),
                'to' => $sellers->lastItem(),
            ],
            'statuses' => ['pending', 'approved', 'rejected', 'blocked'],
        ]);
    }

    public function show(Seller $seller)
    {
        $seller->load(['businessInformation', 'level']);

        return response()->json($seller);
    }

    public function profileOrders(Request $request, Seller $seller)
    {
        $validated = $request->validate([
            'status' => ['nullable', 'in:all,draft,approved,confirmed,packed,shipped,completed,cancelled,rejected'],
            'search' => ['nullable', 'string', 'max:120'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:100'],
        ]);

        $status = (string) ($validated['status'] ?? 'all');
        $search = trim((string) ($validated['search'] ?? ''));
        $perPage = (int) ($validated['per_page'] ?? 20);

        $query = Order::query()
            ->where('seller_id', (int) $seller->id)
            ->withCount('items')
            ->latest('order_datetime')
            ->latest('id');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if (!empty($validated['date_from'])) {
            $query->whereDate('order_datetime', '>=', $validated['date_from']);
        }
        if (!empty($validated['date_to'])) {
            $query->whereDate('order_datetime', '<=', $validated['date_to']);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('id', $search)
                    ->orWhere('customer_name', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%')
                    ->orWhere('additional_phone', 'like', '%' . $search . '%')
                    ->orWhere('waybill_no', 'like', '%' . $search . '%')
                    ->orWhere('delivery_status', 'like', '%' . $search . '%');
            });
        }

        $orders = $query->paginate($perPage);
        $summaryQuery = Order::query()->where('seller_id', (int) $seller->id);

        return response()->json([
            'orders' => collect($orders->items())->map(function (Order $order) {
                return [
                    'id' => (int) $order->id,
                    'order_datetime' => $order->order_datetime,
                    'customer_name' => $order->customer_name,
                    'phone' => $order->phone ?: $order->additional_phone,
                    'waybill_no' => $order->waybill_no,
                    'status' => $order->status,
                    'delivery_status' => $order->delivery_status,
                    'payment_status' => $order->payment_status,
                    'total_collectable_amount' => (float) ($order->total_collectable_amount ?? 0),
                    'commission_amount' => (float) ($order->commission_amount ?? 0),
                    'items_count' => (int) ($order->items_count ?? 0),
                    'invoice_id' => $order->invoice_id ? (int) $order->invoice_id : null,
                ];
            })->values(),
            'summary' => [
                'total_orders' => (int) (clone $summaryQuery)->count(),
                'total_collectable_amount' => round((float) (clone $summaryQuery)->sum('total_collectable_amount'), 2),
                'total_commission_amount' => round((float) (clone $summaryQuery)->sum('commission_amount'), 2),
                'status_counts' => (clone $summaryQuery)
                    ->selectRaw('status, COUNT(*) as total')
                    ->groupBy('status')
                    ->pluck('total', 'status'),
            ],
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    public function profileInvoices(Request $request, Seller $seller)
    {
        $validated = $request->validate([
            'status' => ['nullable', 'in:all,draft,paid,cancelled'],
            'search' => ['nullable', 'string', 'max:120'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:100'],
        ]);

        $status = (string) ($validated['status'] ?? 'all');
        $search = trim((string) ($validated['search'] ?? ''));
        $perPage = (int) ($validated['per_page'] ?? 20);

        $query = Invoice::query()
            ->where('seller_id', (int) $seller->id)
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

        $invoices = $query->paginate($perPage);
        $summaryQuery = Invoice::query()->where('seller_id', (int) $seller->id);

        return response()->json([
            'invoices' => collect($invoices->items())->map(function (Invoice $invoice) {
                return [
                    'id' => (int) $invoice->id,
                    'invoice_date' => $invoice->invoice_date,
                    'invoice_time' => $invoice->invoice_time,
                    'total_commission_value' => (float) ($invoice->total_commission_value ?? 0),
                    'status' => (string) ($invoice->status ?? 'draft'),
                    'orders_count' => (int) ($invoice->orders_count ?? 0),
                    'created_at' => $invoice->created_at,
                ];
            })->values(),
            'summary' => [
                'invoice_count' => (int) (clone $summaryQuery)->count(),
                'total_commission_value' => round((float) (clone $summaryQuery)->sum('total_commission_value'), 2),
                'status_counts' => (clone $summaryQuery)
                    ->selectRaw('status, COUNT(*) as total')
                    ->groupBy('status')
                    ->pluck('total', 'status'),
            ],
            'meta' => [
                'current_page' => $invoices->currentPage(),
                'last_page' => $invoices->lastPage(),
                'per_page' => $invoices->perPage(),
                'total' => $invoices->total(),
            ],
        ]);
    }

    public function uploadImage(Request $request, Seller $seller)
    {
        $validated = $request->validate([
            'seller_image' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $newImagePath = $request->file('seller_image')->store('seller-documents/profile', 'public');

        if ($seller->seller_image) {
            Storage::disk('public')->delete($seller->seller_image);
        }

        $seller->update([
            'seller_image' => $newImagePath,
        ]);

        $seller->load(['businessInformation', 'level']);

        return response()->json([
            'message' => 'Seller image uploaded successfully.',
            'seller' => $seller,
        ]);
    }

    public function approvalOptions()
    {
        $levels = Level::orderBy('level_no')
            ->get(['id', 'level_no', 'level_name', 'points', 'is_default']);

        $defaultLevel = $levels->firstWhere('is_default', true);

        return response()->json([
            'levels' => $levels,
            'default_level_id' => $defaultLevel?->id,
        ]);
    }

    public function activeIndex(Request $request)
    {
        $search = $request->get('search');
        $perPage = (int) $request->get('per_page', 10);
        $perPage = max(1, min($perPage, 100));

        $query = Seller::query()->where('status', 'approved');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $sellers = $query->latest()->paginate($perPage);

        return response()->json([
            'sellers' => $sellers->items(),
            'pagination' => [
                'current_page' => $sellers->currentPage(),
                'last_page' => $sellers->lastPage(),
                'per_page' => $sellers->perPage(),
                'total' => $sellers->total(),
                'from' => $sellers->firstItem(),
                'to' => $sellers->lastItem(),
            ],
        ]);
    }

    public function blockActiveSeller(Request $request, Seller $seller)
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        if ($seller->status === 'blocked') {
            return response()->json([
                'message' => 'Seller is already blocked.',
            ], 422);
        }

        $seller->update([
            'status' => 'blocked',
            'rejection_reason' => $validated['reason'],
        ]);

        if ($seller->user_id) {
            User::where('id', $seller->user_id)->update([
                'is_blocked' => true,
                'blocked_reason' => $validated['reason'],
            ]);
        }

        $this->mailer->sendUserBlockedEmail(
            $seller->email,
            trim($seller->first_name . ' ' . $seller->last_name),
            $validated['reason']
        );

        return response()->json([
            'message' => 'Seller blocked successfully.',
        ]);
    }

    public function block(Request $request, Seller $seller)
    {
        return $this->blockActiveSeller($request, $seller);
    }

    public function unblock(Seller $seller)
    {
        if ($seller->status !== 'blocked') {
            return response()->json([
                'message' => 'Seller is not blocked.',
            ], 422);
        }

        $seller->update([
            'status' => 'approved',
            'rejection_reason' => null,
        ]);

        $unblockedUser = null;
        if ($seller->user_id) {
            User::where('id', $seller->user_id)->update([
                'is_blocked' => false,
                'blocked_reason' => null,
            ]);
            $unblockedUser = User::find($seller->user_id);
        }

        if ($unblockedUser) {
            $sent = $this->mailer->sendUserUnblockedEmail($unblockedUser->email, $unblockedUser->name);
            if (!$sent) {
                return response()->json([
                    'message' => 'Seller unblocked, but failed to send unblock notification email.',
                ], 500);
            }
        }

        return response()->json([
            'message' => 'Seller unblocked successfully.',
        ]);
    }

    public function approve(Request $request, Seller $seller)
    {
        if ($seller->status === 'approved' && $seller->user_id) {
            return response()->json([
                'message' => 'Seller is already approved.',
            ], 422);
        }

        $validated = $request->validate([
            'seller_level_id' => ['nullable', 'integer', 'exists:levels,id'],
            'points' => ['nullable', 'integer', 'min:0'],
        ]);

        $selectedLevel = null;
        if (!empty($validated['seller_level_id'])) {
            $selectedLevel = Level::find($validated['seller_level_id']);
        } else {
            $selectedLevel = Level::where('is_default', true)->first();
        }

        if (!$selectedLevel) {
            return response()->json([
                'message' => 'No default level found. Please set a default level or choose one explicitly.',
            ], 422);
        }

        $assignedPoints = array_key_exists('points', $validated) && $validated['points'] !== null
            ? (int) $validated['points']
            : (int) $selectedLevel->points;

        $password = Str::random(10);

        try {
            DB::transaction(function () use ($seller, $password, $selectedLevel, $assignedPoints) {
                $lockedSeller = Seller::query()
                    ->lockForUpdate()
                    ->findOrFail($seller->id);

                $sellerRole = Role::firstOrCreate([
                    'name' => 'Seller',
                    'guard_name' => 'web',
                ]);

                if ($lockedSeller->user_id) {
                    $user = User::query()
                        ->lockForUpdate()
                        ->findOrFail($lockedSeller->user_id);

                    if ($user->roles()->where('name', '!=', $sellerRole->name)->exists()) {
                        throw new DomainException('The linked user already has a different system role.');
                    }
                } else {
                    $user = User::query()
                        ->where('email', $lockedSeller->email)
                        ->lockForUpdate()
                        ->first();

                    if ($user) {
                        if ($user->roles()->exists()) {
                            throw new DomainException('A user with this seller email already has a system role.');
                        }

                        if ($user->seller()->whereKeyNot($lockedSeller->id)->exists()) {
                            throw new DomainException('A user with this seller email is already linked to another seller.');
                        }

                        if ($user->is_blocked) {
                            throw new DomainException('The existing user with this seller email is blocked.');
                        }
                    } else {
                        $user = User::create([
                            'name' => trim($lockedSeller->first_name.' '.$lockedSeller->last_name),
                            'email' => $lockedSeller->email,
                            'password' => Hash::make($password),
                        ]);
                    }
                }

                $user->update([
                    'name' => trim($lockedSeller->first_name.' '.$lockedSeller->last_name),
                    'email' => $lockedSeller->email,
                    'password' => Hash::make($password),
                ]);

                $user->syncRoles([$sellerRole->name]);

                $lockedSeller->update([
                    'user_id' => $user->id,
                    'seller_level_id' => $selectedLevel->id,
                    'points' => $assignedPoints,
                    'status' => 'approved',
                    'rejection_reason' => null,
                ]);

                $sent = $this->mailer->sendSellerOnboardingEmail(
                    $user->email,
                    $user->name,
                    $password,
                    $selectedLevel->level_name,
                    $assignedPoints
                );
                if (! $sent) {
                    throw new RuntimeException('Unable to send login details email right now.');
                }
            });
        } catch (DomainException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage() ?: 'Unable to approve seller right now.',
            ], 500);
        }

        return response()->json([
            'message' => 'Seller approved, user account activated, and login details emailed.',
        ]);
    }

    public function reject(Request $request, Seller $seller)
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $seller->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['reason'],
        ]);

        return response()->json([
            'message' => 'Seller rejected successfully.',
        ]);
    }
}
