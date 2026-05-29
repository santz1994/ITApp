<?php

namespace App\Repositories;

use Illuminate\Support\ServiceProvider;

class BackendServiceProvider extends ServiceProvider {
  public function register()
  {
    // Vehicle Management Module
    $this->app->bind(
      'App\Repositories\Vehicles\VehicleRepositoryInterface',
      'App\Repositories\Vehicles\VehicleRepository'
    );
  }
}