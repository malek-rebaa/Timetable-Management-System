<?php

namespace Database\Seeders;

use App\Models\SystemRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OwnerSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'owner@timetable-app.com'],
            [
                'first_name' => 'System',
                'last_name' => 'Owner',
                'password' => Hash::make('Owner@dmin2024!'),
                'role' => 'OWNER',
                'is_active' => 1,
            ]
        );

        $ownerRole = SystemRole::firstOrCreate(['code' => 'OWNER']);
        $user->systemRoles()->syncWithoutDetaching([$ownerRole->getKey()]);
    }
}