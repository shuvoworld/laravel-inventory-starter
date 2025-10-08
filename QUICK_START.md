# Quick Start Guide

## System Overview

This is a multi-tenant inventory and sales management system where each store's data is completely isolated.

## User Roles

### 1. Store-Admin (40 permissions)
- Owner/Manager of a store
- Full access to all features
- Can create store-users
- Gets this role automatically on registration

### 2. Store-User (6 permissions)
- Sales staff/cashier
- Can only process sales orders
- View-only access to products and customers
- Created by store-admins

### 3. Superadmin
- System-wide access
- Can access all stores
- Set via `is_superadmin` flag in database

---

## Getting Started

### For New Store Owners

1. **Register an Account**
   - Go to homepage
   - Click "Get Started" or "Register"
   - Fill in: Name, Email, Store Name, Password
   - Submit

2. **What Happens Automatically**
   - ✅ User account created
   - ✅ Assigned `store-admin` role (40 permissions)
   - ✅ Store created with your store name
   - ✅ You're linked to your store
   - ✅ Ready to use!

3. **First Steps**
   - Add products
   - Add customers
   - Add suppliers
   - Configure settings
   - Add team members (store-users)

### For Store Admins - Adding Team Members

1. **Navigate to Users**
   - Login to dashboard
   - Go to Users module (`/modules/users`)

2. **Create New User**
   - Click "Create User"
   - Enter: Name, Email, Password
   - Role automatically set to `store-user`
   - Save

3. **What User Can Do**
   - ✅ Process sales orders
   - ✅ View customers
   - ✅ View products
   - ❌ Cannot manage inventory
   - ❌ Cannot access settings

---

## Key Features

### Multi-Tenancy
- Each store's data is completely isolated
- Users can only see their own store's data
- No cross-store data access

### Automatic Scoping
```php
// When you query products, you only see YOUR store's products
Product::all(); // Automatically filtered by store_id
```

### Role-Based Access
```php
// Check permissions
if (auth()->user()->can('products.create')) {
    // User can create products
}
```

---

## Module Access by Role

| Module | Store-Admin | Store-User |
|--------|-------------|------------|
| Users | ✅ Manage | ❌ No Access |
| Products | ✅ Full CRUD | 👁️ View Only |
| Customers | ✅ Full CRUD | 👁️ View Only |
| Sales Orders | ✅ Full CRUD | ✅ Full CRUD |
| Purchase Orders | ✅ Full CRUD | ❌ No Access |
| Suppliers | ✅ Full CRUD | ❌ No Access |
| Stock Movements | ✅ View | ❌ No Access |
| Reports | ✅ View | ❌ No Access |
| Settings | ✅ Edit | ❌ No Access |
| Operating Expenses | ✅ Full CRUD | ❌ No Access |
| Sales Returns | ✅ Full CRUD | ❌ No Access |
| Purchase Returns | ✅ Full CRUD | ❌ No Access |

---

## Common Tasks

### Adding a Product
```
1. Navigate to Products
2. Click "Create Product"
3. Fill in details (SKU, Name, Price, etc.)
4. Save
```
→ Product is automatically tagged with your store_id

### Creating a Sales Order
```
1. Navigate to Sales Orders
2. Click "Create Order"
3. Select customer
4. Add products
5. Process payment
6. Save
```
→ Order is automatically tagged with your store_id

### Adding a Team Member (Sales Staff)
```
1. Navigate to Users
2. Click "Create User"
3. Enter details
4. Save
```
→ User gets store-user role and is linked to your store

---

## Security & Permissions

### Data Isolation
- ✅ Store-admins see only their store's data
- ✅ Store-users see only their store's data
- ✅ Complete database-level isolation via `store_id`

### Permission Enforcement
- All routes are permission-protected
- Middleware checks: `permission:products.create`
- Automatic scope applied: `StoreScope`

### Role Restrictions
- Store-admins cannot create other store-admins
- Store-admins can only create store-users
- Store-users have minimal permissions

---

## Testing the System

### Test Multi-Tenancy

1. Register as "Store A Owner"
2. Create some products
3. Logout
4. Register as "Store B Owner"
5. Create some products
6. Verify: Store B cannot see Store A's products ✅

### Test User Management

1. Login as store-admin
2. Go to Users → Create User
3. Create "John (Cashier)"
4. Logout and login as John
5. Verify: John can create sales orders ✅
6. Verify: John cannot create products ❌

### Test Permissions

```bash
php artisan tinker
```

```php
// Get a store-admin
$admin = User::whereHas('roles', fn($q) =>
    $q->where('name', 'store-admin')
)->first();

$admin->can('users.create'); // true
$admin->can('products.create'); // true

// Get a store-user
$user = User::whereHas('roles', fn($q) =>
    $q->where('name', 'store-user')
)->first();

$user->can('sales-orders.create'); // true
$user->can('products.create'); // false
```

---

## Troubleshooting

### Can't See Data
**Issue**: User logs in but sees no data

**Solution**:
- Verify user is linked to a store
- Check `store_users` table
- Run: `User::find($id)->stores`

### Permission Denied
**Issue**: User gets "Permission denied" error

**Solution**:
```bash
# Re-run seeder
php artisan db:seed --class=StoreAdminRoleSeeder

# Clear cache
php artisan permission:cache-reset
php artisan optimize:clear
```

### User Not Auto-Assigned to Store
**Issue**: New user created but not linked to store

**Solution**:
- Ensure creator is authenticated
- Check `currentStoreId()` returns a value
- Manually link: `$user->stores()->attach($storeId)`

---

## Quick Commands

```bash
# Run migrations
php artisan migrate

# Create roles and permissions
php artisan db:seed --class=StoreAdminRoleSeeder

# Clear cache
php artisan optimize:clear

# Clear permission cache
php artisan permission:cache-reset

# Start dev server
php artisan serve
```

---

## Documentation

- **`IMPLEMENTATION_SUMMARY.md`** - Complete system overview
- **`MULTI_TENANT_IMPLEMENTATION.md`** - Multi-tenancy details
- **`STORE_ADMIN_ROLE.md`** - Store-admin role & permissions
- **`USER_MANAGEMENT.md`** - User management guide
- **`QUICK_START.md`** - This file

---

## Support Flow

**For Store Owners:**
1. Register → Become store-admin
2. Add products, customers, suppliers
3. Create store-users for your team
4. Start processing orders

**For Team Members:**
1. Receive login credentials from store-admin
2. Login to system
3. Access sales module only
4. Process orders

**For Developers:**
1. Review documentation files
2. Check Spatie Permission docs
3. Review code in:
   - `app/Traits/BelongsToStore.php`
   - `app/Scopes/StoreScope.php`
   - `app/Modules/Users/Http/Controllers/UserController.php`

---

## Key Files

**Multi-Tenant:**
- `app/Modules/Stores/Models/Store.php`
- `app/Traits/BelongsToStore.php`
- `app/Scopes/StoreScope.php`

**User Management:**
- `app/Modules/Users/Http/Controllers/UserController.php`
- `database/seeders/StoreAdminRoleSeeder.php`

**Registration:**
- `app/Http/Controllers/Auth/RegisterController.php`
- `resources/views/auth/register.blade.php`

**Models (Updated for Multi-Tenancy):**
- All 17 module models use `BelongsToStore` trait

---

## Quick Reference

**Check role:**
```php
auth()->user()->hasRole('store-admin'); // true/false
auth()->user()->hasRole('store-user'); // true/false
```

**Check permission:**
```php
auth()->user()->can('products.create'); // true/false
```

**Get current store:**
```php
auth()->user()->currentStore(); // Store object
auth()->user()->currentStoreId(); // Store ID
```

**Create scoped record:**
```php
Product::create([...]); // store_id auto-filled
```

**Bypass scope (admin):**
```php
Product::withoutGlobalScope(StoreScope::class)->get();
```

---

## System Status

✅ **Multi-tenant system** - Complete
✅ **Store-admin role** - 40 permissions
✅ **Store-user role** - 6 permissions
✅ **User management** - Functional
✅ **Auto-scoping** - Active
✅ **Data isolation** - Enforced
✅ **All migrations** - Applied
✅ **Documentation** - Complete

**System is ready for production use!** 🎉
