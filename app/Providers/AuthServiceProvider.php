<?php

namespace App\Providers;

use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
        'App\Asset' => 'App\Policies\AssetPolicy',
        'App\Ticket' => 'App\Policies\TicketPolicy',
        'App\User' => 'App\Policies\UserPolicy',
        'App\AssetRequest' => 'App\Policies\AssetRequestPolicy',
        'App\AuditLog' => 'App\Policies\AuditLogPolicy',
        'App\Role' => 'App\Policies\RolePolicy',
        \App\DailyActivity::class => \App\Policies\DailyActivityPolicy::class,
    ];

    /**
     * Register any application authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //
    }
}
