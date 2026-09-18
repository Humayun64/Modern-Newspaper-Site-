<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@amardesh24.news'],
            [
                'name'        => 'Admin',
                'name_bn'     => 'অ্যাডমিন',
                'password'    => 'ChangeMe123!',   // hashed by the model cast
                'role'        => 'admin',
                'designation' => 'সম্পাদক',
                'is_active'   => true,
            ]
        );
    }
}
