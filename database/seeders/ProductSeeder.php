<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Dimension;
use App\Models\MasterCategory;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();

        // Pastikan ada kategori
        $indoor = MasterCategory::firstOrCreate(['name' => 'Indoor']);
        $outdoor = MasterCategory::firstOrCreate(['name' => 'Outdoor']);

        // Pastikan ada dimensi
        $dimensions = [
            Dimension::firstOrCreate(['width' => 40, 'height' => 80, 'depth' => 40]),
            Dimension::firstOrCreate(['width' => 60, 'height' => 75, 'depth' => 60]),
            Dimension::firstOrCreate(['width' => 120, 'height' => 75, 'depth' => 80]),
            Dimension::firstOrCreate(['width' => 80, 'height' => 45, 'depth' => 50]),
            Dimension::firstOrCreate(['width' => 200, 'height' => 90, 'depth' => 100]),
        ];

        $materials = ['Teak Wood', 'Oak Wood', 'Mahogany Wood', 'Pine Wood', 'Walnut Wood'];

        // INDOOR PRODUCTS (25 items)
        $indoorProducts = [
            'Teak Dining Chair',
            'Modern Coffee Table',
            'Classic Bookshelf',
            'Minimalist Office Desk',
            'Elegant TV Console',
            'Comfortable Armchair',
            'Wooden Side Table',
            'Premium Wardrobe',
            'Sleek Console Table',
            'Rustic Dining Table',
            'Contemporary Sofa Set',
            'Storage Cabinet',
            'Reading Chair',
            'Writing Desk',
            'Display Shelf',
            'Nightstand Table',
            'Study Desk',
            'Media Console',
            'Accent Chair',
            'Bar Cabinet',
            'Shoe Rack',
            'Entryway Bench',
            'Vanity Table',
            'Corner Shelf',
            'Magazine Rack',
        ];

        // OUTDOOR PRODUCTS (25 items)
        $outdoorProducts = [
            'Garden Lounge Chair',
            'Patio Dining Set',
            'Outdoor Bench',
            'Sun Lounger',
            'Garden Swing',
            'Outdoor Sofa',
            'Poolside Table',
            'Terrace Chair',
            'Deck Chair',
            'Outdoor Bar Stool',
            'Garden Table',
            'Picnic Table',
            'Outdoor Rocking Chair',
            'Lawn Chair',
            'Balcony Table Set',
            'Outdoor Ottoman',
            'Garden Armchair',
            'Porch Swing',
            'Outdoor Daybed',
            'Teak Adirondack Chair',
            'Garden Planter Box',
            'Outdoor Storage Bench',
            'Patio Umbrella Stand',
            'Garden Serving Cart',
            'Outdoor Dining Chair',
        ];

        foreach ($indoorProducts as $index => $name) {
            Product::create([
                'name' => $name,
                'description' => "Premium quality {$name} crafted with finest materials. Perfect for modern interiors. Handmade by skilled artisans with attention to detail.",
                'material' => $materials[$index % count($materials)],
                'is_featured' => $index < 5,
                'master_category_id' => $indoor->id,
                'dimension_id' => $dimensions[$index % count($dimensions)]->id,
                'create_by' => $user?->id,
            ]);
        }

        foreach ($outdoorProducts as $index => $name) {
            Product::create([
                'name' => $name,
                'description' => "Durable outdoor {$name} built to withstand weather conditions. Made from premium teak wood with natural finishing.",
                'material' => 'Teak Wood',
                'is_featured' => $index < 3,
                'master_category_id' => $outdoor->id,
                'dimension_id' => $dimensions[$index % count($dimensions)]->id,
                'create_by' => $user?->id,
            ]);
        }
    }
}
