<?php

use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\EntryController;
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

// Entries: the list is open to any authenticated role (scoped to their own
// entries for customers, see EntryController::index). Creating an entry
// requires the `create-entry` Gate (customer only); editing/deleting
// requires the `staff` Gate (technician or admin) — both defined in
// AppServiceProvider. There is no standalone picture-delete route —
// removing a picture is staged client-side in the edit form and only
// takes effect when `update` actually saves (see EntryController::update).
Route::prefix('entries')->name('entries.')->group(function () {
    Route::get('/', [EntryController::class, 'index'])->name('index');
    Route::get('/create', [EntryController::class, 'create'])->middleware('can:create-entry')->name('create');
    Route::post('/', [EntryController::class, 'store'])->middleware('can:create-entry')->name('store');
    Route::get('/{entry}/edit', [EntryController::class, 'edit'])->middleware('can:staff')->name('edit');
    Route::put('/{entry}/edit', [EntryController::class, 'update'])->middleware('can:staff')->name('update');
    Route::delete('/{entry}', [EntryController::class, 'destroy'])->middleware('can:staff')->name('destroy');
});

require __DIR__.'/auth.php';
