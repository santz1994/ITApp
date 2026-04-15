<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        \Illuminate\Auth\Events\Login::class => [
            \App\Listeners\SingleDeviceLoginListener::class,
        ],
    ];

    /**
     * The subscriber classes to register.
     *
     * @var array
     */
    protected $subscribe = [
        \App\Listeners\AuditAuthEventListener::class,
    ];

    /**
     * Register any other events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        // Register model observers
        \App\Ticket::observe(\App\Observers\TicketObserver::class);
        // Ensure Asset observer is registered so status change events are fired
        \App\Asset::observe(\App\Observers\AssetObserver::class);
    }
}
