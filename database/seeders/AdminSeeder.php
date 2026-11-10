<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@unuja.ac.id'],
            [
                'name' => 'Admin Naja UNUJA',
                'password' => 'admin12345',
            ]
        );
    }
}
