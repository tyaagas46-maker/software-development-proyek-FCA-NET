<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'nama',
        'username',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isPelanggan(): bool
    {
        return $this->role === 'pelanggan';
    }

    // Aktifkan relasi ini nanti setelah kamu membuat Model Pelanggan:
    // public function pelanggan()
    // {
    //     return $this->hasOne(Pelanggan::class, 'id_user');
    // }
}