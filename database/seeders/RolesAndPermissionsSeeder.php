<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * This seeds:
     * - All application permissions
     * - Superadmin (all permissions), Admin (restricted), User (basic)
     *
     * @return void
     */
    public function run(): void
    {
        // Reset cached roles & permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        DB::transaction(function () {
            // ----------------------
            //  Create Permissions
            // ----------------------
            $permissions = [
                // User management
                'create users',
                'delete users',
                'edit users',
                'view users',

                // Role management
                'create roles',
                'delete roles',
                'edit roles',
                'view roles',

                // Permission management
                'create permissions',
                'delete permissions',
                'edit permissions',
                'view permissions',

                // Settings
                'create settings',
                'delete settings',
                'edit settings',
                'view settings',

                // Logs
                'view activity logs',
                'clear activity logs',
                'view error logs',
                'clear error logs',
                'view login history',
                'clear login history',
                'view email logs',
                'clear email logs',
                'view api logs',
                'clear api logs',

                // Basic
                'view dashboard',
            ];

            foreach ($permissions as $permission) {
                Permission::firstOrCreate(['name' => $permission]);
            }

            // ----------------------
            //  Create Roles
            // ----------------------

            // Super Admin - all permissions
            $superadmin = Role::firstOrCreate(['name' => 'superadmin']);
            $superadmin->syncPermissions(Permission::all());
        });

        $this->command->info(' Roles and permissions seeded successfully.');
    }
}
