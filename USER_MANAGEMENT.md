# User Management for Store Admins

## Overview

Store admins can create and manage users within their store. These users are assigned the `store-user` role with limited permissions (sales module only).

## Roles

### Store-Admin
- **Full permissions** (40 permissions)
- Can manage all aspects of their store
- Can create and manage store users
- Automatically assigned on registration

### Store-User
- **Sales-only permissions** (6 permissions)
- Can only access the sales module
- Cannot manage products, purchases, or settings
- Must be created by a store-admin

## Store-User Permissions

Store-users have access to:

1. **Sales Orders** (Full CRUD)
   - `sales-orders.view`
   - `sales-orders.create`
   - `sales-orders.edit`
   - `sales-orders.delete`

2. **Customers** (View only)
   - `customers.view`

3. **Products** (View only)
   - `products.view`

## How It Works

### For Store Admins

When a store-admin creates a new user:

1. User account is created
2. User is automatically assigned `store-user` role
3. User is associated with the same store as the admin
4. User can only see and access data from their store

### Data Isolation

- Store-admins can only see and manage users from their own store
- Store-users can only access their own store's data
- Complete data isolation between stores

## Using the User Management Interface

### Accessing User Management

Navigate to: `/modules/users`

**Requirements:**
- Must have `store-admin` role
- Must have `users.view` permission

### Creating a New User

1. Click "Create User" button
2. Fill in user details:
   - Name
   - Email
   - Password
   - Role (automatically set to `store-user`)
3. Click "Save"

**What happens:**
- User is created
- User is assigned `store-user` role
- User is linked to your store
- User receives login credentials (email can be sent separately)

### Editing Users

1. Navigate to Users list
2. Click edit icon on the user
3. Update user details
4. Save changes

**Restrictions:**
- Store-admins cannot change user roles (locked to `store-user`)
- Store-admins can only edit users from their store

### Deleting Users

1. Navigate to Users list
2. Click delete icon on the user
3. Confirm deletion

**Restrictions:**
- Cannot delete your own account
- Can only delete users from your store

## API/Controller Logic

### Store Scoping

```php
// Only show users from the same store
if (!auth()->user()->isSuperAdmin()) {
    $storeId = auth()->user()->currentStoreId();
    $query->whereHas('stores', function($q) use ($storeId) {
        $q->where('stores.id', $storeId);
    });
}
```

### Auto-Association with Store

```php
// Automatically link new user to admin's store
if (!auth()->user()->isSuperAdmin()) {
    $storeId = auth()->user()->currentStoreId();
    if ($storeId) {
        $user->stores()->attach($storeId);
    }
}
```

### Role Restriction

```php
// Store-admins can only assign store-user role
if (auth()->user()->isSuperAdmin()) {
    $roles = Role::orderBy('name')->get(); // All roles
} else {
    $roles = Role::whereIn('name', ['store-user'])->get(); // Only store-user
}
```

## Code Examples

### Check if User is Store-User

```php
if (auth()->user()->hasRole('store-user')) {
    // User has limited access
}
```

### Get All Users in Current Store

```php
$storeId = auth()->user()->currentStoreId();
$users = User::whereHas('stores', function($q) use ($storeId) {
    $q->where('stores.id', $storeId);
})->get();
```

### Create User Programmatically

```php
use App\Models\User;
use Illuminate\Support\Facades\Hash;

$user = User::create([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'password' => Hash::make('password123'),
]);

// Assign role
$user->assignRole('store-user');

// Link to store
$user->stores()->attach($storeId);
```

## Security Considerations

### Multi-Tenant Isolation

1. **Store Scoping**: Users can only see users from their store
2. **Role Restrictions**: Store-admins cannot create store-admins
3. **Permission Checks**: All routes protected by permissions
4. **Data Validation**: Server-side validation on all inputs

### Best Practices

1. **Password Strength**: Enforce minimum 6 characters
2. **Email Verification**: Consider adding email verification
3. **Activity Logging**: Track user creation/updates
4. **Two-Factor Auth**: Consider implementing 2FA for admins

## Permission Comparison

| Feature | Store-Admin | Store-User | Superadmin |
|---------|-------------|------------|------------|
| User Management | ✅ (own store) | ❌ | ✅ (all stores) |
| Products | ✅ Full CRUD | 👁️ View Only | ✅ Full CRUD |
| Customers | ✅ Full CRUD | 👁️ View Only | ✅ Full CRUD |
| Sales Orders | ✅ Full CRUD | ✅ Full CRUD | ✅ Full CRUD |
| Purchase Orders | ✅ Full CRUD | ❌ | ✅ Full CRUD |
| Suppliers | ✅ Full CRUD | ❌ | ✅ Full CRUD |
| Reports | ✅ View | ❌ | ✅ View |
| Settings | ✅ View/Edit | ❌ | ✅ Full Access |
| Operating Expenses | ✅ Full CRUD | ❌ | ✅ Full CRUD |
| Returns | ✅ Full CRUD | ❌ | ✅ Full CRUD |
| Stock Movements | ✅ View | ❌ | ✅ View |

## Workflow Example

### Typical Store Setup

**Day 1: Registration**
```
1. Owner registers → Becomes store-admin
2. Store "Tech Shop" is created
3. Owner has full access
```

**Day 2: Add Sales Staff**
```
1. Store-admin logs in
2. Goes to Users → Create User
3. Creates "Jane (Cashier)" → Role: store-user
4. Jane can now process sales orders
5. Jane cannot access inventory or purchases
```

**Day 3: Add Another Sales Staff**
```
1. Store-admin creates "Bob (Cashier)" → Role: store-user
2. Both Jane and Bob can process sales
3. Both see only Tech Shop's data
```

## Testing

### Test Store-Admin Creating User

```bash
php artisan tinker
```

```php
// Get store-admin user
$admin = User::whereHas('roles', fn($q) => $q->where('name', 'store-admin'))->first();

// Create store-user
$user = User::create([
    'name' => 'Test User',
    'email' => 'test@example.com',
    'password' => Hash::make('password'),
]);

$user->assignRole('store-user');
$user->stores()->attach($admin->currentStoreId());

// Verify
$user->hasRole('store-user'); // true
$user->can('sales-orders.create'); // true
$user->can('products.create'); // false
```

### Test Store Isolation

```php
// User from Store A
$userA = User::find(1);
$userA->currentStoreId(); // e.g., 1

// User from Store B
$userB = User::find(2);
$userB->currentStoreId(); // e.g., 2

// UserA creates a customer
$customer = Customer::create(['name' => 'Customer A']);
// Automatically assigned store_id = 1

// UserB tries to see customers
Customer::all(); // Only sees Store B's customers (not Customer A)
```

## Troubleshooting

### User Can't Access Sales Module

**Check:**
```php
$user = User::find($id);
$user->hasRole('store-user'); // Should be true
$user->can('sales-orders.view'); // Should be true
```

**Solution:**
```bash
# Re-run seeder to ensure permissions exist
php artisan db:seed --class=StoreAdminRoleSeeder

# Clear cache
php artisan permission:cache-reset
```

### User Sees Data from Other Stores

**Check:**
```php
$user->stores; // Should only show one store
$user->currentStoreId(); // Should return a number
```

**Solution:**
Ensure user is properly linked to store in `store_users` table.

### Store-Admin Can't Create Users

**Check:**
```php
$admin = User::find($id);
$admin->hasRole('store-admin'); // Should be true
$admin->can('users.create'); // Should be true
```

**Solution:**
```bash
# Re-seed permissions
php artisan db:seed --class=StoreAdminRoleSeeder
```

## Future Enhancements

Potential improvements:

1. **Email Invitations**: Send invitation emails to new users
2. **User Activation**: Require users to activate account via email
3. **Custom Roles**: Allow store-admins to create custom roles
4. **Permission Templates**: Pre-defined permission sets (cashier, manager, etc.)
5. **Bulk User Import**: Import multiple users via CSV
6. **User Activity Log**: Track user actions and logins
7. **Password Reset**: Self-service password reset for users
8. **Profile Management**: Allow users to update their profiles
9. **Two-Factor Auth**: Additional security for user accounts
10. **Session Management**: View and manage active sessions

## Support

For issues or questions:
- Check this documentation
- Review `STORE_ADMIN_ROLE.md` for permission details
- Review `MULTI_TENANT_IMPLEMENTATION.md` for tenant isolation

## Quick Reference

**Create user as store-admin:**
```
Navigate to /modules/users → Create User
```

**User gets:**
- ✅ Store-user role
- ✅ Sales module access
- ✅ View products and customers
- ❌ Cannot manage inventory
- ❌ Cannot access settings
```

**Verify role:**
```php
auth()->user()->hasRole('store-user');
```

**Check permissions:**
```php
auth()->user()->can('sales-orders.create');
```
