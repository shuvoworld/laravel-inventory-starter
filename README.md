# Tech Inventory System

A complete multi-tenant inventory and sales management system built with Laravel 12 and Blade.

---

## Features

- **Multi-Tenant Architecture** - Complete store-based data isolation
- **Role-Based Access Control** - Store Admin and Store User roles
- **Inventory Management** - Products, stock movements, suppliers
- **Sales Management** - Orders, customers, returns
- **Purchase Management** - Purchase orders, returns
- **Financial Tracking** - Operating expenses, profit/loss reports
- **User Management** - Manage store users and permissions
- **Responsive Dashboard** - Role-specific dashboards with analytics

---

## Quick Start

### Installation

1. Clone the repository
```bash
git clone <repository-url>
cd laravel-daily-starter
```

2. Install dependencies
```bash
composer install
npm install
```

3. Configure environment
```bash
cp .env.example .env
php artisan key:generate
```

4. Setup database
```bash
# Configure your database in .env file
php artisan migrate
```

5. Seed roles and demo users
```bash
php artisan db:seed --class=StoreAdminRoleSeeder
php artisan db:seed --class=DemoUsersSeeder
```

6. Build assets
```bash
npm run build
```

7. Start the server
```bash
php artisan serve
```

---

## Demo Login Credentials

### Store Admin (Full Access)
- Email: `admin@demo.com`
- Password: `password`
- Access: All modules and features

### Store User (Sales Only)
- Email: `user@demo.com`
- Password: `password`
- Access: Sales orders, view customers and products

---

## User Roles

### Store Admin
Full access to all features:
- Products, Customers, Suppliers
- Sales Orders, Purchase Orders
- Stock Movements, Reports
- Operating Expenses, Returns
- User Management, Settings

### Store User
Limited access for sales staff:
- Create and manage sales orders
- View customers and products
- Sales-focused dashboard

---

## Registration

When registering a new account:
1. Provide your name, email, store name, and password
2. A new store is automatically created
3. You are assigned as Store Admin
4. Full access to all features for your store
5. You can create Store Users for your team

---

## Multi-Tenant System

Each store's data is completely isolated:
- Users can only access their own store's data
- All records are automatically filtered by store
- No cross-store data access
- Each store operates independently

---

## Documentation

For detailed documentation, see:
- `IMPLEMENTATION_SUMMARY.md` - System overview
- `MULTI_TENANT_IMPLEMENTATION.md` - Multi-tenancy details
- `STORE_ADMIN_ROLE.md` - Role and permissions
- `USER_MANAGEMENT.md` - User management guide

---

## License

This project is open-sourced software licensed under the MIT license.
