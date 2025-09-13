# Modular Architecture

The project supports a lightweight modular structure without external packages.

## Structure
```
app/Modules/{ModuleName}/
  routes/web.php              # and optional api.php
  Http/Controllers/*          # namespace: App\Modules\{Module}\Http\Controllers
  resources/views             # loaded with view namespace `{module-name-kebab}`
  database/migrations         # auto-loaded
  resources/lang              # optional, auto-loaded
```

## Auto-discovery
The `ModulesServiceProvider` scans `app/Modules/*` and automatically loads module views, migrations, and translations. Web routes for all modules are included centrally from `routes/web.php` to be cache-friendly.

## Example Module
- Types: Full CRUD under `/modules/types`, protected by permissions `types.view/create/edit/delete`.

## Permissions Convention
Each module follows CRUD permission naming:
- `<module>.view`
- `<module>.create`
- `<module>.edit`
- `<module>.delete`

## Adding a New Module (e.g., BlogCategory)
1. Create the folder: `app/Modules/BlogCategory`
2. Add routes: `app/Modules/BlogCategory/routes/web.php`
3. Create controllers under `App\Modules\BlogCategory\Http\Controllers`
4. (Optional) Add views to `app/Modules/BlogCategory/resources/views`
5. Create permissions (use Admin → Roles & Permissions → "Create Module CRUD Permissions" with module = `blog-category`), then assign to roles.
6. Visit the routes (e.g., `/modules/blog-category`) with a user that has appropriate permissions.

## After Changes
- `composer dump-autoload`
- Clear caches if needed: `php artisan route:clear && php artisan config:clear`
- If you changed permissions/roles: `php artisan db:seed --class=RolePermissionSeeder`
