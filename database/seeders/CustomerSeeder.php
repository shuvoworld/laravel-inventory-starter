<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Customers\Models\Customer;
use App\Modules\Stores\Models\Store;

class CustomerSeeder extends Seeder
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

        $customers = [
            [
                'store_id' => $demoStore->id,
                'name' => 'Acme Corporation',
                'email' => 'contact@acmecorp.com',
                'phone' => '+1-555-0101',
                'address' => '123 Business Street',
                'city' => 'New York',
                'state' => 'NY',
                'postal_code' => '10001',
                'country' => 'USA',
            ],
            [
                'store_id' => $demoStore->id,
                'name' => 'Tech Solutions Inc',
                'email' => 'info@techsolutions.com',
                'phone' => '+1-555-0102',
                'address' => '456 Innovation Avenue',
                'city' => 'San Francisco',
                'state' => 'CA',
                'postal_code' => '94102',
                'country' => 'USA',
            ],
            [
                'store_id' => $demoStore->id,
                'name' => 'Global Trading Ltd',
                'email' => 'sales@globaltrading.com',
                'phone' => '+1-555-0103',
                'address' => '789 Commerce Drive',
                'city' => 'Chicago',
                'state' => 'IL',
                'postal_code' => '60601',
                'country' => 'USA',
            ],
            [
                'store_id' => $demoStore->id,
                'name' => 'Metro Retail Group',
                'email' => 'purchasing@metroretail.com',
                'phone' => '+1-555-0104',
                'address' => '321 Market Plaza',
                'city' => 'Boston',
                'state' => 'MA',
                'postal_code' => '02101',
                'country' => 'USA',
            ],
            [
                'store_id' => $demoStore->id,
                'name' => 'Sunrise Electronics',
                'email' => 'orders@sunriseelectronics.com',
                'phone' => '+1-555-0105',
                'address' => '555 Tech Park Boulevard',
                'city' => 'Austin',
                'state' => 'TX',
                'postal_code' => '78701',
                'country' => 'USA',
            ],
            [
                'store_id' => $demoStore->id,
                'name' => 'Pacific Distributors',
                'email' => 'info@pacificdist.com',
                'phone' => '+1-555-0106',
                'address' => '888 Harbor Way',
                'city' => 'Seattle',
                'state' => 'WA',
                'postal_code' => '98101',
                'country' => 'USA',
            ],
            [
                'store_id' => $demoStore->id,
                'name' => 'Mountain View Supplies',
                'email' => 'contact@mountainviewsupplies.com',
                'phone' => '+1-555-0107',
                'address' => '777 Summit Road',
                'city' => 'Denver',
                'state' => 'CO',
                'postal_code' => '80201',
                'country' => 'USA',
            ],
            [
                'store_id' => $demoStore->id,
                'name' => 'Coastal Enterprises',
                'email' => 'sales@coastalent.com',
                'phone' => '+1-555-0108',
                'address' => '999 Ocean Drive',
                'city' => 'Miami',
                'state' => 'FL',
                'postal_code' => '33101',
                'country' => 'USA',
            ],
            [
                'store_id' => $demoStore->id,
                'name' => 'Central Office Supplies',
                'email' => 'orders@centraloffice.com',
                'phone' => '+1-555-0109',
                'address' => '246 Main Street',
                'city' => 'Atlanta',
                'state' => 'GA',
                'postal_code' => '30301',
                'country' => 'USA',
            ],
            [
                'store_id' => $demoStore->id,
                'name' => 'Northwest Equipment Co',
                'email' => 'info@nwequipment.com',
                'phone' => '+1-555-0110',
                'address' => '135 Industry Lane',
                'city' => 'Portland',
                'state' => 'OR',
                'postal_code' => '97201',
                'country' => 'USA',
            ],
            [
                'store_id' => $demoStore->id,
                'name' => 'John Smith',
                'email' => 'john.smith@email.com',
                'phone' => '+1-555-0201',
                'address' => '12 Maple Street',
                'city' => 'Phoenix',
                'state' => 'AZ',
                'postal_code' => '85001',
                'country' => 'USA',
            ],
            [
                'store_id' => $demoStore->id,
                'name' => 'Sarah Johnson',
                'email' => 'sarah.j@email.com',
                'phone' => '+1-555-0202',
                'address' => '45 Oak Avenue',
                'city' => 'Philadelphia',
                'state' => 'PA',
                'postal_code' => '19101',
                'country' => 'USA',
            ],
            [
                'store_id' => $demoStore->id,
                'name' => 'Michael Brown',
                'email' => 'mbrown@email.com',
                'phone' => '+1-555-0203',
                'address' => '78 Pine Road',
                'city' => 'San Diego',
                'state' => 'CA',
                'postal_code' => '92101',
                'country' => 'USA',
            ],
            [
                'store_id' => $demoStore->id,
                'name' => 'Emily Davis',
                'email' => 'emily.davis@email.com',
                'phone' => '+1-555-0204',
                'address' => '90 Cedar Lane',
                'city' => 'Dallas',
                'state' => 'TX',
                'postal_code' => '75201',
                'country' => 'USA',
            ],
            [
                'store_id' => $demoStore->id,
                'name' => 'Robert Wilson',
                'email' => 'rwilson@email.com',
                'phone' => '+1-555-0205',
                'address' => '23 Birch Court',
                'city' => 'Houston',
                'state' => 'TX',
                'postal_code' => '77001',
                'country' => 'USA',
            ],
        ];

        foreach ($customers as $customer) {
            Customer::firstOrCreate(
                ['email' => $customer['email'], 'store_id' => $demoStore->id],
                $customer
            );
        }

        $this->command->info('Customers seeded successfully! (' . count($customers) . ' customers created)');
    }
}
