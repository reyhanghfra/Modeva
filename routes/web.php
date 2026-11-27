<?php

use App\Http\Controllers\Account\PasswordController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Pelanggan\CartController;
use App\Http\Controllers\Pelanggan\CheckoutController;
use App\Http\Controllers\Pelanggan\CustomerAddressController;
use App\Http\Controllers\Pelanggan\ProfileController;
use App\Http\Controllers\Petugas\DashboardController as PetugasDashboardController;
use App\Http\Controllers\Petugas\TransactionController as PetugasTransactionController;
use Illuminate\Support\Facades\Route;

// Beranda pelanggan (publik)
Route::get('/', fn () => view('pelanggan.beranda'))->name('home');

// Keranjang Belanja Pelanggan (Khusus Role Pelanggan)
Route::middleware(['auth', 'role:pelanggan'])->prefix('keranjang')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/', [CartController::class, 'store'])->name('store');
    Route::patch('/{cartItem}', [CartController::class, 'update'])->name('update');
    Route::delete('/{cartItem}', [CartController::class, 'destroy'])->name('destroy');
});

// Checkout Pelanggan (Khusus Role Pelanggan)
Route::middleware(['auth', 'role:pelanggan'])->prefix('checkout')->name('checkout.')->group(function () {
    Route::get('/', [CheckoutController::class, 'index'])->name('index');
    Route::post('/', [CheckoutController::class, 'store'])->name('store');
    Route::get('/sukses/{order}', [CheckoutController::class, 'success'])->name('success');
});

// Profil & Alamat Pelanggan (Khusus Role Pelanggan)
Route::middleware(['auth', 'role:pelanggan'])->prefix('profil')->name('profile.')->group(function () {
    Route::get('/', [ProfileController::class, 'index'])->name('index');
    Route::put('/', [ProfileController::class, 'update'])->name('update');

    Route::get('/alamat/tambah', [CustomerAddressController::class, 'create'])->name('addresses.create');
    Route::post('/alamat', [CustomerAddressController::class, 'store'])->name('addresses.store');
    Route::get('/alamat/{address}/edit', [CustomerAddressController::class, 'edit'])->name('addresses.edit');
    Route::put('/alamat/{address}', [CustomerAddressController::class, 'update'])->name('addresses.update');
    Route::patch('/alamat/{address}/utama', [CustomerAddressController::class, 'setPrimary'])->name('addresses.primary');
    Route::delete('/alamat/{address}', [CustomerAddressController::class, 'destroy'])->name('addresses.destroy');
});

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
    Route::get('/transactions', [PetugasTransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/{order}', [PetugasTransactionController::class, 'show'])->name('transactions.show');
});
