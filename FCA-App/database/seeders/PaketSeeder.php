<?php

namespace Database\Seeders;

use App\Models\Paket;
use Illuminate\Database\Seeder;

class PaketSeeder extends Seeder
{
    public function run(): void
    {
        $daftarPaket = [
            [
                'nama_paket' => 'Paket 20/25 Mbps',
                'kecepatan_mbps' => '20/25',
                'tarif_standar' => 75000,
            ],
            [
                'nama_paket' => 'Paket 25/35 Mbps',
                'kecepatan_mbps' => '25/35',
                'tarif_standar' => 100000,
            ],
            [
                'nama_paket' => 'Paket 35/50 Mbps',
                'kecepatan_mbps' => '35/50',
                'tarif_standar' => 130000,
            ],
        ];

        foreach ($daftarPaket as $paket) {
            Paket::updateOrCreate(
                ['kecepatan_mbps' => $paket['kecepatan_mbps']],
                $paket
            );
        }
    }
}