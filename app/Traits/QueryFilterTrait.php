<?php

namespace App\Traits;

use Illuminate\Http\Request;

trait QueryFilterTrait
{
    /**
     * Apply common filters to query builder
     */
    protected function applyFilters($query, Request $request, array $filterMap = [])
    {
        foreach ($filterMap as $requestKey => $dbColumn) {
            if ($request->filled($requestKey)) {
                $query->where($dbColumn, $request->input($requestKey));
            }
        }

        return $query;
    }

    /**
     * Apply search filter to multiple columns
     */
    protected function applySearch($query, Request $request, string $searchKey = 'search', array $searchColumns = [])
    {
        if ($request->filled($searchKey) && !empty($searchColumns)) {
            $searchTerm = $request->input($searchKey);
            
            $query->where(function($q) use ($searchColumns, $searchTerm) {
                foreach ($searchColumns as $index => $column) {
                    if ($index === 0) {
                        $q->where($column, 'like', "%{$searchTerm}%");
                    } else {
                        $q->orWhere($column, 'like', "%{$searchTerm}%");
                    }
                }
            });
        }

        return $query;
    }

    /**
     * Apply date range filter
     */
    protected function applyDateRange($query, Request $request, string $column, string $startKey = 'start_date', string $endKey = 'end_date')
    {
        if ($request->filled($startKey)) {
            $query->whereDate($column, '>=', $request->input($startKey));
        }

        if ($request->filled($endKey)) {
            $query->whereDate($column, '<=', $request->input($endKey));
        }

        return $query;
    }

    /**
     * Apply sorting with default fallback
     */
    protected function applySorting($query, Request $request, string $defaultColumn = 'created_at', string $defaultDirection = 'desc')
    {
        $sortColumn = $request->input('sort', $defaultColumn);
        $sortDirection = $request->input('direction', $defaultDirection);

        // Validate sort direction
        if (!in_array(strtolower($sortDirection), ['asc', 'desc'])) {
            $sortDirection = $defaultDirection;
        }

        return $query->orderBy($sortColumn, $sortDirection);
    }
}