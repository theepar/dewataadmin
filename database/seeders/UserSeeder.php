<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Membuat Akun Admin
        $admin = User::create([
            'name'     => 'Admin',
            'email'    => 'dewatamanagements@gmail.com',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole('admin');

        // Membuat Akun User
        $user = User::create([
            'name'     => 'Deva',
            'email'    => 'tepargun@gmail.com',
            'password' => Hash::make('password'),
        ]);
        $user->assignRole('user');
    }
}
