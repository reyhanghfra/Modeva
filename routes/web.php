<?php

use App\Http\Controllers\Account\PasswordController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Admin\PhotoController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Customer\DashboardController as CustomerDashboardController;
use App\Http\Controllers\Pelanggan\PaymentController as PelangganPaymentController;
use App\Http\Controllers\Petugas\DashboardController as PetugasDashboardController;
use App\Models\Order;
use Illuminate\Support\Facades\Route;

// Binding rute eksplisit untuk {order}
Route::bind('order', function ($value, $route) {
    if ($route->named('pelanggan.payments.*')) {
        if (! auth()->check()) {
            return is_numeric($value)
                ? Order::find($value)
                : Order::where('order_number', $value)->first();
        }

        // Pada route bukti pembayaran, izinkan resolusi order agar PaymentPolicy::viewProof
        // dapat memverifikasi otorisasi dan mengembalikan 403 untuk pelanggan lain
        if ($route->named('pelanggan.payments.proof')) {
            return is_numeric($value)
                ? Order::findOrFail($value)
                : Order::where('order_number', $value)->firstOrFail();
        }

        // Admin dan petugas diizinkan mengakses konteks pesanan
        if (in_array(auth()->user()->role, ['admin', 'petugas'], true)) {
            return is_numeric($value)
                ? Order::findOrFail($value)
                : Order::where('order_number', $value)->firstOrFail();
        }

        // Khusus pelanggan, pesanan dibatasi hanya milik user login (pesanan orang lain => 404)
        return Order::where(function ($q) use ($value) {
                $q->where('order_number', $value)
                  ->orWhere('id', $value);
            })
            ->where('user_id', auth()->id())
            ->firstOrFail();
    }

    return is_numeric($value)
        ? Order::findOrFail($value)
        : Order::where('order_number', $value)->firstOrFail();
});

// Beranda pelanggan (publik)
Route::get('/', [HomeController::class, 'index'])->name('home');

// Autentikasi Pengguna Tamu (Guest)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');

    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('register.store');
});

// Logout Pengguna Terautentikasi
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Dasbor Pelanggan
Route::middleware(['auth', 'role:pelanggan'])
    ->get('/akun', [CustomerDashboardController::class, 'index'])
    ->name('pelanggan.dashboard');

// Area Pembayaran Pelanggan (Transfer & Bukti Pembayaran)
Route::prefix('pesanan/{order}/pembayaran')->name('pelanggan.payments.')->group(function () {
    // Halaman dan formulir transfer khusus pelanggan pemilik pesanan
    Route::middleware(['auth', 'role:pelanggan'])->group(function () {
        Route::get('/', [PelangganPaymentController::class, 'show'])->name('show');
        Route::get('/upload', [PelangganPaymentController::class, 'upload'])->name('upload');
        Route::post('/upload', [PelangganPaymentController::class, 'store'])
            ->middleware('throttle:10,1')
            ->name('store');
    });

    // Pratinjau Bukti Pembayaran (Diotorisasi via PaymentPolicy: Pemilik Pesanan, Admin, Petugas)
    Route::middleware('auth')
        ->get('/bukti/{payment}', [PelangganPaymentController::class, 'proof'])
        ->name('proof');
});

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

    // Foto Situs (hero, promo, kategori, produk)
    Route::get('/foto', [PhotoController::class, 'edit'])->name('photos.edit');
    Route::post('/foto', [PhotoController::class, 'update'])->name('photos.update');
});

// Area Petugas (Khusus Role Petugas)
Route::middleware(['auth', 'role:petugas'])->prefix('petugas')->name('petugas.')->group(function () {
    Route::get('/dashboard', [PetugasDashboardController::class, 'index'])->name('dashboard');
});
