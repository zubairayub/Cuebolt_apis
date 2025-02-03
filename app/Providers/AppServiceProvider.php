<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

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
        

        // Define a gate to authorize user deletion
        Gate::define('delete-user', function (User $user, User $userToDelete) {
            // Check if the authenticated user has the required permission
            
            return $user->role_id === 2;  // Assuming only admins can delete users
        });
    }
}
