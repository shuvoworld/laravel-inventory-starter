# Laravel + Blade Starter Kit

---

## Introduction

Laravel 12 + Blade starter kit originated from laraveldaily. Tailored to integrate Spatie user permission and a custom modular architecture.

---


## What is Inside?

Inside you will find all the functions that you would expect:

- Authentication
    - Login
    - Registration
    - Password Reset Flow
    - Email Confirmation Flow
- Dashboard Page
- Profile Settings
    - Profile Information Page
    - Password Update Page
    - Appearance Preferences

---

## How to use it?

To use this kit, you can install it in standard laravel project installation approach.

From there, you can modify the kit to your needs.

## Documentation
- Getting Started: doc/getting-started.md
- Roles & Permissions: doc/roles-permissions.md
- Modular Architecture: doc/modules.md
- Module Generator: doc/module-generator.md
- DataTables: doc/datatables.md
- UI and Layout: doc/ui-and-layout.md
- Chained Selects: doc/chained-selects.md
- Debugging: See below for Laravel Debugbar usage

### Debugging with Laravel Debugbar
This project includes barryvdh/laravel-debugbar for local development.

- Installed as a dev dependency and auto-discovered.
- Configuration: config/debugbar.php (published)
- Enable/Disable via .env:
  - APP_ENV=local and APP_DEBUG=true will enable it by default
  - Override explicitly with DEBUGBAR_ENABLED=true|false
- Do not enable on production. Set DEBUGBAR_ENABLED=false on staging/prod if needed.

---

### API Quick Start
This starter does not ship with a global API layer by default. If you need APIs, you can:

- Create per-module API routes in app/Modules/<Module>/routes/api.php (see stubs/module/api.php.stub for a minimal example that returns resources using Laravel API Resources).
- Or add your preferred API package and wire its routes under routes/api.php.

By default, routes/api.php simply auto-loads each module's api.php so your module APIs are available under the /api prefix when needed.

---


## Licence

Starter kit is open-sourced software licensed under the MIT license.


---

## Roles & Permissions (Spatie)

This project includes Spatie Laravel Permission (v6) with a basic role-based auth setup.

What was added:
- Spatie HasRoles trait on App\Models\User
- Middleware aliases: role, permission, role_or_permission
- Seeder that creates roles (admin, editor, viewer) and a set of example permissions (users.*, types.*)
- Demo routes protected by the role/permission middleware

Setup steps:
1. Ensure your DB is configured in .env and run migrations:
   php artisan migrate
2. Seed roles/permissions and demo users:
   php artisan db:seed
   This will create:
   - Admin user: admin@example.com / password (role: admin)
   - Viewer user: viewer@example.com / password (role: viewer)
3. Log in and try these demo routes:
   - GET /admin/area (requires role: admin)
   - POST /modules/types (requires permission: types.create)

Assigning roles/permissions in code:
- $user->assignRole('editor');
- $user->givePermissionTo('types.create');
- $user->syncRoles(['viewer']);
- $user->syncPermissions(['types.view']);

Protecting routes (examples already in routes/web.php):
- ->middleware('role:admin')
- ->middleware('permission:types.create')
- ->middleware('role_or_permission:editor|types.edit')

You can customize the initial roles/permissions in database/seeders/RolePermissionSeeder.php.


## Modular Architecture (Modules)

This project now supports a lightweight modular structure without extra packages.

Structure:
- app/Modules/{ModuleName}/
  - routes/web.php (and optional api.php)
  - Http/Controllers/* (namespace: App\Modules\{Module}\Http\Controllers)
  - resources/views (loadable with view namespace `{module-name-kebab}`)
  - database/migrations (auto-loaded)
  - resources/lang (optional, auto-loaded with same namespace)

Standard permissions per module:
- <module>.view
- <module>.create
- <module>.edit
- <module>.delete

How to add a new module (e.g., Types):
1. Create folder: app/Modules/Types
2. Add routes: app/Modules/Types/routes/web.php
3. Create controllers under App\Modules\Types\Http\Controllers
4. (Optional) Add views to app/Modules/Types/resources/views
5. Create permissions (use Admin → Roles & Permissions page → "Create Module CRUD Permissions" with module = types), then assign to roles.
6. Visit your routes (e.g., /modules/types) with a user that has the appropriate permissions.

