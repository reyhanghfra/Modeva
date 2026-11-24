<?php

use App\Http\Controllers\Account\PasswordController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\UserController;
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

// Pratinjau Bukti Pembayaran (Dapat diakses oleh Admin & Petugas)
Route::middleware(['auth', 'role:admin,petugas'])
    ->get('/admin/payments/{payment}/proof', [PaymentController::class, 'proof'])
    ->name('admin.payments.proof');

// Area Dasbor Admin (Khusus Role Admin)
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Ringkasan
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Fitur 1: Data Pengguna
    Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::resource('users', UserController::class);

    // Fitur 2: Kategori Produk
    Route::resource('categories', CategoryController::class);

    // Fitur 3: Verifikasi Pembayaran
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
    Route::post('/payments/{payment}/verify', [PaymentController::class, 'verify'])->name('payments.verify');
    Route::post('/payments/{payment}/reject', [PaymentController::class, 'reject'])->name('payments.reject');

    // Fitur 4: Pengaturan / Ubah Password
    Route::get('/pengaturan/password', [PasswordController::class, 'edit'])->name('password.edit');
    Route::put('/pengaturan/password', [PasswordController::class, 'update'])
        ->middleware('throttle:5,1')
        ->name('password.update');
});

// Area Petugas
Route::middleware(['auth', 'role:petugas'])->prefix('petugas')->name('petugas.')->group(function () {
    Route::get('/dashboard', fn () => view('petugas.dashboard'))->name('dashboard');
});
