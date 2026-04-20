<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'lajadelabs@gmail.com'],
            [
                'name'     => 'Ayodeji Alalade',
                'password' => Hash::make('Securepass123!'),
            ]
        );

        $this->command->info('Admin user created/updated.');
    }
}