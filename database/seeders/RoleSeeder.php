<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Role::firstOrCreate([
            'name' => 'Admin',
            'guard_name' => 'web',
        ]);

        $admin->syncPermissions([
            'Access Admin Dashboard',
            'Manage Settings',
            'Manage System Configuration',
            'Manage Sellers',
            'Manage Levels',
            'Manage Inventory',
            'Manage Dispatch Management',
        ]);
    }
}
