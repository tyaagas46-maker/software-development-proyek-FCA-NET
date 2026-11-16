<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Paket extends Model
{
    use HasFactory;

    protected $table = 'paket';

    protected $fillable = [
        'nama_paket',
        'kecepatan_mbps',
        'tarif_standar',
    ];

    protected function casts(): array
    {
        return [
            'tarif_standar' => 'integer',
        ];
    }

    public function pelanggan(): HasMany
    {
        return $this->hasMany(Pelanggan::class, 'id_paket');
    }
}