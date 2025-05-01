<?php

namespace App\Providers;

use App\Policies\PermissionPolicy;
use App\Policies\RolePolicy;
use Filament\Facades\Filament;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(Permission::class, PermissionPolicy::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //

        // Filament::registerScripts([
        //     asset('js/my-script.js'),
        // ]);

        // Filament::registerStyles([
        //     // 'https://unpkg.com/tippy.js@6/dist/tippy.css',
        //     asset('resources/css/app.css'),
        // ]);
        FilamentAsset::register([
            // asset('resources/css/app.css'), // Register your custom CSS file
        ], 'app'); // Unique namespace

    }
}
