<?php

namespace App\Support\Tables;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class DataTableResponder
{
    /**
     * Build a DataTables-compatible JSON response from an Eloquent/Query builder.
     * Supports: search (global), column ordering, paging.
     *
     * @param  EloquentBuilder|QueryBuilder  $query
     * @param  Request  $request
     * @param  array<int,string>  $columns  Whitelisted column names that can be searched/sorted
     * @param  callable|null  $mapRow  Optional row mapper: fn($model): array
     * @return array
     */
    public static function respond(EloquentBuilder|QueryBuilder $query, Request $request, array $columns, ?callable $mapRow = null): array
    {
        $draw = (int) $request->input('draw', 1);
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        if ($length < 0) {
            $length = 10;
        }

        // Total records before filtering
        $recordsTotal = (clone $query)->count();

        // Global search
        $searchValue = trim((string) Arr::get($request->input('search', []), 'value', ''));
        if ($searchValue !== '') {
            $query->where(function ($q) use ($columns, $searchValue) {
                foreach ($columns as $i => $col) {
                    $method = $i === 0 ? 'where' : 'orWhere';
                    $q->{$method}($col, 'LIKE', '%' . str_replace(['%', '_'], ['\\%', '\\_'], $searchValue) . '%');
                }
            });
        }

        // Ordering
        $order = $request->input('order', []);
        if (is_array($order)) {
            foreach ($order as $ord) {
                $colIndex = (int) Arr::get($ord, 'column', -1);
                $dir = strtolower((string) Arr::get($ord, 'dir', 'asc')) === 'desc' ? 'desc' : 'asc';
                $colName = Arr::get($request->input('columns', []), $colIndex . '.data');
                if (is_string($colName) && in_array($colName, $columns, true)) {
                    $query->orderBy($colName, $dir);
                }
            }
        }

        // Filtered count
        $recordsFiltered = (clone $query)->count();

        // Paging
        $page = (int) floor($start / max($length, 1)) + 1;
        /** @var LengthAwarePaginator $paginator */
        $paginator = $query->paginate($length, ['*'], 'page', $page);

        $map = $mapRow ?: function ($row) use ($columns) {
            $data = [];
            foreach ($columns as $col) {
                $data[$col] = data_get($row, $col);
            }
            return $data;
        };

        $data = [];
        foreach ($paginator->items() as $row) {
            $data[] = $map($row);
        }

        return [
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ];
    }
}
