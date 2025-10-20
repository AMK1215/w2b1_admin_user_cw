<?php

namespace App\Providers;

use App\Models\Admin\Permission;
use Illuminate\Support\Facades\Gate;
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
        $this->registerPolicies();

        // Dynamically register all permissions as Gates
        try {
            Permission::all()->each(function ($permission) {
                Gate::define($permission->title, function ($user) use ($permission) {
                    return $user->roles()
                        ->whereHas('permissions', function ($query) use ($permission) {
                            $query->where('permissions.id', $permission->id);
                        })
                        ->exists();
                });
            });
        } catch (\Exception $e) {
            // Handle case where permissions table doesn't exist yet (during initial migration)
            \Log::warning('Could not load permissions: ' . $e->getMessage());
        }
    }
}
