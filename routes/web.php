<?php

use App\Http\Controllers\Account\PasswordController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Petugas\DashboardController as PetugasDashboardController;
use App\Http\Controllers\Storefront\ProductController as StorefrontProductController;
use Illuminate\Support\Facades\Route;

// Beranda pelanggan (publik)
Route::view('/', 'pelanggan.beranda')->name('home');
Route::get('/products', [StorefrontProductController::class, 'index'])->name('products.index');
Route::get('/products/{product:slug}', [StorefrontProductController::class, 'show'])->name('products.show');

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
    Route::resource('products', ProductController::class)->except(['show']);
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::patch('/inventory/variants/{productVariant}/stock', [InventoryController::class, 'updateStock'])
        ->name('inventory.stock.update');

    // Fitur 3: Verifikasi Pembayaran
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
    Route::post('/payments/{payment}/verify', [PaymentController::class, 'verify'])->name('payments.verify');
    Route::post('/payments/{payment}/reject', [PaymentController::class, 'reject'])->name('payments.reject');

    // Fitur 4: Data Pelanggan
    Route::resource('customers', CustomerController::class);

    // Fitur 5: Data Transaksi
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/{order}', [TransactionController::class, 'show'])->name('transactions.show');

    // Fitur 6: Pengaturan / Ubah Password
    Route::get('/pengaturan/password', [PasswordController::class, 'edit'])->name('password.edit');
    Route::put('/pengaturan/password', [PasswordController::class, 'update'])
        ->middleware('throttle:5,1')
        ->name('password.update');

    // Fitur 7: Laporan Penjualan
    Route::get('/laporan', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/laporan/ekspor', [ReportController::class, 'export'])->name('reports.export');
});

// Area Petugas (Khusus Role Petugas)
Route::middleware(['auth', 'role:petugas'])->prefix('petugas')->name('petugas.')->group(function () {
    Route::get('/dashboard', [PetugasDashboardController::class, 'index'])->name('dashboard');
});
