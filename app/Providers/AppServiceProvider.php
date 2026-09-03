<?php

namespace App\Providers;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('admin', fn (?User $user): bool => $user?->role === UserRole::Admin);

        // Entries may only be edited/deleted by technicians or the admin —
        // customers can view their own entries but never modify them.
        Gate::define('staff', fn (?User $user): bool => in_array($user?->role, [UserRole::Technician, UserRole::Admin], true));

        // Only customers may create an entry — staff manage entries but
        // don't file them.
        Gate::define('create-entry', fn (?User $user): bool => $user?->role === UserRole::Customer);
    }
}
