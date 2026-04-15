<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to the controller routes in your routes file.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
        
        // Configure rate limiting
        $this->configureRateLimiting();
        
        // Define model bindings
        $this->registerModelBindings();
        
        // Register the application's routes. In newer Laravel versions
        // route registration happens during `boot()` via $this->routes().
        $this->routes(function () {
            // API routes
            \Illuminate\Support\Facades\Route::prefix('api')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('routes/api.php'));
                
            // Web routes
            \Illuminate\Support\Facades\Route::middleware('web')
                ->namespace($this->namespace)
                ->group(base_path('routes/web.php'));
        });
    }
    
    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        // Default API rate limit - 60 requests per minute for authenticated users
        RateLimiter::for('api', function (Request $request) {
            return $request->user()
                ? Limit::perMinute(60)->by($request->user()->id)
                : Limit::perMinute(20)->by($request->ip());
        });

        // Authentication endpoints - more restrictive
        RateLimiter::for('api-auth', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        // Admin operations - moderate limits
        RateLimiter::for('api-admin', function (Request $request) {
            return $request->user() && $request->user()->hasRole(['admin', 'super-admin'])
                ? Limit::perMinute(120)->by($request->user()->id)
                : Limit::perMinute(30)->by($request->user()->id ?? $request->ip());
        });

        // High-frequency operations (like notifications)
        RateLimiter::for('api-frequent', function (Request $request) {
            return $request->user()
                ? Limit::perMinute(200)->by($request->user()->id)
                : Limit::perMinute(50)->by($request->ip());
        });

        // Public endpoints - very restrictive
        RateLimiter::for('api-public', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });

        // Bulk operations - more restrictive
        RateLimiter::for('api-bulk', function (Request $request) {
            return $request->user()
                ? Limit::perMinute(10)->by($request->user()->id)
                : Limit::perMinute(3)->by($request->ip());
        });
    }

    /**
     * Register model bindings for route parameters
     *
     * @return void
     */
    protected function registerModelBindings()
    {
        \Illuminate\Support\Facades\Route::model('ticket', \App\Ticket::class);
        \Illuminate\Support\Facades\Route::model('asset', \App\Asset::class);
        \Illuminate\Support\Facades\Route::model('user', \App\User::class);
        \Illuminate\Support\Facades\Route::model('location', \App\Location::class);
        \Illuminate\Support\Facades\Route::model('division', \App\Division::class);
        \Illuminate\Support\Facades\Route::model('manufacturer', \App\Manufacturer::class);
        \Illuminate\Support\Facades\Route::model('assetModel', \App\AssetModel::class);
        \Illuminate\Support\Facades\Route::model('supplier', \App\Supplier::class);
        \Illuminate\Support\Facades\Route::model('budget', \App\Budget::class);
        \Illuminate\Support\Facades\Route::model('invoice', \App\Invoice::class);
        \Illuminate\Support\Facades\Route::model('dailyActivity', \App\DailyActivity::class);
        \Illuminate\Support\Facades\Route::model('ticketStatus', \App\TicketsStatus::class);
        \Illuminate\Support\Facades\Route::model('ticketPriority', \App\TicketsPriority::class);
        \Illuminate\Support\Facades\Route::model('ticketType', \App\TicketsType::class);
        \Illuminate\Support\Facades\Route::model('notification', \App\Notification::class);
    }
}
