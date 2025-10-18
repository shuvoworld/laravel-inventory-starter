# Seeders Documentation

This document describes the test data seeders created for the Tech Inventory System.

## Overview

Two comprehensive seeders have been created to populate the database with realistic test data:

1. **CustomerSeeder** - 15 diverse customers
2. **ProductSeeder** - 29 products across various categories

## Running the Seeders

### Run All Seeders (Recommended for Fresh Setup)
```bash
php artisan db:seed
```
This will run all seeders in order, including roles, users, customers, and products.

### Run Individual Seeders
```bash
# Seed customers only
php artisan db:seed --class=CustomerSeeder

# Seed products only
php artisan db:seed --class=ProductSeeder
```

### Fresh Database with Seeders
```bash
php artisan migrate:fresh --seed
```

## CustomerSeeder

Creates **15 customers** for the demo store, including:

### Business Customers (10)
- **Acme Corporation** - New York, NY
- **Tech Solutions Inc** - San Francisco, CA
- **Global Trading Ltd** - Chicago, IL
- **Metro Retail Group** - Boston, MA
- **Sunrise Electronics** - Austin, TX
- **Pacific Distributors** - Seattle, WA
- **Mountain View Supplies** - Denver, CO
- **Coastal Enterprises** - Miami, FL
- **Central Office Supplies** - Atlanta, GA
- **Northwest Equipment Co** - Portland, OR

### Individual Customers (5)
- **John Smith** - Phoenix, AZ
- **Sarah Johnson** - Philadelphia, PA
- **Michael Brown** - San Diego, CA
- **Emily Davis** - Dallas, TX
- **Robert Wilson** - Houston, TX

### Customer Data Structure
Each customer includes:
- Full name
- Email address
- Phone number
- Complete address (street, city, state, postal code, country)

## ProductSeeder

Creates **29 products** across multiple categories:

### Electronics (6 products)
- Dell Latitude 5520 Laptop - **$1,299.99** (25 in stock)
- Samsung 27" LED Monitor - **$349.99** (40 in stock)
- Logitech Wireless Keyboard - **$79.99** (60 in stock)
- Microsoft Ergonomic Mouse - **$49.99** (75 in stock)
- Logitech HD Webcam Pro - **$129.99** (30 in stock)
- Sony WH-1000XM5 Headphones - **$399.99** (18 in stock)

### Office Supplies (6 products)
- A4 Paper Ream (500 sheets) - **$8.99** (200 in stock)
- Ballpoint Pen Blue (Pack of 12) - **$6.99** (150 in stock)
- Spiral Notebook A5 - **$4.99** (180 in stock)
- Manila Folder (Box of 100) - **$24.99** (45 in stock)
- Heavy Duty Stapler - **$19.99** (35 in stock)
- Paper Clips (Box of 1000) - **$3.99** (120 in stock)

### Furniture (4 products)
- Executive Office Desk - **$599.99** (12 in stock)
- Ergonomic Office Chair - **$349.99** (28 in stock)
- Filing Cabinet 4-Drawer - **$279.99** (15 in stock)
- Wooden Bookshelf 5-Tier - **$189.99** (20 in stock)

### Cables & Accessories (4 products)
- USB-C Cable 6ft - **$14.99** (100 in stock)
- HDMI Cable 10ft - **$19.99** (85 in stock)
- Universal Power Adapter - **$29.99** (50 in stock)
- USB Hub 7-Port - **$39.99** (42 in stock)

### Printer Supplies (3 products)
- HP Ink Cartridge Black - **$49.99** (65 in stock)
- HP Ink Cartridge Color - **$59.99** (55 in stock)
- Brother Laser Toner - **$89.99** (38 in stock)

### Storage (3 products)
- External Hard Drive 2TB - **$129.99** (32 in stock)
- Samsung SSD 1TB - **$159.99** (28 in stock)
- USB Flash Drive 64GB - **$19.99** (95 in stock)

### Low Stock Items (3 products) - For Testing Reorder Alerts
- Whiteboard Markers Set - **$12.99** (3 in stock, reorder at 10)
- Desk Organizer - **$24.99** (2 in stock, reorder at 8)
- Wireless Presenter Remote - **$34.99** (1 in stock, reorder at 5)

### Product Data Structure
Each product includes:
- **SKU** - Unique product identifier
- **Name** - Descriptive product name
- **Unit** - Measurement unit (piece, box, ream, set, pack)
- **Price** - Selling price
- **Cost Price** - Purchase cost
- **Profit Margin** - Automatically calculated
- **Quantity on Hand** - Current stock level
- **Reorder Level** - Minimum stock threshold

## Key Features

### Multi-Tenant Isolation
All seeded data is automatically associated with the **Demo Store** (`demo-store`), ensuring proper multi-tenant data isolation.

### Realistic Data
- Varied price points from $3.99 to $1,299.99
- Different product categories for diverse testing
- Complete customer information with US addresses
- Realistic stock levels and reorder points

### Testing Scenarios Enabled

#### Stock Management Testing
- **High Stock Items** - Office supplies with 100+ units
- **Medium Stock Items** - Electronics with 15-75 units
- **Low Stock Items** - 3 products below reorder level for testing alerts

#### Price Range Testing
- **Budget Items** - Under $10 (office supplies)
- **Mid-Range Items** - $10-$100 (accessories, cables)
- **Premium Items** - Over $100 (electronics, furniture)

#### Profit Margin Testing
- Margins automatically calculated using the formula:
  ```
  profit_margin = ((price - cost_price) / price) * 100
  ```
- Varied margins from ~20% to ~35% across products

## Integration with Application

### Dashboard
- Products and customers will appear in respective modules
- Low stock alerts will show 3 products needing reorder
- Financial summaries will calculate based on product costs and prices

### Sales Orders
- 15 customers available to create sales orders
- 29 products available for order line items
- Stock will automatically decrease when orders are created

### Reports
- Inventory reports will show varied stock levels
- Profit margin analysis available across all products
- Customer purchase history ready for tracking

### Purchase Orders
- Reorder suggestions based on 3 low-stock items
- All products available for purchase order creation

## Demo Login Credentials

After running seeders, you can log in with:

**Store Admin (Full Access)**
- Email: `admin@demo.com`
- Password: `password`

**Store User (Sales Only)**
- Email: `user@demo.com`
- Password: `password`

## Idempotency

Both seeders use `firstOrCreate()` to ensure:
- Safe to run multiple times without duplicating data
- Updates existing records if run again
- No errors if data already exists

## Next Steps

After seeding, you can:

1. **Test Sales Flow**
   - Create sales orders using the seeded customers
   - Add products to orders to test stock deduction

2. **Test Purchase Flow**
   - Create purchase orders for low-stock items
   - Receive orders to increase stock levels

3. **Test Reporting**
   - View inventory reports with varied stock levels
   - Check profit margin reports across categories
   - Test low stock alerts

4. **Test Multi-Tenancy**
   - Register a new account with different store
   - Verify new store doesn't see demo store's data
   - Create separate data set for new store

## Customization

To modify the seed data:

1. Edit `database/seeders/CustomerSeeder.php` or `ProductSeeder.php`
2. Add, remove, or modify entries in the arrays
3. Run the seeder again: `php artisan db:seed --class=CustomerSeeder`

## Troubleshooting

### "Demo store not found" Error
**Solution:** Run the DemoUsersSeeder first:
```bash
php artisan db:seed --class=DemoUsersSeeder
```

### Duplicate Entry Errors
The seeders use `firstOrCreate()` which should prevent duplicates. If you encounter errors:
```bash
# Clear and reseed
php artisan migrate:fresh --seed
```

### Store Isolation Issues
All data is properly associated with `store_id` via the `BelongsToStore` trait, ensuring multi-tenant isolation is maintained.
