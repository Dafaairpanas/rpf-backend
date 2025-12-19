<?php

namespace Database\Seeders;

use App\Models\Banner;
use Illuminate\Database\Seeder;

class BannerSeeder extends Seeder
{
    public function run(): void
    {
        $banners = [
            [
                'title' => 'New Collection 2024',
                'image_path' => 'banners/banner-1.jpg',
                'link' => '/collections/2024',
                'order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Premium Furniture',
                'image_path' => 'banners/banner-2.jpg',
                'link' => '/products?featured=true',
                'order' => 2,
                'is_active' => true,
            ],
            [
                'title' => 'Sustainable Materials',
                'image_path' => 'banners/banner-3.jpg',
                'link' => '/about/sustainability',
                'order' => 3,
                'is_active' => true,
            ],
        ];

        foreach ($banners as $banner) {
            Banner::updateOrCreate(
                ['title' => $banner['title']],
                $banner
            );
        }

        $this->command->info('Banners seeded successfully!');
    }
}
