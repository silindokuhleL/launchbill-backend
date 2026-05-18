<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Seed demo customers for the billing catalog.
     */
    public function run(): void
    {
        $account = Account::where('name', 'Acme LaunchBill Demo')->first();

        if (! $account) {
            return;
        }

        collect($this->customers())->each(function (array $customer) use ($account): void {
            Customer::updateOrCreate(
                [
                    'account_id' => $account->id,
                    'email' => $customer['email'],
                ],
                [
                    ...$customer,
                    'account_id' => $account->id,
                ],
            );
        });
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function customers(): array
    {
        return [
            [
                'name' => 'Naledi Mokoena',
                'email' => 'naledi@northstar.example',
                'company_name' => 'Northstar Analytics',
                'phone' => '+27 82 100 0101',
                'provider_customer_id' => 'demo_cus_northstar',
                'status' => 'active',
                'billing_address' => [
                    'line1' => '10 Loop Street',
                    'city' => 'Cape Town',
                    'region' => 'Western Cape',
                    'postal_code' => '8001',
                    'country' => 'ZA',
                ],
                'notes' => 'Interested in automated subscription reminders.',
            ],
            [
                'name' => 'Thabo Dlamini',
                'email' => 'thabo@greenledger.example',
                'company_name' => 'GreenLedger Finance',
                'phone' => '+27 71 555 0190',
                'provider_customer_id' => 'demo_cus_greenledger',
                'status' => 'active',
                'billing_address' => [
                    'line1' => '42 Jan Smuts Avenue',
                    'city' => 'Johannesburg',
                    'region' => 'Gauteng',
                    'postal_code' => '2196',
                    'country' => 'ZA',
                ],
                'notes' => 'Needs invoices grouped by department.',
            ],
            [
                'name' => 'Aisha Khan',
                'email' => 'aisha@brightops.example',
                'company_name' => 'BrightOps Studio',
                'phone' => '+27 63 230 1111',
                'provider_customer_id' => 'demo_cus_brightops',
                'status' => 'inactive',
                'billing_address' => [
                    'line1' => '7 Florida Road',
                    'city' => 'Durban',
                    'region' => 'KwaZulu-Natal',
                    'postal_code' => '4001',
                    'country' => 'ZA',
                ],
                'notes' => 'Paused while reviewing plan options.',
            ],
        ];
    }
}
