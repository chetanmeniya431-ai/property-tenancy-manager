<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\Roles;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /** @return array<string, User> keyed by role */
    public function run(): array
    {
        $users = [];

        $superAdmin = User::updateOrCreate(
            ['email' => 'superadmin@gmail.com'],
            ['name' => 'Super Admin', 'password' => Hash::make('nm@2001')]
        );
        $superAdmin->syncRoles([Roles::SUPER_ADMIN]);

        $users[Roles::OWNER] = User::updateOrCreate(
            ['email' => 'admin@propertymanager.local'],
            ['name' => 'Helena Hartwell', 'password' => 'password']
        );
        $users[Roles::OWNER]->syncRoles([Roles::OWNER]);

        $users[Roles::MANAGER] = User::updateOrCreate(
            ['email' => 'manager@propertymanager.local'],
            ['name' => 'Priya Manager', 'password' => 'password']
        );
        $users[Roles::MANAGER]->syncRoles([Roles::MANAGER]);

        $users[Roles::MAINTENANCE_COORDINATOR] = User::updateOrCreate(
            ['email' => 'maintenance@propertymanager.local'],
            ['name' => 'Sam Coordinator', 'password' => 'password']
        );
        $users[Roles::MAINTENANCE_COORDINATOR]->syncRoles([Roles::MAINTENANCE_COORDINATOR]);

        $users[Roles::CONTRACTOR] = User::updateOrCreate(
            ['email' => 'contractor@propertymanager.local'],
            ['name' => 'Dave the Contractor', 'password' => 'password']
        );
        $users[Roles::CONTRACTOR]->syncRoles([Roles::CONTRACTOR]);

        $users[Roles::TENANT] = User::updateOrCreate(
            ['email' => 'tenant@propertymanager.local'],
            ['name' => 'Taylor Tenant', 'password' => 'password']
        );
        $users[Roles::TENANT]->syncRoles([Roles::TENANT]);

        return $users;
    }
}
