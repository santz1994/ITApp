<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * FilterBuilder Trait
 * 
 * Provides advanced filtering capabilities for Eloquent models.
 * Supports date range, multi-select, range, location hierarchy, and complex filters.
 * 
 * Usage:
 *   Asset::filterByDateRange('2025-01-01', '2025-12-31')
 *       ->filterByMultipleIds([1, 2, 3], 'status_id')
 *       ->filterByLocationHierarchy(5, true)
 *       ->paginate();
 * 
 * @package App\Traits
 */
trait FilterBuilder
{
    /**
     * Filter by date range on specified column
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $startDate Date in Y-m-d format
     * @param string|null $endDate Date in Y-m-d format
     * @param string $column Column name to filter (default: created_at)
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilterByDateRange($query, $startDate = null, $endDate = null, $column = 'created_at')
    {
        // Return early if no dates provided
        if (empty($startDate) && empty($endDate)) {
            return $query;
        }

        // Apply start date filter
        if (!empty($startDate)) {
            $query->whereDate($column, '>=', $startDate);
        }

        // Apply end date filter
        if (!empty($endDate)) {
            $query->whereDate($column, '<=', $endDate);
        }

        return $query;
    }

    /**
     * Filter by multiple IDs using IN clause
     * 
     * Accepts array or comma-separated string.
     * Automatically converts to array and validates integers.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array|string $ids Array of IDs or comma-separated string
     * @param string $column Column name for IN clause
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilterByMultipleIds($query, $ids, $column = 'id')
    {
        if (empty($ids)) {
            return $query;
        }

        // Convert comma-separated string to array
        if (is_string($ids)) {
            $ids = array_map('trim', explode(',', $ids));
        }

        // Ensure array and filter out invalid values
        $ids = (array)$ids;
        $ids = array_filter(array_map('intval', $ids));

        if (empty($ids)) {
            return $query;
        }

        return $query->whereIn($column, $ids);
    }

    /**
     * Filter by numeric range (MIN and/or MAX)
     * 
     * Useful for price ranges, age ranges, warranty months, etc.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int|float|null $min Minimum value (inclusive)
     * @param int|float|null $max Maximum value (inclusive)
     * @param string $column Column name to filter
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilterByRangeValue($query, $min = null, $max = null, $column = 'price')
    {
        // Return early if no values provided
        if ($min === null && $max === null) {
            return $query;
        }

        // Apply minimum value filter
        if ($min !== null && $min !== '') {
            $query->where($column, '>=', (float)$min);
        }

        // Apply maximum value filter
        if ($max !== null && $max !== '') {
            $query->where($column, '<=', (float)$max);
        }

        return $query;
    }

    /**
     * Filter by location with optional sublocation inclusion
     * 
     * If includeSublocations is true, includes all child locations.
     * Uses parent_location_id relationship.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $locationId Primary location ID
     * @param bool $includeSublocations Include child locations (default: false)
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilterByLocationHierarchy($query, $locationId = null, $includeSublocations = false)
    {
        if (empty($locationId)) {
            return $query;
        }

        // Simple location filter (no sublocations)
        if (!$includeSublocations) {
            return $query->where('location_id', $locationId);
        }

        // Get location and all child locations
        try {
            $locationClass = class_exists(\App\Location::class) ? \App\Location::class : null;

            if ($locationClass) {
                $locationIds = $locationClass::where('id', $locationId)
                    ->orWhere('parent_location_id', $locationId)
                    ->pluck('id')
                    ->toArray();

                return $query->whereIn('location_id', $locationIds);
            }
        } catch (\Exception $e) {
            // Location class or table doesn't exist, fall back to simple filter
            return $query->where('location_id', $locationId);
        }

        return $query->where('location_id', $locationId);
    }

    /**
     * Filter by status (single or multiple)
     * 
     * Convenience method for status filtering.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int|array|string $statusIds Status ID(s)
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilterByStatus($query, $statusIds = null)
    {
        if (empty($statusIds)) {
            return $query;
        }

        if (is_array($statusIds)) {
            return $query->whereIn('status_id', $statusIds);
        }

        return $query->where('status_id', $statusIds);
    }

    /**
     * Filter by priority (single or multiple)
     * 
     * Convenience method for priority filtering.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int|array|string $priorityIds Priority ID(s)
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilterByPriority($query, $priorityIds = null)
    {
        if (empty($priorityIds)) {
            return $query;
        }

        if (is_array($priorityIds)) {
            return $query->whereIn('priority_id', $priorityIds);
        }

        return $query->where('priority_id', $priorityIds);
    }

    /**
     * Filter by division (single or multiple)
     * 
     * Convenience method for division filtering.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int|array|string $divisionIds Division ID(s)
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilterByDivision($query, $divisionIds = null)
    {
        if (empty($divisionIds)) {
            return $query;
        }

        if (is_array($divisionIds)) {
            return $query->whereIn('division_id', $divisionIds);
        }

        return $query->where('division_id', $divisionIds);
    }

    /**
     * Filter by assigned user (for tickets)
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int|array|string $userIds User ID(s)
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilterByAssignedTo($query, $userIds = null)
    {
        if (empty($userIds)) {
            return $query;
        }

        if (is_array($userIds)) {
            return $query->whereIn('assigned_to', $userIds);
        }

        return $query->where('assigned_to', $userIds);
    }

    /**
     * Apply multiple filters at once
     * 
     * Intelligently applies all provided filters using appropriate scope methods.
     * Handles missing filters gracefully.
     * 
     * Supported filter keys:
     * - date_from, date_to, date_column
     * - status_id, priority_id, division_id
     * - location_id, include_sublocation
     * - price_min, price_max
     * - warranty_months_min, warranty_months_max
     * - assigned_to
     * - manufacturer_id
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $filters Filter parameters
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeApplyFilters($query, $filters = [])
    {
        if (empty($filters)) {
            return $query;
        }

        // Date range filtering
        if (!empty($filters['date_from']) || !empty($filters['date_to'])) {
            $dateColumn = $filters['date_column'] ?? 'created_at';
            $query->filterByDateRange(
                $filters['date_from'] ?? null,
                $filters['date_to'] ?? null,
                $dateColumn
            );
        }

        // Status filtering
        if (!empty($filters['status_id'])) {
            $query->filterByStatus($filters['status_id']);
        }

        // Priority filtering
        if (!empty($filters['priority_id'])) {
            $query->filterByPriority($filters['priority_id']);
        }

        // Division filtering
        if (!empty($filters['division_id'])) {
            $query->filterByDivision($filters['division_id']);
        }

        // Manufacturer filtering
        if (!empty($filters['manufacturer_id'])) {
            $query->filterByMultipleIds($filters['manufacturer_id'], 'manufacturer_id');
        }

        // Assigned to filtering (for tickets)
        if (!empty($filters['assigned_to'])) {
            $query->filterByAssignedTo($filters['assigned_to']);
        }

        // Location hierarchy filtering
        if (!empty($filters['location_id'])) {
            $query->filterByLocationHierarchy(
                $filters['location_id'],
                $filters['include_sublocation'] ?? false
            );
        }

        // Price range filtering
        if (isset($filters['price_min']) || isset($filters['price_max'])) {
            $query->filterByRangeValue(
                $filters['price_min'] ?? null,
                $filters['price_max'] ?? null,
                'purchase_price'
            );
        }

        // Warranty months filtering
        if (isset($filters['warranty_months_min']) || isset($filters['warranty_months_max'])) {
            $query->filterByRangeValue(
                $filters['warranty_months_min'] ?? null,
                $filters['warranty_months_max'] ?? null,
                'warranty_months'
            );
        }

        return $query;
    }

    /**
     * Get available filters for this model
     * 
     * Returns array of filter names that can be used with this model.
     * Can be overridden in specific models.
     * 
     * @return array
     */
    public function getAvailableFilters()
    {
        // Default filters - can be overridden in specific models
        return [
            'status_id' => ['type' => 'multi-select', 'relation' => 'statuses'],
            'priority_id' => ['type' => 'multi-select', 'relation' => 'priorities'],
            'division_id' => ['type' => 'multi-select', 'relation' => 'divisions'],
            'location_id' => ['type' => 'multi-select', 'relation' => 'locations'],
            'manufacturer_id' => ['type' => 'multi-select', 'relation' => 'manufacturers'],
            'assigned_to' => ['type' => 'multi-select', 'relation' => 'assignedUser'],
            'date_from' => ['type' => 'date-range', 'column' => 'created_at'],
            'date_to' => ['type' => 'date-range', 'column' => 'created_at'],
            'price_min' => ['type' => 'range', 'column' => 'purchase_price'],
            'price_max' => ['type' => 'range', 'column' => 'purchase_price'],
            'warranty_months_min' => ['type' => 'range', 'column' => 'warranty_months'],
            'warranty_months_max' => ['type' => 'range', 'column' => 'warranty_months'],
        ];
    }

    /**
     * Get available options for a specific filter
     * 
     * Used to populate dropdown menus and filter options.
     * Returns collection with id, name, and count (if available).
     * 
     * @param string $filterName Filter name (e.g., 'status_id', 'division_id')
     * @return \Illuminate\Support\Collection
     */
    public function getFilterOptions($filterName)
    {
        $filters = $this->getAvailableFilters();

        if (!isset($filters[$filterName])) {
            return collect([]);
        }

        $filterConfig = $filters[$filterName];

        if ($filterConfig['type'] === 'multi-select' && isset($filterConfig['relation'])) {
            try {
                // Get related model
                $relation = $filterConfig['relation'];

                if (method_exists($this, $relation)) {
                    $relatedModel = $this->$relation();
                    $relationModel = $relatedModel->getRelated();

                    // Get options with count
                    return $relationModel
                        ->withCount(class_basename($this) === 'Asset' ? 'assets' : class_basename($this))
                        ->select('id', 'name')
                        ->orderBy('name')
                        ->get();
                }
            } catch (\Exception $e) {
                // If relation fails, return empty collection
                return collect([]);
            }
        }

        // For date-range and range filters, return empty collection
        return collect([]);
    }
}
