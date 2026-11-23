<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Beranda pelanggan (publik)
Route::get('/', fn () => view('pelanggan.beranda'))->name('home');

// Autentikasi Pengguna Tamu (Guest)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

// Logout Pengguna Terautentikasi
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Area Admin
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', fn () => view('admin.dashboard'))->name('dashboard');
});

// Area Petugas
Route::middleware(['auth', 'role:petugas'])->prefix('petugas')->name('petugas.')->group(function () {
    Route::get('/dashboard', fn () => view('petugas.dashboard'))->name('dashboard');
});
