# Stock Movement System

## Overview

The Stock Movement system serves as the auditable source of truth for all inventory transactions in the Laravel Inventory Management system. It tracks every movement of stock in and out of inventory with complete audit trails.

## Features

### 1. Comprehensive Transaction Tracking
- **Purchase Orders**: Records inbound stock when purchases are created
- **Sales Orders**: Records outbound stock when sales are made
- **Purchase Returns**: Records stock returns to suppliers
- **Sales Returns**: Records stock returns from customers
- **Manual Adjustments**: Manual stock corrections and adjustments
- **Special Transactions**: Damage, theft, transfers, opening stock

### 2. Movement Types
- **in**: Stock increases (purchases, returns, opening stock)
- **out**: Stock decreases (sales, purchase returns, damage, theft)
- **adjustment**: Manual stock level adjustments

### 3. Transaction Types
- `purchase`: Purchase order receiving
- `purchase_return`: Items returned to suppliers
- `sale`: Sales order fulfillment
- `sale_return`: Items returned by customers
- `manual_adjustment`: Manual stock corrections
- `opening_stock`: Initial inventory setup
- `transfer`: Stock transfers between locations
- `damage`: Damaged/written-off inventory
- `theft`: Stolen inventory

### 4. Audit Trail
- Full audit logging for every movement
- User tracking for all transactions
- Reference linking to original documents
- Date/time stamping of all movements

## Architecture

### Core Components

#### 1. StockMovement Model (`app/Modules/StockMovement/Models/StockMovement.php`)
- Defines the stock movement entity
- Handles relationships with products, users, and references
- Provides movement type constants and methods
- Automatic stock quantity updates via model events

#### 2. StockMovementService (`app/Services/StockMovementService.php`)
- Central service for recording all stock movements
- Type-safe methods for different transaction types
- Validation and transaction safety
- Bulk movement processing capabilities

#### 3. StockMovementReportService (`app/Services/StockMovementReportService.php`)
- Comprehensive reporting functionality
- Movement history and trend analysis
- Inventory valuation calculations
- Export capabilities (CSV)
- Audit trail generation

## Usage Examples

### Recording a Purchase
```php
StockMovementService::recordPurchase(
    $productId,
    $quantity,
    $purchaseOrderId,
    "Purchase - Order #PO12345"
);
```

### Recording a Sale
```php
StockMovementService::recordSale(
    $productId,
    $quantity,
    $salesOrderId,
    "Sale - Order #SO67890"
);
```

### Recording a Sales Return
```php
StockMovementService::recordSaleReturn(
    $productId,
    $quantity,
    $salesOrderId,
    "Return - Order #SO67890. Reason: Customer dissatisfaction"
);
```

### Manual Stock Adjustment
```php
StockMovementService::recordAdjustment(
    $productId,
    'out', // movement_type: 'in', 'out', or 'adjustment'
    $quantity,
    "Stock adjustment: Physical count discrepancy"
);
```

## Integration Points

### 1. Sales Orders
- Automatic stock movement creation when sales orders are processed
- Stock validation before order completion
- Return processing with stock restoration

### 2. Purchase Orders
- Stock increase when purchase orders are created
- Adjustment handling for order modifications
- Return processing for supplier returns

### 3. Product Model
- Automatic quantity updates via stock movement events
- Current stock calculation through movements
- Stock availability validation

## Reporting Features

### 1. Movement Reports
- Filterable by date, product, movement type, transaction type
- Summary statistics and trends
- User activity tracking

### 2. Product History
- Complete movement history per product
- Stock level progression over time
- Transaction type breakdowns

### 3. Audit Trails
- Individual movement audit logs
- Before/after state tracking
- User responsibility tracking

### 4. Inventory Valuation
- Current stock value calculations
- Cost basis tracking
- Profit/loss analysis

### 5. Trend Analysis
- Daily/weekly/monthly movement trends
- Seasonal pattern identification
- Anomaly detection

## Data Integrity

### 1. Transaction Safety
- All movements wrapped in database transactions
- Rollback capabilities for failed operations
- Atomic quantity updates

### 2. Validation
- Stock availability checks before outbound movements
- Reference validation for linked transactions
- User authentication and authorization

### 3. Audit Compliance
- Immutable movement records
- Complete change tracking
- Regulatory compliance support

## API Endpoints

### Stock Movement Controller Methods
- `index()` - List all movements with DataTables
- `report()` - Comprehensive reporting interface
- `productHistory($productId)` - Product-specific history
- `auditTrail($id)` - Individual movement audit
- `export()` - CSV export functionality
- `valuation()` - Inventory valuation report
- `trends()` - Movement trend analysis

## Database Schema

### Stock Movements Table Structure
```sql
- id (primary key)
- store_id (store association)
- product_id (foreign key to products)
- movement_type (in/out/adjustment)
- transaction_type (purchase/sale/return/etc)
- quantity (movement quantity)
- reference_type (related model type)
- reference_id (related model ID)
- notes (movement description)
- user_id (who performed the movement)
- created_at/updated_at (timestamps)
```

## Best Practices

### 1. Movement Recording
- Always use `StockMovementService` methods
- Provide descriptive notes for audit purposes
- Include proper reference linking
- Validate stock availability before outbound movements

### 2. Error Handling
- Wrap movements in try-catch blocks
- Provide user-friendly error messages
- Log failed movement attempts
- Implement retry logic for transient failures

### 3. Performance
- Use bulk operations for multiple movements
- Implement proper indexing on movement table
- Cache frequently accessed calculations
- Archive old movements periodically

### 4. Security
- Validate user permissions for movement types
- Audit all movement modifications
- Implement proper access controls
- Monitor for suspicious movement patterns

## Future Enhancements

### 1. Advanced Features
- Multi-location inventory support
- Automated stock reordering
- Integration with barcode scanners
- Real-time stock updates

### 2. Analytics
- Predictive stock requirements
- Demand forecasting
- Supplier performance tracking
- Customer return analysis

### 3. Integrations
- Accounting system integration
- POS system synchronization
- E-commerce platform connections
- Third-party logistics support

## Troubleshooting

### Common Issues
1. **Stock Quantity Discrepancies**: Run reconciliation reports and manual adjustments
2. **Missing Movements**: Check service integration and transaction logs
3. **Performance Issues**: Implement proper indexing and data archiving
4. **Audit Gaps**: Verify user permissions and logging configuration

### Maintenance Tasks
- Regular stock reconciliation
- Archive old movement data
- Performance monitoring
- Audit log review
- Index optimization

This comprehensive stock movement system provides the foundation for accurate inventory tracking, complete audit compliance, and valuable business intelligence through detailed reporting and analysis capabilities.