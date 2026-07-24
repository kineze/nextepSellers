<?php

namespace Tests\Feature;

use App\Models\Level;
use App\Models\Seller;
use App\Models\User;
use App\Services\BrevoMailer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SellerApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_approval_reuses_matching_roleless_user(): void
    {
        $level = $this->createDefaultLevel();
        $seller = $this->createPendingSeller('seller@example.com');
        $existingUser = User::factory()->create([
            'email' => $seller->email,
        ]);
        $this->authenticateSellerManager();
        $userCountBeforeApproval = User::count();

        $this->mock(BrevoMailer::class)
            ->shouldReceive('sendSellerOnboardingEmail')
            ->once()
            ->withArgs(fn (
                string $email,
                string $name,
                string $password,
                string $levelName,
                int $points
            ) => $email === $seller->email
                && $name === 'Pending Seller'
                && $password !== ''
                && $levelName === $level->level_name
                && $points === $level->points)
            ->andReturnTrue();

        $response = $this->postJson("/api/sellers/{$seller->id}/approve", [
            'seller_level_id' => $level->id,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('message', 'Seller approved, user account activated, and login details emailed.');

        $seller->refresh();
        $existingUser->refresh();

        $this->assertSame($existingUser->id, $seller->user_id);
        $this->assertSame('approved', $seller->status);
        $this->assertTrue($existingUser->hasRole('Seller'));
        $this->assertDatabaseCount('users', $userCountBeforeApproval);
    }

    public function test_approval_rejects_matching_user_with_another_role(): void
    {
        $level = $this->createDefaultLevel();
        $seller = $this->createPendingSeller('staff@example.com');
        $existingUser = User::factory()->create([
            'email' => $seller->email,
        ]);
        $existingUser->assignRole(Role::create([
            'name' => 'Admin',
            'guard_name' => 'web',
        ]));
        $this->authenticateSellerManager();

        $this->mock(BrevoMailer::class)
            ->shouldNotReceive('sendSellerOnboardingEmail');

        $response = $this->postJson("/api/sellers/{$seller->id}/approve", [
            'seller_level_id' => $level->id,
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonPath('message', 'A user with this seller email already has a system role.');

        $seller->refresh();
        $existingUser->refresh();

        $this->assertNull($seller->user_id);
        $this->assertSame('pending', $seller->status);
        $this->assertTrue($existingUser->hasRole('Admin'));
        $this->assertFalse($existingUser->hasRole('Seller'));
    }

    private function createDefaultLevel(): Level
    {
        return Level::create([
            'level_no' => 1,
            'level_name' => 'Starter',
            'points' => 100,
            'is_default' => true,
        ]);
    }

    private function authenticateSellerManager(): void
    {
        $permission = Permission::create([
            'name' => 'Manage Sellers',
            'guard_name' => 'web',
        ]);
        $user = User::factory()->create();
        $user->givePermissionTo($permission);

        $this->actingAs($user);
    }

    private function createPendingSeller(string $email): Seller
    {
        return Seller::create([
            'first_name' => 'Pending',
            'last_name' => 'Seller',
            'email' => $email,
            'seller_type' => 'individual',
            'status' => 'pending',
        ]);
    }
}
