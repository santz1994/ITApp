<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        'App\User' => 'App\Policies\UserPolicy',
        'App\AuditLog' => 'App\Policies\AuditLogPolicy',
        'App\Role' => 'App\Policies\RolePolicy',
    ];

    public function boot()
    {
        $this->registerPolicies();
    }
}
