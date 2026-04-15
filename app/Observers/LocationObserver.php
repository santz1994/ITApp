<?php

namespace App\Observers;

use App\Location;
use App\Services\CacheService;
use Illuminate\Support\Facades\Cache;

class LocationObserver
{
    /**
     * Handle the Location "created" event.
     */
    public function created(Location $location): void
    {
        Cache::forget('locations_all');
    }

    /**
     * Handle the Location "updated" event.
     */
    public function updated(Location $location): void
    {
        Cache::forget('locations_all');
    }

    /**
     * Handle the Location "deleted" event.
     */
    public function deleted(Location $location): void
    {
        Cache::forget('locations_all');
    }

    /**
     * Handle the Location "restored" event.
     */
    public function restored(Location $location): void
    {
        Cache::forget('locations_all');
    }

    /**
     * Handle the Location "force deleted" event.
     */
    public function forceDeleted(Location $location): void
    {
        Cache::forget('locations_all');
    }
}
