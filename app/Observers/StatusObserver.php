<?php

namespace App\Observers;

use App\Status;
use Illuminate\Support\Facades\Cache;

class StatusObserver
{
    /**
     * Handle the Status "created" event.
     */
    public function created(Status $status): void
    {
        Cache::forget('statuses_all');
    }

    /**
     * Handle the Status "updated" event.
     */
    public function updated(Status $status): void
    {
        Cache::forget('statuses_all');
    }

    /**
     * Handle the Status "deleted" event.
     */
    public function deleted(Status $status): void
    {
        Cache::forget('statuses_all');
    }

    /**
     * Handle the Status "restored" event.
     */
    public function restored(Status $status): void
    {
        Cache::forget('statuses_all');
    }

    /**
     * Handle the Status "force deleted" event.
     */
    public function forceDeleted(Status $status): void
    {
        Cache::forget('statuses_all');
    }
}
