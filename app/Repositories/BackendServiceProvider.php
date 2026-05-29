<?php

namespace App\Repositories;

use Illuminate\Support\ServiceProvider;

class BackendServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(
            'App\Repositories\Vehicles\VehicleRepositoryInterface',
            'App\Repositories\Vehicles\VehicleRepository'
        );

        $this->app->bind(
            'App\Repositories\Users\UserRepositoryInterface',
            'App\Repositories\Users\UserRepository'
        );

        $this->app->bind(
            'App\Repositories\Portal\MainPortalRepositoryInterface',
            'App\Repositories\Portal\MainPortalRepository'
        );
    }
}
