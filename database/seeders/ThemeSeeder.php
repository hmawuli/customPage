<?php

namespace Database\Seeders;

use App\Models\Theme;
use Illuminate\Database\Seeder;

class ThemeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $themes = [
            [
                'name' => 'Classic',
                'slug' => 'classic',
                'description' => 'Timeless black, white and gray color scheme',
                'primary_color' => '#000000',
                'secondary_color' => '#FFFFFF',
                'accent_color' => '#808080',
                'text_color' => '#000000',
                'background_color' => '#FFFFFF',
                'is_active' => true,
            ],
            [
                'name' => 'Ocean',
                'slug' => 'ocean',
                'description' => 'Cool and calm blue tones inspired by the sea',
                'primary_color' => '#006994',
                'secondary_color' => '#00A8CC',
                'accent_color' => '#00D4FF',
                'text_color' => '#003D5C',
                'background_color' => '#F0F8FF',
                'is_active' => true,
            ],
            [
                'name' => 'Sunset',
                'slug' => 'sunset',
                'description' => 'Warm orange, red and yellow sunset colors',
                'primary_color' => '#FF6B35',
                'secondary_color' => '#F7931E',
                'accent_color' => '#FDC830',
                'text_color' => '#2C1810',
                'background_color' => '#FFF8F0',
                'is_active' => true,
            ],
            [
                'name' => 'Forest',
                'slug' => 'forest',
                'description' => 'Natural green and brown earth tones',
                'primary_color' => '#2D5016',
                'secondary_color' => '#6B8E23',
                'accent_color' => '#8FBC8F',
                'text_color' => '#1C3010',
                'background_color' => '#F5F5DC',
                'is_active' => true,
            ],
            [
                'name' => 'Royal',
                'slug' => 'royal',
                'description' => 'Elegant purple and gold combination',
                'primary_color' => '#6A0DAD',
                'secondary_color' => '#9B59B6',
                'accent_color' => '#FFD700',
                'text_color' => '#4A0072',
                'background_color' => '#FAF0FF',
                'is_active' => true,
            ],
        ];

        foreach ($themes as $theme) {
            Theme::create($theme);
        }

        $this->command->info('5 themes created successfully!');
    }
}
