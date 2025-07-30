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
            'email'    => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole('admin');

        // Membuat Akun Pegawai
        $pegawai = User::create([
            'name'     => 'Pegawai',
            'email'    => 'pegawai@example.com',
            'password' => Hash::make('password'),
        ]);
        $pegawai->assignRole('pegawai');
    }
}
