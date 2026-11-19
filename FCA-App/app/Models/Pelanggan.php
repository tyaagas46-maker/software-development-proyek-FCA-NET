<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pelanggan extends Model
{
    use HasFactory;

    protected $table = 'pelanggan';

    protected $fillable = [
        'id_user',
        'nama',
        'no_hp',
        'alamat',
        'id_paket',
        'tarif_khusus',
        'status',
        'siklus_tagihan',
    ];

    protected function casts(): array
    {
        return [
            'tarif_khusus' => 'integer',
            'siklus_tagihan' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function paket(): BelongsTo
    {
        return $this->belongsTo(Paket::class, 'id_paket');
    }

    /**
     * Mengambil tarif efektif pelanggan:
     * jika ada tarif_khusus, pakai itu; jika null, pakai tarif_standar dari paket.
     * Pemakaian di Blade / Controller nanti tinggal: $pelanggan->tarif_aktif
     */
    public function getTarifAktifAttribute(): int
    {
        return $this->tarif_khusus ?? (int) $this->paket?->tarif_standar;
    }

    public function isAktif(): bool
    {
        return $this->status === 'aktif';
    }

    // Aktifkan relasi ini nanti setelah kamu membuat Model Tagihan:
    // public function tagihan()
    // {
    //     return $this->hasMany(Tagihan::class, 'id_pelanggan');
    // }
}