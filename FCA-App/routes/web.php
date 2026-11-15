<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

// Autentikasi
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:10,1') // Batasi percobaan login max 10x per menit
    ->name('login.process');
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// Route khusus Admin
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', function () {
            return 'Login berhasil sebagai Admin: ' . auth()->user()->nama;
            // Nanti diganti Controller / view('admin.dashboard')
        })->name('dashboard');
    });

// Route khusus Pelanggan
Route::middleware(['auth', 'role:pelanggan'])
    ->prefix('pelanggan')
    ->name('pelanggan.')
    ->group(function () {
        Route::get('/dashboard', function () {
            return 'Login berhasil sebagai Pelanggan: ' . auth()->user()->nama;
            // Nanti diganti Controller / view('pelanggan.dashboard')
        })->name('dashboard');
    });