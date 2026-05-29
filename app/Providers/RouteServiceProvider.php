<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;

class RouteServiceProvider extends ServiceProvider
{
    protected $namespace = 'App\Http\Controllers';

    public function boot()
    {
        parent::boot();
        
        $this->configureRateLimiting();
        $this->registerModelBindings();
        
        $this->routes(function () {
            // API routes (existing)
            \Illuminate\Support\Facades\Route::prefix('api')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('routes/api.php'));

            // API routes (new modules - Vehicle, Inventory, Approval)
            \Illuminate\Support\Facades\Route::prefix('api')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('routes/api/new-modules.php'));
                
            // Web routes
            \Illuminate\Support\Facades\Route::middleware('web')
                ->namespace($this->namespace)
                ->group(base_path('routes/web.php'));
        });
    }
    
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return $request->user()
                ? Limit::perMinute(60)->by($request->user()->id)
                : Limit::perMinute(20)->by($request->ip());
        });
    }

    protected function registerModelBindings()
    {
        // Core models that still exist
        \Illuminate\Support\Facades\Route::model('user', \App\User::class);
        \Illuminate\Support\Facades\Route::model('location', \App\Location::class);
        \Illuminate\Support\Facades\Route::model('division', \App\Division::class);

        // New module models
        \Illuminate\Support\Facades\Route::model('vehicle', \App\Vehicle::class);
        \Illuminate\Support\Facades\Route::model('vehicleBooking', \App\VehicleBooking::class);
        \Illuminate\Support\Facades\Route::model('inventoryItem', \App\InventoryItem::class);
        \Illuminate\Support\Facades\Route::model('inventoryCategory', \App\InventoryCategory::class);
        \Illuminate\Support\Facades\Route::model('inventoryRequest', \App\InventoryRequest::class);
        \Illuminate\Support\Facades\Route::model('approvalRule', \App\ApprovalRule::class);
    }
}