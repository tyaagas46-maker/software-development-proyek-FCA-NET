<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Akun Admin
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'nama' => 'Admin FCA Net',
                'password' => Hash::make('admin123'), // Ubah sebelum aplikasi dipakai live
                'role' => 'admin',
            ]
        );

        // Akun dummy pelanggan (untuk tes login sebelum fitur registrasi jadi)
        User::updateOrCreate(
            ['username' => '081234567890'],
            [
                'nama' => 'Pelanggan Uji Coba',
                'password' => Hash::make('12345678'),
                'role' => 'pelanggan',
            ]
        );
    }
}