<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use App\Services\SlackNotifier;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Register custom Blade directives
        $this->registerBladeDirectives();
        
        // Register view composers
        $this->registerViewComposers();
        
        // Configure mail transport with custom SSL settings
        $this->configureMailTransport();
        
        // Register observers for cache invalidation
        \App\Location::observe(\App\Observers\LocationObserver::class);
        \App\Status::observe(\App\Observers\StatusObserver::class);
        
        // Observe assets so status changes dispatch events
        \App\Asset::observe(\App\Observers\AssetObserver::class);
        
        // Observe tickets to keep immutable ticket_history audit
        \App\Ticket::observe(\App\Observers\TicketObserver::class);
        
        // Observe movements to maintain denormalized asset.location_id
        \App\Movement::observe(\App\Observers\MovementObserver::class);
    }
    
    /**
     * Configure mail transport with custom SSL options
     */
    protected function configureMailTransport()
    {
        $this->app->afterResolving('mail.manager', function ($manager) {
            $manager->extend('noverifysmtp', function ($config) {
                return new \App\Mail\Transport\NoVerifySmtpTransport($config);
            });
        });
    }
    
    /**
     * Register custom Blade directives
     */
    protected function registerBladeDirectives()
    {
        // Register @permission directive (alias for @can for permission checking)
        Blade::directive('permission', function ($expression) {
            return "<?php if(auth()->check() && auth()->user()->can($expression)): ?>";
        });
        
        Blade::directive('endpermission', function () {
            return "<?php endif; ?>";
        });
    }
    
    /**
     * Register view composers for common form data
     */
    protected function registerViewComposers()
    {
        // General form data composer for user management forms only
        view()->composer([
            'admin.users.create',
            'admin.users.edit',
            'users.create',
            'users.edit',
        ], \App\Http\ViewComposers\FormDataComposer::class);
        
        // Ticket-specific composer for ticket-related views
        view()->composer([
            'tickets.create',
            'tickets.edit',
            'tickets.show',
            'admin.tickets.create',
            'admin.tickets.edit',
        ], \App\Http\ViewComposers\TicketFormComposer::class);
        
        // Asset-specific composer for asset-related views
        view()->composer([
            'assets.create',
            'assets.edit',
            'admin.assets.create',
            'admin.assets.edit',
            'asset-requests.create',
        ], \App\Http\ViewComposers\AssetFormComposer::class);
        
        // Daily activities composer for activity forms only (not calendar)
        // Temporarily disabled to investigate blank page issues
        // view()->composer([
        //     'daily-activities.create',
        //     'daily-activities.edit',
        // ], \App\Http\ViewComposers\FormDataComposer::class);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(SlackNotifier::class, function ($app) {
            return new SlackNotifier();
        });
        
        // Bind Asset Repository
        $this->app->bind(
            \App\Repositories\Assets\AssetRepositoryInterface::class,
            \App\Repositories\Assets\AssetRepository::class
        );
        
        // Bind Ticket Repository
        $this->app->bind(
            \App\Repositories\Tickets\TicketRepositoryInterface::class,
            \App\Repositories\Tickets\TicketRepository::class
        );
        
        // Bind User Repository
        $this->app->bind(
            \App\Repositories\Users\UserRepositoryInterface::class,
            \App\Repositories\Users\UserRepository::class
        );
        
        // Bind Services
        $this->app->singleton(\App\Services\TicketService::class);
        $this->app->singleton(\App\Services\AssetService::class);
        $this->app->singleton(\App\Services\UserService::class);
    }
}
