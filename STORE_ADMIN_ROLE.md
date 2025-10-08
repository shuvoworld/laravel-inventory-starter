# Store Admin Role Implementation

## Overview

All users who register through the frontend are automatically assigned the **`store-admin`** role with full permissions to manage their store's inventory and sales operations.

## Implementation Details

### 1. Role Creation

The `store-admin` role is created with 48 permissions covering all aspects of store management.

**Seeder:** `database/seeders/StoreAdminRoleSeeder.php`

To create/update the role:
```bash
php artisan db:seed --class=StoreAdminRoleSeeder
```

### 2. Automatic Role Assignment

When a user registers, they are automatically assigned the `store-admin` role in the registration controller.

**File:** `app/Http/Controllers/Auth/RegisterController.php`

```php
// During registration, the user is assigned store-admin role
$user->assignRole('store-admin');
```

### 3. Permissions Granted

Store admins have full CRUD (Create, Read, Update, Delete) access to:

#### Core Inventory
- ✅ Products
- ✅ Stock Movements (view only)

#### Sales Management
- ✅ Customers
- ✅ Sales Orders
- ✅ Sales Returns

#### Purchase Management
- ✅ Suppliers
- ✅ Purchase Orders
- ✅ Purchase Returns

#### Financial
- ✅ Operating Expenses

#### System
- ✅ Settings (view and edit)
- ✅ Reports (view)

## Usage in Code

### Checking Permissions

```php
// Check if user has permission
if (auth()->user()->can('products.create')) {
    // User can create products
}

// Check if user has role
if (auth()->user()->hasRole('store-admin')) {
    // User is a store admin
}
```

### Protecting Routes

```php
// Require specific permission
Route::middleware(['permission:products.create'])->group(function () {
    Route::post('/products', [ProductController::class, 'store']);
});

// Require store-admin role
Route::middleware(['role:store-admin'])->group(function () {
    Route::resource('products', ProductController::class);
});
```

### Blade Directives

```blade
@can('products.create')
    <a href="{{ route('products.create') }}">Create Product</a>
@endcan

@role('store-admin')
    <div class="admin-panel">Admin Controls</div>
@endrole
```

## Permission List

### Products
- `products.view` - View products
- `products.create` - Create new products
- `products.edit` - Edit existing products
- `products.delete` - Delete products

### Customers
- `customers.view` - View customers
- `customers.create` - Create new customers
- `customers.edit` - Edit existing customers
- `customers.delete` - Delete customers

### Sales Orders
- `sales-orders.view` - View sales orders
- `sales-orders.create` - Create new sales orders
- `sales-orders.edit` - Edit existing sales orders
- `sales-orders.delete` - Delete sales orders

### Purchase Orders
- `purchase-orders.view` - View purchase orders
- `purchase-orders.create` - Create new purchase orders
- `purchase-orders.edit` - Edit existing purchase orders
- `purchase-orders.delete` - Delete purchase orders

### Suppliers
- `suppliers.view` - View suppliers
- `suppliers.create` - Create new suppliers
- `suppliers.edit` - Edit existing suppliers
- `suppliers.delete` - Delete suppliers

### Stock Movements
- `stock-movements.view` - View stock movement history

### Reports
- `reports.view` - View reports and analytics

### Settings
- `settings.view` - View settings
- `settings.edit` - Edit settings

### Operating Expenses
- `operating-expenses.view` - View operating expenses
- `operating-expenses.create` - Create new expenses
- `operating-expenses.edit` - Edit existing expenses
- `operating-expenses.delete` - Delete expenses

### Sales Returns
- `sales-returns.view` - View sales returns
- `sales-returns.create` - Create new sales returns
- `sales-returns.edit` - Edit existing sales returns
- `sales-returns.delete` - Delete sales returns

### Purchase Returns
- `purchase-returns.view` - View purchase returns
- `purchase-returns.create` - Create new purchase returns
- `purchase-returns.edit` - Edit existing purchase returns
- `purchase-returns.delete` - Delete purchase returns

## Additional Roles

You can create additional roles with limited permissions for your store:

### Example: Creating a Cashier Role

```php
use Spatie\Permission\Models\Role;

$cashier = Role::create(['name' => 'cashier']);

// Limited permissions for cashier
$cashier->givePermissionTo([
    'sales-orders.view',
    'sales-orders.create',
    'products.view',
    'customers.view',
]);
```

### Example: Creating a Stock Manager Role

```php
$stockManager = Role::create(['name' => 'stock-manager']);

// Stock-focused permissions
$stockManager->givePermissionTo([
    'products.view',
    'products.create',
    'products.edit',
    'stock-movements.view',
    'purchase-orders.view',
    'purchase-orders.create',
]);
```

## Testing

To verify the role assignment:

```bash
php artisan tinker
```

```php
// Get a user
$user = User::first();

// Check roles
$user->getRoleNames(); // Returns: ["store-admin"]

// Check permissions
$user->getAllPermissions()->pluck('name'); // Returns all 48 permissions

// Test specific permission
$user->can('products.create'); // Returns: true
```

## Troubleshooting

### Role Not Found Error

If you get "Role does not exist" error during registration:

```bash
# Run the seeder to create the role
php artisan db:seed --class=StoreAdminRoleSeeder

# Clear cache
php artisan cache:clear
```

### Permissions Not Working

```bash
# Clear permission cache
php artisan permission:cache-reset

# Or clear all cache
php artisan optimize:clear
```

### Checking Role Exists

```bash
php artisan tinker
```

```php
use Spatie\Permission\Models\Role;

// Check if role exists
Role::where('name', 'store-admin')->exists(); // Should return true

// Get permission count
Role::where('name', 'store-admin')->first()->permissions->count(); // Should return 48
```

## Security Notes

1. **Multi-tenant Isolation**: Store-admin permissions only apply to their own store's data due to the global `StoreScope`
2. **No Cross-Store Access**: Users cannot access data from other stores, even with permissions
3. **Superadmin vs Store-Admin**: The `is_superadmin` flag bypasses store restrictions, while `store-admin` is limited to their store
4. **Permission Enforcement**: Always check permissions in controllers and routes for security

## Future Enhancements

Potential improvements:
- Create UI for store owners to invite team members
- Allow store admins to create sub-roles (cashier, stock manager, etc.)
- Permission templates for common role types
- Activity log for permission changes
- Store-level settings for role customization
