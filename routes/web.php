<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\Api\PemasukanController;
use App\Http\Controllers\Api\PengeluaranController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\SettingsController;

// Redirect root ke dashboard
Route::get('/', function () {
    return redirect()->route('dashboard');
});



// Halaman Auth (guest only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Logout (hanya untuk user login)
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Halaman Protected (harus login)
Route::middleware(['auth'])->group(function () {
    // Views
    Route::get('/dashboard', [PageController::class, 'dashboard'])->name('dashboard');
    Route::get('/pemasukan', [PageController::class, 'pemasukan'])->name('pemasukan.view');
    Route::get('/pengeluaran', [PageController::class, 'pengeluaran'])->name('pengeluaran.view');
    Route::get('/setting', [PageController::class, 'settings'])->name('settings.view');
    
    // Dashboard API
    Route::get('/dashboard/data', [DashboardController::class, 'index']);
    
    // Pemasukan API
    Route::get('/api/pemasukan', [PemasukanController::class, 'index']);
    Route::post('/api/pemasukan', [PemasukanController::class, 'store']);
    Route::get('/api/pemasukan/{id}', [PemasukanController::class, 'show']);
    Route::put('/api/pemasukan/{id}', [PemasukanController::class, 'update']);
    Route::delete('/api/pemasukan/{id}', [PemasukanController::class, 'destroy']);
    
    // Pengeluaran API
    Route::get('/api/pengeluaran', [PengeluaranController::class, 'index']);
    Route::post('/api/pengeluaran', [PengeluaranController::class, 'store']);
    Route::get('/api/pengeluaran/{id}', [PengeluaranController::class, 'show']);
    Route::put('/api/pengeluaran/{id}', [PengeluaranController::class, 'update']);
    Route::delete('/api/pengeluaran/{id}', [PengeluaranController::class, 'destroy']);
    
    // Laporan
    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/{tahun}/{bulan}', [LaporanController::class, 'show'])->name('laporan.show');


Route::middleware(['auth'])->group(function () {
    // Settings (gabungan wallet + saving goals)
    Route::prefix('settings')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('settings.index');

        // Wallet
        Route::post('/wallets', [SettingsController::class, 'createWallet']);
        Route::put('/wallets/{id}', [SettingsController::class, 'updateWallet']);
        Route::delete('/wallets/{id}', [SettingsController::class, 'deleteWallet']);
        Route::put('/wallets/{id}/activate', [SettingsController::class, 'setActiveWallet']);
        Route::get('/wallet/active', [SettingsController::class, 'active']);


        // Saving Goals
        Route::post('/saving-goals', [SettingsController::class, 'saveTarget']);
        Route::delete('/saving-goals', [SettingsController::class, 'deleteTarget']);

        // Notifications & Threshold
        Route::put('/notifications', [SettingsController::class, 'updateNotifications']);
        Route::put('/abnormal-threshold', [SettingsController::class, 'updateAbnormalThreshold']);
    });
});

});