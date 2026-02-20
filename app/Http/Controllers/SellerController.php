<?php

namespace App\Http\Controllers;

use App\Models\Seller;
use App\Models\User;
use RuntimeException;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Services\BrevoMailer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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
        $seller->load('businessInformation');

        return response()->json($seller);
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

        if ($seller->user_id) {
            User::where('id', $seller->user_id)->update([
                'is_blocked' => false,
                'blocked_reason' => null,
            ]);
        }

        return response()->json([
            'message' => 'Seller unblocked successfully.',
        ]);
    }

    public function approve(Seller $seller)
    {
        if ($seller->status === 'approved' && $seller->user_id) {
            return response()->json([
                'message' => 'Seller is already approved.',
            ], 422);
        }

        if (User::where('email', $seller->email)->exists() && !$seller->user_id) {
            return response()->json([
                'message' => 'A user with this seller email already exists.',
            ], 422);
        }

        $password = Str::random(10);

        try {
            DB::transaction(function () use ($seller, $password) {
            $sellerRole = Role::firstOrCreate([
                'name' => 'Seller',
                'guard_name' => 'web',
            ]);

            if ($seller->user_id) {
                $user = User::findOrFail($seller->user_id);
                $user->update([
                    'name' => trim($seller->first_name . ' ' . $seller->last_name),
                    'email' => $seller->email,
                    'password' => Hash::make($password),
                ]);
            } else {
                $user = User::create([
                    'name' => trim($seller->first_name . ' ' . $seller->last_name),
                    'email' => $seller->email,
                    'password' => Hash::make($password),
                ]);
            }

            $user->syncRoles([$sellerRole->name]);

            $seller->update([
                'user_id' => $user->id,
                'status' => 'approved',
                'rejection_reason' => null,
            ]);

            $sent = $this->mailer->sendSellerOnboardingEmail($user->email, $user->name, $password);
            if (!$sent) {
                throw new RuntimeException('Unable to send login details email right now.');
            }
            });
        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage() ?: 'Unable to approve seller right now.',
            ], 500);
        }

        return response()->json([
            'message' => 'Seller approved, user account created, and login details emailed.',
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
