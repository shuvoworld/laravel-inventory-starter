<?php

namespace Tests\Feature;

use App\Modules\Products\Models\Product;
use App\Modules\Products\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VariantBasicTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_basic_variant()
    {
        // Create a simple product
        $product = Product::create([
            'name' => 'Test Product',
            'sku' => 'TEST001',
            'has_variants' => true,
            'cost_price' => 10.00,
            'target_price' => 20.00,
            'quantity_on_hand' => 0,
            'is_active' => true
        ]);

        // Create a variant
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'variant_name' => 'Test Variant',
            'sku' => 'TEST001-V1',
            'cost_price' => 12.00,
            'target_price' => 22.00,
            'quantity_on_hand' => 50,
            'is_active' => true
        ]);

        // Assertions
        $this->assertNotNull($product);
        $this->assertNotNull($variant);
        $this->assertEquals('Test Product', $product->name);
        $this->assertEquals('Test Variant', $variant->variant_name);
        $this->assertTrue($product->has_variants);
        $this->assertEquals(50, $variant->quantity_on_hand);
        $this->assertEquals(12.00, $variant->getEffectiveCostPrice());
        $this->assertEquals(22.00, $variant->getEffectiveTargetPrice());
    }

    /** @test */
    public function variant_relationships_work()
    {
        // Create product and variant
        $product = Product::create([
            'name' => 'Test Product',
            'sku' => 'TEST002',
            'has_variants' => true
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'variant_name' => 'Test Variant',
            'sku' => 'TEST002-V1',
            'quantity_on_hand' => 25
        ]);

        // Test relationships
        $this->assertEquals($product->id, $variant->product_id);
        $this->assertEquals($product->name, $variant->product->name);
        $this->assertTrue($variant->product->exists());
        $this->assertTrue($product->variants()->where('id', $variant->id)->exists());
    }

    /** @test */
    public function variant_display_name_works()
    {
        $product = Product::create([
            'name' => 'T-Shirt',
            'sku' => 'SHIRT001',
            'has_variants' => true
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'variant_name' => 'Small / Red',
            'sku' => 'SHIRT001-SR',
            'quantity_on_hand' => 10
        ]);

        $this->assertEquals('T-Shirt (Small / Red)', $variant->getDisplayName());
    }

    /** @test */
    public function variant_stock_calculation()
    {
        $product = Product::create([
            'name' => 'Test Product',
            'has_variants' => true
        ]);

        $variant1 = ProductVariant::create([
            'product_id' => $product->id,
            'variant_name' => 'Variant 1',
            'quantity_on_hand' => 10
        ]);

        $variant2 = ProductVariant::create([
            'product_id' => $product->id,
            'variant_name' => 'Variant 2',
            'quantity_on_hand' => 15
        ]);

        // Test stock aggregation
        $this->assertEquals(25, $product->getTotalStock());
        $this->assertEquals(10, $variant1->quantity_on_hand);
        $this->assertEquals(15, $variant2->quantity_on_hand);
    }

    /** @test */
    public function variant_pricing_inheritance()
    {
        $product = Product::create([
            'name' => 'Test Product',
            'cost_price' => 10.00,
            'target_price' => 20.00,
            'has_variants' => true
        ]);

        // Variant with its own pricing
        $variant1 = ProductVariant::create([
            'product_id' => $product->id,
            'variant_name' => 'Premium Variant',
            'cost_price' => 15.00,
            'target_price' => 30.00,
            'quantity_on_hand' => 5
        ]);

        // Variant without its own pricing (should inherit)
        $variant2 = ProductVariant::create([
            'product_id' => $product->id,
            'variant_name' => 'Standard Variant',
            'quantity_on_hand' => 10
        ]);

        // Test pricing inheritance
        $this->assertEquals(15.00, $variant1->getEffectiveCostPrice());
        $this->assertEquals(30.00, $variant1->getEffectiveTargetPrice());
        $this->assertEquals(10.00, $variant2->getEffectiveCostPrice()); // Inherited
        $this->assertEquals(20.00, $variant2->getEffectiveTargetPrice()); // Inherited
    }

    /** @test */
    public function variant_active_status()
    {
        $product = Product::create([
            'name' => 'Test Product',
            'has_variants' => true
        ]);

        $activeVariant = ProductVariant::create([
            'product_id' => $product->id,
            'variant_name' => 'Active Variant',
            'is_active' => true,
            'quantity_on_hand' => 10
        ]);

        $inactiveVariant = ProductVariant::create([
            'product_id' => $product->id,
            'variant_name' => 'Inactive Variant',
            'is_active' => false,
            'quantity_on_hand' => 5
        ]);

        // Test active status
        $this->assertTrue($activeVariant->is_active);
        $this->assertFalse($inactiveVariant->is_active);
    }
}