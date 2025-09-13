# Roles & Permissions (Spatie)

This project includes Spatie Laravel Permission (v6) with a basic role-based auth setup and a small admin UX for managing roles and permissions.

## What Was Added
- Spatie `HasRoles` trait on `App\Models\User`.
- Middleware aliases: `role`, `permission`, `role_or_permission`.
- Seeder that creates roles (admin, editor, viewer) and example permissions (users.*, types.*).
- Demo routes protected by the role/permission middleware.

## Setup Steps
1. Configure your database in `.env` and run migrations:
   
   ```bash
   php artisan migrate
   ```
2. Seed roles/permissions and demo users:
   
   ```bash
   php artisan db:seed
   ```
   This creates:
   - Admin user: `admin@example.com` / `password` (role: admin)
   - Viewer user: `viewer@example.com` / `password` (role: viewer)

## Assigning Roles/Permissions in Code
```php
$user->assignRole('editor');
$user->givePermissionTo('types.create');
$user->syncRoles(['viewer']);
$user->syncPermissions(['types.view']);
```

## Protecting Routes
Examples (see routes/web.php):
```php
->middleware('role:admin')
->middleware('permission:types.create')
->middleware('role_or_permission:editor|types.edit')
```

You can customize the initial roles/permissions in `database/seeders/RolePermissionSeeder.php`.

## Admin UX for Roles & Permissions
A minimal Blade-based UI is available for managing roles, permissions, and user role assignments.

How to use:
- Log in as Admin (`admin@example.com` / `password`) after seeding.
- Open the sidebar and click "Roles & Permissions" (visible to admins only), or go to `/admin/permissions`.
- From there you can:
  - Assign or revoke roles for users
  - Create or delete roles
  - Create or delete permissions
  - Grant or revoke permissions on roles

All routes under `/admin/permissions` are protected with the `role:admin` middleware.
