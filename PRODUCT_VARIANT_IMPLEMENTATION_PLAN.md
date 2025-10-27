# Product Variant Management - Implementation Plan

## Overview
This document outlines the complete implementation plan for a flexible Product Variant Management system that supports any type of product options (size, color, material, etc.).

## System Architecture

### Design Philosophy
- **Main Products Table**: Remains the primary table for parent products
- **Product Variants**: Child records that represent specific combinations of options
- **Flexible Options**: Support any type of variant attributes (not limited to predefined options)
- **Backward Compatibility**: Non-variant products continue to work as-is
- **Stock Management**: Each variant has independent stock tracking

---

## 1. DATABASE SCHEMA DESIGN

### 1.1 New Tables

#### Table: `product_variant_options`
Defines the types of options available (e.g., "Size", "Color", "Material")

```sql
CREATE TABLE product_variant_options (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    store_id BIGINT NOT NULL,
    name VARCHAR(100) NOT NULL,              -- e.g., "Size", "Color", "Material"
    display_order INT DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,

    FOREIGN KEY (store_id) REFERENCES stores(id),
    INDEX idx_store_id (store_id)
);
```

#### Table: `product_variant_option_values`
Defines the actual values for each option (e.g., "Small", "Red", "Cotton")

```sql
CREATE TABLE product_variant_option_values (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    store_id BIGINT NOT NULL,
    option_id BIGINT NOT NULL,               -- FK to product_variant_options
    value VARCHAR(100) NOT NULL,             -- e.g., "Small", "Red", "Cotton"
    display_order INT DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,

    FOREIGN KEY (store_id) REFERENCES stores(id),
    FOREIGN KEY (option_id) REFERENCES product_variant_options(id) ON DELETE CASCADE,
    INDEX idx_option_id (option_id),
    INDEX idx_store_id (store_id)
);
```

#### Table: `product_variants`
Stores individual product variants

```sql
CREATE TABLE product_variants (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    store_id BIGINT NOT NULL,
    product_id BIGINT NOT NULL,              -- FK to products (parent product)
    sku VARCHAR(255) UNIQUE,                 -- Variant-specific SKU
    variant_name VARCHAR(255),               -- e.g., "Red / Large"

    -- Pricing (inherits from parent if NULL)
    cost_price DECIMAL(12,2) NULL,
    minimum_profit_margin DECIMAL(5,2) NULL,
    standard_profit_margin DECIMAL(5,2) NULL,
    floor_price DECIMAL(10,2) NULL,
    target_price DECIMAL(10,2) NULL,

    -- Stock (variant-specific)
    quantity_on_hand INT DEFAULT 0,
    reorder_level INT DEFAULT 0,

    -- Variant-specific attributes
    weight DECIMAL(10,2) NULL,
    image VARCHAR(255) NULL,                 -- Variant-specific image
    barcode VARCHAR(255) NULL,

    -- Status
    is_active BOOLEAN DEFAULT TRUE,
    is_default BOOLEAN DEFAULT FALSE,        -- Mark one variant as default

    created_at TIMESTAMP,
    updated_at TIMESTAMP,

    FOREIGN KEY (store_id) REFERENCES stores(id),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_product_id (product_id),
    INDEX idx_store_id (store_id),
    INDEX idx_sku (sku)
);
```

#### Table: `product_variant_attribute_values`
Links variants to their specific option values (many-to-many)

```sql
CREATE TABLE product_variant_attribute_values (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    variant_id BIGINT NOT NULL,
    option_value_id BIGINT NOT NULL,         -- FK to product_variant_option_values
    created_at TIMESTAMP,
    updated_at TIMESTAMP,

    FOREIGN KEY (variant_id) REFERENCES product_variants(id) ON DELETE CASCADE,
    FOREIGN KEY (option_value_id) REFERENCES product_variant_option_values(id) ON DELETE CASCADE,
    UNIQUE KEY unique_variant_option (variant_id, option_value_id),
    INDEX idx_variant_id (variant_id)
);
```

### 1.2 Modifications to Existing Tables

#### Table: `products` (No structural changes needed)
Add a new field to indicate if product has variants:

```sql
ALTER TABLE products ADD COLUMN has_variants BOOLEAN DEFAULT FALSE AFTER name;
ALTER TABLE products ADD COLUMN variant_type VARCHAR(50) NULL AFTER has_variants;
-- variant_type could be 'single', 'matrix', etc. for future expansion
```

**Note**: When `has_variants = TRUE`, the parent product's pricing and stock fields serve as defaults/templates for variants.

#### Table: `sales_order_items`
Add variant reference:

```sql
ALTER TABLE sales_order_items ADD COLUMN variant_id BIGINT NULL AFTER product_id;
ALTER TABLE sales_order_items ADD FOREIGN KEY (variant_id) REFERENCES product_variants(id) ON DELETE SET NULL;
ALTER TABLE sales_order_items ADD INDEX idx_variant_id (variant_id);
```

#### Table: `purchase_order_items`
Add variant reference:

```sql
ALTER TABLE purchase_order_items ADD COLUMN variant_id BIGINT NULL AFTER product_id;
ALTER TABLE purchase_order_items ADD FOREIGN KEY (variant_id) REFERENCES product_variants(id) ON DELETE SET NULL;
ALTER TABLE purchase_order_items ADD INDEX idx_variant_id (variant_id);
```

#### Table: `stock_movements`
Add variant reference:

```sql
ALTER TABLE stock_movements ADD COLUMN variant_id BIGINT NULL AFTER product_id;
ALTER TABLE stock_movements ADD FOREIGN KEY (variant_id) REFERENCES product_variants(id) ON DELETE SET NULL;
ALTER TABLE stock_movements ADD INDEX idx_variant_id (variant_id);
```

---

## 2. MODEL STRUCTURE

### 2.1 New Models

#### `ProductVariantOption` Model
```php
namespace App\Modules\Products\Models;

class ProductVariantOption extends Model
{
    use BelongsToStore;

    protected $fillable = [
        'store_id',
        'name',
        'display_order'
    ];

    public function values()
    {
        return $this->hasMany(ProductVariantOptionValue::class, 'option_id');
    }
}
```

#### `ProductVariantOptionValue` Model
```php
namespace App\Modules\Products\Models;

class ProductVariantOptionValue extends Model
{
    use BelongsToStore;

    protected $fillable = [
        'store_id',
        'option_id',
        'value',
        'display_order'
    ];

    public function option()
    {
        return $this->belongsTo(ProductVariantOption::class, 'option_id');
    }

    public function variants()
    {
        return $this->belongsToMany(
            ProductVariant::class,
            'product_variant_attribute_values',
            'option_value_id',
            'variant_id'
        );
    }
}
```

#### `ProductVariant` Model
```php
namespace App\Modules\Products\Models;

class ProductVariant extends Model
{
    use BelongsToStore;

    protected $fillable = [
        'store_id',
        'product_id',
        'sku',
        'variant_name',
        'cost_price',
        'minimum_profit_margin',
        'standard_profit_margin',
        'floor_price',
        'target_price',
        'quantity_on_hand',
        'reorder_level',
        'weight',
        'image',
        'barcode',
        'is_active',
        'is_default'
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'minimum_profit_margin' => 'decimal:2',
        'standard_profit_margin' => 'decimal:2',
        'floor_price' => 'decimal:2',
        'target_price' => 'decimal:2',
        'is_active' => 'boolean',
        'is_default' => 'boolean'
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($variant) {
            $variant->calculatePrices();
        });
    }

    public function calculatePrices()
    {
        $costPrice = $this->cost_price ?? $this->product->cost_price;
        $minMargin = $this->minimum_profit_margin ?? $this->product->minimum_profit_margin;
        $stdMargin = $this->standard_profit_margin ?? $this->product->standard_profit_margin;

        if ($costPrice) {
            $this->floor_price = $costPrice + ($costPrice * ($minMargin / 100));
            $this->target_price = $costPrice + ($costPrice * ($stdMargin / 100));
        }
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function optionValues()
    {
        return $this->belongsToMany(
            ProductVariantOptionValue::class,
            'product_variant_attribute_values',
            'variant_id',
            'option_value_id'
        );
    }

    public function salesOrderItems()
    {
        return $this->hasMany(SalesOrderItem::class, 'variant_id');
    }

    public function purchaseOrderItems()
    {
        return $this->hasMany(PurchaseOrderItem::class, 'variant_id');
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class, 'variant_id');
    }

    // Helper: Get effective price (variant or parent)
    public function getEffectiveCostPrice()
    {
        return $this->cost_price ?? $this->product->cost_price;
    }

    public function getEffectiveTargetPrice()
    {
        return $this->target_price ?? $this->product->target_price;
    }

    // Generate display name from option values
    public function generateVariantName()
    {
        $values = $this->optionValues()
            ->with('option')
            ->get()
            ->sortBy('option.display_order')
            ->pluck('value')
            ->join(' / ');

        return $values;
    }
}
```

### 2.2 Update Existing Models

#### Update `Product` Model
```php
class Product extends Model
{
    // Add to $fillable
    protected $fillable = [
        // ... existing fields ...
        'has_variants',
        'variant_type',
    ];

    protected $casts = [
        // ... existing casts ...
        'has_variants' => 'boolean',
    ];

    // New relationships
    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function activeVariants()
    {
        return $this->hasMany(ProductVariant::class)->where('is_active', true);
    }

    public function defaultVariant()
    {
        return $this->hasOne(ProductVariant::class)->where('is_default', true);
    }

    // Helper methods
    public function getTotalStock()
    {
        if ($this->has_variants) {
            return $this->variants()->sum('quantity_on_hand');
        }
        return $this->quantity_on_hand;
    }

    public function isLowStock(): bool
    {
        if ($this->has_variants) {
            return $this->variants()
                ->where('is_active', true)
                ->where('quantity_on_hand', '<=', DB::raw('reorder_level'))
                ->exists();
        }
        return $this->quantity_on_hand <= $this->reorder_level;
    }
}
```

---

## 3. MIGRATION STRATEGY

### 3.1 Migration Order

1. `create_product_variant_options_table.php`
2. `create_product_variant_option_values_table.php`
3. `create_product_variants_table.php`
4. `create_product_variant_attribute_values_table.php`
5. `add_variant_fields_to_products_table.php`
6. `add_variant_id_to_sales_order_items_table.php`
7. `add_variant_id_to_purchase_order_items_table.php`
8. `add_variant_id_to_stock_movements_table.php`

### 3.2 Data Migration Considerations

**For Existing Products:**
- Set `has_variants = FALSE` for all existing products
- No immediate data migration needed
- Existing functionality remains unchanged

**For Future Variant Products:**
- When converting a product to have variants:
  - Set `has_variants = TRUE`
  - Create at least one variant with parent product's data
  - Optionally transfer stock to default variant

---

## 4. CONTROLLER UPDATES

### 4.1 New Controllers

#### `ProductVariantOptionController`
```php
namespace App\Modules\Products\Http\Controllers;

class ProductVariantOptionController extends Controller
{
    public function index()
    {
        $options = ProductVariantOption::with('values')
            ->where('store_id', auth()->user()->currentStoreId())
            ->orderBy('display_order')
            ->get();

        return view('products::variant-options.index', compact('options'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'values' => 'required|array|min:1',
            'values.*.value' => 'required|string|max:100'
        ]);

        $option = ProductVariantOption::create([
            'store_id' => auth()->user()->currentStoreId(),
            'name' => $request->name,
            'display_order' => $request->display_order ?? 0
        ]);

        foreach ($request->values as $index => $valueData) {
            ProductVariantOptionValue::create([
                'store_id' => auth()->user()->currentStoreId(),
                'option_id' => $option->id,
                'value' => $valueData['value'],
                'display_order' => $index
            ]);
        }

        return redirect()->route('modules.products.variant-options.index')
            ->with('success', 'Variant option created successfully');
    }

    // ... update, destroy methods
}
```

#### `ProductVariantController`
```php
namespace App\Modules\Products\Http\Controllers;

class ProductVariantController extends Controller
{
    public function index(Product $product)
    {
        $variants = $product->variants()->with('optionValues.option')->get();
        $options = ProductVariantOption::with('values')->get();

        return view('products::variants.index', compact('product', 'variants', 'options'));
    }

    public function store(Request $request, Product $product)
    {
        $request->validate([
            'sku' => 'nullable|string|unique:product_variants,sku',
            'option_values' => 'required|array|min:1',
            'option_values.*' => 'exists:product_variant_option_values,id',
            'cost_price' => 'nullable|numeric|min:0',
            'quantity_on_hand' => 'required|integer|min:0',
            'image' => 'nullable|image|max:2048'
        ]);

        $variant = ProductVariant::create([
            'store_id' => auth()->user()->currentStoreId(),
            'product_id' => $product->id,
            'sku' => $request->sku,
            'cost_price' => $request->cost_price,
            'quantity_on_hand' => $request->quantity_on_hand,
            'reorder_level' => $request->reorder_level ?? $product->reorder_level,
            'minimum_profit_margin' => $request->minimum_profit_margin ?? $product->minimum_profit_margin,
            'standard_profit_margin' => $request->standard_profit_margin ?? $product->standard_profit_margin,
        ]);

        // Attach option values
        $variant->optionValues()->attach($request->option_values);

        // Generate variant name
        $variant->variant_name = $variant->generateVariantName();
        $variant->save();

        // Mark product as having variants
        $product->update(['has_variants' => true]);

        return redirect()->route('modules.products.variants.index', $product)
            ->with('success', 'Variant created successfully');
    }

    // Bulk generation method
    public function generateBulk(Request $request, Product $product)
    {
        $request->validate([
            'options' => 'required|array|min:1',
            'options.*.option_id' => 'required|exists:product_variant_options,id',
            'options.*.values' => 'required|array|min:1'
        ]);

        // Generate all combinations
        $combinations = $this->generateCombinations($request->options);

        foreach ($combinations as $combination) {
            $variant = ProductVariant::create([
                'store_id' => auth()->user()->currentStoreId(),
                'product_id' => $product->id,
                'sku' => $this->generateVariantSku($product->sku, $combination),
                // Inherit from parent
                'cost_price' => $product->cost_price,
                'minimum_profit_margin' => $product->minimum_profit_margin,
                'standard_profit_margin' => $product->standard_profit_margin,
                'quantity_on_hand' => 0,
                'reorder_level' => $product->reorder_level
            ]);

            $variant->optionValues()->attach($combination);
            $variant->variant_name = $variant->generateVariantName();
            $variant->save();
        }

        $product->update(['has_variants' => true]);

        return redirect()->route('modules.products.variants.index', $product)
            ->with('success', count($combinations) . ' variants generated successfully');
    }

    private function generateCombinations(array $options)
    {
        // Implementation to generate all combinations
        // Returns array of arrays containing option_value_ids
    }
}
```

### 4.2 Update Existing Controllers

#### Update `ProductController`
```php
class ProductController extends Controller
{
    public function show(Product $product)
    {
        $product->load(['variants.optionValues', 'brand']);

        return view('products::show', compact('product'));
    }

    // In create/edit views, add option to enable variants
}
```

---

## 5. UI/UX IMPLEMENTATION

### 5.1 Variant Option Management
Create admin interface to manage variant options:
- Path: `/admin/products/variant-options`
- Features:
  - Create new options (Size, Color, Material, etc.)
  - Add values to each option
  - Drag-and-drop ordering
  - Edit/Delete options and values

### 5.2 Product Creation/Editing with Variants

#### Enable Variants Checkbox
```blade
<div class="form-group">
    <div class="form-check">
        <input type="checkbox" class="form-check-input" id="has_variants"
               name="has_variants" value="1" {{ old('has_variants', $product->has_variants ?? false) ? 'checked' : '' }}>
        <label class="form-check-label" for="has_variants">
            This product has variants (sizes, colors, etc.)
        </label>
    </div>
</div>
```

#### Variant Management Tab
```blade
@if($product->has_variants)
<ul class="nav nav-tabs">
    <li class="nav-item">
        <a class="nav-link active" data-bs-toggle="tab" href="#product-info">Product Info</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#variants">Variants</a>
    </li>
</ul>

<div class="tab-content">
    <div id="variants" class="tab-pane fade">
        <!-- Variant management interface -->
        <div class="row mb-3">
            <div class="col-md-12">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#generateVariantsModal">
                    <i class="fas fa-plus"></i> Generate Variants
                </button>
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addVariantModal">
                    <i class="fas fa-plus"></i> Add Single Variant
                </button>
            </div>
        </div>

        <!-- Variants Table -->
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Variant</th>
                    <th>SKU</th>
                    <th>Cost Price</th>
                    <th>Target Price</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($product->variants as $variant)
                <tr>
                    <td><img src="{{ $variant->image ?? $product->image }}" width="50"></td>
                    <td>{{ $variant->variant_name }}</td>
                    <td>{{ $variant->sku }}</td>
                    <td>{{ formatMoney($variant->getEffectiveCostPrice()) }}</td>
                    <td>{{ formatMoney($variant->getEffectiveTargetPrice()) }}</td>
                    <td>{{ $variant->quantity_on_hand }}</td>
                    <td>
                        <span class="badge {{ $variant->is_active ? 'bg-success' : 'bg-secondary' }}">
                            {{ $variant->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-primary" onclick="editVariant({{ $variant->id }})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteVariant({{ $variant->id }})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
```

#### Generate Variants Modal
```blade
<div class="modal fade" id="generateVariantsModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Generate Product Variants</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('modules.products.variants.generate-bulk', $product) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p class="text-muted">Select options and their values to automatically generate all possible variant combinations.</p>

                    <div id="variant-options-container">
                        <!-- Option 1 -->
                        <div class="card mb-3 variant-option-card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <label>Option Type</label>
                                        <select name="options[0][option_id]" class="form-select" required>
                                            <option value="">Select option...</option>
                                            @foreach($variantOptions as $option)
                                            <option value="{{ $option->id }}">{{ $option->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-8">
                                        <label>Values</label>
                                        <select name="options[0][values][]" class="form-select" multiple required>
                                            <!-- Populated dynamically via JavaScript -->
                                        </select>
                                        <small class="text-muted">Hold Ctrl/Cmd to select multiple</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn btn-sm btn-secondary" onclick="addVariantOption()">
                        <i class="fas fa-plus"></i> Add Another Option
                    </button>

                    <div class="alert alert-info mt-3">
                        <strong>Preview:</strong> <span id="variant-count">0</span> variants will be generated
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Generate Variants</button>
                </div>
            </form>
        </div>
    </div>
</div>
```

### 5.3 Point of Sale Updates

#### Variant Selection in POS
```javascript
// In product card rendering
function renderProducts(products) {
    grid.innerHTML = products.map(product => {
        if (product.has_variants) {
            return renderVariantProduct(product);
        } else {
            return renderSimpleProduct(product);
        }
    }).join('');
}

function renderVariantProduct(product) {
    return `
        <div class="product-card" onclick="showVariantSelector(${product.id})">
            <img src="${product.image}" class="product-image">
            <div class="product-name">${product.name}</div>
            <div class="product-sku">Multiple Variants</div>
            <div class="product-price">
                <span style="font-size: 0.75rem;">From</span>
                $${parseFloat(product.min_price).toFixed(2)}
            </div>
            <div class="variant-badge">
                <i class="fas fa-layer-group"></i> ${product.variants_count} Options
            </div>
        </div>
    `;
}

function showVariantSelector(productId) {
    // Open modal with variant options
    fetch(`/pos/product-variants/${productId}`)
        .then(response => response.json())
        .then(data => {
            displayVariantModal(data);
        });
}
```

#### Variant Selection Modal
```html
<div class="modal fade" id="variantSelectorModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 id="variantProductName"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Option selectors populated dynamically -->
                <div id="variant-options"></div>

                <!-- Selected variant display -->
                <div id="selected-variant" class="card mt-3" style="display: none;">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-4">
                                <img id="variant-image" class="img-fluid">
                            </div>
                            <div class="col-8">
                                <h6 id="variant-name"></h6>
                                <p class="mb-1">SKU: <span id="variant-sku"></span></p>
                                <p class="mb-1">Price: <span id="variant-price"></span></p>
                                <p class="mb-0">Stock: <span id="variant-stock"></span></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="addVariantToCart()" disabled id="addVariantBtn">
                    Add to Cart
                </button>
            </div>
        </div>
    </div>
</div>
```

---

## 6. BUSINESS LOGIC UPDATES

### 6.1 Stock Management

#### Update `StockMovementService`
```php
class StockMovementService
{
    public static function recordMovement(
        int $productId,
        ?int $variantId,
        int $quantity,
        string $type,
        ?string $referenceType = null,
        ?int $referenceId = null
    ) {
        // If variant_id is provided, use variant's stock
        // Otherwise use product's stock

        if ($variantId) {
            $variant = ProductVariant::findOrFail($variantId);
            $currentStock = $variant->quantity_on_hand;
        } else {
            $product = Product::findOrFail($productId);
            $currentStock = $product->quantity_on_hand;
        }

        // ... rest of the logic
    }
}
```

### 6.2 Sales Order Processing

#### Update `SalesOrderController` and `PointOfSaleController`
```php
// When adding items to order
public function addToCart(Request $request)
{
    $request->validate([
        'product_id' => 'required|exists:products,id',
        'variant_id' => 'nullable|exists:product_variants,id',
        'quantity' => 'required|integer|min:1'
    ]);

    $product = Product::findOrFail($request->product_id);

    if ($request->variant_id) {
        $variant = ProductVariant::findOrFail($request->variant_id);

        // Use variant's stock and pricing
        if ($variant->quantity_on_hand < $request->quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient stock for this variant'
            ]);
        }

        $price = $variant->getEffectiveTargetPrice();
        $costPrice = $variant->getEffectiveCostPrice();
    } else {
        // Handle non-variant product
        $price = $product->target_price;
        $costPrice = $product->cost_price;
    }

    // Add to cart...
}
```

### 6.3 Purchase Order Processing

#### Update `PurchaseOrderController`
```php
// When creating purchase order items
public function store(Request $request)
{
    $request->validate([
        'items' => 'required|array',
        'items.*.product_id' => 'required|exists:products,id',
        'items.*.variant_id' => 'nullable|exists:product_variants,id',
        'items.*.quantity' => 'required|integer|min:1',
        'items.*.unit_price' => 'required|numeric|min:0'
    ]);

    // When receiving stock, update variant stock if variant_id provided
    foreach ($request->items as $item) {
        if ($item['variant_id']) {
            $variant = ProductVariant::find($item['variant_id']);
            $variant->increment('quantity_on_hand', $item['quantity']);
        } else {
            $product = Product::find($item['product_id']);
            $product->increment('quantity_on_hand', $item['quantity']);
        }
    }
}
```

---

## 7. REPORTING UPDATES

### 7.1 Stock Reports
- Update to show variant-level stock information
- Add drill-down capability from product to variants
- Show total stock across all variants

### 7.2 Sales Reports
- Option to view by product (aggregate) or by variant (detailed)
- Best-selling variants analysis
- Variant performance comparison

### 7.3 Profit Analysis
- Calculate profit margins per variant
- Compare variant profitability within same product

---

## 8. API ENDPOINTS

### 8.1 New Endpoints

```
GET    /api/products/{product}/variants              - List all variants
POST   /api/products/{product}/variants              - Create variant
GET    /api/products/{product}/variants/{variant}    - Show variant
PUT    /api/products/{product}/variants/{variant}    - Update variant
DELETE /api/products/{product}/variants/{variant}    - Delete variant
POST   /api/products/{product}/variants/generate     - Bulk generate

GET    /api/variant-options                          - List options
POST   /api/variant-options                          - Create option
GET    /api/variant-options/{option}/values          - List values for option
```

---

## 9. TESTING STRATEGY

### 9.1 Unit Tests
- ProductVariant model calculations
- Variant name generation
- Stock inheritance logic

### 9.2 Feature Tests
- Creating products with variants
- Variant bulk generation
- POS variant selection
- Sales order with variants
- Purchase order with variants
- Stock movement tracking

### 9.3 Integration Tests
- End-to-end POS flow with variants
- Complete sales cycle with variants
- Stock synchronization

---

## 10. IMPLEMENTATION PHASES

### Phase 1: Foundation (Week 1)
- [ ] Create database migrations
- [ ] Create models and relationships
- [ ] Basic CRUD operations for variant options
- [ ] Unit tests for models

### Phase 2: Product Management (Week 2)
- [ ] Update product creation/editing UI
- [ ] Variant management interface
- [ ] Bulk variant generation
- [ ] Image upload for variants

### Phase 3: Sales & Inventory (Week 3)
- [ ] Update POS for variant selection
- [ ] Update sales order processing
- [ ] Update stock management
- [ ] Stock movement tracking

### Phase 4: Purchase & Reports (Week 4)
- [ ] Update purchase order system
- [ ] Update all reports
- [ ] API endpoints
- [ ] Integration tests

### Phase 5: Polish & Deploy (Week 5)
- [ ] UI/UX refinements
- [ ] Performance optimization
- [ ] Documentation
- [ ] Training materials
- [ ] Deployment

---

## 11. ROLLOUT STRATEGY

### 11.1 Backward Compatibility
- All existing products work without changes
- `has_variants = FALSE` by default
- No forced migration of existing data

### 11.2 Gradual Adoption
1. Deploy to staging environment
2. Test with sample products
3. Train staff on variant management
4. Enable for specific product categories first
5. Monitor performance and issues
6. Full rollout

### 11.3 Data Migration Plan
For converting existing products to variants:
```php
// Conversion script
public function convertToVariant(Product $product, array $variantData)
{
    // Create first variant with existing product data
    $defaultVariant = ProductVariant::create([
        'product_id' => $product->id,
        'sku' => $product->sku,
        'cost_price' => $product->cost_price,
        'quantity_on_hand' => $product->quantity_on_hand,
        'is_default' => true
    ]);

    // Update product
    $product->update(['has_variants' => true]);

    // Zero out product stock (now tracked in variant)
    $product->update(['quantity_on_hand' => 0]);
}
```

---

## 12. PERFORMANCE CONSIDERATIONS

### 12.1 Database Indexes
- Index on `variant_id` in all related tables
- Composite index on `(product_id, is_active)` in variants table
- Index on `option_id` in option values table

### 12.2 Query Optimization
- Eager load relationships: `Product::with('variants.optionValues')`
- Use caching for variant options (rarely change)
- Pagination for variant lists

### 12.3 Caching Strategy
```php
// Cache variant options globally
$variantOptions = Cache::remember('variant_options', 3600, function() {
    return ProductVariantOption::with('values')->get();
});

// Cache product variants
$productVariants = Cache::remember("product_{$productId}_variants", 600, function() use ($productId) {
    return ProductVariant::where('product_id', $productId)
        ->with('optionValues.option')
        ->get();
});
```

---

## 13. SECURITY CONSIDERATIONS

### 13.1 Validation Rules
- Ensure variant belongs to specified product
- Validate option values exist and belong to correct option
- Check store_id matches in all operations

### 13.2 Authorization
```php
// Policy method
public function createVariant(User $user, Product $product)
{
    return $user->can('products.create')
        && $product->store_id === $user->currentStoreId();
}
```

---

## 14. FUTURE ENHANCEMENTS

### 14.1 Advanced Features
- Variant-specific images (multiple images per variant)
- Variant-specific pricing rules (seasonal, bulk discounts)
- Variant bundles (buy color set)
- Matrix view for quick stock updates
- Import/Export variants via CSV

### 14.2 Integration Possibilities
- Sync with e-commerce platforms
- Barcode generation for variants
- QR codes for variant quick select
- Mobile app variant scanner

---

## 15. DOCUMENTATION REQUIREMENTS

### 15.1 User Documentation
- How to create variant options
- How to add variants to products
- How to sell variant products in POS
- How to track variant inventory

### 15.2 Developer Documentation
- API documentation
- Model relationships diagram
- Database schema diagram
- Code examples

### 15.3 Training Materials
- Video tutorials
- Step-by-step guides
- FAQ document
- Troubleshooting guide

---

## APPENDIX A: Database Schema Diagram

```
products (1) -----> (N) product_variants
                           |
                           | (N)
                           |
                           v
              product_variant_attribute_values
                           |
                           | (N)
                           v
              product_variant_option_values
                           |
                           | (N)
                           |
                           v
                  product_variant_options
```

---

## APPENDIX B: Example Use Cases

### Use Case 1: T-Shirt with Size and Color
```
Product: "Classic T-Shirt"
Options:
  - Size: [Small, Medium, Large, XL]
  - Color: [Red, Blue, Black, White]

Generated Variants: 16 total
  - Classic T-Shirt (Red / Small)
  - Classic T-Shirt (Red / Medium)
  - ...
  - Classic T-Shirt (White / XL)
```

### Use Case 2: Coffee with Roast Level and Grind
```
Product: "Premium Coffee Beans"
Options:
  - Roast: [Light, Medium, Dark]
  - Grind: [Whole Bean, Coarse, Medium, Fine]

Generated Variants: 12 total
```

### Use Case 3: Phone with Storage and Color
```
Product: "Smartphone X"
Options:
  - Storage: [64GB, 128GB, 256GB, 512GB]
  - Color: [Black, White, Gold, Blue]

Generated Variants: 16 total
Note: Each variant can have different cost_price based on storage
```

---

## SUMMARY

This implementation provides:
- ✅ Flexible variant system supporting any option types
- ✅ Backward compatibility with existing products
- ✅ Independent stock tracking per variant
- ✅ Flexible pricing (inherit or override)
- ✅ Seamless POS integration
- ✅ Complete audit trail
- ✅ Scalable architecture
- ✅ Easy bulk generation
- ✅ Comprehensive reporting

The system is designed to grow with your business needs while maintaining simplicity for basic use cases.
