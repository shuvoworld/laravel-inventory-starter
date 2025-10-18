# Automated Stock Synchronization System

## 🎯 Overview

The Stock Movements table is now the **foundation and single source of truth** for all inventory tracking. This system ensures that product stock quantities (`quantity_on_hand`) are automatically updated every hour based on the cumulative stock movements.

## 🔄 System Architecture

### **Primary Data Source: Stock Movements Table**
- **Foundation**: All stock counts are derived from `stock_movements` table
- **Calculation**: `Stock = SUM(IN quantities) - SUM(OUT quantities)`
- **Authority**: Stock movements override manual product updates

### **Update Mechanisms**
1. **Immediate Updates**: Triggered after each stock movement (asynchronous via queue)
2. **Hourly Synchronization**: Comprehensive system-wide sync (scheduled job)
3. **Manual Commands**: On-demand synchronization capabilities

## 📊 Stock Calculation Logic

### **Positive Quantity (+) - Stock Coming IN**
```php
'purchase'           // Purchase orders from suppliers
'sale_return'        // Customer returns
'opening_stock'       // Initial inventory setup
'transfer_in'         // Stock from other locations
'stock_count_correction' // Physical count positive adjustments
'recovery_found'      // Previously missing items found
'manufacturing_in'    // Finished production items
```

### **Negative Quantity (-) - Stock Going OUT**
```php
'sale'               // Sales to customers
'purchase_return'    // Returns to suppliers
'damage'             // Damaged items removed
'lost_missing'       // Missing/lost inventory
'theft'              // Stolen items
'expired'            // Expired items removed
'transfer_out'       // Stock to other locations
'stock_count_correction_minus' // Physical count negative adjustments
'quality_control'     // QA failures
'manufacturing_out'  // Raw material consumption
'promotional'         // Marketing samples/promotions
```

### **Stock Formula**
```sql
SELECT
    product_id,
    SUM(CASE WHEN movement_type = 'in' THEN quantity ELSE 0 END) as total_in,
    SUM(CASE WHEN movement_type = 'out' THEN quantity ELSE 0 END) as total_out,
    (SUM(CASE WHEN movement_type = 'in' THEN quantity ELSE 0 END) -
     SUM(CASE WHEN movement_type = 'out' THEN quantity ELSE 0 END)) as calculated_stock
FROM stock_movements
GROUP BY product_id;
```

## ⏰ Scheduling System

### **1. Automatic Hourly Synchronization**
```php
// Scheduled in bootstrap/app.php
$schedule->job(new SyncProductStockFromMovements())
    ->hourly()
    ->description('Synchronize product stock quantities from stock movements')
    ->onSuccess(fn() => Log::info('Hourly sync completed'))
    ->onFailure(fn($e) => Log::error('Hourly sync failed', ['error' => $e->getMessage()]));
```

**Schedule**: Runs every hour at the top of the hour
**Scope**: All products with stock movements
**Queue**: `stock-sync` queue
**Timeout**: 5 minutes
**Retries**: 3 attempts (1min, 5min, 15min backoff)

### **2. Immediate Update Triggers**
```php
// Triggered in StockMovement model boot()
static::created(function ($movement) {
    dispatch(function () use ($movement) {
        // Update specific product stock immediately
        $calculatedStock = StockMovement::where('product_id', $movement->product_id)
            ->selectRaw("
                SUM(CASE WHEN movement_type = 'in' THEN quantity ELSE 0 END) as total_in,
                SUM(CASE WHEN movement_type = 'out' THEN quantity ELSE 0 END) as total_out
            ")->first();

        $product->quantity_on_hand = ($calculatedStock->total_in ?? 0) - ($calculatedStock->total_out ?? 0);
        $product->save();
    })->onQueue('stock-sync');
});
```

## 🛠️ Management Commands

### **Primary Command**
```bash
php artisan stock:sync-from-movements [options]
```

### **Command Options**
```bash
# Dry run - see what would be updated without changes
php artisan stock:sync-from-movements --dry-run

# Sync specific product
php artisan stock:sync-from-movements --product-id=123

# Sync from specific date
php artisan stock:sync-from-movements --from-date="2025-01-01 00:00:00"

# Force update all products (not just discrepancies)
php artisan stock:sync-from-movements --force

# Dispatch to queue instead of running synchronously
php artisan stock:sync-from-movements --queue
```

### **Command Output Examples**

#### **Dry Run Output**
```
🔄 Starting stock synchronization from movements...
📋 Configuration:
  Product ID: All products
  From Date: Beginning of time
  Force Update: No
  Use Queue: No
  Dry Run: Yes

🔍 Performing dry run analysis...
Found 4 products with stock movements
+------------+--------------------------------+--------------+------------------+------------+-----------+
| Product ID | Product Name                   | System Stock | Calculated Stock | Difference | Movements |
+------------+--------------------------------+--------------+------------------+------------+-----------+
| 1          | Dell Latitude 5520 Laptop      | 27           | 15               | +12        | 6         |
| 2          | Samsung 27" LED Monitor        | 50           | 10               | +40        | 1         |
+------------+--------------------------------+--------------+------------------+------------+-----------+

Products with discrepancies: 2
💡 Use --force flag to update all products, or let the system update only discrepancies automatically.
```

#### **Successful Sync Output**
```
⚡ Executing stock synchronization synchronously...
✅ Stock synchronization completed in 0.067742 seconds
```

## 📈 Monitoring & Logging

### **Comprehensive Logging**
```php
// Movement creation logs
Log::info('Stock movement created', [
    'movement_id' => 123,
    'product_id' => 456,
    'movement_type' => 'out',
    'transaction_type' => 'sale',
    'quantity' => 5,
    'user_id' => 789
]);

// Sync completion logs
Log::info('Product stock updated', [
    'product_id' => 456,
    'product_name' => 'Dell Laptop',
    'old_stock' => 20,
    'new_stock' => 15,
    'difference' => -5
]);
```

### **Log Categories**
- `info`: Movement creation, stock updates, sync completions
- `warning`: Discrepancies detected, movement deletions
- `error`: Failed updates, database errors, job failures
- `critical`: Permanent sync failures after retries

### **Monitoring Commands**
```bash
# Check recent stock sync logs
php artisan log:show --level=info --grep="stock synchronization"

# Monitor queue jobs
php artisan queue:monitor stock-sync

# Check failed jobs
php artisan queue:failed-table
```

## 🔄 Job System Details

### **SyncProductStockFromMovements Job**
```php
class SyncProductStockFromMovements implements ShouldQueue
{
    use Queueable;

    public $timeout = 300;        // 5 minutes
    public $tries = 3;            // 3 retry attempts
    public $backoff = [60, 300, 900]; // 1min, 5min, 15min backoff

    public function __construct(
        public ?int $productId = null,      // Specific product or all
        public ?Carbon $fromDate = null,   // From specific date
        public bool $forceUpdate = false    // Force all updates
    ) {
        $this->onQueue('stock-sync');
    }
}
```

### **Job Features**
- **Single Product Sync**: `new SyncProductStockFromMovements(123)`
- **Date Range Sync**: `new SyncProductStockFromMovements(null, Carbon::yesterday())`
- **Force Updates**: `new SyncProductStockFromMovements(null, null, true)`

## 📊 Data Flow Diagram

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Stock Movement  │    │   SyncProductStock │    │    Product       │
│   Creation       │───▶│   FromMovements  │───▶│   quantity_on_hand│
│   (IN/OUT)        │    │      (Hourly)    │    │     (Updated)    │
└─────────────────┘    └─────────────────┘    └─────────────────┘
         │                       │                       │
         ▼                       ▼                       ▼
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│  Immediate Sync │    │  Comprehensive   │    │   Real-time     │
│   (Queue)        │    │  Sync (All)      │    │   Accuracy      │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

## 🎯 Business Rules

### **Stock Movement Authority**
1. **Stock movements table** = **Source of Truth**
2. **Product.quantity_on_hand** = **Derived value**
3. **Discrepancies** = **Auto-corrected by sync**
4. **Manual product updates** = **Overridden by sync**

### **Update Priorities**
1. **Immediate**: Queue job after each movement (fast, async)
2. **Scheduled**: Hourly comprehensive sync (complete, reliable)
3. **Manual**: Command execution for specific needs

### **Error Handling**
- **Temporary failures**: Automatic retry with exponential backoff
- **Permanent failures**: Critical alerts and logging
- **Partial failures**: Continue with other products
- **Data integrity**: Database transactions prevent corruption

## 🔧 Configuration

### **Queue Configuration**
```php
// config/queue.php
'connections' => [
    'stock-sync' => [
        'driver' => 'database',
        'table' => 'jobs',
        'queue' => 'stock-sync',
        'retry_after' => 90,
        'after_commit' => false,
    ],
],
```

### **Job Configuration**
```php
// Timeout: 5 minutes (prevents long-running jobs)
// Retries: 3 attempts with backoff
// Queue: 'stock-sync' (isolated from other jobs)
```

## ✅ Benefits

### **1. Data Integrity**
- **Single source of truth** (stock movements)
- **Consistent calculations** across all products
- **Automatic discrepancy correction**
- **Complete audit trail**

### **2. Real-time Accuracy**
- **Immediate updates** after movements
- **Hourly comprehensive sync** for catch-up
- **Manual sync capabilities** for special needs

### **3. Operational Efficiency**
- **Automated process** requiring no manual intervention
- **Comprehensive logging** for monitoring
- **Queue-based processing** for scalability
- **Flexible command options** for admin needs

### **4. Business Intelligence**
- **Accurate stock counts** at all times
- **Discrepancy detection** and reporting
- **Movement pattern analysis**
- **Performance monitoring**

## 🚀 Usage Examples

### **Daily Operations**
```bash
# Check for discrepancies
php artisan stock:sync-from-movements --dry-run

# Force immediate sync if needed
php artisan stock:sync-from-movements --force
```

### **Troubleshooting**
```bash
# Sync specific product with issues
php artisan stock:sync-from-movements --product-id=123

# Check recent sync logs
php artisan log:show --level=error --grep="stock"
```

### **Data Recovery**
```bash
# Reconstruct stock from specific date
php artisan stock:sync-from-movements --from-date="2025-01-01 00:00:00" --force
```

## 📋 Implementation Status

✅ **Completed Features:**
- ✅ Hourly automated synchronization job
- ✅ Immediate update triggers after movements
- ✅ Comprehensive command-line interface
- ✅ Queue-based asynchronous processing
- ✅ Dry-run capability for previewing changes
- ✅ Complete logging and monitoring
- ✅ Error handling with retries
- ✅ Discrepancy detection and correction
- ✅ Data integrity protection
- ✅ Performance optimization

🚀 **System Status: ACTIVE AND FULLY FUNCTIONAL**

The stock movements table now serves as the definitive foundation for all inventory counting, with automatic hourly updates ensuring product stock quantities remain accurate and consistent with all transaction history.