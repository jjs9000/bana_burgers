<?php

use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Pos\PosIndex;
use App\Livewire\Pos\PosOrderHistory;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');

    // POS System Routes
    Route::get('pos', PosIndex::class)->name('pos.index');
    Route::get('pos/history', PosOrderHistory::class)->name('pos.history');

    Route::middleware(['auth'])->prefix('admin')->group(function () {
        Route::get('/products', \App\Livewire\Admin\ProductManager::class)->name('admin.products');
    });
});

require __DIR__ . '/auth.php';
