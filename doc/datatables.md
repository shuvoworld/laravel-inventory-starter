# Server-side Grids (Yajra DataTables)

Every generated module includes a server-side DataTable on the index page powered by Yajra Laravel DataTables.

- Endpoint: `GET /modules/{module}/data` (requires `{module}.view` permission)
- The index view includes CDN assets for jQuery and DataTables and initializes the grid.
- Backend uses `Yajra\DataTables` to process DataTables params (search/order/paging) with `DataTables::eloquent($query)`.
- Default columns: `id`, `name`, `created_at`, `updated_at`, plus an `Actions` column (partials/actions view) where CRUD links are rendered.

## Setup
1) Ensure PHP dependencies are installed:

```
composer install
```

2) Clear caches if needed:

```
php artisan route:clear && php artisan config:clear
```

3) Frontend: pages load DataTables assets per-view and initialize with a minimal layout:

```js
new DataTable('#items-table', {
  serverSide: true,
  processing: true,
  ajax: { url: '/modules/items/data', dataSrc: 'data' },
  columns: [
    { data: 'id' },
    { data: 'name' },
    { data: 'created_at' },
    { data: 'updated_at' },
    { data: 'actions', orderable: false, searchable: false },
  ],
  order: [[0, 'desc']],
  lengthChange: false,
  searching: false,
  pagingType: 'simple_numbers',
  layout: { topStart: null, topEnd: null, bottomStart: 'info', bottomEnd: 'paging' },
});
```

## Minimalist Styling
A compact, minimal design is applied via `resources/css/app.css`:
- Small cell padding
- Subtle row separators
- Hover state
- Simplified pagination

Use table classes: `table table-hover align-middle datatable-minimal table-sm`.
