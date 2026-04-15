<?php

namespace App\Services;

use App\Import;
use App\ImportConflict;
use App\ResolutionChoice;
use App\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ConflictResolutionService
{
    /**
     * Get all unresolved conflicts for an import.
     *
     * @param string $importId
     * @return Collection
     */
    public function getUnresolvedConflicts($importId)
    {
        return ImportConflict::forImport($importId)
            ->unresolved()
            ->get();
    }

    /**
     * Get conflicts grouped by type for an import.
     *
     * @param string $importId
     * @return array
     */
    public function getConflictsByType($importId)
    {
        $conflicts = ImportConflict::forImport($importId)->get();
        
        return $conflicts->groupBy('conflict_type')->map(function ($group) {
            return [
                'count' => $group->count(),
                'conflicts' => $group->values()
            ];
        })->toArray();
    }

    /**
     * Get conflict statistics for an import.
     *
     * @param string $importId
     * @return array
     */
    public function getConflictStatistics($importId)
    {
        $conflicts = ImportConflict::forImport($importId)->get();
        $unresolvedCount = $conflicts->filter(fn($c) => !$c->isResolved())->count();
        $resolvedCount = $conflicts->filter(fn($c) => $c->isResolved())->count();
        
        return [
            'total_conflicts' => $conflicts->count(),
            'unresolved_conflicts' => $unresolvedCount,
            'resolved_conflicts' => $resolvedCount,
            'resolution_rate' => $conflicts->count() > 0 
                ? round(($resolvedCount / $conflicts->count()) * 100, 2)
                : 0,
            'by_type' => $this->getConflictsByType($importId),
        ];
    }

    /**
     * Resolve a single conflict.
     *
     * @param int $conflictId
     * @param string $resolution
     * @param array $details
     * @param int $userId
     * @return ResolutionChoice
     */
    public function resolveConflict($conflictId, $resolution, $details = [], $userId = null)
    {
        $conflict = ImportConflict::findOrFail($conflictId);
        $userId = $userId ?? auth()->id();

        DB::beginTransaction();
        
        try {
            $conflict->resolveWith($resolution, null);

            $resolutionChoice = ResolutionChoice::create([
                'import_id' => $conflict->import_id,
                'conflict_id' => $conflictId,
                'user_id' => $userId,
                'choice' => $resolution,
                'choice_details' => $details,
            ]);

            $conflict->update(['resolution_choice_id' => $resolutionChoice->id]);

            DB::commit();

            return $resolutionChoice;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Resolve multiple conflicts in bulk.
     *
     * @param string $importId
     * @param array $resolutions Array of ['conflict_id' => id, 'resolution' => 'skip|create_new|update|merge', 'details' => []]
     * @param int $userId
     * @return Collection
     */
    public function bulkResolveConflicts($importId, $resolutions, $userId = null)
    {
        $userId = $userId ?? auth()->id();
        $createdChoices = collect();

        DB::beginTransaction();
        
        try {
            foreach ($resolutions as $item) {
                $choice = $this->resolveConflict(
                    $item['conflict_id'],
                    $item['resolution'],
                    $item['details'] ?? [],
                    $userId
                );
                $createdChoices->push($choice);
            }

            DB::commit();

            return $createdChoices;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Auto-resolve conflicts based on strategy.
     *
     * @param string $importId
     * @param string $strategy
     * @param int $userId
     * @return array
     */
    public function autoResolveConflicts($importId, $strategy = 'skip', $userId = null)
    {
        $import = Import::findByImportId($importId);
        $conflicts = $this->getUnresolvedConflicts($importId);
        $userId = $userId ?? auth()->id();

        $resolutions = [];
        $results = [
            'total' => $conflicts->count(),
            'resolved' => 0,
            'failed' => 0,
            'errors' => []
        ];

        foreach ($conflicts as $conflict) {
            try {
                $resolution = $this->determineAutoResolution($conflict, $strategy);
                
                $this->resolveConflict(
                    $conflict->id,
                    $resolution,
                    [],
                    $userId
                );

                $results['resolved']++;
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'conflict_id' => $conflict->id,
                    'error' => $e->getMessage()
                ];
            }
        }

        return $results;
    }

    /**
     * Determine auto-resolution based on strategy and conflict type.
     *
     * @param ImportConflict $conflict
     * @param string $strategy
     * @return string
     */
    protected function determineAutoResolution($conflict, $strategy = 'skip')
    {
        $resolutionMap = [
            ImportConflict::CONFLICT_DUPLICATE_KEY => $strategy === 'update' ? ImportConflict::RESOLUTION_UPDATE_EXISTING : ImportConflict::RESOLUTION_SKIP,
            ImportConflict::CONFLICT_DUPLICATE_RECORD => ImportConflict::RESOLUTION_SKIP,
            ImportConflict::CONFLICT_FOREIGN_KEY_NOT_FOUND => ImportConflict::RESOLUTION_SKIP,
            ImportConflict::CONFLICT_INVALID_DATA => ImportConflict::RESOLUTION_SKIP,
            ImportConflict::CONFLICT_BUSINESS_RULE => ImportConflict::RESOLUTION_SKIP,
        ];

        return $resolutionMap[$conflict->conflict_type] ?? ImportConflict::RESOLUTION_SKIP;
    }

    /**
     * Get resolution history for an import.
     *
     * @param string $importId
     * @return Collection
     */
    public function getResolutionHistory($importId)
    {
        return ResolutionChoice::forImport($importId)
            ->with(['user', 'conflict'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get resolution history by user.
     *
     * @param string $importId
     * @param int $userId
     * @return Collection
     */
    public function getResolutionHistoryByUser($importId, $userId)
    {
        return ResolutionChoice::forImport($importId)
            ->byUser($userId)
            ->with('conflict')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Export conflict summary report.
     *
     * @param string $importId
     * @return array
     */
    public function exportConflictReport($importId)
    {
        $import = Import::findByImportId($importId);
        $stats = $this->getConflictStatistics($importId);
        $conflicts = ImportConflict::forImport($importId)->get();
        $history = $this->getResolutionHistory($importId);

        return [
            'import_id' => $importId,
            'resource_type' => $import->resource_type,
            'file_name' => $import->file_name,
            'created_at' => $import->created_at,
            'statistics' => $stats,
            'conflicts' => $conflicts->map(function ($conflict) {
                return [
                    'id' => $conflict->id,
                    'row_number' => $conflict->row_number,
                    'type' => $conflict->getConflictTypeLabel(),
                    'suggested_resolution' => $conflict->suggested_resolution,
                    'user_resolution' => $conflict->user_resolution,
                    'resolved' => $conflict->isResolved(),
                    'new_data' => $conflict->new_record_data,
                ];
            })->toArray(),
            'resolution_history' => $history->map(function ($choice) {
                return [
                    'conflict_id' => $choice->conflict_id,
                    'choice' => $choice->getChoiceLabel(),
                    'user' => $choice->user->name,
                    'resolved_at' => $choice->created_at,
                ];
            })->toArray(),
        ];
    }

    /**
     * Rollback conflict resolutions.
     *
     * @param string $importId
     * @return int
     */
    public function rollbackResolutions($importId)
    {
        DB::beginTransaction();
        
        try {
            $conflicts = ImportConflict::forImport($importId)->resolved()->get();
            $count = 0;

            foreach ($conflicts as $conflict) {
                $conflict->update([
                    'user_resolution' => null,
                    'resolution_choice_id' => null
                ]);
                $count++;
            }

            ResolutionChoice::forImport($importId)->delete();

            DB::commit();

            return $count;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
