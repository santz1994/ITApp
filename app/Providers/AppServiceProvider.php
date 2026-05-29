<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

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
        
        // Configure mail transport with custom SSL settings
        $this->configureMailTransport();
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
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Services will be auto-discovered by Laravel
    }
}