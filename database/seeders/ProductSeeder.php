<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Products\Models\Product;
use App\Modules\Stores\Models\Store;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the demo store
        $demoStore = Store::where('slug', 'demo-store')->first();

        if (!$demoStore) {
            $this->command->warn('Demo store not found. Please run DemoUsersSeeder first.');
            return;
        }

        $products = [
            // Electronics
            [
                'store_id' => $demoStore->id,
                'sku' => 'ELEC-LAPTOP-001',
                'name' => 'Dell Latitude 5520 Laptop',
                'unit' => 'piece',
                'cost_price' => 999.00,
                'minimum_profit_margin' => 20.00,
                'standard_profit_margin' => 30.00,
                'quantity_on_hand' => 25,
                'reorder_level' => 5,
            ],
            [
                'store_id' => $demoStore->id,
                'sku' => 'ELEC-MONITOR-001',
                'name' => 'Samsung 27" LED Monitor',
                'unit' => 'piece',
                'cost_price' => 250.00,
                'minimum_profit_margin' => 25.00,
                'standard_profit_margin' => 40.00,
                'quantity_on_hand' => 40,
                'reorder_level' => 10,
            ],
            [
                'store_id' => $demoStore->id,
                'sku' => 'ELEC-KEYBOARD-001',
                'name' => 'Logitech Wireless Keyboard',
                'unit' => 'piece',
                'cost_price' => 45.00,
                'minimum_profit_margin' => 40.00,
                'standard_profit_margin' => 77.78,
                'quantity_on_hand' => 60,
                'reorder_level' => 15,
            ],
            [
                'store_id' => $demoStore->id,
                'sku' => 'ELEC-MOUSE-001',
                'name' => 'Microsoft Ergonomic Mouse',
                'unit' => 'piece',
                'cost_price' => 28.00,
                'minimum_profit_margin' => 40.00,
                'standard_profit_margin' => 78.57,
                'quantity_on_hand' => 75,
                'reorder_level' => 20,
            ],
            [
                'store_id' => $demoStore->id,
                'sku' => 'ELEC-WEBCAM-001',
                'name' => 'Logitech HD Webcam Pro',
                'unit' => 'piece',
                'cost_price' => 85.00,
                'minimum_profit_margin' => 30.00,
                'standard_profit_margin' => 52.94,
                'quantity_on_hand' => 30,
                'reorder_level' => 8,
            ],
            [
                'store_id' => $demoStore->id,
                'sku' => 'ELEC-HEADSET-001',
                'name' => 'Sony WH-1000XM5 Headphones',
                'unit' => 'piece',
                'cost_price' => 280.00,
                'minimum_profit_margin' => 25.00,
                'standard_profit_margin' => 42.86,
                'quantity_on_hand' => 18,
                'reorder_level' => 5,
            ],

            // Office Supplies
            [
                'store_id' => $demoStore->id,
                'sku' => 'OFF-PAPER-001',
                'name' => 'A4 Paper Ream (500 sheets)',
                'unit' => 'ream',
                'cost_price' => 5.50,
                'minimum_profit_margin' => 35.00,
                'standard_profit_margin' => 63.45,
                'quantity_on_hand' => 200,
                'reorder_level' => 50,
            ],
            [
                'store_id' => $demoStore->id,
                'sku' => 'OFF-PEN-001',
                'name' => 'Ballpoint Pen Blue (Pack of 12)',
                'unit' => 'pack',
                'cost_price' => 3.50,
                'minimum_profit_margin' => 50.00,
                'standard_profit_margin' => 99.71,
                'quantity_on_hand' => 150,
                'reorder_level' => 30,
            ],
            [
                'store_id' => $demoStore->id,
                'sku' => 'OFF-NOTEBOOK-001',
                'name' => 'Spiral Notebook A5',
                'unit' => 'piece',
                'cost_price' => 2.50,
                'minimum_profit_margin' => 50.00,
                'standard_profit_margin' => 99.60,
                'quantity_on_hand' => 180,
                'reorder_level' => 40,
            ],
            [
                'store_id' => $demoStore->id,
                'sku' => 'OFF-FOLDER-001',
                'name' => 'Manila Folder (Box of 100)',
                'unit' => 'box',
                'cost_price' => 15.00,
                'minimum_profit_margin' => 35.00,
                'standard_profit_margin' => 66.60,
                'quantity_on_hand' => 45,
                'reorder_level' => 10,
            ],
            [
                'store_id' => $demoStore->id,
                'sku' => 'OFF-STAPLER-001',
                'name' => 'Heavy Duty Stapler',
                'unit' => 'piece',
                'cost_price' => 12.00,
                'minimum_profit_margin' => 35.00,
                'standard_profit_margin' => 66.58,
                'quantity_on_hand' => 35,
                'reorder_level' => 10,
            ],
            [
                'store_id' => $demoStore->id,
                'sku' => 'OFF-CLIPS-001',
                'name' => 'Paper Clips (Box of 1000)',
                'unit' => 'box',
                'cost_price' => 2.00,
                'minimum_profit_margin' => 50.00,
                'standard_profit_margin' => 99.50,
                'quantity_on_hand' => 120,
                'reorder_level' => 25,
            ],

            // Furniture
            [
                'store_id' => $demoStore->id,
                'sku' => 'FURN-DESK-001',
                'name' => 'Executive Office Desk',
                'unit' => 'piece',
                'cost_price' => 400.00,
                'minimum_profit_margin' => 30.00,
                'standard_profit_margin' => 50.00,
                'quantity_on_hand' => 12,
                'reorder_level' => 3,
            ],
            [
                'store_id' => $demoStore->id,
                'sku' => 'FURN-CHAIR-001',
                'name' => 'Ergonomic Office Chair',
                'unit' => 'piece',
                'cost_price' => 220.00,
                'minimum_profit_margin' => 35.00,
                'standard_profit_margin' => 59.09,
                'quantity_on_hand' => 28,
                'reorder_level' => 5,
            ],
            [
                'store_id' => $demoStore->id,
                'sku' => 'FURN-CABINET-001',
                'name' => 'Filing Cabinet 4-Drawer',
                'unit' => 'piece',
                'cost_price' => 180.00,
                'minimum_profit_margin' => 35.00,
                'standard_profit_margin' => 55.50,
                'quantity_on_hand' => 15,
                'reorder_level' => 4,
            ],
            [
                'store_id' => $demoStore->id,
                'sku' => 'FURN-BOOKSHELF-001',
                'name' => 'Wooden Bookshelf 5-Tier',
                'unit' => 'piece',
                'cost_price' => 120.00,
                'minimum_profit_margin' => 35.00,
                'standard_profit_margin' => 58.32,
                'quantity_on_hand' => 20,
                'reorder_level' => 5,
            ],

            // Cables & Accessories
            [
                'store_id' => $demoStore->id,
                'sku' => 'ACC-CABLE-USB-001',
                'name' => 'USB-C Cable 6ft',
                'unit' => 'piece',
                'cost_price' => 8.00,
                'minimum_profit_margin' => 45.00,
                'standard_profit_margin' => 87.38,
                'quantity_on_hand' => 100,
                'reorder_level' => 25,
            ],
            [
                'store_id' => $demoStore->id,
                'sku' => 'ACC-CABLE-HDMI-001',
                'name' => 'HDMI Cable 10ft',
                'unit' => 'piece',
                'cost_price' => 10.00,
                'minimum_profit_margin' => 50.00,
                'standard_profit_margin' => 99.90,
                'quantity_on_hand' => 85,
                'reorder_level' => 20,
            ],
            [
                'store_id' => $demoStore->id,
                'sku' => 'ACC-ADAPTER-001',
                'name' => 'Universal Power Adapter',
                'unit' => 'piece',
                'cost_price' => 18.00,
                'minimum_profit_margin' => 35.00,
                'standard_profit_margin' => 66.61,
                'quantity_on_hand' => 50,
                'reorder_level' => 15,
            ],
            [
                'store_id' => $demoStore->id,
                'sku' => 'ACC-HUB-001',
                'name' => 'USB Hub 7-Port',
                'unit' => 'piece',
                'cost_price' => 24.00,
                'minimum_profit_margin' => 35.00,
                'standard_profit_margin' => 66.62,
                'quantity_on_hand' => 42,
                'reorder_level' => 10,
            ],

            // Printer Supplies
            [
                'store_id' => $demoStore->id,
                'sku' => 'PRINT-INK-BK-001',
                'name' => 'HP Ink Cartridge Black',
                'unit' => 'piece',
                'cost_price' => 32.00,
                'minimum_profit_margin' => 35.00,
                'standard_profit_margin' => 56.22,
                'quantity_on_hand' => 65,
                'reorder_level' => 15,
            ],
            [
                'store_id' => $demoStore->id,
                'sku' => 'PRINT-INK-COLOR-001',
                'name' => 'HP Ink Cartridge Color',
                'unit' => 'piece',
                'cost_price' => 38.00,
                'minimum_profit_margin' => 35.00,
                'standard_profit_margin' => 57.87,
                'quantity_on_hand' => 55,
                'reorder_level' => 15,
            ],
            [
                'store_id' => $demoStore->id,
                'sku' => 'PRINT-TONER-001',
                'name' => 'Brother Laser Toner',
                'unit' => 'piece',
                'cost_price' => 60.00,
                'minimum_profit_margin' => 30.00,
                'standard_profit_margin' => 49.98,
                'quantity_on_hand' => 38,
                'reorder_level' => 10,
            ],

            // Storage
            [
                'store_id' => $demoStore->id,
                'sku' => 'STOR-HDD-001',
                'name' => 'External Hard Drive 2TB',
                'unit' => 'piece',
                'cost_price' => 85.00,
                'minimum_profit_margin' => 30.00,
                'standard_profit_margin' => 52.87,
                'quantity_on_hand' => 32,
                'reorder_level' => 8,
            ],
            [
                'store_id' => $demoStore->id,
                'sku' => 'STOR-SSD-001',
                'name' => 'Samsung SSD 1TB',
                'unit' => 'piece',
                'cost_price' => 110.00,
                'minimum_profit_margin' => 30.00,
                'standard_profit_margin' => 45.45,
                'quantity_on_hand' => 28,
                'reorder_level' => 8,
            ],
            [
                'store_id' => $demoStore->id,
                'sku' => 'STOR-USB-001',
                'name' => 'USB Flash Drive 64GB',
                'unit' => 'piece',
                'cost_price' => 12.00,
                'minimum_profit_margin' => 35.00,
                'standard_profit_margin' => 66.58,
                'quantity_on_hand' => 95,
                'reorder_level' => 20,
            ],

            // Low Stock Items (for testing reorder alerts)
            [
                'store_id' => $demoStore->id,
                'sku' => 'LOW-STOCK-001',
                'name' => 'Whiteboard Markers Set',
                'unit' => 'set',
                'cost_price' => 7.50,
                'minimum_profit_margin' => 40.00,
                'standard_profit_margin' => 73.20,
                'quantity_on_hand' => 3,
                'reorder_level' => 10,
            ],
            [
                'store_id' => $demoStore->id,
                'sku' => 'LOW-STOCK-002',
                'name' => 'Desk Organizer',
                'unit' => 'piece',
                'cost_price' => 15.00,
                'minimum_profit_margin' => 35.00,
                'standard_profit_margin' => 66.60,
                'quantity_on_hand' => 2,
                'reorder_level' => 8,
            ],
            [
                'store_id' => $demoStore->id,
                'sku' => 'LOW-STOCK-003',
                'name' => 'Wireless Presenter Remote',
                'unit' => 'piece',
                'cost_price' => 22.00,
                'minimum_profit_margin' => 35.00,
                'standard_profit_margin' => 59.05,
                'quantity_on_hand' => 1,
                'reorder_level' => 5,
            ],
        ];

        foreach ($products as $productData) {
            $product = Product::firstOrCreate(
                ['sku' => $productData['sku'], 'store_id' => $demoStore->id],
                $productData
            );

            // Calculate and save profit margin
            $product->calculateProfitMargin();
        }

        $this->command->info('Products seeded successfully! (' . count($products) . ' products created)');
        $this->command->info('Note: 3 products are marked as low stock for testing reorder alerts.');
    }
}
