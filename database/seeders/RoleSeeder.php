<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Role::create(['name' => 'Admin']);
        
        $admin->givePermissionTo('Access Admin Dashboard');
        $admin->givePermissionTo('Manage Settings');
        $admin->givePermissionTo('Manage Sellers');
    }
}
