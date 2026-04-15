<?php

namespace App\Traits;

/**
 * Trait SortableQuery
 * 
 * Provides methods for safe, relationship-aware sorting in API queries
 * Prevents SQL injection and ensures only valid columns are used for sorting
 * 
 * Usage:
 * Asset::sortBy($request->sort_by, $request->sort_order)->get();
 * 
 * @package App\Traits
 */
trait SortableQuery
{
    /**
     * Define sortable columns and their allowed sort fields
     * Override in model to customize
     * 
     * @return array
     */
    public function getSortableColumns()
    {
        return [
            'id' => 'id',
            'created_at' => 'created_at',
            'updated_at' => 'updated_at',
            'name' => 'name',
        ];
    }

    /**
     * Define relationship-based sorting (requires joins)
     * Format: 'relation_name' => ['join_table', 'join_column', 'display_column']
     * 
     * @return array
     */
    public function getSortableRelations()
    {
        return [];
    }

    /**
     * Apply safe sorting to query
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $sortBy
     * @param string $sortOrder
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortBy($query, $sortBy = 'id', $sortOrder = 'desc')
    {
        $sortBy = $sortBy ?? 'id';
        $sortOrder = strtolower($sortOrder) === 'asc' ? 'asc' : 'desc';

        $sortableColumns = $this->getSortableColumns();
        $sortableRelations = $this->getSortableRelations();

        // Check if it's a direct column
        if (array_key_exists($sortBy, $sortableColumns)) {
            $column = $sortableColumns[$sortBy];
            return $query->orderBy($this->getTable() . '.' . $column, $sortOrder);
        }

        // Check if it's a relationship
        if (array_key_exists($sortBy, $sortableRelations)) {
            $relationConfig = $sortableRelations[$sortBy];
            $joinTable = $relationConfig[0];
            $joinColumn = $relationConfig[1];
            $displayColumn = $relationConfig[2];

            // Apply join and sort with grouping to prevent duplicates
            return $query
                ->leftJoin($joinTable, $this->getTable() . '.id', '=', $joinTable . '.' . $joinColumn)
                ->select($this->getTable() . '.*')
                ->groupBy($this->getTable() . '.id')
                ->orderBy($joinTable . '.' . $displayColumn, $sortOrder);
        }

        // Default to created_at if invalid sort_by
        return $query->orderBy($this->getTable() . '.created_at', 'desc');
    }

    /**
     * Scope to apply multiple sort orders
     * Example: Asset::multiSort([['name', 'asc'], ['created_at', 'desc']])->get()
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $sorts Array of [column, direction] pairs
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeMultiSort($query, array $sorts)
    {
        $sortableColumns = $this->getSortableColumns();

        foreach ($sorts as [$sortBy, $sortOrder]) {
            if (array_key_exists($sortBy, $sortableColumns)) {
                $column = $sortableColumns[$sortBy];
                $sortOrder = strtolower($sortOrder) === 'asc' ? 'asc' : 'desc';
                $query->orderBy($this->getTable() . '.' . $column, $sortOrder);
            }
        }

        return $query;
    }
}
