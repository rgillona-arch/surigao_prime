<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $adminName = (string) env('DEFAULT_ADMIN_NAME', 'Administrator');
        $adminEmail = (string) env('DEFAULT_ADMIN_EMAIL', 'admin@example.com');
        $adminPassword = (string) env('DEFAULT_ADMIN_PASSWORD', 'admin12345');

        $customerName = (string) env('DEFAULT_CUSTOMER_NAME', 'Customer');
        $customerEmail = (string) env('DEFAULT_CUSTOMER_EMAIL', 'customer@example.com');
        $customerPassword = (string) env('DEFAULT_CUSTOMER_PASSWORD', 'customer12345');

        User::updateOrCreate(
            ['email' => $adminEmail],
            [
                'name' => $adminName,
                'password' => $adminPassword,
                'role' => 'admin',
            ]
        );

        User::updateOrCreate(
            ['email' => $customerEmail],
            [
                'name' => $customerName,
                'password' => $customerPassword,
                'role' => 'customer',
            ]
        );
    }
}
