<?php

namespace App\Observers;

use App\Movement;
use Illuminate\Support\Facades\Log;

/**
 * MovementObserver - Maintains denormalized location_id in assets table
 * 
 * When a movement is created, automatically update the asset's location_id
 * to keep it in sync with the latest movement.
 */
class MovementObserver
{
    /**
     * Handle the Movement "created" event.
     * 
     * When a new movement is created, update the asset's location_id
     * to match the movement's location_id.
     */
    public function created(Movement $movement): void
    {
        if ($movement->asset_id && $movement->location_id) {
            try {
                $asset = $movement->asset;
                
                if ($asset) {
                    // Only update if this is the most recent movement for this asset
                    $latestMovement = Movement::where('asset_id', $asset->id)
                        ->orderBy('created_at', 'desc')
                        ->first();
                    
                    if ($latestMovement && $latestMovement->id === $movement->id) {
                        $oldLocation = $asset->location_id;
                        $asset->location_id = $movement->location_id;
                        $asset->save();
                        
                        Log::info("Asset location denormalization updated", [
                            'asset_id' => $asset->id,
                            'asset_tag' => $asset->asset_tag,
                            'old_location_id' => $oldLocation,
                            'new_location_id' => $movement->location_id,
                            'movement_id' => $movement->id
                        ]);
                    }
                }
            } catch (\Exception $e) {
                // Log error but don't throw - denormalization is performance optimization
                Log::error("Failed to update asset location denormalization: " . $e->getMessage(), [
                    'asset_id' => $movement->asset_id,
                    'movement_id' => $movement->id
                ]);
            }
        }
    }
    
    /**
     * Handle the Movement "updated" event.
     * 
     * If a movement's location is changed (rare), update the asset
     * if this is still the most recent movement.
     */
    public function updated(Movement $movement): void
    {
        // Only update if location_id changed
        if ($movement->isDirty('location_id') && $movement->asset_id) {
            try {
                $asset = $movement->asset;
                
                if ($asset) {
                    // Check if this is the most recent movement for this asset
                    $latestMovement = Movement::where('asset_id', $asset->id)
                        ->orderBy('created_at', 'desc')
                        ->first();
                    
                    // Only update if this is the latest movement
                    if ($latestMovement && $latestMovement->id === $movement->id) {
                        $oldLocation = $asset->location_id;
                        $asset->location_id = $movement->location_id;
                        $asset->save();
                        
                        Log::info("Asset location denormalization updated (movement edited)", [
                            'asset_id' => $asset->id,
                            'asset_tag' => $asset->asset_tag,
                            'old_location_id' => $oldLocation,
                            'new_location_id' => $movement->location_id,
                            'movement_id' => $movement->id
                        ]);
                    }
                }
            } catch (\Exception $e) {
                Log::error("Failed to update asset location denormalization on movement update: " . $e->getMessage(), [
                    'asset_id' => $movement->asset_id,
                    'movement_id' => $movement->id
                ]);
            }
        }
    }
    
    /**
     * Handle the Movement "deleted" event.
     * 
     * If the most recent movement is deleted, recalculate the asset's location
     * from the new most recent movement.
     */
    public function deleted(Movement $movement): void
    {
        if ($movement->asset_id) {
            try {
                $asset = $movement->asset;
                
                if ($asset) {
                    // Find the new most recent movement
                    $newLatestMovement = Movement::where('asset_id', $asset->id)
                        ->orderBy('created_at', 'desc')
                        ->first();
                    
                    $oldLocation = $asset->location_id;
                    $newLocation = $newLatestMovement ? $newLatestMovement->location_id : null;
                    
                    $asset->location_id = $newLocation;
                    $asset->save();
                    
                    Log::info("Asset location denormalization recalculated after movement deletion", [
                        'asset_id' => $asset->id,
                        'asset_tag' => $asset->asset_tag,
                        'old_location_id' => $oldLocation,
                        'new_location_id' => $newLocation,
                        'deleted_movement_id' => $movement->id
                    ]);
                }
            } catch (\Exception $e) {
                Log::error("Failed to update asset location denormalization on movement deletion: " . $e->getMessage(), [
                    'asset_id' => $movement->asset_id,
                    'movement_id' => $movement->id
                ]);
            }
        }
    }
}
