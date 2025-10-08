# Multi-Tenant Store Implementation

This document describes the multi-tenant implementation based on `store_id` for the inventory and sales system.

## Overview

The system has been converted to a multi-tenant architecture where:
- Each user can belong to one or more stores
- Data is isolated by `store_id`
- Registration automatically creates a store
- All queries are automatically scoped to the current user's store

## Database Structure

### New Tables

#### `stores`
- `id` - Primary key
- `name` - Store name
- `slug` - Unique slug for the store
- `address` - Store address (optional)
- `phone` - Store phone (optional)
- `email` - Store email (optional)
- `is_active` - Active status flag
- `created_at`, `updated_at`, `deleted_at`

### Modified Tables

#### `users` table
- Added `store_id` - Foreign key to stores table (nullable, with cascade delete)
- Index on `store_id` for performance

All module tables now include:
- `store_id` - Foreign key to stores table (nullable, with cascade delete)
- Index on `store_id` for performance

Affected tables:
- `customers`
- `operating_expenses`
- `products`
- `purchase_orders`
- `purchase_order_items`
- `purchase_returns`
- `purchase_return_items`
- `sales_orders`
- `sales_order_items`
- `sales_returns`
- `sales_return_items`
- `settings`
- `stock_movements`
- `store_settings`
- `types`
- `suppliers`

## Code Implementation

### Models

All models have been updated with the `BelongsToStore` trait:

```php
use App\Traits\BelongsToStore;

class Product extends Model
{
    use BelongsToStore;

    protected $fillable = ['store_id', 'sku', 'name', ...];
}
```

### BelongsToStore Trait

Location: `app/Traits/BelongsToStore.php`

Features:
- Applies `StoreScope` global scope for automatic filtering
- Auto-fills `store_id` when creating records
- Provides `store()` relationship method

### StoreScope

Location: `app/Scopes/StoreScope.php`

Automatically filters all queries to the current user's store:

```php
// Automatically applied:
Product::all(); // Only returns products for current user's store

// To bypass the scope (e.g., for admin):
Product::withoutGlobalScope(StoreScope::class)->get();
```

### User Model Relationships

```php
// Get user's store
$user->store();

// Get current store
$user->currentStore(); // Returns $user->store

// Get current store ID
$user->currentStoreId(); // Returns $user->store_id
```

### Store Model Relationships

```php
// Get store's users (hasMany relationship)
$store->users();

// Check if store is active
$store->isActive();
```

## Registration Process

When a user registers:

1. Store is created with the provided store name
2. User account is created with `store_id` set to the new store
3. **User is assigned the `store-admin` role**
4. Unique slug is generated for the store

Modified files:
- `resources/views/auth/register.blade.php` - Added store name field
- `app/Http/Controllers/Auth/RegisterController.php` - Creates store, creates user with store_id, assigns role

## Roles and Permissions

### Store Admin Role

All newly registered users are automatically assigned the `store-admin` role with the following permissions:

**Products Management:**
- `products.view`
- `products.create`
- `products.edit`
- `products.delete`

**Customer Management:**
- `customers.view`
- `customers.create`
- `customers.edit`
- `customers.delete`

**Sales Orders:**
- `sales-orders.view`
- `sales-orders.create`
- `sales-orders.edit`
- `sales-orders.delete`

**Purchase Orders:**
- `purchase-orders.view`
- `purchase-orders.create`
- `purchase-orders.edit`
- `purchase-orders.delete`

**Supplier Management:**
- `suppliers.view`
- `suppliers.create`
- `suppliers.edit`
- `suppliers.delete`

**Stock Movement:**
- `stock-movements.view`

**Reports:**
- `reports.view`

**Settings:**
- `settings.view`
- `settings.edit`

**Operating Expenses:**
- `operating-expenses.view`
- `operating-expenses.create`
- `operating-expenses.edit`
- `operating-expenses.delete`

**Sales Returns:**
- `sales-returns.view`
- `sales-returns.create`
- `sales-returns.edit`
- `sales-returns.delete`

**Purchase Returns:**
- `purchase-returns.view`
- `purchase-returns.create`
- `purchase-returns.edit`
- `purchase-returns.delete`

### Creating the Role

The `store-admin` role is created via seeder:

```bash
php artisan db:seed --class=StoreAdminRoleSeeder
```

Location: `database/seeders/StoreAdminRoleSeeder.php`

## Usage Examples

### Creating Records

Records automatically get the current user's `store_id`:

```php
// No need to manually set store_id
Product::create([
    'sku' => 'SKU001',
    'name' => 'Product Name',
    'price' => 100
]);
// store_id is automatically set
```

### Querying Records

All queries are automatically scoped:

```php
// Only returns products for current user's store
$products = Product::all();

// Relationships also respect the scope
$customer->salesOrders; // Only orders from same store
```

### Bypassing the Scope

For superadmin or cross-store operations:

```php
// Remove store scope
Product::withoutGlobalScope(StoreScope::class)->get();

// Remove all global scopes
Product::withoutGlobalScopes()->get();
```

## Migration Instructions

To apply the multi-tenant structure:

```bash
# Run migrations
php artisan migrate

# If you have existing data, you'll need to:
# 1. Create a default store
# 2. Update existing records to assign them to stores
# 3. Associate existing users with stores
```

### Migration for Existing Data (Example)

```php
use App\Modules\Stores\Models\Store;
use App\Models\User;

// Create a default store
$store = Store::create([
    'name' => 'Default Store',
    'slug' => 'default-store',
    'is_active' => true
]);

// Assign all existing users to the store
User::all()->each(function($user) use ($store) {
    $user->stores()->attach($store->id);
});

// Update all existing records
DB::table('products')->update(['store_id' => $store->id]);
DB::table('customers')->update(['store_id' => $store->id]);
// ... repeat for other tables
```

## Security Considerations

1. **Data Isolation**: All data is automatically filtered by store_id
2. **Cascade Delete**: When a store is deleted, all related records are deleted
3. **Index Performance**: All store_id columns are indexed for fast queries
4. **Relationship Integrity**: Foreign keys enforce referential integrity

## Future Enhancements

Possible improvements:
- Store switching in the UI for users with multiple stores
- Store-level settings and customization
- Cross-store reporting for superadmins
- Store invitation system for adding users
- Role-based permissions per store
- Store subscription/billing management

## Testing

To test multi-tenancy:

1. Register multiple users with different store names
2. Create products, customers, orders for each user
3. Verify that users only see their own store's data
4. Test that queries are properly scoped
5. Verify cascade deletes work correctly

## Troubleshooting

### Store ID Not Being Set
- Ensure user is authenticated
- Check that `BelongsToStore` trait is used
- Verify `currentStoreId()` returns a value

### Seeing Data from Other Stores
- Check if `StoreScope` is applied to the model
- Verify the user has a store association
- Check for `withoutGlobalScope()` calls

### Migration Errors
- Ensure stores table is created first
- Check that existing data has been migrated
- Verify foreign key constraints are valid
