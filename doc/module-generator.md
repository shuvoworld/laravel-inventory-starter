# Central Module Library & Generator

The project includes a central `ModuleManager` and an artisan generator to scaffold full modules (models, migrations, controllers, requests, policies, routes, and Blade views) under `app/Modules/{Module}`.

## Features
- Auto-discovery of modules (routes, views, migrations, translations)
- Automatic registration of module policies (placed under `app/Modules/{Module}/Policies/*Policy.php`)
- `BaseModulePolicy` maps CRUD abilities to Spatie permissions: `<module>.view/create/edit/delete`
- `module:make` command scaffolds a full module
- Stubs for consistent scaffolding are located in `stubs/module`
- Server-side DataTables grid out of the box for each module’s index page (`/modules/{module}/data`)

## Create a New Module
1) Generate scaffold:

```bash
php artisan module:make BlogCategory
```

This creates `app/Modules/BlogCategory` with:
- `routes/web.php` (includes GET `/data` endpoint for DataTables)
- `Http/Controllers/BlogCategoryController.php` (includes `data()` for server-side grid)
- `Http/Requests/StoreBlogCategoryRequest.php`, `UpdateBlogCategoryRequest.php`
- `Models/BlogCategory.php`
- `Policies/BlogCategoryPolicy.php` (extends `BaseModulePolicy`)
- `database/migrations/*create_blog_categories_table.php`
- `resources/views/{index,create,edit,show}.blade.php` and `resources/views/partials/actions.blade.php`

2) Load and migrate:
- `composer dump-autoload`
- `php artisan migrate`

3) Permissions:
The generator ensures CRUD permissions exist for the module using the kebab-case name (e.g., `blog-category.view/create/edit/delete`). Assign them to roles in Admin → Roles & Permissions.

4) Accessing views/routes:
- Routes under `/modules/{module-kebab}`, e.g. `/modules/blog-category`.
- Views are referenced via the view namespace `{module-kebab}` (e.g. `blog-category::index`).

## Updating a Module
- Add fields: update the module’s migration(s) and Eloquent `$fillable`. Adjust form fields in `resources/views`.
- Policy updates: modify the module’s Policy (or keep `BaseModulePolicy` if CRUD-per-permission is sufficient).
- Run `php artisan migrate` for DB changes. If schema change requires, create a new migration inside the module folder.

## Notes
- Policies are auto-registered when placed under `app/Modules/{Module}/Policies` with the class name `{Model}Policy` mapped to `Models/{Model}`.
- Permission convention per module: `<module>.view`, `<module>.create`, `<module>.edit`, `<module>.delete`.
