# Getting Started

This starter kit is a Laravel 12 + Blade project providing authentication, a dashboard, profile settings, and a small modular system for building CRUD modules quickly.

## Prerequisites
- PHP 8.2+
- Composer
- Node.js + npm
- Database (MySQL, PostgreSQL, SQLite, etc.)

## Installation
1) Create a new project from this starter:

```
laravel new --using=laraveldaily/starter-kit
```

Or clone/download the repository and run:

```
composer install
cp .env.example .env   # set your DB connection
php artisan key:generate
```

2) Migrate the database:

```
php artisan migrate
```

3) Seed demo roles/permissions and users:

```
php artisan db:seed
```

This creates demo users:
- Admin: admin@example.com / password
- Viewer: viewer@example.com / password

4) Build frontend assets (Vite):

```
npm install
npm run dev   # or: npm run build
```

5) Run the app:

```
php artisan serve
```

Visit http://localhost:8000 and log in with the demo credentials.

## Next Steps
- Review the sidebar modules (Types, Blog Categories, Users, Roles, Permissions) depending on your permissions.
- Generate a new module using the module generator (see doc/module-generator.md).
- Adjust roles/permissions as needed (see doc/roles-permissions.md).
