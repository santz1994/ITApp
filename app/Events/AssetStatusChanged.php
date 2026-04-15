<?php

namespace App\Events;

use App\Asset;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AssetStatusChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $asset;
    public $oldStatus;
    public $newStatus;
    public $changedBy;
    public $notes;

    public function __construct(Asset $asset, $oldStatus, $newStatus, $changedBy, $notes = null)
    {
        $this->asset = $asset;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
        $this->changedBy = $changedBy;
        $this->notes = $notes;
    }
}
