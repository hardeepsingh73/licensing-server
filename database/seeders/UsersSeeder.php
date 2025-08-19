<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates default Admin and User accounts and assigns roles to them.
     */
    public function run(): void
    {
        // Ensure minimal roles exist before assigning
        $this->ensureRolesExist();

        // ------- SuperAdmin Account -------
        $superadmin = User::firstOrCreate(
            ['email' => 'superadmin@licensing.com'],
            [
                'name' => 'SuperAdmin User',
                'password' => Hash::make('password123!'),
                'email_verified_at' => now(),
            ]
        );

        $superadmin->syncRoles('superadmin');
        $this->command->info(' Default superadmin account seeded successfully.');
    }

    /**
     * Ensure required roles exist before user creation.
     *
     * @return void
     */
    protected function ensureRolesExist(): void
    {
        $requiredRoles = ['admin', 'user'];

        foreach ($requiredRoles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }
    }
}
