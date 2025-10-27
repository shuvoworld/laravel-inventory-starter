# Product Variant Management System - User Training Guide

## Table of Contents
1. [Getting Started](#getting-started)
2. [Admin Panel Training](#admin-panel-training)
3. [Point of Sale Training](#point-of-sale-training)
4. [Inventory Management](#inventory-management)
5. [Reporting & Analytics](#reporting--analytics)
6. [Best Practices](#best-practices)
7. [Troubleshooting Guide](#troubleshooting-guide)
8. [Quick Reference](#quick-reference)

---

## Getting Started

### System Overview
The Product Variant Management System allows you to sell products that come in different variations such as sizes, colors, materials, or any other attributes. Each variant can have its own:
- Price
- Stock level
- SKU (barcode)
- Image
- Cost and profit tracking

### When to Use Variants
Use product variants when you sell the same base product in different configurations:
- **Clothing**: T-shirts in Small/Medium/Large, different colors
- **Electronics**: Phones with different storage capacities
- **Food Products**: Items in different sizes or packaging
- **Furniture**: Products with different materials or finishes

### Key Concepts
- **Product**: The base product (e.g., "T-Shirt")
- **Variant Options**: Attributes that vary (e.g., "Size", "Color")
- **Variant Values**: Specific values for options (e.g., "Small", "Red")
- **Variant**: Specific combination of values (e.g., "Small / Red")

---

## Admin Panel Training

### 1. Creating Variant Options

#### Step 1: Navigate to Variant Options
1. Go to **Products** → **Variant Options** in the admin menu
2. Click **"Add New Option"**

#### Step 2: Create Option
1. **Option Name**: Enter the attribute name (e.g., "Size", "Color", "Material")
2. **Display Order**: Set the order for display (lower numbers appear first)
3. **Add Values**: Enter all possible values for this option
   - For "Size": Small, Medium, Large, X-Large
   - For "Color": Red, Blue, Green, Black
4. **Save** the option

#### Best Practices
- **Logical Naming**: Use clear, consistent option names
- **Complete Values**: Add all possible values upfront
- **Order Matters**: Set display order for logical flow (Size before Color)

### 2. Creating Products with Variants

#### Step 1: Create Base Product
1. Go to **Products** → **Add New Product**
2. Fill in basic product information:
   - Name (e.g., "Premium T-Shirt")
   - Base SKU (e.g., "TSHIRT")
   - Description, brand, category
   - **Enable "Has Variants" checkbox**

#### Step 2: Configure Variants
1. Click the **"Variants"** tab
2. Select the options you want to use (e.g., Size and Color)
3. Choose values for each option
4. Click **"Generate Variants"** to create all combinations

#### Step 3: Configure Individual Variants
For each generated variant:
1. **Variant Name**: Auto-generated, can be customized
2. **SKU**: Auto-generated (e.g., "TSHIRT-S-RED"), can be edited
3. **Price**: Set variant-specific price
4. **Cost Price**: Set variant-specific cost
5. **Stock**: Initial stock quantity
6. **Reorder Level**: When to reorder this variant
7. **Image**: Upload variant-specific image (optional)
8. **Active**: Enable/disable individual variants

#### Example Setup
```
Product: "Premium T-Shirt"
Options: Size (S, M, L) × Color (Red, Blue)

Generated Variants:
- Small / Red (SKU: TSHIRT-S-RED) - $19.99
- Small / Blue (SKU: TSHIRT-S-BLU) - $19.99
- Medium / Red (SKU: TSHIRT-M-RED) - $21.99
- Medium / Blue (SKU: TSHIRT-M-BLU) - $21.99
- Large / Red (SKU: TSHIRT-L-RED) - $23.99
- Large / Blue (SKU: TSHIRT-L-BLU) - $23.99
```

### 3. Managing Existing Variants

#### Adding New Variants
1. Go to **Products** → Edit product
2. Click **"Variants"** tab
3. Click **"Add Variant"** to create individual variants
4. Fill in variant details and save

#### Editing Variants
1. Find the variant in the list
2. Click **Edit** icon
3. Modify details (price, stock, etc.)
4. **Save** changes

#### Bulk Operations
1. Select multiple variants using checkboxes
2. Choose bulk action:
   - **Update Stock**: Set same stock level for selected variants
   - **Update Price**: Apply price changes
   - **Activate/Deactivate**: Enable/disable variants
   - **Delete**: Remove variants (with history check)

### 4. Stock Management

#### Viewing Stock Levels
- **Products List**: Shows total stock across all variants
- **Variant List**: Shows individual variant stock
- **Low Stock Alert**: Variants below reorder level are highlighted

#### Updating Stock
1. **Manual Update**: Edit variant and change stock quantity
2. **Bulk Update**: Select multiple variants and update stock
3. **Purchase Orders**: Receive stock from suppliers
4. **Stock Adjustment**: Manual adjustments with reasons

#### Stock Movement History
1. Go to **Reports** → **Stock Movements**
2. Filter by variant, date range, or movement type
3. View complete audit trail of all stock changes

---

## Point of Sale Training

### 1. Selling Variants

#### Identifying Products with Variants
- **Variant Indicator**: Layer icon shows product has variants
- **Variant Count**: Shows number of available variants
- **Price Range**: Displays minimum to maximum price

#### Selecting Variants
1. **Click** on a product with variants
2. **Variant Selection Modal** opens showing:
   - Product image and details
   - **Quick Filter Buttons**: Filter by Size, Color, etc.
   - **Variant Cards**: Visual display of each variant
3. **Choose Variant**: Click the desired variant card
4. **Add to Cart**: Variant is added to cart with correct price

#### Reading Variant Information
Each variant card shows:
- **Image**: Variant-specific image if available
- **Name**: Variant combination (e.g., "Small / Red")
- **SKU**: Unique variant barcode
- **Price**: Variant-specific price
- **Stock**: Current stock level with color coding
  - 🟢 Green: Good stock (>5 items)
  - 🟡 Yellow: Low stock (1-5 items)
  - 🔴 Red: Out of stock

#### Adding to Cart
1. **Click** variant card or **"Add to Cart"** button
2. **Confirmation**: Toast message confirms addition
3. **Cart Display**: Shows variant name with product name

### 2. Cart Management

#### Viewing Cart Items
Cart shows:
- **Product Name**: Base product name
- **Variant Name**: Specific variant in smaller text
- **Price**: Variant-specific price
- **Quantity**: Current quantity
- **Total**: Line total

#### Modifying Cart Items
1. **Quantity**: Use + / - buttons to adjust quantity
2. **Price**: Click price to edit (with floor price validation)
3. **Remove**: Click trash icon to remove item
4. **Stock Validation**: System prevents adding more than available stock

#### Checkout with Variants
- **Order Creation**: Creates sales order with variant information
- **Stock Update**: Automatically reduces variant stock
- **Receipt**: Shows variant details on printed receipt

### 3. Advanced POS Features

#### Quick Filters
- **Size Filter**: Click "Small" to show only Small variants
- **Color Filter**: Click "Red" to show only Red variants
- **Multiple Filters**: Combine filters (e.g., Small AND Red)
- **Clear Filters**: Click "Clear" to reset all filters

#### Search Functionality
- **Product Search**: Search by product name or SKU
- **Variant Search**: Finds variants by variant name or SKU
- **Real-time Results**: Updates as you type

#### Stock Alerts
- **Out of Stock**: Grayed out cards with "Out of Stock" message
- **Low Stock**: Yellow warning with remaining quantity
- **Cannot Add**: System prevents adding out-of-stock variants

---

## Inventory Management

### 1. Stock Monitoring

#### Dashboard Overview
- **Total Variants**: Count of all active variants
- **Low Stock**: Number of variants below reorder level
- **Out of Stock**: Number of variants with zero stock
- **Stock Value**: Total value of all variant inventory

#### Stock Reports
1. **Inventory Report**: Complete list with stock levels
2. **Low Stock Report**: Variants needing reordering
3. **Stock Valuation**: Financial value by variant
4. **Movement History**: Complete audit trail

#### Setting Reorder Levels
1. **Edit Variant**: Go to product variants
2. **Set Reorder Level**: Minimum stock before reordering
3. **Save Changes**: System will alert when stock drops below this level

### 2. Receiving Stock

#### Purchase Order Creation
1. **Create PO**: Go to Purchase Orders → Create New
2. **Select Supplier**: Choose supplier for variants
3. **Add Items**: Select variants with quantities and costs
4. **Receive Stock**: Mark as received to update stock levels

#### Manual Stock Updates
1. **Stock Adjustment**: Go to Inventory → Stock Adjustment
2. **Select Variant**: Choose variant to update
3. **Set Quantity**: Enter new stock level
4. **Add Reason**: Explain why stock was adjusted
5. **Confirm**: System records movement and updates stock

### 3. Stock Auditing

#### Physical Count
1. **Generate Count Sheet**: Export current stock levels
2. **Count Physical Stock**: Count actual inventory
3. **Compare Results**: Identify discrepancies
4. **Make Adjustments**: Update system to match physical count

#### Discrepancy Resolution
- **Investigate**: Check movement history for errors
- **Correct**: Make necessary adjustments
- **Document**: Record reasons for changes
- **Prevent**: Review procedures to avoid future issues

---

## Reporting & Analytics

### 1. Sales Reports

#### Variant Performance Report
**What it shows:**
- Total quantity sold per variant
- Revenue per variant
- Profit per variant
- Profit margin percentages
- Order count per variant
- Last sold date

**How to use:**
1. Go to **Reports** → **Sales** → **Variant Performance**
2. **Set Date Range**: Choose period to analyze
3. **View Results**: See top and bottom performing variants
4. **Export Data**: Download for further analysis

**Business Insights:**
- Identify best-selling variants
- Spot underperforming variants
- Optimize inventory based on sales data
- Make pricing decisions

#### Product vs Variant Analysis
Compare sales of:
- Products without variants vs. with variants
- Individual variant performance
- Customer preferences for specific variants

### 2. Inventory Reports

#### Stock Valuation Report
**What it shows:**
- Total value of variant inventory
- Cost vs. retail value
- Potential profit at current stock levels
- Breakdown by product and variant

**How to use:**
1. Go to **Reports** → **Inventory** → **Stock Valuation**
2. **Review Totals**: See overall inventory value
3. **Analyze Details**: View value by variant
4. **Make Decisions**: Identify slow-moving stock

#### Low Stock Analysis
**What it shows:**
- Variants below reorder level
- Days of stock remaining
- Sales velocity
- Recommended reorder quantities

**How to use:**
1. Go to **Reports** → **Inventory** → **Low Stock**
2. **Prioritize**: Sort by urgency
3. **Create Orders**: Generate purchase orders
4. **Prevent Stockouts**: Avoid losing sales

### 3. Financial Reports

#### Profit Analysis by Variant
**What it shows:**
- Revenue per variant
- Cost of goods sold per variant
- Gross profit per variant
- Profit margin comparison
- Seasonal trends

**How to use:**
1. Go to **Reports** → **Financial** → **Variant Profit Analysis**
2. **Set Period**: Choose date range
3. **Analyze**: Identify most profitable variants
4. **Optimize**: Focus on high-margin variants

#### Cost Tracking
- Track actual costs vs. expected costs
- Monitor supplier pricing changes
- Calculate landed costs per variant
- Analyze cost trends over time

---

## Best Practices

### 1. Variant Setup

#### Planning Variants
- **Start Simple**: Begin with basic options (Size, Color)
- **Think Scalable**: Consider future variant needs
- **Consistent Naming**: Use logical, consistent naming conventions
- **Complete Setup**: Add all variants before going live

#### Naming Conventions
```
Good Examples:
- T-Shirt: Small / Red / Cotton
- Phone: 128GB / Black / Unlocked
- Coffee: 1lb / Whole Bean / Medium Roast

Avoid:
- Inconsistent abbreviations
- Vague descriptions
- Mixed languages
- Special characters
```

#### SKU Generation
```
Pattern: [BASE-SKU]-[VARIANT-CODE]
Examples:
- TSHIRT-S-RED (T-Shirt, Small, Red)
- PHONE-128-BLK (Phone, 128GB, Black)
- COFFEE-1L-WB (Coffee, 1lb, Whole Bean)
```

### 2. Inventory Management

#### Stock Levels
- **Safety Stock**: Maintain buffer stock for popular variants
- **Reorder Points**: Set appropriate reorder levels
- **Seasonal Planning**: Adjust stock for seasonal demand
- **ABC Analysis**: Classify variants by sales volume

#### Stock Accuracy
- **Regular Counts**: Schedule physical inventory counts
- **Daily Reconciliation**: Check for discrepancies
- **Movement Tracking**: Record all stock movements
- **Investigate Issues**: Address discrepancies quickly

### 3. Pricing Strategy

#### Cost-Based Pricing
- **Calculate Costs**: Include all costs per variant
- **Add Margin**: Apply consistent profit margins
- **Consider Value**: Price based on customer value
- **Monitor Competition**: Compare with market prices

#### Dynamic Pricing
- **Seasonal Adjustments**: Higher prices during peak demand
- **Clearance Pricing**: Discount slow-moving variants
- **Bundle Pricing**: Combine related variants
- **Promotional Pricing**: Limited-time offers

### 4. Customer Experience

#### Product Presentation
- **Clear Images**: Show each variant clearly
- **Detailed Descriptions**: Explain variant differences
- **Easy Selection**: Make choosing variants intuitive
- **Stock Information**: Show availability upfront

#### Communication
- **Accurate Descriptions**: Avoid confusion about variants
- **Visual Differentiation**: Help customers see differences
- **Size Guides**: Provide sizing information
- **Color Accuracy**: Ensure color representation is accurate

---

## Troubleshooting Guide

### Common Issues & Solutions

#### 1. Variant Not Showing in POS
**Problem**: Product shows variant indicator but no variants appear
**Solutions**:
- Check if variants are active
- Verify stock levels (out of stock variants may be hidden)
- Clear browser cache
- Check POS settings for variant filtering

#### 2. Incorrect Stock Levels
**Problem**: Stock levels don't match actual inventory
**Solutions**:
- Check recent sales and purchases
- Review stock movement history
- Perform manual stock adjustment
- Investigate for system errors

#### 3. Price Discrepancies
**Problem**: Wrong prices showing for variants
**Solutions**:
- Verify variant pricing in admin panel
- Check for price inheritance from parent product
- Clear cache
- Review recent price changes

#### 4. SKU Conflicts
**Problem**: Duplicate SKUs or missing SKUs
**Solutions**:
- Review SKU generation pattern
- Check for manual SKU edits
- Use bulk SKU regeneration
- Implement SKU validation rules

#### 5. Performance Issues
**Problem**: Slow loading of variant data
**Solutions**:
- Check database indexes
- Review cache configuration
- Limit variant display per page
- Optimize image sizes

### Error Messages & Meanings

#### "No variants available for this product"
- **Cause**: Product has no active variants
- **Solution**: Create and activate variants for the product

#### "Insufficient stock for this variant"
- **Cause**: Requested quantity exceeds available stock
- **Solution**: Reduce quantity or restock variant

#### "Variant SKU already exists"
- **Cause**: Duplicate SKU being created
- **Solution**: Use unique SKU for each variant

#### "Cannot delete variant with transaction history"
- **Cause**: Variant has sales or purchase history
- **Solution**: Deactivate variant instead of deleting

### Getting Help

#### Self-Service Resources
- **Documentation**: Complete system documentation
- **Video Tutorials**: Step-by-step video guides
- **Knowledge Base**: Common questions and answers
- **Community Forum**: User discussions and tips

#### Support Channels
- **Help Desk**: Submit support tickets
- **Phone Support**: Direct assistance during business hours
- **Email Support**: Detailed issue reporting
- **Live Chat**: Real-time help for urgent issues

#### Reporting Issues
When reporting problems, include:
- **Description**: Clear explanation of the issue
- **Steps to Reproduce**: How to trigger the problem
- **Expected Behavior**: What should happen
- **Actual Behavior**: What actually happened
- **Screenshots**: Visual evidence of the issue
- **System Information**: Browser, device, and user details

---

## Quick Reference

### Keyboard Shortcuts

#### Admin Panel
- **Ctrl + N**: New product
- **Ctrl + S**: Save current form
- **Ctrl + F**: Search products
- **Esc**: Close modal/cancel action
- **Enter**: Confirm action

#### Point of Sale
- **F1**: Product search
- **F2**: Customer search
- **F3**: Quick cash
- **F4**: Clear cart
- **F5**: Refresh products
- **Enter**: Complete sale
- **Ctrl + Z**: Undo last action

### Common Tasks

#### Create New Variant
1. Products → Add Product
2. Enable "Has Variants"
3. Configure options and values
4. Generate variants
5. Set pricing and stock
6. Save and activate

#### Update Stock
1. Products → Edit Product
2. Variants tab
3. Edit variant
4. Update quantity
5. Save changes

#### Run Sales Report
1. Reports → Sales → Variant Performance
2. Set date range
3. Click "Generate Report"
4. Review results
5. Export if needed

### Important Numbers

#### Recommended Stock Levels
- **High-Volume**: 30-60 days supply
- **Medium-Volume**: 14-30 days supply
- **Low-Volume**: 7-14 days supply
- **New Variants**: Start with 10-20 units

#### Profit Margins
- **Standard Products**: 40-60% gross margin
- **Premium Variants**: 50-70% gross margin
- **Basic Variants**: 30-50% gross margin

#### Reorder Points
- **Fast-Moving**: When stock reaches 30% of maximum
- **Medium-Moving**: When stock reaches 25% of maximum
- **Slow-Moving**: When stock reaches 20% of maximum

### Contact Information

#### Technical Support
- **Email**: support@company.com
- **Phone**: 1-800-SUPPORT
- **Hours**: Mon-Fri, 8AM-6PM EST

#### Training Resources
- **Online Portal**: training.company.com
- **Video Library**: videos.company.com/variants
- **Documentation**: docs.company.com/variants
- **Community**: community.company.com

---

## Conclusion

The Product Variant Management System is designed to be intuitive and powerful. With proper setup and following these best practices, you can efficiently manage complex product catalogs and provide excellent customer experiences.

Remember to:
- **Plan your variant structure** before implementation
- **Maintain accurate inventory** through regular checks
- **Use reports** to make informed business decisions
- **Train your staff** on variant procedures
- **Monitor performance** and optimize continuously

For additional training or support, don't hesitate to reach out to our team. We're here to help you succeed!

---

**Training Guide Version**: 1.0
**Last Updated**: October 26, 2025
**Next Review**: 6 months from implementation