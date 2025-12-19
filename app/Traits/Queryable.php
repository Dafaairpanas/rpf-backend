<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Trait Queryable
 *
 * Provides common query functionality for API list endpoints:
 * - Search: q parameter for fulltext search
 * - Filters: filter[key]=value for exact matching
 * - Date range: date_from, date_to
 * - Sorting: sort=field (asc) or sort=-field (desc)
 * - Pagination: per_page
 */
trait Queryable
{
    /**
     * Columns that can be searched with 'q' parameter
     * Override in model to customize
     */
    protected function getSearchableColumns(): array
    {
        return $this->searchable ?? [];
    }

    /**
     * Columns that can be filtered with 'filter[key]' parameter
     * Override in model to customize
     */
    protected function getFilterableColumns(): array
    {
        return $this->filterable ?? [];
    }

    /**
     * Columns that can be used for sorting
     * Override in model to customize
     */
    protected function getSortableColumns(): array
    {
        return $this->sortable ?? ['id', 'created_at', 'updated_at'];
    }

    /**
     * Apply queryable scopes to builder
     */
    public function scopeQueryable(Builder $query, Request $request): Builder
    {
        return $query
            ->when($request->filled('q'), fn($q) => $this->applySearch($q, $request->q))
            ->when($request->query('filter'), fn($q) => $this->applyFilters($q, $request->query('filter')))
            ->when($request->filled('date_from'), fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->when($request->filled('sort'), fn($q) => $this->applySort($q, $request->sort));
    }

    /**
     * Apply search to query
     */
    protected function applySearch(Builder $query, string $search): Builder
    {
        $columns = $this->getSearchableColumns();

        if (empty($columns)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($columns, $search) {
            foreach ($columns as $column) {
                // Support relationship columns like 'role.name'
                if (str_contains($column, '.')) {
                    [$relation, $field] = explode('.', $column, 2);
                    $q->orWhereHas($relation, function ($subQ) use ($field, $search) {
                        $subQ->where($field, 'like', "%{$search}%");
                    });
                } else {
                    $q->orWhere($column, 'like', "%{$search}%");
                }
            }
        });
    }

    /**
     * Apply filters to query
     */
    protected function applyFilters(Builder $query, array $filters): Builder
    {
        $allowed = $this->getFilterableColumns();

        foreach ($filters as $key => $value) {
            if (in_array($key, $allowed)) {
                // Handle relationship filters like 'role'
                if (method_exists($this, $key) || str_contains($key, '_id')) {
                    $query->where($key . '_id', $value);
                } else {
                    $query->where($key, $value);
                }
            }
        }

        return $query;
    }

    /**
     * Apply sorting to query
     */
    protected function applySort(Builder $query, string $sort): Builder
    {
        $direction = 'asc';
        $field = $sort;

        if (str_starts_with($sort, '-')) {
            $direction = 'desc';
            $field = ltrim($sort, '-');
        }

        $allowed = $this->getSortableColumns();

        if (in_array($field, $allowed)) {
            $query->orderBy($field, $direction);
        }

        return $query;
    }
}
