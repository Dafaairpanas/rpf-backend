<?php

namespace Database\Seeders;

use App\Models\Dimension;
use App\Models\MasterCategory;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        $dimension = Dimension::first();

        $indoor = MasterCategory::where('name', 'Indoor')->first();
        $outdoor = MasterCategory::where('name', 'Outdoor')->first();

        $products = [
            // INDOOR (10)
            ['Teak Wood Chair', 'Premium solid teak chair', 'Teak Wood', $indoor],
            ['Minimalist Table', 'Modern minimalist table', 'Oak Wood', $indoor],
            ['Indoor Bookshelf', 'Wooden bookshelf', 'Pine Wood', $indoor],
            ['Dining Chair', 'Comfort dining chair', 'Teak Wood', $indoor],
            ['Coffee Table', 'Small coffee table', 'Oak Wood', $indoor],
            ['Wardrobe Cabinet', 'Spacious wardrobe', 'Mahogany Wood', $indoor],
            ['TV Console', 'Modern TV console', 'Teak Wood', $indoor],
            ['Side Table', 'Compact side table', 'Oak Wood', $indoor],
            ['Office Desk', 'Minimal office desk', 'Plywood', $indoor],
            ['Indoor Bench', 'Simple indoor bench', 'Teak Wood', $indoor],

            // OUTDOOR (10)
            ['Outdoor Lounge Chair', 'Outdoor relaxing chair', 'Teak Wood', $outdoor],
            ['Garden Bench', 'Garden wooden bench', 'Teak Wood', $outdoor],
            ['Outdoor Dining Table', 'Outdoor dining table', 'Teak Wood', $outdoor],
            ['Sun Lounger', 'Poolside sun lounger', 'Teak Wood', $outdoor],
            ['Outdoor Sofa', 'Outdoor wooden sofa', 'Teak Wood', $outdoor],
            ['Patio Chair', 'Patio seating chair', 'Teak Wood', $outdoor],
            ['Outdoor Coffee Table', 'Outdoor coffee table', 'Teak Wood', $outdoor],
            ['Garden Table', 'Garden round table', 'Teak Wood', $outdoor],
            ['Outdoor Bar Stool', 'Outdoor bar stool', 'Teak Wood', $outdoor],
            ['Terrace Bench', 'Terrace bench seating', 'Teak Wood', $outdoor],
        ];

        foreach ($products as [$name, $desc, $material, $category]) {
            Product::create([
                'name' => $name,
                'description' => $desc,
                'material' => $material,
                'master_category_id' => $category?->id,
                'dimension_id' => $dimension?->id,
                'create_by' => $user?->id,
            ]);
        }
    }
}
