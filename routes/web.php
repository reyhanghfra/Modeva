<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Auth
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Placeholder dashboards (nanti diganti dengan middleware)
Route::get('/admin/dashboard', fn() => view('admin.dashboard'));
Route::get('/petugas/dashboard', fn() => view('petugas.dashboard'));
Route::get('/', fn() => view('pelanggan.beranda'));