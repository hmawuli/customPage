<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        Client::create([
            'name' => 'Admin User',
            'email' => 'admin@cms.com',
            'password' => Hash::make('password'),
            'slug' => 'admin',
            'company_name' => 'CMS Admin',
            'phone' => '+1234567890',
            'theme_id' => 1,
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Create test clients
        $clients = [
            [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => Hash::make('password'),
                'company_name' => 'Doe Enterprises',
                'phone' => '+1234567891',
                'theme_id' => 1,
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane@example.com',
                'password' => Hash::make('password'),
                'company_name' => 'Smith Solutions',
                'phone' => '+1234567892',
                'theme_id' => 2,
            ],
            [
                'name' => 'Bob Wilson',
                'email' => 'bob@example.com',
                'password' => Hash::make('password'),
                'company_name' => 'Wilson Works',
                'phone' => '+1234567893',
                'theme_id' => 3,
            ],
        ];

        foreach ($clients as $client) {
            Client::create($client);
        }

        $this->command->info('Test clients created successfully!');
    }
}
