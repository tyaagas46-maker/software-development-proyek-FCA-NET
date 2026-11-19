<?php

namespace Database\Seeders;

use App\Models\Paket;
use App\Models\Pelanggan;
use Illuminate\Database\Seeder;

class PelangganSeeder extends Seeder
{
    public function run(): void
    {
        $paketDasar = Paket::where('kecepatan_mbps', '20/25')->first();
        $paketMenengah = Paket::where('kecepatan_mbps', '25/35')->first();

        if (! $paketDasar || ! $paketMenengah) {
            return;
        }

        $samples = [
            [
                'nama' => 'Budi Santoso',
                'no_hp' => '085711112222',
                'alamat' => 'Dusun Karangpakis RT 01 / RW 02',
                'id_paket' => $paketDasar->id,
                'tarif_khusus' => null,
                'status' => 'aktif',
                'siklus_tagihan' => 5,
            ],
            [
                'nama' => 'Siti Aminah',
                'no_hp' => '085733334444',
                'alamat' => 'Dusun Karangpakis RT 03 / RW 01',
                'id_paket' => $paketMenengah->id,
                'tarif_khusus' => 90000, // Contoh tarif khusus
                'status' => 'aktif',
                'siklus_tagihan' => 21,
            ],
        ];

        foreach ($samples as $data) {
            Pelanggan::updateOrCreate(
                ['no_hp' => $data['no_hp']],
                $data
            );
        }
    }
}