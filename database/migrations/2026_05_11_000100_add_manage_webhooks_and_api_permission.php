<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $permission = Permission::firstOrCreate([
            'name' => 'Manage Webhooks and API',
            'guard_name' => 'web',
        ]);

        $admin = Role::query()
            ->where('name', 'Admin')
            ->where('guard_name', 'web')
            ->first();

        if ($admin) {
            $admin->givePermissionTo($permission);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $permission = Permission::query()
            ->where('name', 'Manage Webhooks and API')
            ->where('guard_name', 'web')
            ->first();

        if (!$permission) {
            return;
        }

        Role::query()
            ->where('guard_name', 'web')
            ->get()
            ->each(fn (Role $role) => $role->revokePermissionTo($permission));

        $permission->delete();
    }
};
