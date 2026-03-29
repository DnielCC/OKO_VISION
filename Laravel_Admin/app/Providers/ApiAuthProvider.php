<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

class ApiAuthProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Reemplazar el EloquentUserProvider con nuestro proveedor personalizado
        Auth::provider('users', ApiUserProvider::class);
        
        // Definir gates para permisos
        Gate::define('admin', function ($user) {
            return $user && $user->isAdmin();
        });
        
        Gate::define('manage-users', function ($user) {
            return $user && $user->isAdmin();
        });
    }
}
