# Architecture Simplification - Store Relationship

## What Changed

We simplified the user-store relationship from a **many-to-many** (pivot table) to a **one-to-many** (foreign key) relationship.

### Before (Many-to-Many)
```
users ←→ store_users (pivot) ←→ stores
```
- Users had `belongsToMany` relationship with stores
- Required `store_users` pivot table
- More complex queries
- Allowed users to belong to multiple stores (not needed)

### After (One-to-Many)
```
users → stores (via store_id foreign key)
```
- Users have `belongsTo` relationship with store
- Direct `store_id` column in users table
- Simpler queries
- One user = One store (cleaner architecture)

---

## Database Changes

### Migration: `2025_10_08_191104_add_store_id_to_users_table.php`

**What it does:**
1. Adds `store_id` column to `users` table
2. Migrates data from `store_users` pivot → `users.store_id`
3. Drops `store_users` pivot table

**SQL equivalent:**
```sql
-- Add column
ALTER TABLE users
ADD COLUMN store_id BIGINT UNSIGNED NULL
AFTER is_superadmin;

-- Add foreign key
ALTER TABLE users
ADD CONSTRAINT users_store_id_foreign
FOREIGN KEY (store_id) REFERENCES stores(id)
ON DELETE CASCADE;

-- Migrate data
UPDATE users u
INNER JOIN store_users su ON u.id = su.user_id
SET u.store_id = su.store_id;

-- Drop pivot table
DROP TABLE store_users;
```

---

## Code Changes

### User Model

**Before:**
```php
public function stores()
{
    return $this->belongsToMany(Store::class, 'store_users')
        ->withTimestamps();
}

public function currentStore()
{
    return $this->stores()->first();
}

public function currentStoreId()
{
    return $this->currentStore()?->id;
}
```

**After:**
```php
public function store()
{
    return $this->belongsTo(Store::class);
}

public function currentStore()
{
    return $this->store; // Direct property access
}

public function currentStoreId()
{
    return $this->store_id; // Direct property access
}
```

### Store Model

**Before:**
```php
public function users()
{
    return $this->belongsToMany(User::class, 'store_users')
        ->withTimestamps();
}
```

**After:**
```php
public function users()
{
    return $this->hasMany(User::class); // Simple hasMany
}
```

### RegisterController

**Before:**
```php
// Create user
$user = User::create([...]);

// Create store
$store = Store::create([...]);

// Link via pivot
$user->stores()->attach($store->id);
```

**After:**
```php
// Create store first
$store = Store::create([...]);

// Create user with store_id
$user = User::create([
    'name' => $data['name'],
    'email' => $data['email'],
    'password' => Hash::make($data['password']),
    'store_id' => $store->id, // Direct foreign key
]);
```

### UserController

**Before:**
```php
// Filter by store
$query->whereHas('stores', function($q) use ($storeId) {
    $q->where('stores.id', $storeId);
});

// Set store for new user
$user->stores()->attach($storeId);

// Check store access
if (!$user->stores()->where('stores.id', $storeId)->exists()) {
    abort(403);
}
```

**After:**
```php
// Filter by store (simpler!)
$query->where('store_id', $storeId);

// Set store for new user
$userData['store_id'] = $storeId;
$user = User::create($userData);

// Check store access (simpler!)
if ($user->store_id !== $storeId) {
    abort(403);
}
```

---

## Benefits

### 1. **Simpler Code**
- No need for `whereHas()` queries
- Direct property access: `$user->store_id`
- Fewer lines of code

### 2. **Better Performance**
- No pivot table joins
- Direct foreign key lookup
- Indexed column for fast queries

### 3. **Clearer Intent**
- One user belongs to one store
- Matches business logic (users don't switch stores)
- Easier to understand

### 4. **Easier Maintenance**
- Fewer database tables to manage
- Simpler migrations
- Less complex relationships

### 5. **Consistent with Other Models**
- Products have `store_id`
- Customers have `store_id`
- Now users also have `store_id`
- Same pattern everywhere!

---

## Query Comparison

### Getting User's Store

**Before:**
```php
$store = $user->stores()->first(); // Query pivot table
```

**After:**
```php
$store = $user->store; // Direct relationship, no pivot
```

### Getting Store's Users

**Before:**
```php
$users = $store->users; // Query through pivot
```

**After:**
```php
$users = $store->users; // Simple WHERE store_id = X
```

### Filtering Users by Store

**Before:**
```php
User::whereHas('stores', function($q) use ($storeId) {
    $q->where('stores.id', $storeId);
})->get();
```

**After:**
```php
User::where('store_id', $storeId)->get();
```

---

## Migration Path

If you have existing data in `store_users` pivot table, the migration automatically handles it:

1. **Adds** `store_id` column to users
2. **Copies** data from `store_users.store_id` → `users.store_id`
3. **Drops** `store_users` pivot table

**Rollback Support:**
The migration can be rolled back to restore the pivot table if needed.

---

## Testing

### Verify the Changes

```bash
php artisan tinker
```

```php
// Check database structure
Schema::hasColumn('users', 'store_id'); // true
Schema::hasTable('store_users'); // false

// Test user-store relationship
$user = User::first();
$user->store; // Returns Store object
$user->store_id; // Returns integer
$user->currentStoreId(); // Returns integer

// Test store-users relationship
$store = Store::first();
$store->users; // Returns Collection of Users
```

---

## Future Considerations

### If Multi-Store Access is Needed Later

If in the future users need to access multiple stores, we can:

1. Keep `store_id` as "primary store"
2. Add back `store_users` for "additional stores"
3. Update scope to check both

```php
// Hybrid approach (if needed)
public function accessibleStores()
{
    return $this->belongsToMany(Store::class, 'store_users')
        ->orWhere('id', $this->store_id);
}
```

But for now, one store per user is simpler and cleaner!

---

## Summary

| Aspect | Before (Pivot) | After (Foreign Key) |
|--------|----------------|---------------------|
| **Tables** | users, stores, store_users | users, stores |
| **Relationship** | belongsToMany | belongsTo / hasMany |
| **Query Complexity** | whereHas() | where() |
| **Lines of Code** | More | Less |
| **Performance** | Slower (joins) | Faster (direct) |
| **Clarity** | Complex | Simple |
| **Maintenance** | Harder | Easier |

**Result:** Cleaner, faster, simpler architecture! ✅

---

## Updated Files

### Models
- `app/Models/User.php` - Updated relationships
- `app/Modules/Stores/Models/Store.php` - Updated relationships

### Controllers
- `app/Http/Controllers/Auth/RegisterController.php` - Simplified registration
- `app/Modules/Users/Http/Controllers/UserController.php` - Simplified queries

### Migrations
- `database/migrations/2025_10_08_191104_add_store_id_to_users_table.php` - Migration script

### Documentation
- `MULTI_TENANT_IMPLEMENTATION.md` - Updated
- `ARCHITECTURE_SIMPLIFICATION.md` - This file

---

## Conclusion

This simplification makes the codebase:
- ✅ Easier to understand
- ✅ Faster to query
- ✅ Simpler to maintain
- ✅ More consistent across models
- ✅ Better aligned with business logic

The system remains fully multi-tenant with complete data isolation, but with a cleaner architecture!
