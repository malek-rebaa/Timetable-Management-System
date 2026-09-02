<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\SystemRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'superadmin@timetable-app.com'],
            [
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'password' => Hash::make('Super@dmin2024!'),
                'role' => 'SUPER_ADMIN',
                'is_active' => 1,
            ]
        );

        $superAdminRole = SystemRole::firstOrCreate(['code' => 'SUPER_ADMIN']);
        $user->systemRoles()->syncWithoutDetaching([$superAdminRole->getKey()]);

        $this->command->info('Super Admin account verified/created: superadmin@timetable-app.com');
    }
}
