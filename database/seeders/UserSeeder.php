<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('name', 'admin')->first();
        $employeeRole = Role::where('name', 'employee')->first();
        $citizenRole = Role::where('name', 'citizen')->first();

        User::create([
            'name' => 'مدير النظام',
            'email' => 'admin@gov.sy',
            'national_id' => '00000000001',
            'password' => Hash::make('password'),
            'role_id' => $adminRole->id,
        ]);

        User::create([
            'name' => 'موظف 1',
            'email' => 'emp1@gov.sy',
            'national_id' => '00000000002',
            'password' => Hash::make('password'),
            'role_id' => $employeeRole->id,
        ]);

        User::create([
            'name' => 'موظف 2',
            'email' => 'emp2@gov.sy',
            'national_id' => '00000000003',
            'password' => Hash::make('password'),
            'role_id' => $employeeRole->id,
        ]);

        for ($i = 1; $i <= 5; $i++) {
            User::create([
                'name' => "مواطن {$i}",
                'email' => "citizen{$i}@example.com",
                'national_id' => '1000000000'.$i,
                'password' => Hash::make('password'),
                'role_id' => $citizenRole->id,
            ]);
        }
    }
}
