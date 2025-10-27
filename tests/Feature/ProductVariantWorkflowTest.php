<?php

namespace Tests\Feature;

use App\Modules\Products\Models\Product;
use App\Modules\Products\Models\ProductVariant;
use App\Modules\Products\Models\ProductVariantOption;
use App\Modules\Products\Models\ProductVariantOptionValue;
use App\Modules\SalesOrder\Models\SalesOrder;
use App\Modules\SalesOrderItem\Models\SalesOrderItem;
use App\Modules\PurchaseOrder\Models\PurchaseOrder;
use App\Modules\PurchaseOrderItem\Models\PurchaseOrderItem;
use App\Modules\StockMovement\Models\StockMovement;
use App\Modules\Suppliers\Models\Supplier;
use App\Modules\Customers\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProductVariantWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user and store for testing
        $this->user = \App\Models\User::factory()->create([
            'store_id' => 1 // Set store_id directly for testing
        ]);
        $this->store = \App\Models\Store::factory()->create();
        $this->user->stores()->attach($this->store->id);
        $this->actingAs($this->user);

        // Set current store context
        session(['current_store_id' => $this->store->id]);
    }

    /** @test */
    public function it_can_create_product_with_variants()
    {
        // Create variant options
        $sizeOption = ProductVariantOption::create([
            'store_id' => 1,
            'name' => 'Size',
            'display_order' => 1
        ]);

        $sizeValues = [
            ProductVariantOptionValue::create([
                'store_id' => 1,
                'option_id' => $sizeOption->id,
                'value' => 'Small',
                'display_order' => 1
            ]),
            ProductVariantOptionValue::create([
                'store_id' => 1,
                'option_id' => $sizeOption->id,
                'value' => 'Medium',
                'display_order' => 2
            ])
        ];

        $colorOption = ProductVariantOption::create([
            'store_id' => 1,
            'name' => 'Color',
            'display_order' => 2
        ]);

        $colorValues = [
            ProductVariantOptionValue::create([
                'store_id' => 1,
                'option_id' => $colorOption->id,
                'value' => 'Red',
                'display_order' => 1
            ]),
            ProductVariantOptionValue::create([
                'store_id' => 1,
                'option_id' => $colorOption->id,
                'value' => 'Blue',
                'display_order' => 2
            ])
        ];

        // Create product
        $product = Product::create([
            'store_id' => 1,
            'name' => 'Test T-Shirt',
            'sku' => 'TSHIRT',
            'has_variants' => true,
            'cost_price' => 10.00,
            'target_price' => 20.00,
            'quantity_on_hand' => 0
        ]);

        // Create variants
        $variant1 = ProductVariant::create([
            'store_id' => 1,
            'product_id' => $product->id,
            'sku' => 'TSHIRT-S-RED',
            'variant_name' => 'Small / Red',
            'cost_price' => 10.00,
            'quantity_on_hand' => 50,
            'is_active' => true
        ]);
        $variant1->optionValues()->attach([$sizeValues[0]->id, $colorValues[0]->id]);

        $variant2 = ProductVariant::create([
            'store_id' => 1,
            'product_id' => $product->id,
            'sku' => 'TSHIRT-M-BLU',
            'variant_name' => 'Medium / Blue',
            'cost_price' => 10.50,
            'quantity_on_hand' => 30,
            'is_active' => true
        ]);
        $variant2->optionValues()->attach([$sizeValues[1]->id, $colorValues[1]->id]);

        // Assertions
        $this->assertTrue($product->has_variants);
        $this->assertEquals(2, $product->variants()->count());
        $this->assertEquals(80, $product->getTotalStock());
        $this->assertEquals('Small / Red', $variant1->variant_name);
        $this->assertEquals('Medium / Blue', $variant2->variant_name);
    }

    /** @test */
    public function it_can_sell_variant_through_pos()
    {
        // Setup product with variant
        $product = Product::factory()->create([
            'store_id' => 1,
            'has_variants' => true,
            'quantity_on_hand' => 0
        ]);

        $variant = ProductVariant::factory()->create([
            'store_id' => 1,
            'product_id' => $product->id,
            'quantity_on_hand' => 100,
            'cost_price' => 15.00,
            'target_price' => 25.00
        ]);

        // Create customer
        $customer = Customer::factory()->create([
            'store_id' => 1
        ]);

        // Simulate POS sale
        $salesOrder = SalesOrder::create([
            'store_id' => 1,
            'customer_id' => $customer->id,
            'order_date' => now(),
            'status' => 'delivered',
            'payment_status' => 'paid',
            'paid_amount' => 50.00,
            'subtotal' => 50.00,
            'total_amount' => 50.00,
            'cogs_amount' => 30.00,
            'profit_amount' => 20.00
        ]);

        $salesOrderItem = SalesOrderItem::create([
            'store_id' => 1,
            'sales_order_id' => $salesOrder->id,
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'quantity' => 2,
            'unit_price' => 25.00,
            'cost_price' => 15.00,
            'total_price' => 50.00,
            'final_price' => 50.00,
            'cogs_amount' => 30.00,
            'profit_amount' => 20.00
        ]);

        // Update variant stock
        $variant->decrement('quantity_on_hand', 2);

        // Assertions
        $variant->refresh();
        $this->assertEquals(98, $variant->quantity_on_hand);
        $this->assertEquals($product->id, $salesOrderItem->product_id);
        $this->assertEquals($variant->id, $salesOrderItem->variant_id);
        $this->assertEquals('Test Product (Variant 1)', $salesOrderItem->getDisplayName());
    }

    /** @test */
    public function it_can_purchase_variant_stock()
    {
        // Setup supplier
        $supplier = Supplier::factory()->create([
            'store_id' => 1
        ]);

        // Setup product with variant
        $product = Product::factory()->create([
            'store_id' => 1,
            'has_variants' => true
        ]);

        $variant = ProductVariant::factory()->create([
            'store_id' => 1,
            'product_id' => $product->id,
            'quantity_on_hand' => 10,
            'cost_price' => 15.00
        ]);

        // Create purchase order
        $purchaseOrder = PurchaseOrder::create([
            'store_id' => 1,
            'supplier_id' => $supplier->id,
            'supplier_name' => $supplier->name,
            'order_date' => now(),
            'status' => 'received',
            'subtotal' => 150.00,
            'total_amount' => 150.00
        ]);

        $purchaseOrderItem = PurchaseOrderItem::create([
            'store_id' => 1,
            'purchase_order_id' => $purchaseOrder->id,
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'quantity' => 10,
            'unit_price' => 15.00,
            'total_price' => 150.00
        ]);

        // Update variant stock
        $variant->increment('quantity_on_hand', 10);

        // Create stock movement
        StockMovement::create([
            'store_id' => 1,
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'movement_type' => 'in',
            'transaction_type' => 'purchase',
            'quantity' => 10,
            'reference_type' => 'purchase_order',
            'reference_id' => $purchaseOrder->id,
            'notes' => 'Purchase - Order #' . $purchaseOrder->po_number,
            'user_id' => $this->user->id
        ]);

        // Assertions
        $variant->refresh();
        $this->assertEquals(20, $variant->quantity_on_hand);
        $this->assertEquals($product->id, $purchaseOrderItem->product_id);
        $this->assertEquals($variant->id, $purchaseOrderItem->variant_id);
        $this->assertEquals('Test Product (Variant 1)', $purchaseOrderItem->getDisplayName());

        // Check stock movement
        $stockMovement = StockMovement::where('variant_id', $variant->id)->first();
        $this->assertNotNull($stockMovement);
        $this->assertEquals('in', $stockMovement->movement_type);
        $this->assertEquals(10, $stockMovement->quantity);
    }

    /** @test */
    public function it_tracks_variant_stock_movements()
    {
        // Setup product with variant
        $product = Product::factory()->create([
            'store_id' => $this->store->id,
            'has_variants' => true
        ]);

        $variant = ProductVariant::factory()->create([
            'store_id' => $this->store->id,
            'product_id' => $product->id,
            'quantity_on_hand' => 50
        ]);

        // Create multiple stock movements
        StockMovement::create([
            'store_id' => $this->store->id,
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'movement_type' => 'in',
            'transaction_type' => 'purchase',
            'quantity' => 20,
            'reference_type' => 'purchase_order',
            'reference_id' => 1,
            'notes' => 'Initial purchase',
            'user_id' => $this->user->id
        ]);

        StockMovement::create([
            'store_id' => $this->store->id,
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'movement_type' => 'out',
            'transaction_type' => 'sale',
            'quantity' => 5,
            'reference_type' => 'sales_order',
            'reference_id' => 1,
            'notes' => 'Sale to customer',
            'user_id' => $this->user->id
        ]);

        StockMovement::create([
            'store_id' => $this->store->id,
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'movement_type' => 'out',
            'transaction_type' => 'manual_adjustment',
            'quantity' => 2,
            'reference_type' => 'stock_adjustment',
            'reference_id' => null,
            'notes' => 'Damaged items',
            'user_id' => $this->user->id
        ]);

        // Assertions
        $movements = StockMovement::where('variant_id', $variant->id)->get();
        $this->assertEquals(3, $movements->count());

        $totalIn = $movements->where('movement_type', 'in')->sum('quantity');
        $totalOut = $movements->where('movement_type', 'out')->sum('quantity');

        $this->assertEquals(20, $totalIn);
        $this->assertEquals(7, $totalOut);

        // Check variant relationships
        $movementWithVariant = StockMovement::with('variant')->where('variant_id', $variant->id)->first();
        $this->assertNotNull($movementWithVariant->variant);
        $this->assertEquals($variant->id, $movementWithVariant->variant->id);
    }

    /** @test */
    public function it_generates_variant_sales_reports()
    {
        // Setup products with variants
        $product1 = Product::factory()->create(['store_id' => $this->store->id, 'has_variants' => true]);
        $variant1 = ProductVariant::factory()->create([
            'store_id' => $this->store->id,
            'product_id' => $product1->id,
            'variant_name' => 'Small',
            'cost_price' => 10.00,
            'quantity_on_hand' => 100
        ]);

        $product2 = Product::factory()->create(['store_id' => $this->store->id, 'has_variants' => true]);
        $variant2 = ProductVariant::factory()->create([
            'store_id' => $this->store->id,
            'product_id' => $product2->id,
            'variant_name' => 'Large',
            'cost_price' => 15.00,
            'quantity_on_hand' => 50
        ]);

        // Create sales orders with variants
        $customer = Customer::factory()->create(['store_id' => $this->store->id]);

        $salesOrder1 = SalesOrder::factory()->create([
            'store_id' => $this->store->id,
            'customer_id' => $customer->id,
            'total_amount' => 150.00
        ]);

        SalesOrderItem::create([
            'store_id' => $this->store->id,
            'sales_order_id' => $salesOrder1->id,
            'product_id' => $product1->id,
            'variant_id' => $variant1->id,
            'quantity' => 5,
            'unit_price' => 30.00,
            'cost_price' => 10.00,
            'final_price' => 150.00,
            'cogs_amount' => 50.00,
            'profit_amount' => 100.00
        ]);

        $salesOrder2 = SalesOrder::factory()->create([
            'store_id' => $this->store->id,
            'customer_id' => $customer->id,
            'total_amount' => 200.00
        ]);

        SalesOrderItem::create([
            'store_id' => $this->store->id,
            'sales_order_id' => $salesOrder2->id,
            'product_id' => $product2->id,
            'variant_id' => $variant2->id,
            'quantity' => 4,
            'unit_price' => 50.00,
            'cost_price' => 15.00,
            'final_price' => 200.00,
            'cogs_amount' => 60.00,
            'profit_amount' => 140.00
        ]);

        // Generate variant sales report
        $report = \App\Services\VariantInventoryReportService::getVariantSalesPerformanceReport();

        // Assertions
        $this->assertEquals(2, $report['summary']['total_variants_sold']);
        $this->assertEquals(350.00, $report['summary']['total_revenue']);
        $this->assertEquals(240.00, $report['summary']['total_profit']);

        // Check variant details
        $variant1Data = collect($report['variants'])->firstWhere('variant_id', $variant1->id);
        $this->assertEquals(5, $variant1Data['total_quantity_sold']);
        $this->assertEquals(150.00, $variant1Data['total_revenue']);
        $this->assertEquals(100.00, $variant1Data['total_profit']);

        $variant2Data = collect($report['variants'])->firstWhere('variant_id', $variant2->id);
        $this->assertEquals(4, $variant2Data['total_quantity_sold']);
        $this->assertEquals(200.00, $variant2Data['total_revenue']);
        $this->assertEquals(140.00, $variant2Data['total_profit']);
    }

    /** @test */
    public function it_validates_variant_stock_availability()
    {
        // Setup product with variant
        $product = Product::factory()->create(['store_id' => $this->store->id, 'has_variants' => true]);
        $variant = ProductVariant::factory()->create([
            'store_id' => $this->store->id,
            'product_id' => $product->id,
            'quantity_on_hand' => 10
        ]);

        // Test stock availability validation
        $this->assertTrue(
            \App\Services\StockMovementService::validateStockAvailability($product->id, $variant->id, 5)
        );

        $this->assertFalse(
            \App\Services\StockMovementService::validateStockAvailability($product->id, $variant->id, 15)
        );

        // Test for regular product
        $regularProduct = Product::factory()->create([
            'store_id' => $this->store->id,
            'has_variants' => false,
            'quantity_on_hand' => 20
        ]);

        $this->assertTrue(
            \App\Services\StockMovementService::validateStockAvailability($regularProduct->id, null, 10)
        );

        $this->assertFalse(
            \App\Services\StockMovementService::validateStockAvailability($regularProduct->id, null, 25)
        );
    }
}