<?php

use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])
    ->withoutMiddleware(Authenticate::class)
    ->name('home');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Admin Panel: requires being logged in (global `auth` middleware, see
// bootstrap/app.php) AND holding the admin role specifically (see the
// `admin` Gate in AppServiceProvider).
Route::resource('admin/users', AdminUserController::class)
    ->except(['show'])
    ->names('admin.users')
    ->middleware('can:admin');

require __DIR__.'/auth.php';
