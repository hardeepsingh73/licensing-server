<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Class SearchService
 *
 * A reusable service to dynamically apply filters to Eloquent queries
 * based on a configuration array and request input.
 *
 * Supports:
 *  - Basic field filters (LIKE, =, IN, >, <, >=, <=)
 *  - JSON field filters (-> syntax)
 *  - Relationship filters (via whereHas)
 */
class SearchService
{
    /**
     * Operators allowed for filtering.
     *
     * @var array<int, string>
     */
    protected array $allowedOperator = ['LIKE', '->', '=', 'IN', '>', '<', '>=', '<=', 'OR'];

    /**
     * Apply configured search filters to a query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  array  $searchConfig    Field & relationship config
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function search(Builder $query, array $searchConfig, Request $request): Builder
    {
        foreach ($searchConfig as $field => $config) {
            // Relationship-based filter configuration
            if (is_array($config) && isset($config['relationship'])) {
                $this->applyRelationshipFilter($query, $field, $config, $request);
                continue;
            }

            // Normalize operator/column based on config type
            $column   = is_numeric($field) ? $config : $field;
            $operator = is_numeric($field) ? 'LIKE' : $config;

            $this->applyFieldFilter($query, $column, $operator, $request);
        }

        return $query;
    }

    /**
     * Apply a filter for a direct column on the model's table.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $column
     * @param  string  $operator
     * @param  \Illuminate\Http\Request  $request
     */
    protected function applyFieldFilter(Builder $query, string $column, string $operator, Request $request): void
    {
        if ($request->has($column) && !empty($request->input($column)) && in_array($operator, $this->allowedOperator)) {
            $value = $request->input($column);

            // Handle JSON fields (e.g., data->name)
            if (str_contains($column, '->')) {
                $query->whereJsonContains($column, $value);

                // Handle exact match
            } elseif ($operator === '=') {
                $query->where($column, $value);

                // Handle multiple values
            } elseif ($operator === 'IN') {
                $query->whereIn($column, (array) $value);

                // Handle partial match (default)
            } elseif ($operator === 'LIKE') {
                $query->where($column, 'LIKE', "%{$value}%");

                // Handle numeric or comparison operators
            } else {
                $query->where($column, $operator, $value);
            }
        }
    }

    /**
     * Apply a filter for a related model using whereHas().
     *
     * Example config:
     * [
     *   'roles' => [
     *       'relationship' => true,
     *       'field' => 'id',
     *       'operator' => '=',
     *       'request_key' => 'role'
     *   ]
     * ]
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $relationship
     * @param  array   $config
     * @param  \Illuminate\Http\Request  $request
     */
    protected function applyRelationshipFilter(Builder $query, string $relationship, array $config, Request $request): void
    {
        $field      = $config['field'] ?? 'id';
        $operator   = $config['operator'] ?? 'LIKE';
        $requestKey = $config['request_key'] ?? $relationship;

        if ($request->filled($requestKey)) {
            $value = $request->input($requestKey);

            $query->whereHas($relationship, function (Builder $q) use ($field, $operator, $value) {
                if ($operator === 'IN') {
                    $q->whereIn($field, (array) $value);
                } elseif ($operator === 'LIKE') {
                    $q->where($field, 'LIKE', "%{$value}%"); // Add wildcards for LIKE
                } else {
                    $q->where($field, $operator, $value);
                }
            });
        }
    }
}
