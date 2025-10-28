<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\Client;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clients = Client::where('role', 'client')->get();

        foreach ($clients as $client) {
            // Create home page
            Page::create([
                'client_id' => $client->id,
                'title' => 'Home',
                'slug' => 'home',
                'meta_description' => 'Welcome to ' . $client->company_name,
                'meta_keywords' => ['home', 'welcome', $client->company_name],
                'content' => [
                    'hero_title' => 'Welcome to ' . $client->company_name,
                    'hero_subtitle' => 'Your trusted partner for excellence',
                    'section_1_title' => 'About Us',
                    'section_1_content' => 'We are dedicated to providing the best services to our clients.',
                    'section_2_title' => 'Our Services',
                    'section_2_content' => 'Discover what we can do for you.',
                ],
                'images' => [],
                'theme_id' => $client->theme_id,
                'status' => 'published',
                'published_at' => now(),
            ]);

            // Create about page
            Page::create([
                'client_id' => $client->id,
                'title' => 'About Us',
                'slug' => 'about',
                'meta_description' => 'Learn more about ' . $client->company_name,
                'meta_keywords' => ['about', 'company', $client->company_name],
                'content' => [
                    'page_title' => 'About ' . $client->company_name,
                    'intro' => 'Our story and mission',
                    'mission' => 'To deliver exceptional value to our customers',
                    'vision' => 'To be the leading provider in our industry',
                ],
                'images' => [],
                'theme_id' => $client->theme_id,
                'status' => 'published',
                'published_at' => now(),
            ]);

            // Create contact page
            Page::create([
                'client_id' => $client->id,
                'title' => 'Contact Us',
                'slug' => 'contact',
                'meta_description' => 'Get in touch with ' . $client->company_name,
                'meta_keywords' => ['contact', 'email', 'phone'],
                'content' => [
                    'page_title' => 'Contact Us',
                    'intro' => 'We would love to hear from you',
                    'email' => $client->email,
                    'phone' => $client->phone,
                    'address' => '123 Business Street, City, Country',
                ],
                'images' => [],
                'theme_id' => $client->theme_id,
                'status' => 'published',
                'published_at' => now(),
            ]);
        }

        $this->command->info('Sample pages created for all clients!');
    }
}
