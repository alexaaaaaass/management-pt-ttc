<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\Gate::before(function ($user, string $ability) {
            if (! $user) {
                return null;
            }
            try {
                if (method_exists($user, 'hasRole')) {
                    if ($user->hasRole('super_admin')) {
                        return true;
                    }
                    if ($user->roles()->count() === 0 || \Spatie\Permission\Models\Permission::count() === 0) {
                        return true;
                    }
                }
            } catch (\Throwable $e) {
                return true;
            }

            return null;
        });
    }
}
