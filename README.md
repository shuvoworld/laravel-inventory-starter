# Laravel + Blade Starter Kit

---

## Introduction

Laravel 12 + Blade starter kit from laraveldaily. Tailored to integrate Spatie user permission and a custom modular architecture.

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

To use this kit, you can install it using:

```bash
laravel new --using=laraveldaily/starter-kit
```

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

Auto-discovery:
- The ModulesServiceProvider scans app/Modules/* and automatically loads module routes, views, migrations, and translations.

Example module included:
- Types: Full CRUD under /modules/types, protected by permissions types.view/create/edit/delete.

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

Seeding permissions:
- The seeder creates CRUD permissions for types and users. Roles:
  - admin: all permissions
  - editor: manage types (view/create/edit)
  - viewer: read-only for types

After pulling changes or adding modules:
- Run: composer dump-autoload
- Clear route/config cache if needed: php artisan route:clear && php artisan config:clear
- If you changed permissions/roles: php artisan db:seed --class=RolePermissionSeeder


---

## Central Module Library & Generator

This project includes a central ModuleManager and an artisan generator to scaffold full modules (models, migrations, controllers, requests, policies, routes, and Blade views) that are self-contained under app/Modules/{Module}.

Features:
- Auto-discovery of modules (routes, views, migrations, translations)
- Automatic registration of module policies (app/Modules/{Module}/Policies/*Policy.php)
- BaseModulePolicy that maps CRUD abilities to Spatie permissions: <module>.view/create/edit/delete
- Module generator command: module:make
- Stubs for consistent scaffolding located in stubs/module
- Server-side DataTables grid out of the box for each module’s index page (/modules/{module}/data endpoint)

Create a new module
1) Generate scaffold:
   php artisan module:make BlogCategory

   This creates the folder app/Modules/BlogCategory with:
   - routes/web.php (includes GET /data endpoint for DataTables)
   - Http/Controllers/BlogCategoryController.php (includes data() for server-side grid)
   - Http/Requests/StoreBlogCategoryRequest.php, UpdateBlogCategoryRequest.php
   - Models/BlogCategory.php
   - Policies/BlogCategoryPolicy.php (extends BaseModulePolicy)
   - database/migrations/*create_blog_categories_table.php
   - resources/views/{index,create,edit,show}.blade.php and resources/views/partials/actions.blade.php

2) Load and migrate:
   - composer dump-autoload
   - php artisan migrate

3) Permissions:
   The generator ensures CRUD permissions exist for the module using the kebab-case name (e.g. blog-category.view/create/edit/delete).
   Assign them to roles in Admin → Roles & Permissions.

4) Accessing views/routes:
   The generator registers routes under /modules/{module-kebab}, e.g. /modules/blog-category.
   Views are referenced via the view namespace {module-kebab} (e.g. blog-category::index).

Updating a module
- Add fields: update the module’s migration(s) and Eloquent $fillable. Adjust form fields in resources/views.
- Policy updates: modify the module’s Policy (or stick with BaseModulePolicy if CRUD-per-permission is sufficient).
- Run php artisan migrate for DB changes. If schema change requires, create a new migration inside the module folder.

Notes
- Policies are auto-registered when placed under app/Modules/{Module}/Policies with the class name {Model}Policy mapped to Models/{Model}.
- Permission convention per module remains: <module>.view, <module>.create, <module>.edit, <module>.delete.


### Module API Resources
Each module also ships with an API Resource class for serializing models in API responses.
- Path: app/Modules/{Module}/Http/Resources/{Module}Resource.php
- Generated automatically by: php artisan module:make {Name}

Module API routes
- The module generator also scaffolds routes/api.php for the module with sample endpoints:
  - GET /api/modules/{module} → paginated collection using {Module}Resource
  - GET /api/modules/{module}/{id} → single {Module}Resource
- These routes are explicitly registered with the `api` middleware and `api/modules/{module}` prefix.
- Protect them with Spatie permissions (e.g. {module}.view) as needed.

Example (Types module already included):
- Resource: app/Modules/Types/Http/Resources/TypeResource.php
- Routes: /api/modules/types (index), /api/modules/types/{id} (show)

Note: If you add fields to your model, update the Resource to expose them appropriately.


---



---

## Chained Selects (Reusable)

A small utility is included to build chained (dependent) dropdowns that fetch child options when the parent value changes. It works with plain Bootstrap selects and with Select2.

How it works:
- The parent select declares where to send an AJAX request and which target select to populate using data attributes.
- The endpoint should return either an array of objects like [{ id, text }, ...] or a key/value map { value: label, ... }. The utility also supports { data: [...] } envelopes.

Helper include
Use the reusable include to render a pair of parent/child fields quickly:

@include('form.chain-select', ['var' => [
  'parent' => [
    'name' => 'country_id',
    'label' => 'Country',
    'model' => \App\Models\Country::class, // or 'query' => Country::query()->where(...)
    'null_option' => true,
    'select2' => true,
    'chain' => [
      'target' => 'state_id',
      // supports {value} placeholder or query parameter (?parent=)
      'url' => url('/api/states?country={value}'),
      'param' => 'country',        // optional; used if not using {value}
      'id_field' => 'id',          // default id
      'text_field' => 'name',      // default text
    ],
  ],
  'child' => [
    'name' => 'state_id',
    'id' => 'state_id',
    'label' => 'State/Province',
    'placeholder' => '— Select State —',
    'null_option' => true,
    'select2' => true,
  ],
]])

Parent-only (manual markup)
If you want to wire it manually, add these attributes to the parent select:

<select
  id="country_id"
  name="country_id"
  class="form-select select2"
  data-chain-target="state_id"
  data-chain-url="/api/states"         
  data-chain-param="country"           
  data-id-field="id"
  data-text-field="name"
>
  ...
</select>

The child select should exist with the given id (state_id) and may be empty initially. Add data-placeholder and data-include-empty to control UX:

<select id="state_id" name="state_id" class="form-select select2" data-placeholder="— Select State —" data-include-empty></select>

Notes
- Endpoint response formats supported:
  - Array: [{ id: 1, text: "Alaska" }, ...]
  - Map: { "1": "Alaska", "2": "Arizona" }
  - Wrapped: { data: [...] } | { items: [...] } | { options: [...] }
- If using Select2, the utility triggers change() after repopulating.
- If the parent has a pre-selected value, the utility auto-loads the child on page load.
- You can pass a preselected child value to the include via 'child' => ['value' => old('state_id', 5)]. A temporary option will be rendered until the AJAX fills real options.
- Security: The fetch uses GET and sends X-Requested-With=XMLHttpRequest. If your endpoint requires auth, keep it under auth middleware.

Backend example (Laravel):

Route::get('/api/states', function (Illuminate\Http\Request $request) {
    $countryId = $request->get('country');
    $states = \App\Models\State::where('country_id', $countryId)
        ->orderBy('name')
        ->get(['id','name']);
    return $states->map(fn($s) => ['id' => $s->id, 'text' => $s->name]);
});
