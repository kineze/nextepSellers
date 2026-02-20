<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Services\BrevoMailer;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function sysUsers(){


        
        return view('dashboards.admin.settings.users');
    }

    protected $mailer;

    public function __construct(BrevoMailer $mailer)
    {
        $this->mailer = $mailer;
    }
    
    public function index(Request $request)
    {
        $search = $request->get('search');
        $role = $request->get('role');
        $perPage = (int) $request->get('per_page', 10);
        $perPage = max(1, min($perPage, 100));

        $query = User::with('roles')
            ->whereDoesntHave('roles', fn($q) => $q->where('name', 'Seller'));

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role) {
            $query->whereHas('roles', fn($q) => $q->where('name', $role));
        }

        $users = $query->latest()->paginate($perPage);

        return response()->json([
            'users' => $users->items(),
            'pagination' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
                'from' => $users->firstItem(),
                'to' => $users->lastItem(),
            ],
            'roles' => Role::where('name', '!=', 'Seller')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'role'     => 'required|string|exists:roles,name',
            'password' => 'nullable|string|min:6',
        ]);

        // Use provided password or auto-generate
        $password = $validated['password'] ?? Str::random(10);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($password),
        ]);

        $user->assignRole($validated['role']);

        // Always email the password
        $this->mailer->sendWelcomeEmail($user->email, $user->name, $password);

        return response()->json(['message' => 'User created successfully']);
    }


    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => "required|email|unique:users,email,{$user->id}",
            'role'  => 'required|string|exists:roles,name',
        ]);

        $user->update([
            'name'  => $validated['name'],
            'email' => $validated['email'],
        ]);

        $user->syncRoles([$validated['role']]);

        return response()->json(['message' => 'User updated successfully']);
    }

    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(['message' => 'User deleted successfully']);
    }

    public function assignRole(Request $request, User $user)
    {
        $validated = $request->validate(['role' => 'required|string|exists:roles,name']);
        $user->syncRoles([$validated['role']]);
        return response()->json(['message' => 'Role updated successfully']);
    }

    public function resetPassword(User $user)
    {
        $password = Str::random(10);
        $user->update(['password' => Hash::make($password)]);

        $this->mailer->sendPasswordResetEmail($user->email, $user->name, $password);

        return response()->json(['message' => 'Password reset and emailed successfully']);
    }

    public function block(Request $request, User $user)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        if ($request->user() && (int) $request->user()->id === (int) $user->id) {
            return response()->json(['message' => 'You cannot block your own account.'], 422);
        }

        if ($user->is_blocked) {
            return response()->json(['message' => 'User is already blocked.'], 422);
        }

        $user->update([
            'is_blocked' => true,
            'blocked_reason' => $validated['reason'],
        ]);

        $sent = $this->mailer->sendUserBlockedEmail($user->email, $user->name, $validated['reason']);
        if (!$sent) {
            return response()->json([
                'message' => 'User blocked, but failed to send block notification email.',
            ], 500);
        }

        return response()->json(['message' => 'User blocked and notification email sent successfully.']);
    }

    public function unblock(User $user)
    {
        if (!$user->is_blocked) {
            return response()->json(['message' => 'User is not blocked.'], 422);
        }

        $user->update([
            'is_blocked' => false,
            'blocked_reason' => null,
        ]);

        $sent = $this->mailer->sendUserUnblockedEmail($user->email, $user->name);
        if (!$sent) {
            return response()->json([
                'message' => 'User unblocked, but failed to send unblock notification email.',
            ], 500);
        }

        return response()->json(['message' => 'User unblocked successfully.']);
    }
}
