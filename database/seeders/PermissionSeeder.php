<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    
    public function run(): void
    {
        $permissions = [
            'Manage Settings',
            'Manage System Configuration',
            'Manage Sellers',
            'Manage Levels',
            'Access Admin Dashboard',
            'Manage Inventory',
            'Manage Dispatch Management',
            'Manage Finance',
            'Manage Learning',
            'Manage Webhooks and API',
            'View Reports',
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName]);
        }
    }
}
